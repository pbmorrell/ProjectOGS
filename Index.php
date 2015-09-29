<?php
    $mobileLoginPage = false;
    $welcomeUserName = "Sign Up";
    $sessionRequired = false;
    $sessionAllowed = false;
    
    include "Header.php";  
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
                                            <div class="row double">
						<section class="6u">
                                                    <header>
                                                        <h2>Schedule online video gaming events with friends around the world.</h2>
                                                    </header>									
                                                    <p><i class="fa fa-calendar"></i> &nbsp Create gaming events and invite friends.</p>
                                                    <p><i class="fa fa-user"></i> &nbsp Find new friends to connect with and game.</p>
                                                    <p><i class="fa fa-trophy"></i> &nbsp Level up your online gaming experience.</p>
						</section>								
						<section class="6u">
                                                    <header>
                                                        <h2>Sign Up</h2>
                                                    </header>
                                                    <div id="signupFormDiv">
                                                        <form name="signupForm" method="POST" action="">
                                                            <input id="signupEmail" type="text" maxlength="100" placeholder=" Email Address"><span></span><br/>
                                                            <input id="signupPW" type="password" maxlength="50" placeholder=" Password"><span></span>
                                                            <span id="togglePasswordSpan">
                                                                <a href="#" id="togglePassword" 
                                                                   onclick="return togglePasswordField('#togglePassword', '#signupPW', '#signupPW', '#signupPWConfirm', 
												' Password', false);">Show Password</a>
                                                            </span>&nbsp;&nbsp;
                                                            <span id="passwordStrength" class="passwordNone"></span><br/>
                                                            <input id="signupPWConfirm" type="password" maxlength="50" placeholder=" Confirm Password"><span></span>
                                                            <span id="togglePasswordConfirmSpan">
                                                                <a href="#" id="togglePasswordConfirm" 
                                                                   onclick="return togglePasswordField('#togglePasswordConfirm', '#signupPWConfirm', '#signupPW', '#signupPWConfirm', 
												' Confirm Password', true);">Show Password</a>
                                                            </span>&nbsp;&nbsp;
                                                            <span id="passwordMatch" class="passwordWeak"></span><br/><br/>
								<div class="captchaInputDiv">
                                                                    <input type="text" name="captcha_code" id="captcha_code" maxlength="6" class="captchaInput" placeholder="Code" />
								</div>
								<div class="captchaImageDiv">
                                                                    <img id="captcha" src="securimage/securimage_show.php" alt="CAPTCHA Image" />
                                                                    <a href="#" title="Refresh Image"  
                                                                        onclick="document.getElementById('captcha').src = 'securimage/securimage_show.php?' + Math.random(); this.blur(); return false">
									<img src="securimage/images/refresh.png" height="32" width="32" alt="Reload Image" onclick="this.blur()" /></a>
								</div>
								<br/><br/>
                                                            <button type="submit" class="controlBtn button icon fa-cogs" id="signupBtn">Create Free Account</button>
                                                        </form>
                                                    </div>
                                                    <div id="signupErr" class="signupErr" />
						</section>
                                            </div>
                                        </article>
                                        <!-- Lower Article Wrapper -->
					<!-- Lower Article Wrapper -->
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