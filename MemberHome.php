<?php
    $curPageName = "MemberHome";
    $mobileLoginPage = false;
    $authFailureRedirectPage = "Index.php";
    $sessionRequired = true;
    $sessionAllowed = true;
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
        <script src="js/moment.min.js"></script>
        <script src="js/moment-timezone-with-data.min.js"></script>
	<script src="js/jTable/jquery.jtable.min.js"></script>
	<link rel="stylesheet" href="css/jTable/lightcolor/green/jtable.min.css" />
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
                                <div class="controlPanelSection">						
                                    <!-- Content -->
                                    <div id="content">							
                                        <section class="box ctaz">
                                                <section>
                                                        <h2>Control Panel</h2>
                                                        <ul class="style3 contact">
                                                            <li class="icon fa-calendar">
                                                                <a href="#scheduleEventDiv" target="" onclick="ToggleControlPanelDisplay(panelEnum.EventScheduler, 0);" style="text-decoration:none;">Schedule Event</a>
                                                            </li>
                                                            <li class="icon fa-gamepad">
                                                                <a href="#manageEventsDiv" target="" onclick="ToggleControlPanelDisplay(panelEnum.MyEventViewer, 0);" style="text-decoration:none;">Manage My Events</a>
                                                            </li>
                                                            <li class="icon fa-flask">
                                                                <a href="#currentEventsDiv" target="" onclick="ToggleControlPanelDisplay(panelEnum.CurrentEventFeed, 0);" style="text-decoration:none;">Event Feed</a>
                                                            </li><br/><br/>
                                                            <input type="text" id="search" placeholder=" Search Events"/><br/><br/>
                                                            <a href="" style="text-decoration:none;">Filters</a><br/>
                                                            <input type="checkbox" name="platform" value="PS4"/>PS4<br/>
                                                            <input type="checkbox" name="platform" value="PS3"/>PS3<br/>
                                                            <input type="checkbox" name="platform" value="X1"/>Xbox One<br/>
                                                            <input type="checkbox" name="platform" value="X360"/>Xbox 360<br/>
                                                            <input type="checkbox" name="platform" value="PC"/>PC<br/>
                                                            <input type="checkbox" name="platform" value="Mac"/>Mac<br/><br/>										
                                                            <button class="controlBtn button icon fa-cogs" id="searchBtn">Start Search</button>
                                                        </ul>
                                                </section>
                                        </section>				
                                    </div>
				</div>
				<div id="scheduleEventDiv" class="eventManagerSection">
                                    <section class="box style1">
                                        <h2>
                                            Schedule an Event
                                            <div id="scheduleEventSubmitDiv" class="memberHomeSubmitDiv">
                                                <button type="submit" class="memberHomeBtn icon fa-cogs" id="createEventBtn">Create Event!</button>
                                            </div>
                                        </h2>
                                        <div id="scheduleEventContent">
                                        </div>
                                    </section>
				</div>
				<div id="manageEventsDiv" class="9u">
                                    <section class="box style1">
                                        <h2>Manage Your Events</h2>
                                        <label style="float:right;"><input type="checkbox" id="toggleHiddenEvents" />Show Hidden Events</label><br />
                                        <div id="manageEventsContent">
                                        </div>
                                    </section>
                                </div>
				<div id="currentEventsDiv" class="9u">					
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