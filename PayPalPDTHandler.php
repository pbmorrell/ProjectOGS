<?php
include_once 'classes/DataAccess.class.php';
include_once 'classes/DBSessionHandler.class.php';
include_once 'classes/PayPalMsgHandler.class.php';
include_once 'classes/Logger.class.php';
include_once 'classes/Constants.class.php';
include_once 'classes/PayPalTxnMsg.class.php';

$dataAccess = new DataAccess();
$loggerDataAccess = new DataAccess();
$logger = new Logger($loggerDataAccess);
$payPalMsgHandler = new PayPalMsgHandler();

// Get txn ID from this notification and send to PayPal to retrieve full txn details
if(isset($_GET['tx'])) {
    $tx = filter_var($_GET['tx'], FILTER_SANITIZE_STRING);
    if(strlen($tx) > 0) {
	$replyMsg = PayPalTxnMsg::ConstructDefaultMsg();
	$url = Constants::$isPayPalTest ? Constants::$payPalTestButtonFormUrl : Constants::$payPalProdButtonFormUrl;
	$identityToken = Constants::$isPayPalTest ? Constants::$payPalTestPostIdentityToken : Constants::$payPalProdPostIdentityToken;
	$status = 500;
	$response = $payPalMsgHandler->SendPDTPostRequest($tx, $url, $status, $identityToken);
		
	if(($status != 200) || (strpos($response, 'SUCCESS') !== 0)) {
            $logger->LogError(sprintf("Received bad PayPal PDT message (status %d): %s", $status, $response));
            $replyMsg->UserMessage = "Error: Unable to retrieve transaction details. Please check again later.";
	}
	else {
            $txnDetailsAssociativeArray = $payPalMsgHandler->FormatPDTResponseInAssociativeArray($response);
            $rawPdtMessage = urldecode($response);
            $notificationType = "PDT";
            $replyMsg = $payPalMsgHandler->HandlePayPalTxnResponse($txnDetailsAssociativeArray, $rawPdtMessage, $dataAccess, $logger, $notificationType);
	}
		
	// Redirect to BecomeMember page to report results to user
        $sessionDataAccess = new DataAccess();
        $sessionHandler = new DBSessionHandler($sessionDataAccess);
        session_set_save_handler($sessionHandler, true);
        session_start();
	$_SESSION["PayPalTxnDetails"] = $replyMsg;
	header("Location: BecomeMember.php");
    }
}
