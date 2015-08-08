<?php
    include_once 'classes/DataAccess.class.php';
    include_once 'classes/User.class.php';
    $database = new DataAccess();    
?>
<!DOCTYPE HTML>
<!--
	Project OGS
	by => Stephen Giles and Paul Morrell
-->
<html>
    <head>
        <meta charset="UTF-8" />
        <title>Project OGS | Sign Up</title>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="description" content="" />
	<meta name="keywords" content="" />

        <!-- For Skel framework -->
        <noscript>
            <link rel="stylesheet" href="css/skel.css" />
            <link rel="stylesheet" href="css/style.css" />
            <link rel="stylesheet" href="css/style-desktop.css" />
        </noscript>
	
	<script src="js/jquery.min.js"></script>
	<script src="js/jquery.dropotron.min.js"></script>
	<script src="js/skel.min.js"></script>
	<script src="js/skel-layers.min.js"></script>
	<script src="js/init.js"></script>
	<script src="js/main.js"></script>
	<script src="js/ajax.js"></script>
	<script src="js/jquery-1.10.2.js"></script>
	<script src="js/jquery-ui-1.10.4.custom.js"></script>
        <script>		
            // JQuery functionality
            $(document).ready(function($) {
                $('#togglePassword').hide();
                $('#togglePasswordConfirm').hide();
					
                $('#signupPW').keyup(function() {
                    evaluateCurrentPWVal('#signupPW', '#signupPWConfirm', '#passwordStrength', '#passwordMatch', '#togglePassword');
                });
                    
                $('#signupPWConfirm').keyup(function() {
                    evaluateCurrentPWConfirmVal('#signupPW', '#signupPWConfirm', '#passwordMatch', '#togglePasswordConfirm');
                });
                    
                $('#signupBtn').click(function() {
                    var email = $('#signupEmail').val();
                    var password = $('#signupPW').val();
                    var captcha = $('#captcha_code').val();
                    var passwordConf = $('#signupPWConfirm').val();
                    
                    return OnSignupButtonClick(email, password, passwordConf, captcha, 'CreateBasicAccount.php', 'EditProfile.php', 
                                               '#signupPW', '#signupPWConfirm', '#captcha_code', '#captcha', '#passwordMatch', 
                                               '#passwordStrength', '#togglePassword', '#togglePasswordConfirm', '#signupErr');
                });
            
                $('#loginBtn').click(function() {
                    $('#loginErr').attr('class', 'preLogin');
                    $('#loginErr').html("Logging In...");
                    $('#loginErr').fadeIn(200);

                    $.ajax({
			type: "POST",
			url: "ExecuteLogin.php",
			data: $('#loginForm').serialize(),
			success: function(response){
                            if(response === 'true') {
				window.location.href = "MemberHome.php";
                            }
                            else {
				$('#loginErr').attr('class', 'loginError');
				$('#loginErr').html(response);
									
				$('#loginPassword').val('');
							
				setTimeout(function() {
                                    $('#loginErr').hide();
                                    }, 3000
                                );
                            }
			}
                    });
							
                    return false;
                });
					
		// Display auth failure redirection message, if present and valid
		<?php
                    if(isset($_GET['redirectMsg'])){
			$onloadPopupJSCode = "alert('" . filter_var($_GET['redirectMsg'], FILTER_SANITIZE_STRING) . "');";
			unset($_GET['redirectMsg']);
			echo "window.history.pushState('Login', '', '/ogs/Login.php');";
			echo $onloadPopupJSCode;
                    }
		?>
            }
        );
        </script>
    </head>
    <body class="">
        <!-- Navigation Wrapper -->
	<div id="header-wrapper">
            <div class="container">
                <div class="row">
                    <div class="12u">
                        <!-- Header -->
                            <header id="header">	
                            <!-- Logo -->
                            <h1>
                                <a href="#" id="logo">Project OGS</a>
                                <div id="login">
                                    <form id="loginForm" name="loginForm" method="POST" action="">
                                        <input id="loginUsername" name="loginUsername" type="text" maxlength="100" placeholder=" Username">
                                        <input id="loginPassword" name="loginPassword" type="password" maxlength="50" placeholder=" Password">
					<button type="submit" class="button icon fa-sign-in" id="loginBtn">Log In</button>&nbsp;
                                    </form>
                                </div>
                            </h1>
                            <!-- Nav -->
                            <nav id="nav" style="display:none;">
                                <ul>
                                    <li><a href="MobileLogin.php">Log In</a></li>
                                </ul>
                            </nav>
                        </header>
                        <div id="loginErr" class="preLogin">&nbsp;</div>
                    </div>
                </div>
            </div>
        </div>
	<!-- Navigation Wrapper -->
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
                                                    <div id="formexp1">
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
                                                            <button type="submit" class="button icon fa-cogs" id="signupBtn">Create Free Account</button>
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