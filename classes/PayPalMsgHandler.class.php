<?php
include_once 'classes/DataAccess.class.php';
include_once 'classes/SecurityHandler.class.php';
include_once 'classes/Logger.class.php';
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
            $rawString .= ($key . "=>" . $value . "|");
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
        $userId = "";
        
        if(strlen($this->GetResponseValByKey($msgInAssocArray, "custom")) > 0) {
            $customVars = explode('|', $msgInAssocArray["custom"]);
            $action = $customVars[0];
            $userId = $customVars[1];
        }
        
        $replyMsg = $this->ValidatePayPalResponse($msgInAssocArray, $userId);
	$replyMsg->PDTOperation = $action;
        $replyMsg->NotificationType = $notificationType;
		
	// If this notification is intended for another business, log error and take no further actions
	if(!$replyMsg->IsValidBusiness) {
            $logger->LogInfo(sprintf("Transaction [%s] for payer [%s]: this message not for this business. Recipient business: [%s]",
                             $replyMsg->TxnId, $replyMsg->PayerId, $this->GetResponseValByKey($msgInAssocArray, "business")));
            $replyMsg->UserMessage = "ERROR: Invalid payment recipient specified. Transaction unsuccessful.";
	}
	else {
            // Whether or not subscription has changed, archive this transaction message in PayPalTransactions table
            $txnRecordAdded = $this->InsertMsgIntoTxnLog($replyMsg, $rawPdtMessage, $dataAccess, $logger);
            
            // Only proceed if DB update returns > 0 records affected (unique index prevents insert of records with identical txn ID and payment status)
            if($txnRecordAdded) {
                // If we have a user ID in this message, insert new record in Payments.PayPalUsers table to map this 
                // user's PayPal payer_id to their user ID in our system
                if(strlen($userId) > 0) {
                    $insertedPayPalUser = $this->InsertPayPalUser($replyMsg, $dataAccess, $logger);
                }
                
                // If payment was completed and payment was made in full, proceed to update user status
                if($replyMsg->IsValidated) {
                    // Handle current PayPal message based on transaction type, if it's a type requiring further action (modification of user membership status or terms)
                    if($replyMsg->TxnType == "subscr_signup") {
                        // Upgrade user subscription status in DB...now a premium member
                        $replyMsg->UserMessage = "Subscription successfully created!";
                        $replyMsg->UserUpgradedPremium = true;
                    }
                    else if(($replyMsg->TxnType == "subscr_modify") || ($replyMsg->TxnType == "subscr_payment")) {
                        // If renewal/upgrade/payment was successful, update user subscription status in DB (expiration date, period, etc.)
                        $replyMsg->UserMessage = "Subscription successfully updated!";
                        $replyMsg->UserSubscriptionRenewed = true;
                    }
                    else if($replyMsg->TxnType == "subscr_cancel") {
                        // If an active subscription was cancelled, alter user membership expiration date but leave as premium for now
                        $replyMsg->UserMessage = "Subscription successfully cancelled, and will expire at the end of your current term. We're sorry to see you go!";
                        $replyMsg->UserSubscriptionCancelledPending = true;
                    }
                    else if($replyMsg->TxnType == "subscr_eot") {
                        // If an active subscription is expired now, set user to basic member
                        $replyMsg->UserMessage = "Your subscription has expired. Please join us again soon!";
                        $replyMsg->UserSubscriptionCancelledImmediate = true;
                    }
                    
                    // If we don't have the userID in this notification, look up user by the payer ID passed in the message, 
                    // so that we can update this user's membership status as needed
                    if(strlen($userId) == 0) {
                        $replyMsg->UserId = $this->LookUpPayPalUserByPayerId($replyMsg->PayerId, $dataAccess, $logger);
                    }
                    
                    // Update user membership status
                    if(strlen($replyMsg->UserId) > 0) {
                        $this->UpdateUserMembershipStatus($replyMsg, $dataAccess, $logger);
                    }
                    else {
                        $logger->LogError(sprintf("Could not update membership status for Transaction [%s], payer [%s], payment status = [%s]: no user ID found for this payer.",
                                                 $replyMsg->TxnId, $replyMsg->PayerId, $replyMsg->PaymentStatus));
                        $replyMsg->UserMessage .= "ERROR: could not update membership status, because your user ID could not be found. Please contact PlayerUnite for assistance.";
                    }
                }
                else if(($replyMsg->PaymentStatus != 'Completed') && 
                        (($replyMsg->TxnType == "subscr_signup") || ($replyMsg->TxnType == "subscr_modify") || ($replyMsg->TxnType == "subscr_payment"))
                       ) {
                    $subscrAction = ($replyMsg->TxnType == "subscr_signup") ? 'created' : (($replyMsg->TxnType == "subscr_modify") ? 'changed' : 'renewed');
                    $logger->LogInfo(sprintf("Transaction [%s] for payer [%s]: payment status = [%s]. Subscription payment not completed.",
                                             $replyMsg->TxnId, $replyMsg->PayerId, $replyMsg->PaymentStatus));

                    if(($replyMsg->PaymentStatus == "Processed") || ($replyMsg->PaymentStatus == "Pending")) {
                        $replyMsg->UserMessage = "Payment received, but not complete. Check back with us soon: your membership status will be updated as soon as your payment has completed!";
                    }
                    else {
                        $replyMsg->UserMessage = "Payment could not be processed. Please try again, so that we can update your membership status and allow you to enjoy the benefits of full membership!";
                    }
                }
            }
        }
        
        return $replyMsg;
    }
    
    public function SendIPNHttpOKResponse($url)
    {
        
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
    
    private function ValidatePayPalResponse($msgInAssocArray, $userId)
    {
        // Initialize response object
        $replyMsg = new PayPalTxnMsg($this->GetResponseValByKey($msgInAssocArray, "txn_id"),
                                     $this->GetResponseValByKey($msgInAssocArray, "txn_type"), 
                                     $this->GetResponseValByKey($msgInAssocArray, "payer_id"), 
                                     $userId, "", $this->GetResponseValByKey($msgInAssocArray, "payment_status"),
                                     "", "0", "0", "", "", gmdate('Y-m-d H:i:s'));
        
        // Verify that current payment status is "Completed"...if not, only log this update
        $isCompletedPaymentStatus = ($replyMsg->PaymentStatus == "Completed");
        
        // Verify that the payment recipient is this website
        $thisPayPalMerchantId = Constants::$isPayPalTest ? Constants::$payPalTestMerchantId : Constants::$payPalProdMerchantId;
        if(!(strtolower($thisPayPalMerchantId) == $this->GetResponseValByKey($msgInAssocArray, "business"))) {
            $replyMsg->IsValidBusiness = false;
        }
        // Verify that the payment amount is what we expect if this is a subscription start, upgrade/renewal, or recurring payment
        else if(($replyMsg->TxnType == "subscr_signup") || ($replyMsg->TxnType == "subscr_modify") ||($replyMsg->TxnType == "subscr_payment"))
        {
            $replyMsg->SubscriptionAmtPaid = $this->GetResponseValByKey($msgInAssocArray, "payment_gross");
            
            // Don't need to perform payment amount check until and unless payment status reaches Completed (funds added to business acct)
            if($isCompletedPaymentStatus) {
                $selectedSubscriptionOption = $this->GetResponseValByKey($msgInAssocArray, "item_name");
                if((strlen($selectedSubscriptionOption) > 0) && (array_key_exists($selectedSubscriptionOption, Constants::$subscriptionOptionNames))) {
                    $expectedPaymentAmt = Constants::$subscriptionOptionNames[$selectedSubscriptionOption];
                    $correctCurrencyAmt = ($replyMsg->SubscriptionAmtPaid == $expectedPaymentAmt);
                    $replyMsg->SelectedSubscriptionOption = $selectedSubscriptionOption;
                    $replyMsg->SubscriptionAmtTotal = $expectedPaymentAmt;
                    
                    if($isCorrectBusiness && $correctCurrencyAmt) {
                        $replyMsg->IsValidated = true;
						
			if($replyMsg->TxnType == "subscr_signup") {
                            $replyMsg->SubscriptionModifyDate = $this->ConvertPayPalTimestampToDBDateTime($this->GetResponseValByKey($msgInAssocArray, "subscr_date"));
			}
			else if($replyMsg->TxnType == "subscr_modify") {
                            $replyMsg->SubscriptionModifyDate = $this->ConvertPayPalTimestampToDBDateTime($this->GetResponseValByKey($msgInAssocArray, "subscr_effective"));
			}
						
			if(($replyMsg->TxnType == "subscr_signup") || ($replyMsg->TxnType == "subscr_modify")) {
                            $replyMsg->SubscriptionIsRecurring = ($this->GetResponseValByKey($msgInAssocArray, "recurring") == "1");
			}
                    }
                }
            }
        }
	else if($replyMsg->TxnType == "subscr_cancel") {
            $replyMsg->SubscriptionModifyDate = $this->ConvertPayPalTimestampToDBDateTime($this->GetResponseValByKey($msgInAssocArray, "subscr_date"));
            $replyMsg->IsValidated = true; // Consider this a validated (requires additional handler action) message, since this is not a txn type that requires payment
	}
	else if($replyMsg->TxnType == "subscr_eot") {
            $replyMsg->SubscriptionModifyDate = gmdate('Y-m-d H:i:s'); // Current UTC date
            $replyMsg->IsValidated = true; // Consider this a validated (requires additional handler action) message, since this is not a txn type that requires payment
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
	
    private function InsertMsgIntoTxnLog($replyMsg, $msgData, $dataAccess, $logger)
    {
	$recordsAffected = 0;
		
        $insertTxnMsgQuery = "INSERT INTO `Payments.PayPalTransactions` (`TxnId`,`PayerId`, `TxnType`, `PDTOperation`, `PaymentStatus`, 
                                                                         `NotificationType`, `NotificationDate`, `PayPalMsgData`) " .
                             "VALUES (:txnId, :payerId, :txnType, :pdtOp, :pymtStatus, :notType, :notDate, :msgData);";
		
	$parmTxnId = new QueryParameter(':txnId', (int)$replyMsg->TxnId, PDO::PARAM_INT);
	$parmUserId = new QueryParameter(':payerId', (int)$replyMsg->PayerId, PDO::PARAM_INT);
	$parmTxnType = new QueryParameter(':txnType', $replyMsg->TxnType, PDO::PARAM_STR);
	$parmPdtOp = new QueryParameter(':pdtOp', $replyMsg->PDTOperation, PDO::PARAM_STR);
	$parmPymtStatus = new QueryParameter(':pymtStatus', $replyMsg->PaymentStatus, PDO::PARAM_STR);
	$parmNotType = new QueryParameter(':notType', $replyMsg->NotificationType, PDO::PARAM_STR);
	$parmNotDate = new QueryParameter(':notDate', $replyMsg->NotificationDate, PDO::PARAM_STR);
	$parmMsgData = new QueryParameter(':msgData', $msgData, PDO::PARAM_STR);
		
	$queryParms = array($parmTxnId, $parmUserId, $parmTxnType, $parmPdtOp, $parmPymtStatus, $parmNotType, $parmNotDate, $parmMsgData);

	if($dataAccess->BuildQuery($insertTxnMsgQuery, $queryParms)){
            $dataAccess->ExecuteNonQuery();
	}
			
	$errors = $dataAccess->CheckErrors();

	if(strlen($errors) != 0) {
            $logger->LogError(sprintf("Could not insert PayPal transaction msg [txnId=%d] for payer ID %d. %s", $replyMsg->TxnId, $replyMsg->PayerId, $errors));
	}
	else {
            $recordsAffected = $dataAccess->RowCount();
	}
	
	return ($recordsAffected > 0);
    }
    
    private function InsertPayPalUser($replyMsg, $dataAccess, $logger)
    {		
        $insertPayPalUserQuery = "INSERT INTO `Payments.PayPalUsers` (`FK_User_ID`,`PayerId`, `SubscriptionType`, `SubscriptionAmtTotal`, 
                                                                      `SubscriptionAmtPaidLastCycle`, `IsActive`) " .
                                 "VALUES (:userId, :payerId, :subscrType, :subscrAmtTotal, :subscrAmtPaidLastCycle, :isActive);";
		
	$parmUserId = new QueryParameter(':userId', (int)$replyMsg->UserId, PDO::PARAM_INT);
	$parmPayerId = new QueryParameter(':payerId', (int)$replyMsg->PayerId, PDO::PARAM_INT);
	$parmSubscrType = new QueryParameter(':subscrType', $replyMsg->SelectedSubscriptionOption, PDO::PARAM_STR);
	$parmSubscrAmtTotal = new QueryParameter(':subscrAmtTotal', $replyMsg->SubscriptionAmtTotal, PDO::PARAM_STR);
        $parmSubscrAmtPaidLastCycle = new QueryParameter(':subscrAmtPaidLastCycle', $replyMsg->SubscriptionAmtPaid, PDO::PARAM_STR);
	$parmIsActive = new QueryParameter(':isActive', 1, PDO::PARAM_INT);
		
	$queryParms = array($parmUserId, $parmPayerId, $parmSubscrType, $parmSubscrAmtTotal, $parmSubscrAmtPaidLastCycle, $parmIsActive);

	if($dataAccess->BuildQuery($insertPayPalUserQuery, $queryParms)){
            $dataAccess->ExecuteNonQuery();
	}
			
	$errors = $dataAccess->CheckErrors();

	if(strlen($errors) != 0) {
            $logger->LogError(sprintf("Could not insert PayPal user %d [payer ID: %d; subscription type: %s; subscription amount paid: %s]. %s", 
                                        $replyMsg->UserId, $replyMsg->PayerId, $replyMsg->SelectedSubscriptionOption, $replyMsg->SubscriptionAmtPaid, $errors));
            return false;
	}
	
	return true;
    }
}
