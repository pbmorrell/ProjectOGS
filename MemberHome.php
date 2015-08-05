<?php
    include_once 'classes/DataAccess.class.php';
    include_once 'classes/SecurityHandler.class.php';
    include_once 'classes/DBSessionHandler.class.php';
    include_once 'classes/Logger.class.php';
    include_once 'classes/User.class.php';
    
    $dataAccess = new DataAccess();
    $logger = new Logger($dataAccess);
    $securityHandler = new SecurityHandler();
    
    $sessionDataAccess = new DataAccess();
    $sessionHandler = new DBSessionHandler($sessionDataAccess);
    session_set_save_handler($sessionHandler, true);
    session_start();
    
    $objUser = User::constructDefaultUser();
    // If user not logged in or unauthorized to view this page, redirect to login page
    if($securityHandler->UserCanAccessThisPage($dataAccess, $logger, "MemberHome", "Login.php")) {
        $objUser = $_SESSION['WebUser'];
        $_SESSION['lastActivity'] = time();
	session_write_close();
    }
?>
<html>
    <head>
        <meta charset="UTF-8">
        <?php echo "<title>Project OGS | " . $objUser->FirstName . " " . $objUser->LastName . "</title>"; ?>
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
                            <?php echo "<h1><a href='#' id='logo'>Project OGS | " . ((strlen($objUser->FirstName) > 0) ? ($objUser->FirstName . " " . $objUser->LastName) : $objUser->EmailAddress) . "</a></h1>"; ?>
                            <!-- Nav -->
                            <nav id="nav">
                                <ul>
                                    <li>
                                        <?php echo "<a href='' class='arrow'>" . ((strlen($objUser->UserName) > 0) ? $objUser->UserName : $objUser->EmailAddress) . "</a>"; ?>
                                        <ul>
                                            <li><a href="#">Become a Premium Member</a></li>
                                            <li><a href="EditProfile.php">Edit Profile</a></li>
                                            <li><a href="#">Find Friends</a></li>
                                            <li><a href="ExecuteLogout.php">Log Out</a></li>
                                        </ul>
                                    </li>
                                    <li>
                                        <a href="" class="arrow">Menu</a>
                                        <ul>
                                            <li><a href="#">Recent News</a></li>
                                            <li><a href="#">Developer Blog</a></li>
                                            <li><a href="#">About Us</a></li>
                                            <li><a href="#">Contact Us</a></li>
                                        </ul>
                                    </li>
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
                                <div class="3u">						
                                    <!-- Content -->
                                    <div id="content">							
                                        <section class="box ctaz">
                                            <section>
						<h2>Control Panel</h2>
						<ul class="style3 contact">
                                                    <li class="icon fa-calendar">
							<a href="" target="" style="text-decoration:none;">Schedule Event</a>
                                                    </li>
                                                    <li class="icon fa-flask">
							<a href="" target="" style="text-decoration:none;">View My Events</a>
                                                    </li><br/><br/>										
                                                    <input type="text" id="search" placeholder=" Search Events"/><br/><br/>
                                                    <a href="" style="text-decoration:none;">Filters</a><br/>
                                                    <input type="checkbox" name="platform" value="PS4"/>PS4<br/>
                                                    <input type="checkbox" name="platform" value="PS3"/>PS3<br/>
                                                    <input type="checkbox" name="platform" value="X1"/>Xbox One<br/>
                                                    <input type="checkbox" name="platform" value="X360"/>Xbox 360<br/>
                                                    <input type="checkbox" name="platform" value="PC"/>PC<br/>
                                                    <input type="checkbox" name="platform" value="Mac"/>Mac<br/><br/>										
                                                    <button class="button icon fa-cogs" id="signupbtn">Start Search</button>										
						</ul>
                                            </section>
					</section>												
					<!-- <article class="box style2">
                                            <header>
                                                <h2>My Scheduled Events</h2>
                                                <span class="byline">(2) Scheduled</span>
                                            </header>
                                            <p><strong>Game:</strong> Destiny<br/><strong>Platform:</strong> PS4<br/><strong>Time:</strong> 10:00 pm edt<br/><strong>Date:</strong> June 6th 2015</p>
                                            <h3>Notes:</h3>
                                            <p>Looking to put a team together for the Vault of Glass Raid: Need 5 players to join. Must have a mic!</p>
                                            <h3>Players currently signed up:</h3>
                                            <p>SteGiles01<br/>
                                            PBMorrell<br/>
                                            Wil0791</p>
                                            <footer>
                                                <a href="#" class="button icon fa-wrench">Edit Event</a>
                                            </footer><br/><br/>
                                            <p><strong>Game:</strong> Mortal Kombat X<br/><strong>Platform:</strong> Xbox One<br/><strong>Time:</strong> 9:15 pm edt<br/><strong>Date:</strong> June 14th 2015</p>
                                            <h3>Notes:</h3>
                                            <p>Looking for some friends to battle against in the Faction Mode. I'll start a private lobby and send out invites to all members.</p>
                                            <h3>Players currently signed up:</h3>
                                            <p>SteGiles01<br/>
                                            wrecks123<br/>
                                            XxColeWxX<br/>
                                            PerfectShot</p>
                                            <footer>
                                                <a href="#" class="button icon fa-wrench">Edit Event</a>
                                            </footer><br/>
					</article> -->				
                                    </div>
				</div>				
				<div class="9u">					
                                    <!-- Sidebar 1 -->
                                    <div id="content">
                                        <section class="box style1">
                                            <h2>Current Events</h2>
                                            <span class="byline">(4,387) Need Joining!</span><br/><br/>
                                            <article>
                                                <p><strong>Game:</strong> Diablo III: Reaper of Souls<br/><strong>Platform:</strong> Mac<br/><strong>Time:</strong> 10:00 am gmt <br/><strong>Date:</strong> June 4th 2015<br/></p>
						<h3>Notes:</h3>
						<p>I would like to play the main campaign. Two players needed. This is my first play through, so please be patience.</p>
						<h3>Players currently signed up:</h3>
						<p>AlexTheGreat</p>
                                                <a href="#" class="button icon fa-sign-in">Sign Up</a>
                                            </article>
                                            <article>
						<p><strong>Game:</strong> Grand Theft Auto V<br/><strong>Platform:</strong>  PC<br/><strong>Time:</strong> 3:23 pm pst <br/><strong>Date:</strong> June 4th 2015</p>
						<h3>Notes:</h3>
						<p>Need three players to help with a heist! Must be experienced.</p>
						<h3>Players currently signed up:</h3>
						<p>DarthMaximus298<br/>
						WhoShotYa092</p>
						<a href="#" class="button icon fa-sign-in">Sign Up</a>
                                            </article>
                                            <article>
                                                <p><strong>Game:</strong> Splatoon<br/><strong>Platform:</strong>  Wii U<br/><strong>Time:</strong> 10:00 am gmt <br/><strong>Date:</strong> June 4th 2015</p>
						<h3>Notes:</h3>
						<p>Looking for players to play against. Please no foul language, kids will be in the same room.</p>
						<h3>Players currently signed up:</h3>
						<p>AsianMan0111xx</p>
						<a href="#" class="button icon fa-sign-in">Sign Up</a>
                                            </article><br/><br/>
                                            <a href="#" class="button icon fa-file-o">Load More</a>
					</section><br/>
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