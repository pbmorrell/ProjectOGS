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
	<script src="js/pStrength.jquery.js"></script>
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
                                                        <h2>The simplest "looking for group" site on the web - and it's free.</h2>
                                                    </header><br/><br/>								
                                                    <p><i id="colorCalendar" class="fa fa-calendar"></i>&nbsp; Easily create public or friends-only gaming events.</p><br/>
                                                    <p><i id="colorUser" class="fa fa-user"></i>&nbsp; Team up with new allies, or smack down new victims.</p><br/>
                                                    <p><i id="colorTrophy" class="fa fa-trophy"></i>&nbsp; Never be stuck looking for online players again.</p><br/>
						</section>
						<section class="6u">
                                                    <header>
                                                        <h2>Sign Up</h2>
                                                    </header>
                                                    <div id="signupFormDiv" class="signupInp">
                                                        <form name="signupForm" method="POST" action="">
                                                            <input id="signupEmail" type="text" maxlength="100" placeholder=" Email Address"><span></span><br/>
                                                            <input id="signupPW" type="password" maxlength="50" placeholder=" Password" data-display="passwordStrength"><span></span>
                                                            <span id="togglePasswordSpan">
                                                                <a href="#" id="togglePassword" 
                                                                   onclick="return togglePasswordField('#togglePassword', '#signupPW', '#signupPW', '#signupPWConfirm', 
                                                                                                       ' Password', false, 'passwordStrength');">Show Text</a>
                                                            </span>&nbsp;&nbsp;
                                                            <span id="passwordStrength"></span><br/>
                                                            <input id="signupPWConfirm" type="password" maxlength="50" placeholder=" Confirm Password"><span></span>
                                                            <span id="togglePasswordConfirmSpan">
                                                                <a href="#" id="togglePasswordConfirm" 
                                                                   onclick="return togglePasswordField('#togglePasswordConfirm', '#signupPWConfirm', '#signupPW', '#signupPWConfirm', 
                                                                                                       ' Confirm Password', true);">Show Text</a>
                                                            </span>&nbsp;&nbsp;
                                                            <img id="passwordMatch" src="images/green_checkmark.gif" /><br/><br/>
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
                                                            <button type="submit" class="controlBtn controlBtnIndex button icon fa-cogs" id="signupBtn">Create Free Account</button>
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
