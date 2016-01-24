<?php
include_once 'classes/DataAccess.class.php';
include_once 'classes/SecurityHandler.class.php';
include_once 'classes/Logger.class.php';
include_once 'classes/Constants.class.php';
include_once 'classes/PayPalTxnMsg.class.php';
include_once 'classes/PayPalUser.class.php';

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
                CURLOPT_HEADER => FALSE
                //CURLOPT_SSL_VERIFYPEER => FALSE,
                //CURLOPT_SSL_VERIFYHOST => FALSE
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
    
    public function CancelSubscriptionForUser($dataAccess, $logger, $userID)
    {
        $payPalUser             = $this->LookUpPayPalUserByUserId($dataAccess, $logger, $userID);
        $payPalAPIURL           = Constants::$isPayPalTest ? Constants::$payPalTestAPIURL           : Constants::$payPalProdAPIURL;
        $payPalAPIUsername      = Constants::$isPayPalTest ? Constants::$payPalTestAPIUsername      : Constants::$payPalProdAPIUsername;
        $payPalAPIPassword      = Constants::$isPayPalTest ? Constants::$payPalTestAPIPassword      : Constants::$payPalProdAPIPassword;
        $payPalAPISignature     = Constants::$isPayPalTest ? Constants::$payPalTestAPISignature     : Constants::$payPalProdAPISignature;
        
        return $this->SendCancelRequestToPayPal($logger, $userID, $payPalUser->SubscriptionID, $payPalAPIURL, $payPalAPIUsername, $payPalAPIPassword, 
                                                $payPalAPISignature);
    }
    
    public function HandlePayPalTxnResponse($msgInAssocArray, $rawPdtMessage, $dataAccess, $logger, $notificationType)
    {
	// PDT and IPN transaction detail messages essentially have the same structure and may be processed in similar ways.
	//  The main difference between them is that PDT messages are triggered by a button click on the website, and therefore 
	//  require a response to be presented to the user.
        // In addition to listening for IPN responses from Subscription Purchase and Subscription 
        //  Cancellation requests initiated from a button on this website (in case PDT immediate response is not received),
        //  we also need to handle notifications of canceled/failed subcription. Such notifications may occur at random times, 
        //  such as when the user's payment method for a recurring subscription is declined, or if user cancels their subscription 
        //  directly from PayPal instead of by clicking "Cancel" from their Player Unite page.
        
        $action = "";
        $userId = -1;
        
        if(strlen($this->GetResponseValByKey($msgInAssocArray, "custom")) > 0) {
            $customVars = explode('|', $msgInAssocArray["custom"]);
            $userId = (int)$customVars[0];
            $action = $customVars[1];
        }
        
        $replyMsg = $this->ValidatePayPalResponse($msgInAssocArray, $action, $notificationType);
		
	// If this notification is intended for another business, log error and take no further actions
	if(!$replyMsg->IsValidBusiness) {
            $logger->LogInfo(sprintf("[Notification Type: %s]  Transaction [%s] for payer [%s]: this message not for this business. Recipient business: [%s]",
                             $notificationType, $replyMsg->TxnId, $replyMsg->PayerId, $this->GetResponseValByKey($msgInAssocArray, "business")));
            $replyMsg->UserMessage = "ERROR: Invalid payment recipient specified. Transaction unsuccessful.";
	}
	else {
            // Whether or not subscription has changed, archive this transaction message in PayPalTransactions table
            $replyMsg->IsTxnLogged = $this->InsertMsgIntoTxnLog($replyMsg, $rawPdtMessage, $dataAccess, $logger, $notificationType);
            
            // Only proceed if DB update returns > 0 records affected (unique index prevents insert of records with identical txn ID, txn type, and payment status)
            if($replyMsg->IsTxnLogged) {
                // Look up user by the payer ID passed in the message, so that we can update this user's membership status as needed
		$payPalUser = $this->LookUpPayPalUser($replyMsg->PayerId, $replyMsg->SubscriptionID, $dataAccess, $logger, $notificationType);
                $replyMsg->UserId = $payPalUser->UserID;
                
                // If we have a user ID in this message, insert new record in Payments.PayPalUsers table to map this 
                // user's PayPal payer_id to their user ID in our system, unless this user already in table
                if(($replyMsg->UserId < 0) && ($userId > -1)) {
                    $replyMsg->UserId = $userId;
					
                    $insertedID = $this->InsertPayPalUser($replyMsg, $dataAccess, $logger, $notificationType, $payPalUser);
                    if($insertedID > 0) {
			$this->DisableOldUserSubscriptions($dataAccess, $logger, $notificationType, $replyMsg->UserId, $insertedID);
                    }
                }
				
		// Retrieve most recent PayPal user acct for this user before current one, if this is a signup
		$payPalUserOldAcct = PayPalUser::constructDefaultPayPalUser();
		$extendedMembershipDays = 0;
		if($replyMsg->TxnType == "subscr_signup") {
                    $payPalUserOldAcct = $this->LookUpPayPalUserByPayerId($dataAccess, $logger, $replyMsg->PayerId, $replyMsg->SubscriptionID);
					
                    // If this user is signing up for a new membership profile, but already has an active account that has been cancelled,
                    // calculate the number of days until the end of the current billing cycle, so that the unused days may be credited to
                    // the user's account when/if they cancel this new recurring membership.
                    if(($payPalUserOldAcct->ID > -1) && (!$payPalUserOldAcct->IsRecurring)) {
			sscanf($payPalUserOldAcct->MembershipExpDate, "%s %s", $dateStr, $timeStr);
			$membershipExpDateUTC = date_create_from_format("Y-m-d", $dateStr, new DateTimeZone("UTC"));
			$curDatetimeUTC = new DateTime(null, new DateTimeZone("UTC"));
			$curDateUTC = date_create_from_format("Y-m-d", $curDatetimeUTC->format("Y-m-d"), new DateTimeZone("UTC"));
									
			if($curDateUTC < $membershipExpDateUTC) {
                            $extendedMembershipDays = $membershipExpDateUTC->diff($curDateUTC)->days;
			}
                    }
		}
                
		// Determine if this is the first payment received for the current billing cycle
		$billFrequency = str_replace(' ', '', $this->GetResponseValByKey($msgInAssocArray, "period3"));
		if(strlen($billFrequency) == 0)  $billFrequency = "1M";
										
		$amtAlreadyPaid = $payPalUser->SubscriptionAmtPaidLastCycle;
		$curPaymentAmt = floatval($replyMsg->SubscriptionAmtPaid);
		$requiredPaymentAmt = floatval($replyMsg->SubscriptionAmtTotal);
		$billingInterval = new DateInterval("P" . $billFrequency);
		$curDate = gmdate('Y-m-d H:i:s');
										
		$isFirstPaymentOfThisCycle = false;
                $lastBillDateTime = date_create_from_format('Y-m-d H:i:s', $payPalUser->LastBillDate, new DateTimeZone("UTC"));
		if(($replyMsg->TxnType == "subscr_payment") && ((strlen($payPalUser->LastBillDate) == 0)  || 
                                                                (date_add($lastBillDateTime, $billingInterval) >= $curDate))) {
                    $isFirstPaymentOfThisCycle = true;
                    $lastBillDateTime = date_create_from_format('Y-m-d H:i:s', $curDate, new DateTimeZone("UTC"));
                    $amtAlreadyPaid = 0; // Reset amount already paid: this value does not count towards current cycle's required payment
		}
		else {
                    $replyMsg->SubscriptionAmtPaid = strval($amtAlreadyPaid + $curPaymentAmt);
		}
				
		// If txn was found invalid because current payment is less than expected amount for this subscription type,
		//  check SubscriptionAmtPaid from current txn and combine with SubscriptionAmtPaidLastCycle...if combined they
		//  are equal to SubscriptionAmtTotal, then this was a partial payment that finishes their payment obligation, 
		//  and the user should be upgraded/renewed
		if((!$replyMsg->IsValidated) && ($replyMsg->TxnType == "subscr_payment") && ($replyMsg->PaymentStatus == "Completed")) {
                    if(($amtAlreadyPaid + $curPaymentAmt) >= $requiredPaymentAmt) {
			$replyMsg->IsValidated = true;
			$replyMsg->UpdateUserMembershipStatus = true;
                    }
		}
				
		if(($replyMsg->IsValidated) && ($replyMsg->TxnType == "subscr_payment")) {
                    if(strlen($payPalUser->LastBillDate) == 0) {                            
			// Upgrade user subscription status in DB...now a premium member
			$replyMsg->UserMessage = "Subscription successfully created!";
			$replyMsg->UserUpgradedPremium = true;
                    }
                    else {
			// If user account for this subscription ID already exists, this must be an automatic recurring payment IPN
			$replyMsg->UserMessage = "Subscription successfully renewed!";
			$replyMsg->UserSubscriptionRenewed = true;
                    }
		}
				
                // If payment was completed and payment was made in full, proceed to update user information related to membership
                if($replyMsg->IsValidated) {
                    if($replyMsg->UserId > 0) {
                        // Always update user PayPal account info when a validated notification is received
                        $this->UpdateUserPayPalAccountInformation($replyMsg, $dataAccess, $logger, $notificationType, $isFirstPaymentOfThisCycle, 
								  ($lastBillDateTime !== FALSE), $lastBillDateTime, $billingInterval, $extendedMembershipDays);

                        // Only update user membership status when we receive a Completed payment or a membership expiration notice
                        if($replyMsg->UpdateUserMembershipStatus) {
                            // If this is a EOT IPN, downgrade user to basic membership only if they are not currently in extended membership
                            $extendedMember = false;
                            $curDatetimeUTC = new DateTime(null, new DateTimeZone("UTC"));
                            $membershipExpDateTime = date_create_from_format('Y-m-d H:i:s', $payPalUser->MembershipExpDate, new DateTimeZone("UTC"));
                            if(($replyMsg->UserSubscriptionCancelledImmediate) && ((($payPalUser->ExtendedMembershipDays > 0) && $payPalUser->IsRecurring) || 
                                                                                    ($curDatetimeUTC < $membershipExpDateTime))) {
                                $extendedMember = true;
                            }
                            
                            $this->UpdateUserMembershipStatus($replyMsg, $dataAccess, $logger, $notificationType, $payPalUser->IsActive, $extendedMember);
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
                CURLOPT_HEADER => FALSE
                //CURLOPT_SSL_VERIFYPEER => FALSE,
                //CURLOPT_SSL_VERIFYHOST => FALSE
            )
        );
        
        $response = curl_exec($request);
        $status = curl_getinfo($request, CURLINFO_HTTP_CODE);
        curl_close($request);
        return $response;
    }
    
    private function SendCancelRequestToPayPal($logger, $userID, $subscriptionID, $payPalAPIURL, $payPalAPIUsername, $payPalAPIPassword, $payPalAPISignature)
    {
        $status = 200;
        $request = curl_init();
        curl_setopt_array($request, array
            (
                CURLOPT_URL => $payPalAPIURL,
                CURLOPT_POST => TRUE,
                CURLOPT_POSTFIELDS => http_build_query(array(
                        'USER' => $payPalAPIUsername,
                        'PWD' => $payPalAPIPassword,
                        'SIGNATURE' => $payPalAPISignature,
                        'VERSION' => '76.0',
                        'METHOD' => 'ManageRecurringPaymentsProfileStatus',
                        'PROFILEID' => $subscriptionID,
                        'ACTION' => 'Cancel',
                        'NOTE' => 'Subscription cancelled by user request'
                    )
                ),
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HEADER => FALSE,
                CURLOPT_VERBOSE => TRUE
            )
        );
        
        $response = curl_exec($request);
        $status = curl_getinfo($request, CURLINFO_HTTP_CODE);
        $failureACK = ($response != null) ? stripos($response, 'ACK=Failure') : -1;
        $responseMsg = "SUCCESS: Your recurring PayPal subscription to PlayerUnite has been cancelled. Your premium member benefits will remain active until the end of the current cycle. We hope to see you again soon.";
        
        if(!$response || ($status != 200) || ($failureACK > 0)) {
            $curlError = curl_error($request);
            $errMsg = "";
            if(strlen($curlError) > 0) {
                $errMsg = sprintf("CURL err #: %d; CURL error: %s", curl_errno($request), $curlError);
            }
            else if($failureACK > 0) {
                parse_str($response, $parsedResponse);
                $errMsg = "PayPal error message: " . $parsedResponse["L_LONGMESSAGE0"];
            }
            
            $logger->LogError(sprintf("Unable to send cancel request to PayPal for user '%s', subscription ID '%s'. HTTP Status: %d; %s", 
                                      $userID, $subscriptionID, $status, $errMsg));
            $logger->LogInfo(sprintf("Full PayPal response for user '%s', subscription ID '%s': %s", $userID, $subscriptionID, $response));
            
            $responseMsg = "SYSTEM ERROR: PayPal was unable to process cancel request. Please try again later. If this issue continues, try cancelling " . 
                           "this subscription directly from your PayPal account page.";
        }
        else {
            $logger->LogInfo(sprintf("Sent cancel request to PayPal for user '%s', subscription ID '%s'. Response: %s", $userID, $subscriptionID, $response));
        }
        
        curl_close($request);
        return $responseMsg;
    }
    
    private function ValidatePayPalResponse($msgInAssocArray, $action, $notificationType)
    {
        // Initialize response object
        $replyMsg = new PayPalTxnMsg($this->GetResponseValByKey($msgInAssocArray, "txn_id"),
                                     $this->GetResponseValByKey($msgInAssocArray, "txn_type"), 
                                     $this->GetResponseValByKey($msgInAssocArray, "payer_id"), 
                                     -1, $notificationType, $this->GetResponseValByKey($msgInAssocArray, "payment_status"),
                                     //$this->GetResponseValByKey($msgInAssocArray, "option_selection1"), "", 
                                     "Monthly", "", "", "", "", gmdate('Y-m-d H:i:s'), $action, 
                                     $this->GetResponseValByKey($msgInAssocArray, "subscr_id"));
		
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
            $replyMsg->SubscriptionModifyDate = $this->ConvertPayPalTimestampToDBDateTime($this->GetResponseValByKey($msgInAssocArray, "payment_date"));
			
            if($replyMsg->NotificationType == "PDT") {
		$replyMsg->SubscriptionIsRecurring = ($this->GetResponseValByKey($msgInAssocArray, "recurring") == "1");
            }
			
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
                    }
                }
            }
        }
	else if($replyMsg->TxnType == "subscr_cancel") {
            $replyMsg->IsValidated = true; // Consider this a validated (requires additional handler action) message, since this is not a txn type that requires payment
            $replyMsg->PDTOperation = "";
            
            // If an active subscription was cancelled, alter user membership expiration date but leave as premium for now
            $replyMsg->UserMessage = "Subscription successfully cancelled, and will expire at the end of your current term. We're sorry to see you go!";
            $replyMsg->UserSubscriptionCancelledPending = true;
	}
	else if($replyMsg->TxnType == "subscr_eot") {
            $replyMsg->SubscriptionModifyDate = gmdate('Y-m-d H:i:s'); // Current UTC date
            $replyMsg->IsValidated = true; // Consider this a validated (requires additional handler action) message, since this is not a txn type that requires payment
            $replyMsg->UpdateUserMembershipStatus = true; // Immediately change user to Basic member
            $replyMsg->PDTOperation = "";
            
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
		
        $insertTxnMsgQuery = "INSERT INTO `Payments.PayPalTransactions` (`TxnId`,`PayerId`, `SubscriptionID`, `TxnType`, `PDTOperation`, `PaymentStatus`, " .
                                                                         "`NotificationType`, `NotificationDate`, `PayPalMsgData`, `TransactionDate`) " .
                             "VALUES (:txnId, :payerId, :subscrID, :txnType, :pdtOp, :pymtStatus, :notType, :notDate, :msgData, :txnDate);";
	
        $txnID = $replyMsg->TxnId;
        if(strlen($txnID) == 0)  $txnID = $replyMsg->PayerId . "_" . $replyMsg->TxnType;
        
        $paymentStatus = $replyMsg->PaymentStatus;
        if(strlen($paymentStatus) == 0)  $paymentStatus = "N/A";
        
	$parmTxnId = new QueryParameter(':txnId', $txnID, PDO::PARAM_STR);
	$parmUserId = new QueryParameter(':payerId', $replyMsg->PayerId, PDO::PARAM_STR);
	$parmSubscrId = new QueryParameter(':subscrID', $replyMsg->SubscriptionID, PDO::PARAM_STR);
	$parmTxnType = new QueryParameter(':txnType', $replyMsg->TxnType, PDO::PARAM_STR);
	$parmPdtOp = new QueryParameter(':pdtOp', $replyMsg->PDTOperation, PDO::PARAM_STR);
	$parmPymtStatus = new QueryParameter(':pymtStatus', $paymentStatus, PDO::PARAM_STR);
	$parmNotType = new QueryParameter(':notType', $replyMsg->NotificationType, PDO::PARAM_STR);
	$parmNotDate = new QueryParameter(':notDate', $replyMsg->NotificationDate, PDO::PARAM_STR);
	$parmMsgData = new QueryParameter(':msgData', $msgData, PDO::PARAM_STR);
		
	$txnDate = $replyMsg->SubscriptionModifyDate;
	if(strlen($txnDate) == 0) {
            $txnDate = gmdate('Y-m-d H:i:s'); // Current UTC date
	}
        $parmTxnDate = new QueryParameter(':txnDate', $txnDate, PDO::PARAM_STR);
			
	$queryParms = array($parmTxnId, $parmUserId, $parmSubscrId, $parmTxnType, $parmPdtOp, $parmPymtStatus, $parmNotType, $parmNotDate, 
                            $parmMsgData, $parmTxnDate);

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
    
    private function InsertPayPalUser($replyMsg, $dataAccess, $logger, $notificationType, &$payPalUser)
    {		
        $insertPayPalUserQuery = "INSERT INTO `Payments.PayPalUsers` (`FK_User_ID`,`PayerId`, `SubscriptionID`, `SubscriptionType`, `SubscriptionAmtTotal`, 
                                                                      `SubscriptionAmtPaidLastCycle`, `IsActive`) " .
                                 "VALUES (:userId, :payerId, :subscrID, :subscrType, :subscrAmtTotal, :subscrAmtPaidLastCycle, :isActive);";
		
	$parmUserId = new QueryParameter(':userId', $replyMsg->UserId, PDO::PARAM_INT);
	$parmPayerId = new QueryParameter(':payerId', $replyMsg->PayerId, PDO::PARAM_STR);
	$parmSubscrId = new QueryParameter(':subscrID', $replyMsg->SubscriptionID, PDO::PARAM_STR);
	$parmSubscrType = new QueryParameter(':subscrType', $replyMsg->SelectedSubscriptionOption, PDO::PARAM_STR);
	$parmSubscrAmtTotal = new QueryParameter(':subscrAmtTotal', $replyMsg->SubscriptionAmtTotal, PDO::PARAM_STR);
        $parmSubscrAmtPaidLastCycle = new QueryParameter(':subscrAmtPaidLastCycle', $replyMsg->SubscriptionAmtPaid, PDO::PARAM_STR);
	$parmIsActive = new QueryParameter(':isActive', 1, PDO::PARAM_INT);
		
	$queryParms = array($parmUserId, $parmPayerId, $parmSubscrId, $parmSubscrType, $parmSubscrAmtTotal, $parmSubscrAmtPaidLastCycle, $parmIsActive);

	if($dataAccess->BuildQuery($insertPayPalUserQuery, $queryParms)){
            $dataAccess->ExecuteNonQuery();
	}
			
	$errors = $dataAccess->CheckErrors();

	if(strlen($errors) > 0) {
            $logger->LogError(sprintf("[Notification Type: %s]  Could not insert PayPal user '%d' [payer ID: %s; subscription type: %s; subscription amount paid: %s]. %s", 
                                        $notificationType, $replyMsg->UserId, $replyMsg->PayerId, $replyMsg->SelectedSubscriptionOption, $replyMsg->SubscriptionAmtPaid, $errors));
            return -1;
	}
		
	$payPalUser->UserID = $replyMsg->UserId;
	$payPalUser->PayerId = $replyMsg->PayerId;
	$payPalUser->SubscriptionID = $replyMsg->SubscriptionID;
	$payPalUser->SubscriptionType = $replyMsg->SelectedSubscriptionOption;
	$payPalUser->SubscriptionAmtTotal = $replyMsg->SubscriptionAmtTotal;
	$payPalUser->SubscriptionAmtPaidLastCycle = $replyMsg->SubscriptionAmtPaid;
	$payPalUser->IsActive = true;
	return $dataAccess->GetLastInsertId();
    }
    
    public function LookUpPayPalUser($payerId, $subscrID, $dataAccess, $logger, $notificationType)
    {
        $getUserQuery = "SELECT `ID`, IFNULL(`FK_User_ID`, -1) AS UserID, IFNULL(`SubscriptionType`, '') AS SubscriptionType, IFNULL(`SubscriptionAmtTotal`, 0) AS SubscriptionAmtTotal, " .
                            "IFNULL(`SubscriptionAmtPaidLastCycle`, 0) AS SubscriptionAmtPaidLastCycle, IFNULL(`SubscriptionStartedDate`, '') AS SubscriptionStartedDate, " .
                            "IFNULL(`SubscriptionModifiedDate`, '') AS SubscriptionModifiedDate, IFNULL(`LastBillDate`, '') AS LastBillDate, " .
                            "IFNULL(`MembershipExpirationDate`, '') AS MembershipExpirationDate, `IsRecurring`, `IsActive`, `ExtendedMembershipDays` " .
			"FROM `Payments.PayPalUsers` " .
                        "WHERE (`PayerId` = :payerId) AND (`SubscriptionID` = :subscrID);";
        
        $parmPayerId = new QueryParameter(':payerId', $payerId, PDO::PARAM_STR);
	$parmSubscrId = new QueryParameter(':subscrID', $subscrID, PDO::PARAM_STR);
        $queryParms = array($parmPayerId, $parmSubscrId);
	$payPalUser = PayPalUser::constructDefaultPayPalUser();
        
        if($dataAccess->BuildQuery($getUserQuery, $queryParms)){
            $results = $dataAccess->GetSingleResult();

            if($results != null){
		$payPalUser = new PayPalUser($results['ID'], $results['UserID'], ($results['IsActive'] == 1), ($results['IsRecurring'] == 1), $results['LastBillDate'], 
                                             $results['MembershipExpirationDate'], $payerId, $results['SubscriptionType'], $results['SubscriptionAmtTotal'], 
                                             $results['SubscriptionAmtPaidLastCycle'], $results['SubscriptionStartedDate'], $results['SubscriptionModifiedDate'],
                                             $subscrID, $results['ExtendedMembershipDays']);
            }
        }
        
	$errors = $dataAccess->CheckErrors();
	if(strlen($errors) > 0) {
            $logger->LogError(sprintf("[Notification Type: %s]  Could not retrieve information associated with payer ID '%s'. %s", $notificationType, $payerId, $errors));
	}
        
        return $payPalUser;
    }
    
    public function LookUpPayPalUserByUserId($dataAccess, $logger, $userID)
    {		
        $getUserQuery = "SELECT `ID`, IFNULL(`SubscriptionType`, '') AS SubscriptionType, IFNULL(`SubscriptionAmtTotal`, 0) AS SubscriptionAmtTotal, " .
                            "IFNULL(`SubscriptionAmtPaidLastCycle`, 0) AS SubscriptionAmtPaidLastCycle, IFNULL(`SubscriptionStartedDate`, '') AS SubscriptionStartedDate, " .
                            "IFNULL(`SubscriptionModifiedDate`, '') AS SubscriptionModifiedDate, IFNULL(`LastBillDate`, '') AS LastBillDate, " .
                            "IFNULL(`MembershipExpirationDate`, '') AS MembershipExpirationDate, `IsRecurring`, `IsActive`, `SubscriptionID`, `PayerId`, `ExtendedMembershipDays` " .
			"FROM `Payments.PayPalUsers` " .
                        "WHERE (`FK_User_ID` = :userId) AND (`IsActive` = 1) " .
			"ORDER BY `SubscriptionStartedDate` DESC LIMIT 1;";
        
        $parmUserId = new QueryParameter(':userId', $userID, PDO::PARAM_STR);
        $queryParms = array($parmUserId);
	$payPalUser = PayPalUser::constructDefaultPayPalUser();
        
        if($dataAccess->BuildQuery($getUserQuery, $queryParms)){
            $results = $dataAccess->GetSingleResult();

            if($results != null){
		$payPalUser = new PayPalUser($results['ID'], $userID, ($results['IsActive'] == 1), ($results['IsRecurring'] == 1), $results['LastBillDate'], 
                                             $results['MembershipExpirationDate'], $results['PayerId'], $results['SubscriptionType'], $results['SubscriptionAmtTotal'], 
                                             $results['SubscriptionAmtPaidLastCycle'], $results['SubscriptionStartedDate'], $results['SubscriptionModifiedDate'], 
                                             $results['SubscriptionID'], $results['ExtendedMembershipDays']);
            }
        }
        
	$errors = $dataAccess->CheckErrors();
	if(strlen($errors) > 0) {
            $logger->LogError(sprintf("Could not retrieve information associated with user ID '%s'. %s", $userID, $errors));
	}
        
        return $payPalUser;
    }
	
    public function LookUpPayPalUserByPayerId($dataAccess, $logger, $payerID, $subscriptionID = "-1")
    {
        $getUserQuery = "SELECT `ID`, IFNULL(`SubscriptionType`, '') AS SubscriptionType, IFNULL(`SubscriptionAmtTotal`, 0) AS SubscriptionAmtTotal, " .
                            "IFNULL(`SubscriptionAmtPaidLastCycle`, 0) AS SubscriptionAmtPaidLastCycle, IFNULL(`SubscriptionStartedDate`, '') AS SubscriptionStartedDate, " .
                            "IFNULL(`SubscriptionModifiedDate`, '') AS SubscriptionModifiedDate, IFNULL(`LastBillDate`, '') AS LastBillDate, " .
                            "IFNULL(`MembershipExpirationDate`, '') AS MembershipExpirationDate, `IsRecurring`, `IsActive`, `SubscriptionID`, `FK_User_ID` " .
			"FROM `Payments.PayPalUsers` " .
                        "WHERE (`PayerId` = :payerId) AND (`SubscriptionID` != :subscrID) " .
			"ORDER BY `SubscriptionStartedDate` DESC LIMIT 1;";
        
        $parmPayerId = new QueryParameter(':payerId', $payerID, PDO::PARAM_STR);
	$parmSubscrId = new QueryParameter(':subscrID', $subscriptionID, PDO::PARAM_STR);
        $queryParms = array($parmPayerId, $parmSubscrId);
	$payPalUser = PayPalUser::constructDefaultPayPalUser();
        
        if($dataAccess->BuildQuery($getUserQuery, $queryParms)){
            $results = $dataAccess->GetSingleResult();

            if($results != null){
		$payPalUser = new PayPalUser($results['ID'], $results['FK_User_ID'], ($results['IsActive'] == 1), ($results['IsRecurring'] == 1), $results['LastBillDate'], 
                                         $results['MembershipExpirationDate'], $payerID, $results['SubscriptionType'], $results['SubscriptionAmtTotal'], 
                                         $results['SubscriptionAmtPaidLastCycle'], $results['SubscriptionStartedDate'], $results['SubscriptionModifiedDate'], 
                                         $results['SubscriptionID']);
            }
        }
        
	$errors = $dataAccess->CheckErrors();
	if(strlen($errors) > 0) {
            $logger->LogError(sprintf("Could not retrieve information associated with payer ID '%s'. %s", $payerID, $errors));
	}
        
        return $payPalUser;
    }
    
    private function UpdateUserMembershipStatus($replyMsg, $dataAccess, $logger, $notificationType, $isActive, $isExtendedMember = false)
    {
	$updateSuccess = false;
        $transactionComplete = false;
        $isPremium = ($replyMsg->UserUpgradedPremium || (($replyMsg->UserSubscriptionRenewed || $replyMsg->UserSubscriptionModified || 
                                                          $replyMsg->UserSubscriptionCancelledPending) && $isActive) || 
                     ($replyMsg->UserSubscriptionCancelledImmediate && $isExtendedMember));
														  
	$updateUserQuery = "UPDATE `Security.Users` SET `IsPremiumMember` = :isPremium WHERE `ID` = :userId;";
	
        $parmIsPremium = new QueryParameter(':isPremium', ($isPremium ? 1 : 0), PDO::PARAM_INT);
	$parmUserId = new QueryParameter(':userId', $replyMsg->UserId, PDO::PARAM_INT);
	$queryParms = array($parmIsPremium, $parmUserId);
        
        // Wrap Security.Users and Security.UserRoles updates in transaction:
        // if one fails, must roll back the successful update, if any.
        $dataAccess->BeginTransaction();

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
		
	if($transactionComplete == false) {
            $logger->LogError(sprintf("[Notification Type: %s]  Could not update user membership status to '%s' for user ID '%d'. %s", 
                                      $notificationType, (($isPremium == 1) ? "Premium" : "Basic"), $replyMsg->UserId, $errors));
            $dataAccess->RollbackTransaction();
	}
			
	return $transactionComplete;
    }
	
    private function DisableOldUserSubscriptions($dataAccess, $logger, $notificationType, $userId, $curPayPalUserID)
    {
	$updateSuccess = false;
	$parmUserId = new QueryParameter(':userId', $userId, PDO::PARAM_INT);
	$parmCurPayPalUserId = new QueryParameter(':paypalUserID', $curPayPalUserID, PDO::PARAM_INT);
	$queryParms = array($parmUserId, $parmCurPayPalUserId);
			
	$updateUserQuery = "UPDATE `Payments.PayPalUsers` SET `IsActive` = 0 WHERE (`FK_User_ID` = :userId) AND (`ID` <> :paypalUserID);";
        
	// Update Payments.PayPalUsers
	if($dataAccess->BuildQuery($updateUserQuery, $queryParms)){
            $updateSuccess = $dataAccess->ExecuteNonQuery();
            $errors = $dataAccess->CheckErrors();
			
            if($updateSuccess && (strlen($errors) == 0)) {
		$logger->LogInfo(sprintf("[Notification Type: %s]  Flagged old PayPal account records as inactive for user ID '%d'.", 
                                        $notificationType, $userId));
            }
	}
		
	return $updateSuccess;
    }
	
    private function UpdateUserPayPalAccountInformation($replyMsg, $dataAccess, $logger, $notificationType, $isFirstPaymentOfThisCycle, $hasLastBillDate, 
                                                        $lastBillDate, $billingInterval, $extendedMembershipDays)
    {
	$updateSuccess = false;
	$queryParms = [];
			
	$varsToSet = "SET `MembershipExpirationDate` = NULL";
	$expDate = "";
	if($replyMsg->UserSubscriptionCancelledImmediate) {
            $expDate = $replyMsg->SubscriptionModifyDate;
	}
	else if($hasLastBillDate) {
            $expDateTime = date_add($lastBillDate, $billingInterval);
            $expDate = $expDateTime->format(DateTime::ATOM);
	}
		
	if(strlen($expDate) > 0) {
            if($replyMsg->UserSubscriptionCancelledPending) {
                // If we are processing a cancel request, update user's membership expiration date by adding any unused billing cycle days from previous cycle(s)
                $varsToSet = "SET `MembershipExpirationDate` = DATE_ADD(:expDate, INTERVAL `ExtendedMembershipDays` DAY)";
                $parmMembershipExpDate = new QueryParameter(':expDate', $expDate, PDO::PARAM_STR);
                array_push($queryParms, $parmMembershipExpDate);
            }
            else if($replyMsg->UserSubscriptionCancelledImmediate) {
                // If we are receiving a notification that the user's paid subscription has ended, apply extended (unused) membership days
                // to user's membership expiration date, if have not already done so on a prior user-requested cancellation
                $varsToSet = ("SET `MembershipExpirationDate` = (CASE WHEN ((`ExtendedMembershipDays` > 0) AND (`IsRecurring` = 1)) " .
                                    "THEN DATE_ADD(:expDate, INTERVAL `ExtendedMembershipDays` DAY) ELSE `MembershipExpirationDate` END), " .
                               "`SubscriptionAmtPaidLastCycle` = 0, " . 
                               "`IsActive` = (CASE WHEN (((`ExtendedMembershipDays` > 0) AND (`IsRecurring` = 1)) OR " .
                                    "(NOW() < :expDate2)) THEN `IsActive` ELSE 0 END)"
                              );
                
                $parmMembershipExpDate = new QueryParameter(':expDate', $expDate, PDO::PARAM_STR);
                $parmMembershipExpDate2 = new QueryParameter(':expDate2', $expDate, PDO::PARAM_STR);
                array_push($queryParms, $parmMembershipExpDate, $parmMembershipExpDate2);
            }
            else {
                $varsToSet = "SET `MembershipExpirationDate` = :expDate";
                $parmMembershipExpDate = new QueryParameter(':expDate', $expDate, PDO::PARAM_STR);
                array_push($queryParms, $parmMembershipExpDate);
            }
	}
		
	if($replyMsg->UserSubscriptionCancelledPending) {
            $varsToSet .= ", `IsRecurring` = 0";
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
            $varsToSet .= ", `IsRecurring` = :isRecurring, `SubscriptionStartedDate` = :subscrStartDate, `ExtendedMembershipDays` = (`ExtendedMembershipDays` + :extMemDays)";
            $parmIsRecurring = new QueryParameter(':isRecurring', ($replyMsg->SubscriptionIsRecurring ? 1 : 0), PDO::PARAM_INT);
            $parmSubscrStartDate = new QueryParameter(':subscrStartDate', $replyMsg->SubscriptionModifyDate, PDO::PARAM_STR);
            $parmExtMemDays = new QueryParameter(':extMemDays', $extendedMembershipDays, PDO::PARAM_INT);
            array_push($queryParms, $parmIsRecurring, $parmSubscrStartDate, $parmExtMemDays);
	}
		
	$curDate = gmdate('Y-m-d H:i:s');
	if(($replyMsg->TxnType == "subscr_payment") && $isFirstPaymentOfThisCycle) {
            $varsToSet .= ", `LastBillDate` = :lastBillDate, `IsActive` = 1";
            $parmLastBillDate = new QueryParameter(':lastBillDate', $curDate, PDO::PARAM_STR);
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
	$parmSubscrId = new QueryParameter(':subscrID', $replyMsg->SubscriptionID, PDO::PARAM_STR);
	array_push($queryParms, $parmUserId, $parmSubscrId);
				
	$updateUserQuery = "UPDATE `Payments.PayPalUsers` " . $varsToSet . " WHERE (`FK_User_ID` = :userId) AND (`SubscriptionID` = :subscrID);";
        
	// Update Payments.PayPalUsers
	if($dataAccess->BuildQuery($updateUserQuery, $queryParms)){
            $updateSuccess = $dataAccess->ExecuteNonQuery();
            $errors = $dataAccess->CheckErrors();
			
            if($updateSuccess && (strlen($errors) == 0)) {
		$logger->LogInfo(sprintf("[Notification Type: %s]  Updated user PayPal account information for user ID '%d'.", 
                                         $notificationType, $replyMsg->UserId));
            }
            else {
                $logger->LogError(sprintf("[Notification Type: %s]  Could not update user PayPal account info for user ID '%d'. %s", 
                                          $notificationType, $replyMsg->UserId, $errors));
            }
	}
	
	return $updateSuccess;
    }
}
