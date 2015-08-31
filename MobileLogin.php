<?php
    $curPageName = "MobileLogin";
    $mobileLoginPage = true;
    $welcomeUserName = "Log In";
    $sessionRequired = false;
    $sessionAllowed = false;
    include "Header.php";
?>
<!DOCTYPE HTML>
<html>
    <head>
        <?php echo $pageHeaderHTML; ?>
        <script>
            // JQuery functionality
            $(document).ready(function($) {
                displayHiddenAdsByBrowsingDevice();
                MobileLoginOnReady();
                
		// Display auth failure redirection message, if present and valid
		<?php echo $onloadPopupJSCode; ?>
            });
        </script>
    </head>
    <body class="">
        <?php echo $headerHTML; ?>
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
                                                            <form id="mobileLoginForm" name="mobileLoginForm" method="POST" action="">
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
        <?php include 'Footer.php'; ?>
        <!-- Footer Wrapper -->
    </body>
</html>