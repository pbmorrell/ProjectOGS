<?php
    $mobileLoginPage = false;
    $sessionRequired = false;
    $sessionAllowed = false;
    
    include "Header.php";
    
    $sessId = '';
    $userId = -1;
    if(isset($_GET['sessID'])) {
        $sessId = filter_var(trim($_GET['sessID']), FILTER_SANITIZE_STRING);
        $securityHandler = new SecurityHandler();
        $dataAccess = new DataAccess();
        $logger = new Logger($dataAccess);
        $userId = $securityHandler->LookupPasswordRecoverySession($dataAccess, $logger, $sessId);
    }
    
    $headerMessage = "<h2>Sorry, Your Password Reset Session Has Expired.</h2><h3>Please Click 'Forgot Password' To Make A New Session.</h3>";
    if($userId > -1)  $headerMessage = "<h2>Choose Your New Password</h2>";
?>
<!DOCTYPE HTML>
<!--
	Project OGS
	by => Stephen Giles and Paul Morrell
-->
<html>
    <head>
        <?php echo $pageHeaderHTML; ?>
    </head>
    <body class="">
        <!-- Navigation Wrapper -->
	<?php echo $headerHTML; ?>
	<!-- Main Wrapper -->
	<div id="main-wrapper">
            <div class="container">
                <div class="row">
                    <div class="12u">
                        <div id="main">
                            <div class="row">
                                <div class="12u">
                                    <!-- Content -->
                                    <div id="content">
                                        <article class="box main">
                                            <div class="row single">
						<section class="12u">
                                                    <header>
                                                        <?php echo $headerMessage; ?>
                                                    </header><br/>
                                                    <a href="#" class="actionLinkHeader" id="forgotPasswordLinkMobile">Forgot Password?</a>
                                                    <?php if($userId > -1): ?>
                                                        <div id="resetPasswordFormDiv" class="signupInp">
                                                            <form name="resetPasswordForm" method="POST" action="">
                                                                <?php echo '<input id="userId" type="hidden" value="' . $userId . '" />'; ?>
                                                                <input id="newPW" type="password" maxlength="50" placeholder=" New Password"><span></span>&nbsp;
                                                                <span id="togglePasswordSpan">
                                                                    <a href="#" id="togglePassword" 
                                                                       onclick="return togglePasswordField('#togglePassword', '#newPW', '#newPW', '#newPWConfirm', 
                                                                                                           ' New Password', false);">Show Password</a>
                                                                </span>&nbsp;&nbsp;
                                                                <span id="passwordStrength" class="passwordNone"></span><br/><br/>
                                                                <input id="newPWConfirm" type="password" maxlength="50" placeholder=" Confirm New Password"><span></span>&nbsp;
                                                                <span id="togglePasswordConfirmSpan">
                                                                    <a href="#" id="togglePasswordConfirm" 
                                                                       onclick="return togglePasswordField('#togglePasswordConfirm', '#newPWConfirm', '#newPW', '#newPWConfirm', 
                                                                                                           ' Confirm New Password', true);">Show Password</a>
                                                                </span>&nbsp;&nbsp;
                                                                <span id="passwordMatch" class="passwordWeak"></span><br/><br/>
                                                                <button type="submit" class="controlBtn controlBtnIndex button icon fa-cogs" id="resetPwdBtn">Reset Password</button>
                                                            </form>
                                                        </div>
                                                    <?php endif; ?>
						</section>
                                            </div>
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
        <?php include 'Footer.php'; ?>
        <!-- Footer Wrapper -->
    </body>
</html>
