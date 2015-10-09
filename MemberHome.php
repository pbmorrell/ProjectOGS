<?php
    $mobileLoginPage = false;
    $sessionRequired = true;
    $sessionAllowed = true;
    include "Header.php";
    $gamingHandler = new GamingHandler();
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
        <script src="js/sweetalert.min.js"></script>
        <link rel="stylesheet" type="text/css" href="css/sweetalert.css" />
	<link rel="stylesheet" href="css/jTable/lightcolor/green/jtable.min.css" />
    </head>
    <body>
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
							<a id="searchFilterLink" href="#" style="text-decoration:none;">Filter Events</a>
                                                    </li>
                                                    <div id="searchPanel" class="overlayPanel overlayPanelCurEvts">
                                                        <form name="searchForm" method="POST" action="">
                                                            <div class="overlayPanelToggleGroup"><a href="#" id="dateRangeFilterLink" class="fa fa-plus-square overlayPanelToggleElementInactive" 
                                                                    onclick="return ToggleSearchDivDisplay('#dateRangeFilterDiv', this);">&nbsp;&nbsp;Date Range</a>
								<input id="dateRangeFilterActiveToggle" class="overlayPanelToggleActiveChk" linkId="dateRangeFilterLink" groupId="dateRangeFilterDiv" type="checkbox">
								<label class="overlayPanelToggleActiveLbl">Activate Filter</label>
                                                            </div><br />
                                                            <div id="dateRangeFilterDiv" style="display:none" class="overlayPanelFilterGroup">
                                                                <div id="dateRangeFilterStart" class="overlayPanelFilterSubGroup">
                                                                    <div class="overlayPanelElementContainer">
                                                                        <label class="overlayPanelLabel">Start Date:</label><br />
                                                                        <input id="gameFilterStartDate" class="overlayPanelElement" name="gameFilterStartDate" type="text" 
                                                                               maxlength="50" placeholder=" Start Date">
                                                                    </div>
                                                                    <div class="overlayPanelElementContainer">
                                                                        <label class="overlayPanelLabel">Start Time:</label><br />
                                                                        <input id="gameFilterStartTime" class="overlayPanelElement" name="gameFilterStartTime" type="text" 
                                                                               maxlength="9" placeholder=" Start Time">
                                                                    </div>
                                                                    <div class="overlayPanelElementContainer">
                                                                        <label class="overlayPanelLabel">Time Zone:</label><br />
                                                                         <?php 
                                                                            echo $gamingHandler->GetTimezoneList($dataAccess, -1, 'Start'); 
                                                                         ?>
                                                                    </div>
                                                                </div><br />
                                                                <div id="dateRangeFilterEnd" class="overlayPanelFilterSubGroup">
                                                                    <div class="overlayPanelElementContainer">
                                                                        <label class="overlayPanelLabel">End Date:</label><br />
                                                                        <input id="gameFilterEndDate" class="overlayPanelElement" name="gameFilterEndDate" type="text" 
                                                                               maxlength="50" placeholder=" End Date">
                                                                    </div>
                                                                    <div class="overlayPanelElementContainer">
                                                                        <label class="overlayPanelLabel">End Time:</label><br />
                                                                        <input id="gameFilterEndTime" class="overlayPanelElement" name="gameFilterEndTime" type="text" 
                                                                               maxlength="9" placeholder=" End Time">
                                                                    </div>
                                                                    <div class="overlayPanelElementContainer">
                                                                        <label class="overlayPanelLabel">Time Zone:</label><br />
                                                                         <?php 
                                                                            echo $gamingHandler->GetTimezoneList($dataAccess, -1, 'End'); 
                                                                         ?>
                                                                    </div>
                                                                </div>
                                                            </div><br />
                                                            <div class="overlayPanelToggleGroup searchPanelCurEvtsFilter searchPanelEvtMgrFilter">
																<a href="#" id="gameTitleFilterLink" class="fa fa-plus-square overlayPanelToggleElementInactive" 
                                                                   onclick="return ToggleSearchDivDisplay('#gameTitleFilterDiv', this);">&nbsp;&nbsp;Game Title</a>
																<input id="gameTitleFilterActiveToggle" class="overlayPanelToggleActiveChk" linkId="gameTitleFilterLink" groupId="gameTitleFilterDiv" type="checkbox">
																<label class="overlayPanelToggleActiveLbl">Activate Filter</label>
                                                            </div><br />
                                                            <div id="gameTitleFilterDiv" style="display:none" class="overlayPanelFilterGroup searchPanelCurEvtsFilter searchPanelEvtMgrFilter">
                                                                <div id="gameTitleFilterStart" class="overlayPanelFilterSubGroup">
                                                                    <div class="overlayPanelElementContainerWide">
                                                                        <label class="overlayPanelLabel">Existing Game Titles:</label><br />
                                                                        <div class="fixedHeightScrollableContainerLarge">
                                                                            <?php 
                                                                               echo $gamingHandler->ConstructGameTitleMultiSelector($dataAccess, $logger);
                                                                            ?>
                                                                        </div>
                                                                    </div>
                                                                    <div class="overlayPanelElementContainerWide">
                                                                        <label class="overlayPanelLabel">Or Enter A Title:</label><br />
                                                                        <input id="gameCustomTitleFilter" class="overlayPanelElement" name="gameCustomTitleFilter" type="text" 
                                                                               maxlength="50" placeholder=" Custom Title Search">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <br /><br />
                                                            <div class="overlayPanelControlElementGroup">
                                                                <button class="controlBtn button icon fa-cogs overlayPanelControlElement" id="searchBtn">Search</button>
                                                                <button class="controlBtn button overlayPanelControlElement" id="closePanelBtn">Close</button>
                                                            </div>
                                                        </form>
                                                        <br />
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
