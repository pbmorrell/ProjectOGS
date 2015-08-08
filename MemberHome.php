<?php
    $curPageName = "MemberHome";
    $authFailureRedirectPage = "Login.php";
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
        <script>
            // JQuery functionality
            $(document).ready(function($) {
                displayHiddenAdsByBrowsingDevice();
                MemberHomeOnReady();
            });
        </script>
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
        <?php include 'Footer.php'; ?>
        <!-- Footer Wrapper -->
    </body>
</html>