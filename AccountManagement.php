<?php
    $mobileLoginPage = false;
    $sessionRequired = true;
    $sessionAllowed = true;
    $customSessionVarsToRetrieve = array("PayPalTxnDetails");
    include "Header.php";

    // Retrieve PayPal transaction details, if redirected to this page from a PayPal message handler
    $replyMsg = PayPalTxnMsg::ConstructDefaultMsg();
    if((isset($customSessionVars)) && (count($customSessionVars) > 0)) {
	$replyMsg = $customSessionVars["PayPalTxnDetails"];
		
	// If transaction details message is available, need to update related session variables
        $sessionDataAccess = new DataAccess();
        $sessionHandler = new DBSessionHandler($sessionDataAccess);
        session_set_save_handler($sessionHandler, true);
        session_start();
		
	// Update user membership status in session
	$curMembershipStatus = ($replyMsg->UserUpgradedPremium || $replyMsg->UserSubscriptionRenewed || $replyMsg->UserSubscriptionCancelledPending);
	if($objUser->IsPremiumMember != $curMembershipStatus) {
            $objUser->IsPremiumMember = $curMembershipStatus;
            $_SESSION['WebUser'] = $objUser;
	}
	
	// Convert SubscriptionStartedTime from UTC to user's local timezone
	if(strlen($replyMsg->SubscriptionModifyDate) > 0) {
            $utcTimestamp = strtotime($replyMsg->SubscriptionModifyDate . ' UTC');
            $replyMsg->SubscriptionModifyDate = date("Y-m-d H:i:s", $utcTimestamp);
	}
		
	// Remove transaction details from session storage, so that they are only shown immediately after user is returned to this page from PayPal
	unset($_SESSION['PayPalTxnDetails']);
		
	session_write_close();
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
            $payPalChangeSubscriptionButtonId   = Constants::$isPayPalTest ? Constants::$payPalTestChangeSubscriptionButtonId 	: Constants::$payPalProdChangeSubscriptionButtonId;
            $payPalSubscribeButtonImgUrl 	= Constants::$isPayPalTest ? Constants::$payPalTestSubscribeButtonImgUrl 	: Constants::$payPalProdSubscribeButtonImgUrl;
            $payPalPixelImgUrl 			= Constants::$isPayPalTest ? Constants::$payPalTestPixelImgUrl                  : Constants::$payPalProdPixelImgUrl;
            //$payPalCancelSubscriptionUrl        = Constants::$isPayPalTest ? Constants::$payPalTestCancelSubscriptionUrl 	: Constants::$payPalProdCancelSubscriptionUrl;
            $payPalUnsubscribeButtonImgUrl      = Constants::$isPayPalTest ? Constants::$payPalTestUnsubscribeButtonImgUrl 	: Constants::$payPalProdUnsubscribeButtonImgUrl;
        ?>
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
						<p style="color:red;"><?php echo $replyMsg->UserMessage; ?></p>
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
                                                        <div class="detailsTableCol"><?php echo ((strlen($replyMsg->SubscriptionModifyDate) > 0) ? $replyMsg->SubscriptionModifyDate : 'N/A'); ?></div>
                                                    </div>
                                                    <div class="detailsTableRow">
                                                        <div class="detailsTableCol">PayPal Payer ID:</div>
                                                        <div class="detailsTableCol"><?php echo $replyMsg->PayerId; ?></div>
                                                    </div>
                                                </div>
                                            </article>
                                        <?php endif; ?>
                                        <article class="box style1">
                                            <?php if(!$objUser->IsPremiumMember): ?>
                                                <h2>Become a Member</h2>
                                                <p>By upgrading your free account to a membership, you are helping us cover our hosting cost and paving a way for future expansions of the site!</p>
                                                <br />
                                                <h2>What is included in the membership?</h2>
                                                <ul><li class="icon fa-search">&nbsp;&nbsp;Additional search tools for quickly pinpointing the information you need</li></ul>
                                                <ul><li class="icon fa-user">&nbsp;&nbsp;A dedicated friends section that allows you to see other users information.</li></ul>
                                                <ul><li class="icon fa-flask">&nbsp;&nbsp;The ability to keep your events open to only your friends, keeping random users out.</li></ul>
                                                <article>
                                                    <form action="<?php echo $payPalButtonFormUrl; ?>" method="post" target="_top">
                                                        <input type="hidden" name="cmd" value="_s-xclick">
                                                        <input type="hidden" name="hosted_button_id" value="<?php echo $payPalMakeSubscriptionButtonId; ?>">
                                                        <input type="hidden" name="custom" value="<?php echo ($objUser->UserID . "|SubscribePremium"); ?>">
                                                        <table>
                                                            <tr><td><input type="hidden" name="on0" value="Membership Plans">Membership Plans</td></tr>
                                                            <tr>
                                                                <td>
                                                                    <select name="os0">
                                                                        <option value="Month-by-month">Month-by-month : $3.95 USD - monthly</option>
                                                                        <option value="Full Year">Full Year : $39.95 USD - yearly</option>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                        <input type="hidden" name="currency_code" value="USD">
                                                        <input style="width: 150px; background:#0066CC;" type="image" src="<?php echo $payPalSubscribeButtonImgUrl; ?>" border="0" name="submit" height="30" 
                                                               alt="PayPal - The safer, easier way to pay online!" id="btnSubscribe" onclick="return SubscribeOnClick('<?php echo $action; ?>');">
                                                        <img alt="" border="0" src="<?php echo $payPalPixelImgUrl; ?>" width="1" height="1">
                                                    </form>
                                                </article>
                                            <?php else: ?>
                                                <h2>Update Your Membership Plan</h2>
                                                <p>Choose a membership plan with the billing cycle that best fits your needs!</p>
                                                <article class="box style1" style="padding-top: 1em;">
                                                    <form action="<?php echo $payPalButtonFormUrl; ?>" method="post" target="_top">
                                                        <input type="hidden" name="cmd" value="_s-xclick">
                                                        <input type="hidden" name="hosted_button_id" value="<?php echo $payPalChangeSubscriptionButtonId; ?>">
                                                        <input type="hidden" name="custom" value="<?php echo ($objUser->UserID . "|RenewSubscription"); ?>">
                                                        <table>
                                                            <tr><td style="font-weight: bold;"<input type="hidden" name="on0" value="Membership Plans">Choose A New Membership Plan<br /></td></tr>
                                                            <tr>
                                                                <td>
                                                                    <select name="os0">
                                                                        <option value="Month-by-month">Month-by-month : $3.95 USD - monthly</option>
                                                                        <option value="Full Year">Full Year : $39.95 USD - yearly</option>
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                        <input type="hidden" name="currency_code" value="USD">
                                                        <input style="width: 150px; background:#0066CC;" type="image" src="<?php echo $payPalSubscribeButtonImgUrl; ?>" border="0" name="submit" height="30" 
                                                               alt="PayPal - The safer, easier way to pay online!" id="btnSubscribe" onclick="return SubscribeOnClick('<?php echo $action; ?>');">
                                                        <img alt="" border="0" src="<?php echo $payPalPixelImgUrl; ?>" width="1" height="1">
                                                    </form>
                                                </article>
                                                <article class="box style1" style="padding-top: 1em; padding-bottom: 1.5em !important;">
                                                    <div style="font-weight: bold;">Cancel Your Membership</div><br />
                                                    <div><a id="btnCancelMembership" href="#">
                                                        <img src="<?php echo $payPalUnsubscribeButtonImgUrl; ?>" border="0" width="125" height="25">
                                                    </a></div>
                                                </article>
                                            <?php endif; ?>
					</article>
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
