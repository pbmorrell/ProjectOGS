<?php
include_once 'classes/DataAccess.class.php';
include_once 'classes/SecurityHandler.class.php';
include_once 'classes/Logger.class.php';
include_once 'classes/Constants.class.php';
include_once 'classes/PayPalTxnMsg.class.php';

class PayPalMsgHandler
{        
    public function SendPDTPostRequest($tx, $url, &$status, $identityToken)
    {
        $status = 200;
        $request = curl_init();
        curl_setopt_array($request, array
            (
                CURLOPT_URL => $url,
                CURLOPT_POST => TRUE,
                CURLOPT_POSTFIELDS => http_build_query(array
                    (
                        'cmd' => '_notify-synch',
                        'tx' => $tx,
                        'at' => $identityToken
                    )
                ),
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HEADER => FALSE,
//                CURLOPT_SSL_VERIFYPEER => TRUE,
//                CURLOPT_CAINFO => 'cacert.pem'
            )
        );
        
        $response = curl_exec($request);
        $status = curl_getinfo($request, CURLINFO_HTTP_CODE);
        curl_close($request);
        return $response;
    }
	
    public function FormatPDTResponseInAssociativeArray($pdtMessage)
    {        
        // Remove SUCCESS from msg and URL-decode
        $pdtMessage = urldecode(substr($pdtMessage, 7));
        
        // Convert text response to associative array
        $out = [];
        preg_match_all('/^([^=\s]++)=(.*+)/m', $pdtMessage, $out, PREG_PATTERN_ORDER);
        $msgInAssocArray = array_combine($out[1], $out[2]);
        ksort($msgInAssocArray);
	return $msgInAssocArray;
    }

    public function ConvertIPNPostDataToRawString($ipnPostData)
    {
        $rawString = "";
	foreach($ipnPostData as $key => $value) {
            $rawString .= ($key . "=>" . $value . "\r\n");
        }
        return $rawString;
    }
    
    public function HandlePayPalTxnResponse($msgInAssocArray, $rawPdtMessage, $dataAccess, $logger, $notificationType)
    {
	// PDT and IPN transaction detail messages essentially have the same structure and may be processed in similar ways.
	//  The main difference between them is that PDT messages are triggered by a button click on the website, and therefore 
	//  require a response to be presented to the user.
        // In addition to listening for IPN responses from Subscription Purchase, Subscription Renewal, and Subscription 
        //  Cancellation requests initiated from a button on this website (in case PDT immediate response is not received) 
        //  also need to handle notifications of canceled/failed subcription. Such notifications may occur at random times, 
        //  such as when the user's payment method for a recurring subscription is declined, or if user cancels their subscription 
        //  directly from PayPal instead of by clicking "Cancel" from their Player Unite page.
        
        $action = "";
        $userId = -1;
        
        if(strlen($this->GetResponseValByKey($msgInAssocArray, "custom")) > 0) {
            $customVars = explode('|', $msgInAssocArray["custom"]);
            $userId = (int)$customVars[0];
            $action = $customVars[1];
        }
        
        $replyMsg = $this->ValidatePayPalResponse($msgInAssocArray, $action);
	$replyMsg->PDTOperation = $action;
        $replyMsg->NotificationType = $notificationType;
		
	// If this notification is intended for another business, log error and take no further actions
	if(!$replyMsg->IsValidBusiness) {
            $logger->LogInfo(sprintf("[Notification Type: %s]  Transaction [%s] for payer [%s]: this message not for this business. Recipient business: [%s]",
                             $notificationType, $replyMsg->TxnId, $replyMsg->PayerId, $this->GetResponseValByKey($msgInAssocArray, "business")));
            $replyMsg->UserMessage = "ERROR: Invalid payment recipient specified. Transaction unsuccessful.";
	}
	else {
            // Whether or not subscription has changed, archive this transaction message in PayPalTransactions table
            $txnRecordAdded = $this->InsertMsgIntoTxnLog($replyMsg, $rawPdtMessage, $dataAccess, $logger, $notificationType);
            
            // Only proceed if DB update returns > 0 records affected (unique index prevents insert of records with identical txn ID, txn type, and payment status)
            if($txnRecordAdded) {
                // Look up user by the payer ID passed in the message, so that we can update this user's membership status as needed
                $replyMsg->UserId = $this->LookUpPayPalUserByPayerId($replyMsg->PayerId, $dataAccess, $logger, $notificationType);
                
                // If we have a user ID in this message, insert new record in Payments.PayPalUsers table to map this 
                // user's PayPal payer_id to their user ID in our system, unless this user already in table
                if(($replyMsg->UserId < 0) && ($userId > -1)) {
                    $replyMsg->UserId = $userId;
                    $this->InsertPayPalUser($replyMsg, $dataAccess, $logger, $notificationType);
                }
                
                // If payment was completed and payment was made in full, proceed to update user information related to membership
                if($replyMsg->IsValidated) {
                    if($replyMsg->UserId > 0) {
                        // Always update user PayPal account info when a validated notification is received
                        $this->UpdateUserPayPalAccountInformation($replyMsg, $dataAccess, $logger, $notificationType);

                        // Only update user membership status when we receive a Completed payment or a membership expiration notice
                        if($replyMsg->UpdateUserMembershipStatus) {
                            $this->UpdateUserMembershipStatus($replyMsg, $dataAccess, $logger, $notificationType);
                        }
                    }
                    else {
                        $logger->LogError(sprintf("[Notification Type: %s]  Could not update membership status for Transaction [%s], payer [%s], payment status = [%s]: no user ID found for this payer.",
                                                 $notificationType, $replyMsg->TxnId, $replyMsg->PayerId, $replyMsg->PaymentStatus));
                        $replyMsg->UserMessage .= "ERROR: could not update membership status, because your user ID could not be found. Please contact PlayerUnite for assistance.";
                    }
                }
            }
			
            if((!$replyMsg->IsValidated) && ($replyMsg->PaymentStatus != 'Completed') && 
               (($replyMsg->TxnType == "subscr_signup") || ($replyMsg->TxnType == "subscr_modify") || ($replyMsg->TxnType == "subscr_payment"))) {
                $subscrAction = ($replyMsg->TxnType == "subscr_signup") ? 'upgrade' : (($replyMsg->TxnType == "subscr_modify") ? 'change' : 'renew');
                $logger->LogInfo(sprintf("[Notification Type: %s]  Transaction [%s] for payer [%s]: payment status = [%s]. Subscription payment not completed.",
                                         $notificationType, $replyMsg->TxnId, $replyMsg->PayerId, $replyMsg->PaymentStatus));

                if(($replyMsg->PaymentStatus == "Processed") || ($replyMsg->PaymentStatus == "Pending")) {
                    $replyMsg->UserMessage = sprintf("Payment still processing. Check back soon: we will %s your membership status as soon as your payment has cleared!",
                                                    $subscrAction);
                }
                else {
                    $replyMsg->UserMessage = sprintf("Payment could not be processed. Please try again, so that we can %s your membership status!", $subscrAction);
                }
	   }
        }
        
        return $replyMsg;
    }
    
    public function SendIPNPostRequest($ipnData, $url, &$status)
    {
        $status = 200;
        $request = curl_init();
        curl_setopt_array($request, array
            (
                CURLOPT_URL => $url,
                CURLOPT_POST => TRUE,
                CURLOPT_POSTFIELDS => http_build_query(array('cmd' => '_notify-validate') + $ipnData),
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HEADER => FALSE,
//                CURLOPT_SSL_VERIFYPEER => TRUE,
//                CURLOPT_CAINFO => 'cacert.pem'
            )
        );
        
        $response = curl_exec($request);
        $status = curl_getinfo($request, CURLINFO_HTTP_CODE);
        curl_close($request);
        return $response;
    }
    
    private function ValidatePayPalResponse($msgInAssocArray, $action)
    {
        // Initialize response object
        $replyMsg = new PayPalTxnMsg($this->GetResponseValByKey($msgInAssocArray, "txn_id"),
                                     $this->GetResponseValByKey($msgInAssocArray, "txn_type"), 
                                     $this->GetResponseValByKey($msgInAssocArray, "payer_id"), 
                                     -1, "", $this->GetResponseValByKey($msgInAssocArray, "payment_status"),
                                     $this->GetResponseValByKey($msgInAssocArray, "option_selection1"), "0", "0", "", "", gmdate('Y-m-d H:i:s'));
		
	// If payment status is "Pending", store the pending reason
	if(($replyMsg->PaymentStatus == "Pending")) {
            $replyMsg->PaymentPendingReason = $this->GetResponseValByKey($msgInAssocArray, "pending_reason");
	}
        
        // Verify that the payment recipient is this website
        $thisPayPalMerchantId = Constants::$isPayPalTest ? Constants::$payPalTestMerchantId : Constants::$payPalProdMerchantId;
        if(!(strtolower($thisPayPalMerchantId) == $this->GetResponseValByKey($msgInAssocArray, "business"))) {
            $replyMsg->IsValidBusiness = false;
        }
	else if($replyMsg->TxnType == "subscr_signup") {
            $replyMsg->SubscriptionModifyDate = $this->ConvertPayPalTimestampToDBDateTime($this->GetResponseValByKey($msgInAssocArray, "subscr_date"));
            $replyMsg->SubscriptionIsRecurring = ($this->GetResponseValByKey($msgInAssocArray, "recurring") == "1");
            $replyMsg->IsValidated = true; // Consider this a validated (requires additional handler action) message, since this is not a txn type directly involving a payment
	}
	else if($replyMsg->TxnType == "subscr_modify") {
            $replyMsg->SubscriptionModifyDate = $this->ConvertPayPalTimestampToDBDateTime($this->GetResponseValByKey($msgInAssocArray, "subscr_effective"));
            $replyMsg->SubscriptionIsRecurring = ($this->GetResponseValByKey($msgInAssocArray, "recurring") == "1");
            $replyMsg->IsValidated = true; // Consider this a validated (requires additional handler action) message, since this is not a txn type directly involving a payment
	}
        // Verify that the payment amount is what we expect if this is a subscription start, upgrade/renewal, or recurring payment
        else if($replyMsg->TxnType == "subscr_payment")
        {
            // If the payment status is not "Completed" for this transaction type, this update will only be logged, but will not trigger updates to the user account
            $isCompletedPaymentStatus = ($replyMsg->PaymentStatus == "Completed");
			
            // Don't need to perform payment amount check until and unless payment status is Completed (funds added to business acct)
            if($isCompletedPaymentStatus) {
		$replyMsg->SubscriptionAmtPaid = $this->GetResponseValByKey($msgInAssocArray, "payment_gross");
                if((strlen($replyMsg->SelectedSubscriptionOption) > 0) && (array_key_exists($replyMsg->SelectedSubscriptionOption, Constants::$subscriptionOptionNames))) {
                    $expectedPaymentAmt = Constants::$subscriptionOptionNames[$replyMsg->SelectedSubscriptionOption];
                    $correctCurrencyAmt = ($replyMsg->SubscriptionAmtPaid == $expectedPaymentAmt);
                    $replyMsg->SubscriptionAmtTotal = $expectedPaymentAmt;
                    
                    if($correctCurrencyAmt) {
                        $replyMsg->IsValidated = true;
			$replyMsg->UpdateUserMembershipStatus = true;
						
			if($action == "SubscribePremium") {                            
                            // Upgrade user subscription status in DB...now a premium member
                            $replyMsg->UserMessage = "Subscription successfully created!";
                            $replyMsg->UserUpgradedPremium = true;
			}
                        else if($action == "RenewSubscription") {
                            // If renewal/upgrade/payment was successful, update user subscription status in DB (expiration date, period, etc.)
                            $replyMsg->UserMessage = "Subscription successfully updated!";
                            $replyMsg->UserSubscriptionRenewed = true;
                        }
			else {
                            // If no PDT operation included in this notification's custom variable, must assume that this is an automatic recurring payment IPN, and that this is a renewal
                            $replyMsg->UserMessage = "Subscription successfully renewed!";
                            $replyMsg->UserSubscriptionRenewed = true;
			}
                    }
                }
            }
        }
	else if($replyMsg->TxnType == "subscr_cancel") {
            $replyMsg->SubscriptionModifyDate = $this->ConvertPayPalTimestampToDBDateTime($this->GetResponseValByKey($msgInAssocArray, "subscr_date"));
            $replyMsg->IsValidated = true; // Consider this a validated (requires additional handler action) message, since this is not a txn type that requires payment
            
            // If an active subscription was cancelled, alter user membership expiration date but leave as premium for now
            $replyMsg->UserMessage = "Subscription successfully cancelled, and will expire at the end of your current term. We're sorry to see you go!";
            $replyMsg->UserSubscriptionCancelledPending = true;
	}
	else if($replyMsg->TxnType == "subscr_eot") {
            $replyMsg->SubscriptionModifyDate = gmdate('Y-m-d H:i:s'); // Current UTC date
            $replyMsg->IsValidated = true; // Consider this a validated (requires additional handler action) message, since this is not a txn type that requires payment
            $replyMsg->UpdateUserMembershipStatus = true; // Immediately change user to Basic member
            
            // If an active subscription is expired now, set user to basic member
            $replyMsg->UserMessage = "Your subscription has expired. Please join us again soon!";
            $replyMsg->UserSubscriptionCancelledImmediate = true;
	}
        
        return $replyMsg;
    }
    
    public function GetResponseValByKey($msgInAssocArray, $key)
    {
        $retVal = "";
        if(array_key_exists($key, $msgInAssocArray)) {
            $retVal = $msgInAssocArray[$key];
        }
        
        return $retVal;
    }
	
    // PayPal timestamp format -- HH:MM:SS DD Mmm YY, YYYY PST
    private function ConvertPayPalTimestampToDBDateTime($timestamp)
    {
	if(strlen($timestamp) == 0) {
            return "";
	}
		
	return gmdate('Y-m-d H:i:s', strtotime($timestamp));
    }
	
    private function InsertMsgIntoTxnLog($replyMsg, $msgData, $dataAccess, $logger, $notificationType)
    {
	$recordsAffected = 0;
		
        $insertTxnMsgQuery = "INSERT INTO `Payments.PayPalTransactions` (`TxnId`,`PayerId`, `TxnType`, `PDTOperation`, `PaymentStatus`, " .
                                                                         "`NotificationType`, `NotificationDate`, `PayPalMsgData`, `TransactionDate`) " .
                             "VALUES (:txnId, :payerId, :txnType, :pdtOp, :pymtStatus, :notType, :notDate, :msgData, :txnDate);";
	
        $txnID = $replyMsg->TxnId;
        if(strlen($txnID) == 0)  $txnID = $replyMsg->PayerId . "_" . $replyMsg->TxnType;
        
        $paymentStatus = $replyMsg->PaymentStatus;
        if(strlen($paymentStatus) == 0)  $paymentStatus = "N/A";
        
        $txnDate = $replyMsg->SubscriptionModifyDate;
        $txnDateParmType = PDO::PARAM_STR;
        if(strlen($txnDate) == 0) {
            if($replyMsg->IsValidated) {
                // If payment was completed and paid in full for this subscription, set txn date to null,
                // to prevent further duplicate notifications for this same transaction from being processed
                // unnecessarily
                $txnDate = null;
                $txnDateParmType = PDO::PARAM_INT;
            }
            else {                
                // If payment for this subscription only made partially, set txn date to current date to cause
                // unique index to count it as distinct from any further subscr_payment transactions
                // that later come in for this transaction, payer and transaction type, thus allowing
                // them to be processed by the PDT or IPN handler
                $txnDate = $replyMsg->NotificationDate;
            }
        }
        
	$parmTxnId = new QueryParameter(':txnId', $txnID, PDO::PARAM_STR);
	$parmUserId = new QueryParameter(':payerId', $replyMsg->PayerId, PDO::PARAM_STR);
	$parmTxnType = new QueryParameter(':txnType', $replyMsg->TxnType, PDO::PARAM_STR);
	$parmPdtOp = new QueryParameter(':pdtOp', $replyMsg->PDTOperation, PDO::PARAM_STR);
	$parmPymtStatus = new QueryParameter(':pymtStatus', $paymentStatus, PDO::PARAM_STR);
	$parmNotType = new QueryParameter(':notType', $replyMsg->NotificationType, PDO::PARAM_STR);
	$parmNotDate = new QueryParameter(':notDate', $replyMsg->NotificationDate, PDO::PARAM_STR);
	$parmMsgData = new QueryParameter(':msgData', $msgData, PDO::PARAM_STR);
        $parmTxnDate = new QueryParameter(':txnDate', $txnDate, $txnDateParmType);
			
	$queryParms = array($parmTxnId, $parmUserId, $parmTxnType, $parmPdtOp, $parmPymtStatus, $parmNotType, $parmNotDate, $parmMsgData, $parmTxnDate);

	if($dataAccess->BuildQuery($insertTxnMsgQuery, $queryParms)){
            $dataAccess->ExecuteNonQuery();
	}
			
	$errors = $dataAccess->CheckErrors();
	$txnAlreadyExists = (stripos($errors, "Integrity constraint violation: 1062") !== false);

	if((strlen($errors) > 0) && !$txnAlreadyExists) {
            $logger->LogError(sprintf("[Notification Type: %s]  Could not insert PayPal transaction msg [txnId=%s] for payer ID '%s'. %s", $notificationType, $replyMsg->TxnId, $replyMsg->PayerId, $errors));
	}
	else if(!$txnAlreadyExists) {
            $recordsAffected = $dataAccess->RowCount();
	}
	
	return ($recordsAffected > 0);
    }
    
    private function InsertPayPalUser($replyMsg, $dataAccess, $logger, $notificationType)
    {		
        $insertPayPalUserQuery = "INSERT INTO `Payments.PayPalUsers` (`FK_User_ID`,`PayerId`, `SubscriptionType`, `SubscriptionAmtTotal`, 
                                                                      `SubscriptionAmtPaidLastCycle`, `IsActive`) " .
                                 "VALUES (:userId, :payerId, :subscrType, :subscrAmtTotal, :subscrAmtPaidLastCycle, :isActive);";
		
	$parmUserId = new QueryParameter(':userId', $replyMsg->UserId, PDO::PARAM_INT);
	$parmPayerId = new QueryParameter(':payerId', $replyMsg->PayerId, PDO::PARAM_STR);
	$parmSubscrType = new QueryParameter(':subscrType', $replyMsg->SelectedSubscriptionOption, PDO::PARAM_STR);
	$parmSubscrAmtTotal = new QueryParameter(':subscrAmtTotal', $replyMsg->SubscriptionAmtTotal, PDO::PARAM_STR);
        $parmSubscrAmtPaidLastCycle = new QueryParameter(':subscrAmtPaidLastCycle', $replyMsg->SubscriptionAmtPaid, PDO::PARAM_STR);
	$parmIsActive = new QueryParameter(':isActive', 1, PDO::PARAM_INT);
		
	$queryParms = array($parmUserId, $parmPayerId, $parmSubscrType, $parmSubscrAmtTotal, $parmSubscrAmtPaidLastCycle, $parmIsActive);

	if($dataAccess->BuildQuery($insertPayPalUserQuery, $queryParms)){
            $dataAccess->ExecuteNonQuery();
	}
			
	$errors = $dataAccess->CheckErrors();

	if(strlen($errors) > 0) {
            $logger->LogError(sprintf("[Notification Type: %s]  Could not insert PayPal user '%d' [payer ID: %s; subscription type: %s; subscription amount paid: %s]. %s", 
                                        $notificationType, $replyMsg->UserId, $replyMsg->PayerId, $replyMsg->SelectedSubscriptionOption, $replyMsg->SubscriptionAmtPaid, $errors));
            return false;
	}
	
	return true;
    }
    
    private function LookUpPayPalUserByPayerId($payerId, $dataAccess, $logger, $notificationType)
    {
        $getUserIdQuery = "SELECT `FK_User_ID` FROM `Payments.PayPalUsers` " .
                          "WHERE `PayerId` = :payerId;";
        
        $parmPayerId = new QueryParameter(':payerId', $payerId, PDO::PARAM_STR);
        $queryParms = array($parmPayerId);
        $userID = -1;
        
        if($dataAccess->BuildQuery($getUserIdQuery, $queryParms)){
            $results = $dataAccess->GetSingleResult();

            if($results != null){
                $userID = $results['FK_User_ID'];
            }
        }
        
	$errors = $dataAccess->CheckErrors();
	if(strlen($errors) > 0) {
            $logger->LogError(sprintf("[Notification Type: %s]  Could not retrieve user ID associated with payer ID '%s'. %s", $notificationType, $payerId, $errors));
	}
        
        return $userID;
    }
    
    private function UpdateUserMembershipStatus($replyMsg, $dataAccess, $logger, $notificationType)
    {
	$updateSuccess = false;
        $transactionComplete = false;
        $isPremium = ($replyMsg->UserUpgradedPremium || $replyMsg->UserSubscriptionRenewed || $replyMsg->UserSubscriptionCancelledPending);
	$updateUserQuery = "UPDATE `Security.Users` SET `IsPremiumMember` = :isPremium WHERE `ID` = :userId;";
	
        $parmIsPremium = new QueryParameter(':isPremium', ($isPremium ? 1 : 0), PDO::PARAM_INT);
	$parmUserId = new QueryParameter(':userId', $replyMsg->UserId, PDO::PARAM_INT);
	$queryParms = array($parmIsPremium, $parmUserId);
        
        // Wrap Security.Users and Security.UserRoles updates in transaction:
        // if one fails, must roll back the successful update, if any.
        $dataAccess->BeginTransaction();
        $errors = $dataAccess->CheckErrors();

        if(strlen($errors) == 0) {
            // Update Security.Users
            if($dataAccess->BuildQuery($updateUserQuery, $queryParms)){
                $updateSuccess = $dataAccess->ExecuteNonQuery();
                $errors = $dataAccess->CheckErrors();
                
                if($updateSuccess && (strlen($errors) == 0)) {
                    $roleName = Constants::$basicMemberRoleName;
                    if($isPremium)  $roleName = Constants::$premiumMemberRoleName;
                    $updateUserQuery = "UPDATE `Security.UserRoles` ur, `Security.Roles` r " . 
                                       "SET ur.`FK_Role_ID` = r.`ID` " . 
                                       "WHERE (r.`Name` = :roleName) AND (ur.`FK_User_ID` = :userId);";
                    
                    $parmRoleName = new QueryParameter(':roleName', $roleName, PDO::PARAM_STR);
                    $queryParms = array($parmUserId, $parmRoleName);
                    
                    // Update Security.UserRoles
                    if($dataAccess->BuildQuery($updateUserQuery, $queryParms)){
                        $transactionComplete = $dataAccess->ExecuteNonQuery();
                        $errors = $dataAccess->CheckErrors();
                        
                        if($transactionComplete && (strlen($errors) == 0)) {
                            $dataAccess->CommitTransaction();
                            $logger->LogInfo(sprintf("[Notification Type: %s]  Updated user membership status to '%s' for user ID '%d'.", 
                                                     $notificationType, (($isPremium == 1) ? "Premium" : "Basic"), $replyMsg->UserId));
                        }
                    }
                }
            }
        }
		
	if($transactionComplete == false) {
            $logger->LogError(sprintf("[Notification Type: %s]  Could not update user membership status to '%s' for user ID '%d'. %s", 
                                      $notificationType, (($isPremium == 1) ? "Premium" : "Basic"), $replyMsg->UserId, $errors));
            $dataAccess->RollbackTransaction();
	}
			
	return $transactionComplete;
    }
	
    private function UpdateUserPayPalAccountInformation($replyMsg, $dataAccess, $logger, $notificationType)
    {
	$updateSuccess = false;
	$queryParms = [];
		
	$varsToSet = "SET `MembershipExpirationDate` = NULL";
	if($replyMsg->UserSubscriptionCancelledPending || $replyMsg->UserSubscriptionCancelledImmediate) {
            $expDate = $replyMsg->SubscriptionModifyDate;
            $varsToSet = "SET `MembershipExpirationDate` = :expDate";
            $parmMembershipExpDate = new QueryParameter(':expDate', $expDate, PDO::PARAM_STR);
            array_push($queryParms, $parmMembershipExpDate);
	}
		
	if(strlen($replyMsg->SelectedSubscriptionOption) > 0) {
            $varsToSet .= ", `SubscriptionType` = :subscrType";
            $parmSubscrType = new QueryParameter(':subscrType', $replyMsg->SelectedSubscriptionOption, PDO::PARAM_STR);
            array_push($queryParms, $parmSubscrType);
	}
		
	if(strlen($replyMsg->SubscriptionAmtTotal) > 0) {
            $varsToSet .= ", `SubscriptionAmtTotal` = :subscrAmtTotal";
            $parmSubscrAmtTotal = new QueryParameter(':subscrAmtTotal', $replyMsg->SubscriptionAmtTotal, PDO::PARAM_STR);
            array_push($queryParms, $parmSubscrAmtTotal);
	}
		
	if(strlen($replyMsg->SubscriptionAmtPaid) > 0) {
            $varsToSet .= ", `SubscriptionAmtPaidLastCycle` = :subscrAmtLastPaid";
            $parmSubscrAmtLastPaid = new QueryParameter(':subscrAmtLastPaid', $replyMsg->SubscriptionAmtPaid, PDO::PARAM_STR);
            array_push($queryParms, $parmSubscrAmtLastPaid);
	}
		
	if($replyMsg->TxnType == "subscr_signup") {
            $varsToSet .= ", `IsRecurring` = :isRecurring, `SubscriptionStartedDate` = :subscrStartDate";
            $parmIsRecurring = new QueryParameter(':isRecurring', ($replyMsg->SubscriptionIsRecurring ? 1 : 0), PDO::PARAM_INT);
            $parmSubscrStartDate = new QueryParameter(':subscrStartDate', $replyMsg->SubscriptionModifyDate, PDO::PARAM_STR);
            array_push($queryParms, $parmIsRecurring, $parmSubscrStartDate);
	}
		
	if($replyMsg->TxnType == "subscr_payment") {
            $lastBillDate = gmdate('Y-m-d H:i:s');
            $varsToSet .= ", `LastBillDate` = :lastBillDate";
            $parmLastBillDate = new QueryParameter(':lastBillDate', $lastBillDate, PDO::PARAM_STR);
            array_push($queryParms, $parmLastBillDate);
	}
		
	if(($replyMsg->TxnType == "subscr_signup") || ($replyMsg->TxnType == "subscr_modify") || 
	   ($replyMsg->TxnType == "subscr_cancel") || ($replyMsg->TxnType == "subscr_eot")) {
            $modifiedDate = gmdate('Y-m-d H:i:s');
            $varsToSet .= ", `SubscriptionModifiedDate` = :subscrModifyDate";
            $parmSubscrModifiedDate = new QueryParameter(':subscrModifyDate', $modifiedDate, PDO::PARAM_STR);
            array_push($queryParms, $parmSubscrModifiedDate);
	}
		
	$parmUserId = new QueryParameter(':userId', $replyMsg->UserId, PDO::PARAM_INT);
	array_push($queryParms, $parmUserId);
		
	$updateUserQuery = "UPDATE `Payments.PayPalUsers` " . $varsToSet .
			   " WHERE `FK_User_ID` = :userId;";
        
	// Update Payments.PayPalUsers
	if($dataAccess->BuildQuery($updateUserQuery, $queryParms)){
            $updateSuccess = $dataAccess->ExecuteNonQuery();
            $errors = $dataAccess->CheckErrors();
			
            if($updateSuccess && (strlen($errors) == 0)) {
		$logger->LogInfo(sprintf("[Notification Type: %s]  Updated user PayPal account information for user ID '%d'.", 
					  $notificationType, $replyMsg->UserId));
            }
	}
		
	return $updateSuccess;
    }
}
