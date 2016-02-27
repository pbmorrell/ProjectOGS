<?php
include_once 'classes/DataAccess.class.php';
include_once 'classes/PayPalMsgHandler.class.php';
include_once 'classes/Logger.class.php';
include_once 'classes/Constants.class.php';

$dataAccess = new DataAccess();
$loggerDataAccess = new DataAccess();
$logger = new Logger($loggerDataAccess);
$payPalMsgHandler = new PayPalMsgHandler();

$ipnData = $_POST;
$status = 500;
$url = Constants::$payPalProdButtonFormUrl;
if($payPalMsgHandler->GetResponseValByKey($ipnData, 'test_ipn') == "1") {
    $url = Constants::$payPalTestButtonFormUrl;
}

// Debugging: log what we received
//$logger->LogInfo("IPN Received. Post Data: " . $payPalMsgHandler->ConvertIPNPostDataToRawString($ipnData));

// For all instant notifications, must acknowledge as per PayPal requirement by sending a POST request containing the same data back to PayPal, 
//  but only need to take further action for subscription creation, subscription renewal, and subscription cancellation.
$response = $payPalMsgHandler->SendIPNPostRequest($ipnData, $url, $status);

if(($status == 200) && ($response == "VERIFIED")) {
    // If this notification really came from PayPal, proceed to handle it and execute any necessary actions
    $rawIpnMessage = $payPalMsgHandler->ConvertIPNPostDataToRawString($ipnData);
    $notificationType = "IPN";
    $payPalMsgHandler->HandlePayPalTxnResponse($ipnData, $rawIpnMessage, $dataAccess, $logger, $notificationType);
}
else {
    $txnId = "";
    if(isset($ipnData["txn_id"]))  $txnId = $ipnData["txn_id"];
    $logger->LogError(sprintf("Received bad PayPal IPN message. Status=[%d]; Response=[%s]; txn ID=[%s]", $status, $response, $txnId));
}