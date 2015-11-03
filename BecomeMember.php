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
                                            <?php else: ?>
                                                <h2>Renew or Upgrade Your Membership</h2>
                                                <p>By renewing or upgrading membership, you are helping us cover our hosting cost and paving a way for future expansions of the site!</p>
                                                <br />
                                            <?php endif; ?>
                                            <article>
                                                <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
                                                    <input type="hidden" name="cmd" value="_s-xclick">
                                                    <input type="hidden" name="hosted_button_id" value="9H4QGFRL3F7HS">
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
                                                    <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_subscribeCC_LG.gif" border="0" name="submit"
                                                            alt="PayPal - The safer, easier way to pay online!" id="btnSubscribe" 
                                                            onclick="return SubscribeOnClick('<?php echo $action; ?>');">
                                                    <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
                                                </form>
                                            </article>
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
