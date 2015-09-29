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
                                <div id="manageEventsDiv" class="9u">
                                    <section class="box style1">
                                        <h2>Manage Your Events</h2>
                                        <label id="mobileEventsTableToolbar" class="mobileToolbarContainer hidden">
                                            <button class="controlBtn button" id="refreshEventsBtn" >
                                                <img style="margin-right:5px" src="images/refresh.png">Refresh
                                            </button>
                                            <button class="controlBtn button" id="activateEventsBtn" >
                                                <img style="margin-right:5px" src="images/activate.png">Activate
                                            </button>
                                            <button class="controlBtn button" id="hideEventsBtn" >
                                                <img style="margin-right:5px" src="images/deactivate.png">Hide
                                            </button>
                                            <button class="controlBtn button" id="deleteEventsBtn" >
                                                <img style="margin-right:5px" src="images/delete.png">Delete
                                            </button>
                                        </label>
                                        <span id="toolbarSpacer" class="hidden">
                                            <br /><br /><br /><br /><br /><br /><br /><br />
                                        </span>
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
                                <div id="currentEventsDiv" class="9u">					
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
