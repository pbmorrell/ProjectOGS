<?php
    include_once 'classes/DataAccess.class.php';
    include_once 'classes/SecurityHandler.class.php';
    include_once 'classes/DBSessionHandler.class.php';
    include_once 'classes/Logger.class.php';
    
    $dataAccess = new DataAccess();
    $logger = new Logger($dataAccess);
    $securityHandler = new SecurityHandler();
    $userSecLevel = -1;
    
    $sessionHandler = new DBSessionHandler($dataAccess, $logger);
    session_set_save_handler($sessionHandler, true);
    session_start();
    
    // Ensure user is logged in
    $redirectToLogin = true;
    if(isset($_SESSION['userID'])) {
        $userSecLevel = $_SESSION['userSecurityLevel'];
        
        // Check that user is authorized to view this page
        if($securityHandler->UserAuthorizedForThisPage($dataAccess, $logger, "Signup", $userSecLevel)) {
            // For authenticated, authorized users: set welcome text and allow access to this page
            $userName = $_SESSION['userName'];
            $welcomeHeaderText = "Welcome " . $userName;
            $redirectToLogin = false;
        }
    }
    
    if($redirectToLogin == true) {
        // Redirect to Login page if user not logged in or authorized for this page
        session_write_close();
        header("HTTP/1.1 401 Unauthorized");
        header("Location: Login.php");
        exit();
    }
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>Project OGS | Welcome</title>
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
        <noscript>
            <link rel="stylesheet" href="css/skel.css" />
            <link rel="stylesheet" href="css/style.css" />
            <link rel="stylesheet" href="css/style-desktop.css" />
        </noscript>
        <!--[if lte IE 8]><link rel="stylesheet" href="css/ie/v8.css" /><![endif]-->
        <script>
            $(function(){
                $('#datepicker').datepicker({
                    inline: true,
                    changeYear: true,
                    showButtonPanel: true,
                    showOtherMonths: true,
                    dayNamesMin: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
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
                            <h1><a href="#" id="logo">Project OGS</a></h1>				
                            <!-- Nav -->
                            <nav id="nav">
                                <ul>
                                    <li><a href="">Log Out</a></li>
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
                                            <header>
                                                <?php echo "<h2>" . $welcomeHeaderText . "</h2>"; ?>
						<p>Let's begin by setting up your profile. It only takes a moment!</p>
                                            </header><br/>
                                            <div class="row double">
						<section class="3u">
                                                    <p><i class="fa fa-user"></i> &nbsp What is your gender?</p>
                                                    <input type="radio" name="gender" value="F">Female <br/>
                                                    <input type="radio" name="gender" value="M">Male<br/><br/>
                                                    <p><i class="fa fa-birthday-cake"></i> &nbsp When were you born?</p>
                                                    <input type="text" id ="datepicker" name="dob"><br/><br/>
						</section>
						<section class="3u">
                                                    <p><i class="fa fa-gamepad"></i> &nbsp Which console(s) do you game on?</p>
                                                    <?php
                                                        $errors = $dataAccess->CheckErrors();
                                                        $ddlPlatformsHTML = "";
                                                        $ddlPlatformsErrorHTML = "<p>Cannot load console list, please try later</p>";

                                                        if(strlen($errors) == 0) {
                                                            $platformQuery = "SELECT `ID`, `Name` FROM `Configuration.Platforms` ORDER BY `Name`;";
                                                            if($dataAccess->BuildQuery($platformQuery)){
                                                                $results = $dataAccess->GetResultSet();
                                                                if($results != null){
                                                                    foreach($results as $row){
                                                                        $ddlPlatformsHTML = $ddlPlatformsHTML . "<input type='checkbox' value='" . $row['ID'] . "'>" . $row['Name'] . "</input><br/>";
                                                                    }
                                                                }
                                                            }
                                                        }

                                                        $errors = $dataAccess->CheckErrors();
                                                        if(strlen($errors) == 0) {
                                                            echo $ddlPlatformsHTML;
                                                        }
                                                        else { 
                                                            echo $ddlPlatformsErrorHTML;
                                                        }
                                                    ?>
						</section>
						<section class="6u">
                                                    <p><i class="fa fa-clock-o"></i> &nbsp What is your time zone?</p>
                                                    <?php
                                                        $errors = $dataAccess->CheckErrors();
                                                        $ddlTimeZonesHTML = "";
                                                        $ddlTimeZonesErrorHTML = "<select name='ddltimeZones'><option value='-1'>Cannot load time zones, please try later</option></select><br/><br/>";
																	
                                                        if(strlen($errors) == 0) {
                                                            $timeZoneQuery = "SELECT `ID`, `Abbreviation` FROM `Configuration.TimeZones` ORDER BY `SortOrder`;";
                                                            if($dataAccess->BuildQuery($timeZoneQuery)){
                                                                $results = $dataAccess->GetResultSet();
                                                                if($results != null){
                                                                    $ddlTimeZonesHTML = $ddlTimeZonesHTML . "<select name='ddltimeZones'>";
                                                                    foreach($results as $row){
                                                                        $ddlTimeZonesHTML = $ddlTimeZonesHTML . "<option value='" . $row['ID'] . "'>" . $row['Abbreviation'] . "</option>";
                                                                    }
                                                                    $ddlTimeZonesHTML = $ddlTimeZonesHTML . "</select><br/><br/>";
                                                                }
                                                            }
                                                        }
																	
                                                        $errors = $dataAccess->CheckErrors();
                                                        if(strlen($errors) == 0) {
                                                            echo $ddlTimeZonesHTML;
                                                        }
                                                        else { 
                                                            echo $ddlTimeZonesErrorHTML;
                                                        }
                                                    ?>
                                                    <p><i class="fa fa-comment"></i> &nbsp Tell us about yourself.</p>
                                                    <textarea name="message" id="message" placeholder="This is your bio! What games are your favorite? When do you do most of your online gaming? etc.." rows="6" required></textarea><br/><br/>
                                                    <button class="button icon fa-wrench" id="signupbtn">Update Profile</button>
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