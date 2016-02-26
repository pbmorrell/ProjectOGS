<?php
    $mobileLoginPage = false;
    $sessionRequired = true;
    $sessionAllowed = true;
    $customSessionVarsToRetrieve = array("PayPalTxnDetails");
    include "Header.php";
    include_once "classes/PayPalMsgHandler.class.php";
    include_once "classes/PayPalUser.class.php";

    // If redirected to this page from PDTHandler after error, set error message
    $errMsg = "";
    if(isset($_GET['error'])) {
	if($_GET['error'] == "PDTError") {
            $errMsg = "There was a problem retrieving your completed transaction details -- please log out, then log in again, to check your current membership status. ";
	}
    }
	
    // Retrieve PayPal transaction details, if redirected to this page from a PayPal message handler
    $replyMsg = PayPalTxnMsg::ConstructDefaultMsg();
    $payPalUser = PayPalUser::constructDefaultPayPalUser();
    $numDaysTillNextCycle = 0;
    $isRecurringArticleClass = "";
    $extendArticleClass = "";
    
    if((isset($customSessionVars)) && (count($customSessionVars) > 0)) {
	// Get transaction details
	$replyMsg = $customSessionVars["PayPalTxnDetails"];
        $replyMsg->SubscriptionIsRecurring = true; // If new signup, we can assume subscription is recurring
        $extendArticleClass = "hidden";
		
	// Look up PayPal user info for this user
	$payPalMsgHandler = new PayPalMsgHandler();
	$payPalUser = $payPalMsgHandler->LookUpPayPalUserByUserId($dataAccess, $logger, $objUser->UserID);
			
	// If transaction details message is available, need to update related session variables
        $sessionDataAccess = new DataAccess();
        $sessionHandler = new DBSessionHandler($sessionDataAccess);
        session_set_save_handler($sessionHandler, true);
        session_start();
		
	// Remove transaction details from session storage, so that they are only shown immediately after user is returned to this page from PayPal
	unset($_SESSION['PayPalTxnDetails']);
	session_write_close();
    }
    else if($objUser->IsPremiumMember) {
	// If this page not being accessed right after completed PayPal txn, look up this user's current subscription type
	$payPalMsgHandler = new PayPalMsgHandler();
	$payPalUser = $payPalMsgHandler->LookUpPayPalUserByUserId($dataAccess, $logger, $objUser->UserID);
	$replyMsg->SelectedSubscriptionOption = $payPalUser->SubscriptionType;
	$replyMsg->SubscriptionIsRecurring = $payPalUser->IsRecurring;
        
        if($replyMsg->SubscriptionIsRecurring)  $extendArticleClass = "hidden";
        else                                    $isRecurringArticleClass = "hidden";
    }
?>
<!DOCTYPE HTML>
<!--
    Project OGS
    by => Stephen Giles and Paul Morrell
-->
<html>
    <head>
        <?php 
            echo $pageHeaderHTML;
            $action = $objUser->IsPremiumMember ? "upgrade" : "join";
			
            // PayPal config settings
            $payPalButtonFormUrl                = Constants::$isPayPalTest ? Constants::$payPalTestButtonFormUrl                : Constants::$payPalProdButtonFormUrl;
            $payPalMakeSubscriptionButtonId     = Constants::$isPayPalTest ? Constants::$payPalTestMakeSubscriptionButtonId  	: Constants::$payPalProdMakeSubscriptionButtonId;
            $payPalSubscribeButtonImgUrl 	= Constants::$isPayPalTest ? Constants::$payPalTestSubscribeButtonImgUrl 	: Constants::$payPalProdSubscribeButtonImgUrl;
            $payPalPixelImgUrl 			= Constants::$isPayPalTest ? Constants::$payPalTestPixelImgUrl                  : Constants::$payPalProdPixelImgUrl;
            $payPalUnsubscribeButtonImgUrl      = Constants::$isPayPalTest ? Constants::$payPalTestUnsubscribeButtonImgUrl 	: Constants::$payPalProdUnsubscribeButtonImgUrl;
        ?>
        <script src="js/moment.min.js"></script>
        <script src="js/moment-timezone-with-data.min.js"></script>
    </head>
    <body class="">
        <?php echo $headerHTML; ?>
        <!-- Main Wrapper -->
	<div id="main-wrapper">
            <div class="container">
		<div class="row">
                    <div class="12u">
			<div id="main">
                            <div class="row">
				<div class="12u">
                                    <div id="content">
					<?php if(strlen($replyMsg->TxnId) > 0): ?>
                                            <article  class="box style1">
						<h2>Transaction Details</h2>
						<?php if($replyMsg->IsValidated): ?>
                                                    <p style="color:#00dd92; font-weight: 700;"><?php echo $replyMsg->UserMessage; ?></p>
						<?php else: ?>
                                                    <p style="color:#FC4753; font-weight: 700;"><?php echo $errMsg . $replyMsg->UserMessage; ?></p>
						<?php endif; ?>
                                                <div id="txnDetailsTable" class="detailsTable">
                                                    <div class="detailsTableRow">
							<div class="detailsTableCol">Transaction ID:</div>
							<div class="detailsTableCol"><?php echo $replyMsg->TxnId; ?></div>
                                                    </div>
                                                    <div class="detailsTableRow">
							<div class="detailsTableCol">Transaction Type:</div>
							<div class="detailsTableCol"><?php echo (($replyMsg->PDTOperation == 'SubscribePremium') ? 'New Subscription' : 'Update To Existing Subscription'); ?></div>
                                                    </div>
                                                    <div class="detailsTableRow">
                                                        <div class="detailsTableCol">Transaction Amount:</div>
                                                        <div class="detailsTableCol"><?php echo '$' . $replyMsg->SubscriptionAmtPaid; ?></div>
                                                    </div>
                                                    <div class="detailsTableRow">
                                                        <div class="detailsTableCol">Payment Status:</div>
                                                        <div class="detailsTableCol"><?php echo $replyMsg->PaymentStatus; ?></div>
                                                    </div>
                                                    <?php if(strlen($replyMsg->PaymentPendingReason) > 0): ?>
                                                        <div class="detailsTableRow">
                                                            <div class="detailsTableCol">Pending Reason:</div>
                                                            <div class="detailsTableCol"><?php echo $replyMsg->PaymentPendingReason; ?></div>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="detailsTableRow">
                                                        <div class="detailsTableCol">Date Effective:</div>
                                                        <div id="subscrDateColumn" class="detailsTableCol"><?php echo ((strlen($replyMsg->SubscriptionModifyDate) > 0) ? $replyMsg->SubscriptionModifyDate : 'N/A'); ?></div>
                                                    </div>
                                                    <div class="detailsTableRow">
                                                        <div class="detailsTableCol">PayPal Payer ID:</div>
                                                        <div class="detailsTableCol"><?php echo $replyMsg->PayerId; ?></div>
                                                    </div>
                                                </div>
                                            </article>
                                        <?php endif; ?>
					<?php if($objUser->IsPremiumMember): ?>
                                            <article  class="box style1">
						<h2>Current Membership Status</h2>
						<h3><div style="color:#00dd92;">You are a premium member!</div></h3>
						<p style="font-weight: 700;">
                                                    <div>
                                                        Current billing cycle:&nbsp;
                                                        <span style="font-style:italic;">
                                                            <?php echo  '$' . Constants::$subscriptionOptionNames[$replyMsg->SelectedSubscriptionOption] . 
                                                                        ' ' . $replyMsg->SelectedSubscriptionOption; ?>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <?php if($replyMsg->SubscriptionIsRecurring): ?>
                                                            <label id="expDateLabel">Next automatic bill date:</label>&nbsp;
                                                            <span id="expDateSpan" style="font-style:italic;">
                                                                <?php echo $payPalUser->MembershipExpDate; ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <label id="expDateLabel">Your membership will expire on:</label>&nbsp;
                                                            <span id="expDateSpan" style="font-style:italic;">
                                                                <?php echo $payPalUser->MembershipExpDate; ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
						</p>
                                            </article>
                                            <article id="memberActionsArticleRecurring" class="box style1 <?php echo $isRecurringArticleClass; ?>">
                                                <h2>Cancel Your Membership</h2>
                                                <div style="padding-top: 1em;">
                                                    <a id="btnCancelMembership" href="#">
                                                        <img src="<?php echo $payPalUnsubscribeButtonImgUrl; ?>" border="0" width="125" height="25">
                                                    </a>
                                                </div>
                                            </article>
                                            <article id="memberActionsArticleExtend" class="box style1 <?php echo $extendArticleClass; ?>">
                                                <h2>Extend Your Membership</h2>
                                                <p>Your premium membership will only be active until the end of the current cycle. Click&nbsp;&nbsp;<span style="font-weight: 700;">Subscribe</span>
                                                    &nbsp;to extend your membership, and don't miss a beat!</p>
                                                <article>
                                                    <h3>Recurring Membership Plan: Only $6.50 per month!</h3>
                                                    <p>When you re-subscribe, your new billing cycle will begin immediately, but the unused days from your current cycle will be credited to your account if you should cancel again.</p>
                                                    <form action="<?php echo $payPalButtonFormUrl; ?>" method="post" target="_top">
                                                        <input type="hidden" name="cmd" value="_s-xclick">
                                                        <input type="hidden" name="hosted_button_id" value="<?php echo $payPalMakeSubscriptionButtonId; ?>">
                                                        <input type="hidden" name="custom" value="<?php echo ($objUser->UserID . "|SubscribePremium"); ?>">
                                                        <input style="width: 150px; background:#0066CC;" type="image" src="<?php echo $payPalSubscribeButtonImgUrl; ?>" border="0" name="submit" height="30" 
                                                               alt="PayPal - The safer, easier way to pay online!" id="btnSubscribe" onclick="return SubscribeOnClick('<?php echo $action; ?>');">
                                                        <img alt="" border="0" src="<?php echo $payPalPixelImgUrl; ?>" width="1" height="1">
                                                    </form>
                                                </article>
                                            </article>
					<?php else: ?>
                                            <article class="box style1">
                                                <h2>Become a Member</h2>
                                                <p>By upgrading your free account to a membership, you are helping us cover our hosting cost and paving a way for future expansions of the site!</p>
                                                <br />
                                                <h2>What is included in the membership?</h2>
                                                <ul><li class="icon fa-search">&nbsp;&nbsp;Enhanced search tools for quickly pinpointing the information you need</li></ul>
                                                <ul><li class="icon fa-user">&nbsp;&nbsp;A dedicated friends section that allows you to create a closed network of friends.</li></ul>
                                                <ul><li class="icon fa-flask">&nbsp;&nbsp;The ability to keep your events open to only your friends, keeping random users out.</li></ul>
						<br />
                                                <article>
                                                    <h2>Recurring Membership Plan: Only $6.50 per month!</h2>
                                                    <form action="<?php echo $payPalButtonFormUrl; ?>" method="post" target="_top">
                                                        <input type="hidden" name="cmd" value="_s-xclick">
                                                        <input type="hidden" name="hosted_button_id" value="<?php echo $payPalMakeSubscriptionButtonId; ?>">
                                                        <input type="hidden" name="custom" value="<?php echo ($objUser->UserID . "|SubscribePremium"); ?>">
                                                        <input style="width: 150px; background:#0066CC;" type="image" src="<?php echo $payPalSubscribeButtonImgUrl; ?>" border="0" name="submit" height="30" 
                                                               alt="PayPal - The safer, easier way to pay online!" id="btnSubscribe" onclick="return SubscribeOnClick('<?php echo $action; ?>');">
                                                        <img alt="" border="0" src="<?php echo $payPalPixelImgUrl; ?>" width="1" height="1">
                                                    </form>
                                                </article>
                                            </article>
                                        <?php endif; ?>
                                    </div>
				</div>
                            </div>
                        </div>
                    </div>
		</div>
            </div>
	</div>
        <!-- Footer Wrapper -->
        <?php include("Footer.php"); ?>
        <!-- Footer Wrapper -->
    </body>
</html>
