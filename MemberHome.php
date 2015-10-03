<?php
    $mobileLoginPage = false;
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
	<script src="js/jquery.slidereveal.min.js"></script>
	<link rel="stylesheet" href="css/jTable/lightcolor/green/jtable.min.css" />
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
							<a href="#" onclick="return DisplayCreateEventDialog();" style="text-decoration:none;">Schedule Event</a>
                                                    </li>
                                                    <li class="icon fa-gamepad">
							<a href="#manageEventsDiv" onclick="ToggleControlPanelDisplay(panelEnum.MyEventViewer, 0);" style="text-decoration:none;">Manage Your Events</a>
                                                    </li>
                                                    <li class="icon fa-flask">
							<a href="#currentEventsDiv" onclick="ToggleControlPanelDisplay(panelEnum.CurrentEventFeed, 0);" style="text-decoration:none;">See Event Feed</a>
                                                    </li>
                                                    <li class="icon fa-bullseye">
							<a id="searchFilterLink" href="#" style="text-decoration:none;">Filter Results</a>
                                                    </li>
                                                    <div id="searchPanel" class="overlayPanel">
							<input type="text" id="search" placeholder=" Search Events" class="overlayPanelElement" /><br/><br/>
							<button class="controlBtn button icon fa-cogs overlayPanelElement" id="searchBtn">Search</button>
							<button class="controlBtn button overlayPanelElement" id="closePanelBtn">Close</button>
                                                    </div>
						</ul>
                                            </section>
					</section>				
                                    </div>
				</div>
				<div id="manageEventsDiv" class="9u jTableContainer">
                                    <section class="box style1">
					<h2>Manage Your Events</h2>
					<label style="float:right;">
                                            <label><input type="checkbox" id="toggleHiddenEvents" />Show Hidden Events</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            <label>
                                                <select id="toggleShowPastEvents">
                                                    <option value="-1">No</option>
                                                    <option value="15">15 days</option>
                                                    <option value="30">30 days</option>
                                                </select>&nbsp;Show Past Events</label>
					</label>
					<br /><br />
					<div id="manageEventsContent">
					</div>
                                    </section>
				</div>
				<div id="currentEventsDiv" class="9u jTableContainer">					
                                    <section class="box style1">
					<h2>Current Events</h2>
					<span id="totalGamesToJoin" class="byline"></span><br/><br/>
					<div id="currentEventsContent">
					</div>
                                    </section><br/>
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
