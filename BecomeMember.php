<?php
    $mobileLoginPage = false;
    $sessionRequired = true;
    $sessionAllowed = true;
    include "Header.php";
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
            $action = $objUser->IsPremiumMember ? "renew" : "join";
			
            // PayPal config settings
            $payPalButtonFormUrl                = Constants::$isPayPalTest ? Constants::$payPalTestButtonFormUrl                : Constants::$payPalProdButtonFormUrl;
            $payPalMakeSubscriptionButtonId     = Constants::$isPayPalTest ? Constants::$payPalTestMakeSubscriptionButtonId  	: Constants::$payPalProdMakeSubscriptionButtonId;
            $payPalRenewSubscriptionButtonId    = Constants::$isPayPalTest ? Constants::$payPalTestRenewSubscriptionButtonId 	: Constants::$payPalProdRenewSubscriptionButtonId;
            $payPalSubscribeButtonImgUrl 	= Constants::$isPayPalTest ? Constants::$payPalTestSubscribeButtonImgUrl 	: Constants::$payPalProdSubscribeButtonImgUrl;
            $payPalPixelImgUrl 			= Constants::$isPayPalTest ? Constants::$payPalTestPixelImgUrl 			: Constants::$payPalProdPixelImgUrl;
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
                                    <!-- About -->
                                    <div id="content">
					<article class="box style1">
                                            <?php if(!$objUser->IsPremiumMember): ?>
                                                <h2>Become a Member</h2>
                                                <p>By upgrading your free account to a membership, you are helping us cover our hosting cost and paving a way for future expansions of the site!</p>
                                                <br />
                                                <h2>What is included in the membership?</h2>
                                                <ul><li class="icon fa-search">&nbsp;Additional search tools for quickly pinpointing the information you need</li></ul>
                                                <ul><li class="icon fa-user">&nbsp;A dedicated friends section that allows you to see other users information.</li></ul>
                                                <ul><li class="icon fa-flask">&nbsp;The ability to keep your events open to only your friends, keeping random users out.</li></ul>
                                                <article>
                                                    <form action="<?php echo $payPalButtonFormUrl; ?>" method="post" target="_top">
                                                        <input type="hidden" name="cmd" value="_s-xclick">
                                                        <input type="hidden" name="hosted_button_id" value="<?php echo $payPalMakeSubscriptionButtonId; ?>">
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
                                                <h2>Renew Your Membership</h2>
                                                <p>By renewing your membership, you are helping us cover our hosting cost and paving a way for future expansions of the site!</p>
                                                <br />
                                                <article>
                                                    <form action="<?php echo $payPalButtonFormUrl; ?>" method="post" target="_top">
                                                        <input type="hidden" name="cmd" value="_s-xclick">
                                                        <input type="hidden" name="hosted_button_id" value="<?php echo $payPalRenewSubscriptionButtonId; ?>">
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
