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
                                                    <div id="modalOverlay" class='overlayPanelModalBackground'></div>
                                                    <div id="searchPanel" class="overlayPanel overlayPanelCurEvts">
                                                        <form name="searchForm" method="POST" action="">
                                                            <div class="overlayPanelFixedHeightScrollableContainer">
                                                                <div class="overlayPanelToggleGroup"><a href="#" id="dateRangeFilterLink" class="fa fa-plus-square overlayPanelToggleElementInactive" 
                                                                        onclick="return ToggleSearchDivDisplay('#dateRangeFilterDiv', this);">&nbsp;&nbsp;Date Range</a>
                                                                    <input id="dateRangeFilterActiveToggle" class="overlayPanelToggleActiveChk" linkId="dateRangeFilterLink" 
								       groupId="dateRangeFilterDiv" lblId="dateRangeFilterActiveToggleLabel" type="checkbox">
                                                                    <label id="dateRangeFilterActiveToggleLabel" class="overlayPanelToggleActiveLbl">Activate Filter</label>
                                                                </div>
                                                                <div id="dateRangeFilterDiv" class="overlayPanelFilterGroup">
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
                                                                </div>
                                                                <div class="overlayPanelToggleGroup searchPanelCurEvtsFilter searchPanelEvtMgrFilter">
                                                                    <a href="#" id="gameTitleFilterLink" class="fa fa-plus-square overlayPanelToggleElementInactive" 
                                                                       onclick="return ToggleSearchDivDisplay('#gameTitleFilterDiv', this);">&nbsp;&nbsp;Game Title</a>
                                                                    <input id="gameTitleFilterActiveToggle" class="overlayPanelToggleActiveChk" linkId="gameTitleFilterLink" 
									   groupId="gameTitleFilterDiv" lblId="gameTitleFilterActiveToggleLabel" type="checkbox">
                                                                    <label id="gameTitleFilterActiveToggleLabel" class="overlayPanelToggleActiveLbl">Activate Filter</label>
                                                                </div>
                                                                <div id="gameTitleFilterDiv" class="overlayPanelFilterGroup searchPanelCurEvtsFilter searchPanelEvtMgrFilter">
                                                                    <div id="gameTitleFilterStart" class="overlayPanelFilterSubGroup">
                                                                        <div class="overlayPanelElementContainerCheckboxList">
                                                                            <label class="overlayPanelLabel">Select Game Titles:</label><br />
                                                                            <div class="fixedHeightScrollableContainerLarge">
                                                                                <?php 
                                                                                   echo $gamingHandler->ConstructGameTitleMultiSelector($dataAccess, $logger);
                                                                                ?>
                                                                            </div>
                                                                        </div>
                                                                        <div class="overlayPanelElementContainerCheckboxListSibling">
                                                                            <label class="overlayPanelLabel">Or Enter A Title:</label><br />
                                                                            <input id="gameCustomTitleFilter" class="overlayPanelElement" name="gameCustomTitleFilter" type="text" 
                                                                                   maxlength="50" placeholder=" Custom Title Search">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="overlayPanelToggleGroup searchPanelCurEvtsFilter">
                                                                    <a href="#" id="eventCreatorFilterLink" class="fa fa-plus-square overlayPanelToggleElementInactive" 
                                                                       onclick="return ToggleSearchDivDisplay('#eventCreatorFilterDiv', this);">&nbsp;&nbsp;Event Creator</a>
                                                                    <input id="eventCreatorFilterActiveToggle" class="overlayPanelToggleActiveChk" linkId="eventCreatorFilterLink" 
									   groupId="eventCreatorFilterDiv" lblId="eventCreatorFilterActiveToggleLabel" type="checkbox">
                                                                    <label id="eventCreatorFilterActiveToggleLabel" class="overlayPanelToggleActiveLbl">Activate Filter</label>
                                                                </div>
                                                                <div id="eventCreatorFilterDiv" class="overlayPanelFilterGroup searchPanelCurEvtsFilter">
                                                                    <div id="eventCreatorFilterStart" class="overlayPanelFilterSubGroup">
                                                                        <div class="overlayPanelElementContainerCheckboxList">
                                                                            <label class="overlayPanelLabel">Select Users:</label><br />
                                                                            <div class="fixedHeightScrollableContainerLarge">
                                                                                <?php 
                                                                                   echo $gamingHandler->ConstructUserMultiSelector($dataAccess, $logger, "filterActiveUsers[]", $objUser->UserID);
                                                                                ?>
                                                                            </div>
                                                                        </div>
                                                                        <div class="overlayPanelElementContainerCheckboxListSibling">
                                                                            <label class="overlayPanelLabel">Or Enter A Username:</label><br />
                                                                            <input id="gameCustomUserFilter" class="overlayPanelElement" name="gameCustomUserFilter" type="text" 
                                                                                   maxlength="50" placeholder=" Custom User Search">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="overlayPanelToggleGroup searchPanelEvtMgrFilter searchPanelCurEvtsFilter">
                                                                    <a href="#" id="joinedUserFilterLink" class="fa fa-plus-square overlayPanelToggleElementInactive" 
                                                                       onclick="return ToggleSearchDivDisplay('#joinedUserFilterDiv', this);">&nbsp;&nbsp;Joined Users</a>
                                                                    <input id="joinedUserFilterActiveToggle" class="overlayPanelToggleActiveChk" linkId="joinedUserFilterLink" 
									   groupId="joinedUserFilterDiv" lblId="joinedUserFilterActiveToggleLabel" type="checkbox">
                                                                    <label id="joinedUserFilterActiveToggleLabel" class="overlayPanelToggleActiveLbl">Activate Filter</label>
                                                                </div>
                                                                <div id="joinedUserFilterDiv" class="overlayPanelFilterGroup searchPanelEvtMgrFilter searchPanelCurEvtsFilter">
                                                                    <div id="joinedUserFilterStart" class="overlayPanelFilterSubGroup">
                                                                        <div class="overlayPanelElementContainerCheckboxList">
                                                                            <label class="overlayPanelLabel">Select Users:</label><br />
                                                                            <div class="fixedHeightScrollableContainerLarge">
                                                                                <?php 
                                                                                   echo $gamingHandler->ConstructUserMultiSelector($dataAccess, $logger, "filterActiveJoinedUsers[]", $objUser->UserID);
                                                                                ?>
                                                                            </div>
                                                                        </div>
                                                                        <div class="overlayPanelElementContainerCheckboxListSibling">
                                                                            <label class="overlayPanelLabel">Or Enter A Username:</label><br />
                                                                            <input id="gameCustomJoinedUserFilter" class="overlayPanelElement" name="gameCustomJoinedUserFilter" type="text" 
                                                                                   maxlength="50" placeholder=" Custom User Search">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="overlayPanelToggleGroup searchPanelCurEvtsFilter searchPanelEvtMgrFilter">
                                                                    <a href="#" id="platformFilterLink" class="fa fa-plus-square overlayPanelToggleElementInactive" 
                                                                       onclick="return ToggleSearchDivDisplay('#platformFilterDiv', this);">&nbsp;&nbsp;Console Type</a>
                                                                    <input id="gameTitleFilterActiveToggle" class="overlayPanelToggleActiveChk" linkId="platformFilterLink" 
									   groupId="platformFilterDiv" lblId="platformFilterActiveToggleLabel" type="checkbox">
                                                                    <label id="platformFilterActiveToggleLabel" class="overlayPanelToggleActiveLbl">Activate Filter</label>
                                                                </div>
                                                                <div id="platformFilterDiv" class="overlayPanelFilterGroup searchPanelCurEvtsFilter searchPanelEvtMgrFilter">
                                                                    <div id="platformFilterStart" class="overlayPanelFilterSubGroup">
                                                                        <div class="overlayPanelElementContainerCheckboxList">
                                                                            <label class="overlayPanelLabel">Select Consoles:</label><br />
                                                                            <div class="fixedHeightScrollableContainerLarge">
                                                                                <?php 
                                                                                   echo $gamingHandler->GetPlatformCheckboxList($dataAccess, [], 'filterPlatforms[]', true);
                                                                                ?>
                                                                            </div>
                                                                        </div>
                                                                        <div class="overlayPanelElementContainerCheckboxListSibling">
                                                                            <label class="overlayPanelLabel">Or Enter A Console:</label><br />
                                                                            <input id="customPlatformFilter" class="overlayPanelElement" name="customPlatformFilter" type="text" 
                                                                                   maxlength="50" placeholder=" Custom Console Search">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            
                                                            </div>
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
