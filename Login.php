<?php
    include_once 'classes/DataAccess.class.php';
    $database = new DataAccess();    
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>Project OGS | Sign Up</title>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<!--[if lte IE 8]><script src="css/ie/html5shiv.js"></script><![endif]-->
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
                
                    $('#signupPW').keyup(function() {
                         var strongRegex = new RegExp("^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\\W).*$", "g");
                         var mediumRegex = new RegExp("^(?=.{7,})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*$", "g");
                         var enoughRegex = new RegExp("(?=.{6,}).*", "g");
                         
                         if($(this).val().length === 0) {
                             $('#passwordStrength').attr('class', 'passwordNone');
                             $('#passwordStrength').html('');
                             $('#passwordMatch').html('');
                         } else if (false === enoughRegex.test($(this).val())) {
                                 $('#passwordStrength').attr('class', 'passwordWeak');
                                 $('#passwordStrength').html('More Characters');
                         } else if (strongRegex.test($(this).val())) {
                                 $('#passwordStrength').attr('class', 'passwordStrong');
                                 $('#passwordStrength').html('Strong!');
                         } else if (mediumRegex.test($(this).val())) {
                                 $('#passwordStrength').attr('class', 'passwordOK');
                                 $('#passwordStrength').html('Medium');
                         } else {
                                 $('#passwordStrength').attr('class', 'passwordWeak');
                                 $('#passwordStrength').html('Weak');
                         }
                         return true;
                    });
                    
                    $('#signupPWConfirm').keyup(function() {
                        if(($(this).val().length === 0) && 
                           ($('#signupPW').val().length === 0)) {
                            $('#passwordMatch').html('');
                        } else if($(this).val() !== $('#signupPW').val()) {
                            $('#passwordMatch').attr('class', 'passwordWeak');
                            $('#passwordMatch').html('Passwords do not match');
                        } else {
                            $('#passwordMatch').attr('class', 'passwordStrong');
                            $('#passwordMatch').html('Passwords match!');
                        }
                    });
                    
                    $('#signupBtn').click(function() {
                            var username = $('#signupUsername').val();
                            var password = $('#signupPW').val();
                            var email = $('#signupEmail').val();
                            
                            $.ajax({
                               type: "POST",
                               url: "CreateBasicAccount.php",
                               data: "signupUsername=" + username + "&signupPW=" + password + "&signupEmail=" + email,
                               success: function(response){
                                   if(response === 'true') {
                                       window.location.href = "Signup.php";
                                   }
                                   else {
                                       $('#signupPW').val('');
                                       $('#signupPWConfirm').val('');
                                       $('#passwordMatch').html('');
                                       $('#passwordStrength').html('');
                                       $('#signupErr').html(response);
                                   }
                               }
                            });
                            
                            return false;
                        }
                    );
            
                    $('#loginBtn').click(function() {
                            var username = $('#loginUsername').val();
                            var password = $('#loginPassword').val();
                            //alert('Logging in...');
                            $.ajax({
                               type: "POST",
                               url: "ExecuteLogin.php",
                               data: "loginUsername=" + username + "&loginPassword=" + password,
                               success: function(response){
                                   if(response === 'true') {
                                       window.location.href = "MemberHome.php";
                                   }
                                   else {
                                       $('#loginPassword').val('');
                                       $('#loginErr').html(response);
                                   }
                               }
                            });
                            
                            return false;
                        }
                    );
                }
            );
        </script>
	<noscript>
            <link rel="stylesheet" href="css/skel.css" />
            <link rel="stylesheet" href="css/style.css" />
            <link rel="stylesheet" href="css/style-desktop.css" />
            <!--<link rel="stylesheet" href="css/strength.css" />-->
	</noscript>
	<!--[if lte IE 8]><link rel="stylesheet" href="css/ie/v8.css" /><![endif]-->
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
                                    <form name="loginForm" method="POST" action="">
                                        <input id="loginUsername" type="text" maxlength="50" placeholder=" Username">
                                        <input id="loginPassword" type="password" maxlength="50" placeholder=" Password">
                                        <button type="submit" class="button icon fa-sign-in" id="loginBtn">Log In</button>&nbsp;
                                    </form>
                                </div>
                            </h1>
                            <!-- Nav -->
                            <nav id="nav" style="display:none;">
                                <ul>
                                    <li><a href="">Log In</a></li>
                                </ul>
                            </nav>
                        </header>
                        <div id="loginErr" class="loginErrStyle">&nbsp;</div>
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
                                                            <input id="signupUsername" type="text" maxlength="50" placeholder=" Username"><span></span><br/>
                                                            <input id="signupEmail" type="text" maxlength="50" placeholder=" Email Address"><span></span><br/>
                                                            <input id="signupPW" type="password" maxlength="50" placeholder=" Password"><span></span>
                                                            <span id="passwordStrength" class="passwordNone"></span><br/>
                                                            <input id="signupPWConfirm" type="password" maxlength="50" placeholder=" Confirm Password"><span></span>
                                                            <span id="passwordMatch" class="passwordWeak"></span><br/><br/>
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
	<div class="container">
            <div class="row">
                <div class="12u">
                    <!-- Footer -->
                    <!-- <footer id="footer">
                        <div class="row">
                            <!-- <div class="6u">								
                                <section>
                                    <h2>Membership</h2>
                                    <p>Want additional features? Tired of seeing ads? For just a dollar a month you can become a premium member.</p>
                                    <a href="#" class="button icon fa-sign-in">Sign Up!</a>
				</section>						
                            </div> 
                            <div class="6u">							
                                <section>
                                    <h2>Quick Links</h2>
                                    <ul class="style3">
                                        <li>
                                            <a href="" target="" style="text-decoration:none;">Recent News</a>
                                        </li>
                                        <li>
                                            <a href="" target="" style="text-decoration:none;">Developer Blog</a>
                                        </li>
                                        <li>
                                            <a href="" target="" style="text-decoration:none;">About Us</a>
                                        </li>
                                    </ul>
                                </section>							
                            </div>
                            <div class="6u">							
                                <section>
                                    <h2>Contact Us</h2>
                                    <ul class="contact">
                                        <li class="icon fa-envelope">
                                            <a href="" target="" style="text-decoration:none;">Email</a>
                                        </li>
                                        <li class="icon fa-youtube">
                                            <a href="" target="" style="text-decoration:none;">YouTube</a>
                                        </li>
                                        <li class="icon fa-twitch">
                                            <a href="" target="" style="text-decoration:none;">Twitch</a>
                                        </li>
                                    </ul>
                                </section>							
                            </div>
                        </div>
                    </footer> -->
                    <!-- Copyright -->
                    <div id="copyright">
                        &copy; <script>document.write(new Date().getFullYear());</script> Project OGS<br/> All Rights Reserved.<br/>
                        <a href="" target="" style="text-decoration:none;">Developed by<br/>Stephen Giles and Paul Morrell</a>&nbsp<i class="fa fa-cogs"></i>
                    </div>				
                </div>
            </div>
        </div>			
    <!-- Footer Wrapper -->
    </body>
</html>