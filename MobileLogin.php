<?php
    include_once 'classes/DataAccess.class.php';
    $database = new DataAccess();    
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>Project OGS | Log In</title>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	
        <!-- For Skel framework -->
	<noscript>
            <link rel="stylesheet" href="css/skel.css" />
            <link rel="stylesheet" href="css/style.css" />
            <link rel="stylesheet" href="css/style-desktop.css" />
	</noscript>
	
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
            
                $('#loginBtn').click(function() {
                    $('#loginErr').attr('class', 'preLogin');
                    $('#loginErr').html("Logging In...");
                    $('#loginErr').fadeIn(200);

                    $.ajax({
                        type: "POST",
                        url: "ExecuteLogin.php",
                        data: $('#mobileLoginForm').serialize(),
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
                                    }, 
                                    3000
                                );
                            }
                        }
                    });
                            
                    return false;
                });
            });
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
                            </h1>
                            <!-- Nav -->
                            <nav id="nav" style="display:none;">
                                <ul>
                                    <li><a href="Login.php">Sign Up</a></li>
                                </ul>
                            </nav>
                        </header>
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
                                                        <h2>Log In</h2>
                                                        <div id="mobileLogin">
                                                            <form name="mobileLoginForm" method="POST" action="">
                                                                <input id="loginUsername" name="loginUsername" type="text" maxlength="50" placeholder=" Username"><br/>
                                                                <input id="loginPassword" name="loginPassword" type="password" maxlength="50" placeholder=" Password"><br/><br/>
                                                                <button type="submit" class="button icon fa-sign-in" id="mobileLoginBtn">Log In</button>&nbsp;
                                                            </form>
                                                        </div>
                                                        <div id="loginErr" class="preLogin"></div>
                                                    </header>
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