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
	
	<?php if(Constants::$isDebugMode): ?>
            <script src="js/GamerTagViewer.js"></script>
	<?php else: ?>
            <script src="js/GamerTagViewer.min.js"></script>
	<?php endif; ?>
	
	<link rel="stylesheet" href="css/jTable/lightcolor/green/jtable.min.css" />
    </head>
    <body>
	<?php 
            echo $headerHTML;
            $searchPanelTextboxWrapperClass = 'overlayPanelElementContainerCheckboxListSiblingWide';
            if($objUser->IsPremiumMember) {
                $searchPanelTextboxWrapperClass = 'overlayPanelElementContainerCheckboxListSibling';
            }
        ?>
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
                                                        <a href="#" onclick="return DisplayCreateEventDialog();" 
                                                           style="text-decoration:none;font-weight:bold;">Schedule Event</a>
                                                    </li>
                                                    <li class="icon fa-gamepad">
							<a href="#manageEventsDiv" onclick="ToggleControlPanelDisplay(panelEnum.MyEventViewer);" 
                                                           style="text-decoration:none;font-weight:bold;">Manage Your Events</a>
                                                    </li>
                                                    <li class="icon fa-flask">
							<a href="#currentEventsDiv" onclick="ToggleControlPanelDisplay(panelEnum.CurrentEventFeed);" 
                                                           style="text-decoration:none;font-weight:bold;">See Event Feed</a>
                                                    </li>
                                                    <li class="icon fa-bullseye">
							<a id="searchFilterLink" href="#" style="text-decoration:none;font-weight:bold;">Filter Events</a>
                                                    </li>
                                                    <?php if($objUser->IsPremiumMember): ?>
                                                        <li class="icon fa-cogs">
                                                            <a id="manageAccountLink" href="AccountManagement.php" 
                                                               style="text-decoration:none;font-weight:bold;">Manage Your Account</a>
                                                        </li>
                                                    <?php endif; ?>
                                                    <div id="modalOverlay" class="overlayPanelModalBackground"></div>
                                                    <div id="searchPanel" class="overlayPanel overlayPanelOrangeBackground">
                                                        <form name="searchForm" method="POST" action="">
                                                            <div class="overlayPanelFixedHeightScrollableContainer">
								<?php if($objUser->IsPremiumMember): ?>
                                                                    <div class="overlayPanelToggleGroup">
									<div class="overlayPanelToggleLinkWrap">
                                                                            <a href="#" id="dateRangeFilterLink" class="fa fa-plus-square overlayPanelToggleElementInactive" 
                                                                               onclick="return ToggleSearchDivDisplay('#dateRangeFilterDiv', this);">&nbsp;&nbsp;Date Range</a>
									</div>
									<div class="overlayPanelToggleChkboxWrap">
                                                                            <input id="dateRangeFilterActiveToggle" class="overlayPanelToggleActiveChk" linkId="dateRangeFilterLink" 
										groupId="dateRangeFilterDiv" lblId="dateRangeFilterActiveToggleLabel" type="checkbox">
                                                                            <label id="dateRangeFilterActiveToggleLabel" class="overlayPanelToggleActiveLbl">Activate Filter</label>
									</div>
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
								<?php else: ?>
                                                                    <div class="overlayPanelToggleGroup searchPanelCurEvtsFilter searchPanelEvtMgrFilter">
									<div class="overlayPanelToggleLinkWrap">
                                                                            <a href="#" id="dateRangeFilterSelectorLink" class="fa fa-plus-square overlayPanelToggleElementInactive" 
                                                                                onclick="return ToggleSearchDivDisplay('#dateRangeFilterSelectorDiv', this);">&nbsp;&nbsp;Date Range</a>
									</div>
									<div class="overlayPanelToggleChkboxWrap">
                                                                            <input id="dateRangeFilterActiveToggle" class="overlayPanelToggleActiveChk" linkId="dateRangeFilterSelectorLink" 
										groupId="dateRangeFilterSelectorDiv" lblId="dateRangeFilterActiveToggleLabel" type="checkbox">
                                                                            <label id="dateRangeFilterActiveToggleLabel" class="overlayPanelToggleActiveLbl">Activate Filter</label>
									</div>
                                                                    </div>
                                                                    <div id="dateRangeFilterSelectorDiv" class="overlayPanelFilterGroup searchPanelCurEvtsFilter searchPanelEvtMgrFilter">
									<div id="dateRangeFilterSubGroup" class="overlayPanelFilterSubGroup">
                                                                            <div class="overlayPanelElementContainerWide">
										<label class="overlayPanelLabel">See Games Starting From:</label><br />
										<select id="gameFilterDateRangeStart" name="gameFilterDateRangeStart" class="overlayPanelElement">
                                                                                    <option value="0">Now</option>
                                                                                    <option value="1">1 Day Ago</option>
                                                                                    <option value="2">2 Days Ago</option>
                                                                                    <option value="3">3 Days Ago</option>
                                                                                    <option value="7">1 Week Ago</option>
                                                                                    <option value="14">2 Weeks Ago</option>
                                                                                    <option value="30">1 Month Ago</option>
										</select>
                                                                            </div>
                                                                            <div class="overlayPanelElementContainerWide">
										<label class="overlayPanelLabel">See Games Up Through:</label><br />
										<select id="gameFilterDateRangeEnd" name="gameFilterDateRangeEnd" class="overlayPanelElement">
                                                                                    <option value="365">1 Year From Now</option>
                                                                                    <option value="0">Today</option>
                                                                                    <option value="1">Tomorrow</option>
                                                                                    <option value="3">3 Days From Now</option>
                                                                                    <option value="7">1 Week From Now</option>
                                                                                    <option value="14">2 Weeks From Now</option>
                                                                                    <option value="30">1 Month From Now</option>
                                                                                    <option value="61">2 Months From Now</option>
                                                                                    <option value="91">3 Months From Now</option>
                                                                                    <option value="182">6 Months From Now</option>
										</select>
                                                                            </div>
									</div>
                                                                    </div>
								<?php endif; ?>
                                                                <div class="overlayPanelToggleGroup searchPanelCurEvtsFilter searchPanelEvtMgrFilter">
                                                                    <div class="overlayPanelToggleLinkWrap">
                                                                        <a href="#" id="gameTitleFilterLink" class="fa fa-plus-square overlayPanelToggleElementInactive" 
                                                                           onclick="return ToggleSearchDivDisplay('#gameTitleFilterDiv', this);">&nbsp;&nbsp;Game Title</a>
                                                                    </div>
                                                                    <div class="overlayPanelToggleChkboxWrap">
                                                                        <input id="gameTitleFilterActiveToggle" class="overlayPanelToggleActiveChk" linkId="gameTitleFilterLink" 
                                                                               groupId="gameTitleFilterDiv" lblId="gameTitleFilterActiveToggleLabel" type="checkbox">
                                                                        <label id="gameTitleFilterActiveToggleLabel" class="overlayPanelToggleActiveLbl">Activate Filter</label>
                                                                    </div>
                                                                </div>
                                                                <div id="gameTitleFilterDiv" class="overlayPanelFilterGroup searchPanelCurEvtsFilter searchPanelEvtMgrFilter">
                                                                    <div class="overlayPanelFilterSubGroup">
									<?php if($objUser->IsPremiumMember): ?>
                                                                            <div class="overlayPanelElementContainerCheckboxList">
										<label class="overlayPanelLabel">Select Game Titles:</label><br />
										<div class="fixedHeightScrollableContainerLarge">
                                                                                    <?php 
                                                                                        echo $gamingHandler->ConstructGameTitleMultiSelector($dataAccess, $logger);
                                                                                    ?>
                                                                                </div>
                                                                            </div>
									<?php endif; ?>
                                                                        <div class="<?php echo $searchPanelTextboxWrapperClass; ?>">
                                                                            <label class="overlayPanelLabel"><?php if($objUser->IsPremiumMember): ?>Or&nbsp;<?php endif; ?>Enter A Title:</label><br />
                                                                            <input id="gameCustomTitleFilter" class="overlayPanelElement" name="gameCustomTitleFilter" type="text" 
                                                                                   maxlength="50" placeholder=" Custom Title Search">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="overlayPanelToggleGroup searchPanelCurEvtsFilter">
                                                                    <div class="overlayPanelToggleLinkWrap">
                                                                        <a href="#" id="eventCreatorFilterLink" class="fa fa-plus-square overlayPanelToggleElementInactive" 
                                                                           onclick="return ToggleSearchDivDisplay('#eventCreatorFilterDiv', this);">&nbsp;&nbsp;Event Creator</a>
                                                                    </div>
                                                                    <div class="overlayPanelToggleChkboxWrap">
                                                                        <input id="eventCreatorFilterActiveToggle" class="overlayPanelToggleActiveChk" linkId="eventCreatorFilterLink" 
                                                                               groupId="eventCreatorFilterDiv" lblId="eventCreatorFilterActiveToggleLabel" type="checkbox">
                                                                        <label id="eventCreatorFilterActiveToggleLabel" class="overlayPanelToggleActiveLbl">Activate Filter</label>
                                                                    </div>
                                                                </div>
                                                                <div id="eventCreatorFilterDiv" class="overlayPanelFilterGroup searchPanelCurEvtsFilter">
                                                                    <div class="overlayPanelFilterSubGroup">
									<?php if($objUser->IsPremiumMember): ?>
                                                                            <div class="overlayPanelElementContainerCheckboxList">
										<label class="overlayPanelLabel">Select Users:</label><br />
                                                                                <div class="fixedHeightScrollableContainerLarge">
                                                                                    <?php 
                                                                                        echo $gamingHandler->ConstructUserMultiSelector($dataAccess, $logger, "filterActiveUsers[]", $objUser->UserID);
                                                                                    ?>
                                                                                </div>
                                                                            </div>
									<?php endif; ?>
                                                                        <div class="<?php echo $searchPanelTextboxWrapperClass; ?>">
                                                                            <label class="overlayPanelLabel"><?php if($objUser->IsPremiumMember): ?>Or&nbsp;<?php endif; ?>Enter A Username:</label><br />
                                                                            <input id="gameCustomUserFilter" class="overlayPanelElement" name="gameCustomUserFilter" type="text" 
                                                                                   maxlength="50" placeholder=" Custom User Search">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="overlayPanelToggleGroup searchPanelEvtMgrFilter searchPanelCurEvtsFilter">
                                                                    <div class="overlayPanelToggleLinkWrap">
                                                                        <a href="#" id="joinedUserFilterLink" class="fa fa-plus-square overlayPanelToggleElementInactive" 
                                                                           onclick="return ToggleSearchDivDisplay('#joinedUserFilterDiv', this);">&nbsp;&nbsp;Joined Users</a>
                                                                    </div>
                                                                    <div class="overlayPanelToggleChkboxWrap">
                                                                        <input id="joinedUserFilterActiveToggle" class="overlayPanelToggleActiveChk" linkId="joinedUserFilterLink" 
                                                                               groupId="joinedUserFilterDiv" lblId="joinedUserFilterActiveToggleLabel" type="checkbox">
                                                                        <label id="joinedUserFilterActiveToggleLabel" class="overlayPanelToggleActiveLbl">Activate Filter</label>
                                                                    </div>
                                                                </div>
                                                                <div id="joinedUserFilterDiv" class="overlayPanelFilterGroup searchPanelEvtMgrFilter searchPanelCurEvtsFilter">
                                                                    <div class="overlayPanelFilterSubGroup">
									<?php if($objUser->IsPremiumMember): ?>
                                                                            <div class="overlayPanelElementContainerCheckboxList">
										<label class="overlayPanelLabel">Select Users:</label><br />
                                                                                <div class="fixedHeightScrollableContainerLarge">
                                                                                    <?php 
											echo $gamingHandler->ConstructUserMultiSelector($dataAccess, $logger, "filterActiveJoinedUsers[]", $objUser->UserID);
                                                                                    ?>
										</div>
                                                                            </div>
									<?php endif; ?>
                                                                        <div class="<?php echo $searchPanelTextboxWrapperClass; ?>">
                                                                            <label class="overlayPanelLabel"><?php if($objUser->IsPremiumMember): ?>Or&nbsp;<?php endif; ?>Enter A Username:</label><br />
                                                                            <input id="gameCustomJoinedUserFilter" class="overlayPanelElement" name="gameCustomJoinedUserFilter" type="text" 
                                                                                   maxlength="50" placeholder=" Custom User Search">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="overlayPanelToggleGroup searchPanelCurEvtsFilter searchPanelEvtMgrFilter">
                                                                    <div class="overlayPanelToggleLinkWrap">
                                                                        <a href="#" id="platformFilterLink" class="fa fa-plus-square overlayPanelToggleElementInactive" 
                                                                           onclick="return ToggleSearchDivDisplay('#platformFilterDiv', this);">&nbsp;&nbsp;Console Type</a>
                                                                    </div>
                                                                    <div class="overlayPanelToggleChkboxWrap">
                                                                        <input id="platformFilterActiveToggle" class="overlayPanelToggleActiveChk" linkId="platformFilterLink" 
                                                                               groupId="platformFilterDiv" lblId="platformFilterActiveToggleLabel" type="checkbox">
                                                                        <label id="platformFilterActiveToggleLabel" class="overlayPanelToggleActiveLbl">Activate Filter</label>
                                                                    </div>
                                                                </div>
                                                                <div id="platformFilterDiv" class="overlayPanelFilterGroup searchPanelCurEvtsFilter searchPanelEvtMgrFilter">
                                                                    <div class="overlayPanelFilterSubGroup">
									<?php if($objUser->IsPremiumMember): ?>
                                                                            <div class="overlayPanelElementContainerCheckboxList">
										<label class="overlayPanelLabel">Select Consoles:</label><br />
                                                                                <div class="fixedHeightScrollableContainerLarge">
                                                                                    <?php 
											echo $gamingHandler->GetPlatformCheckboxList($dataAccess, [], 'filterPlatforms[]', true);
                                                                                    ?>
										</div>
                                                                            </div>
									<?php endif; ?>
                                                                        <div class="<?php echo $searchPanelTextboxWrapperClass; ?>">
                                                                            <label class="overlayPanelLabel"><?php if($objUser->IsPremiumMember): ?>Or&nbsp;<?php endif; ?>Enter A Console:</label><br />
                                                                            <input id="customPlatformFilter" class="overlayPanelElement" name="customPlatformFilter" type="text" 
                                                                                   maxlength="50" placeholder=" Custom Console Search">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <br />
                                                                <div id="joinStatusFilterDiv" class="overlayPanelFilterGroup overlayPanelGroupBorder">
                                                                    <div class="overlayPanelFilterSubGroup">
                                                                        <div class="overlayPanelElementContainerWide searchPanelEvtMgrFilter">
                                                                            <div><label class="overlayPanelLabel">Show My Joined Events</label></div>
                                                                            <div><input type="checkbox" id="evtStatusJoined" name="evtStatus[]" 
                                                                                   class="overlayPanelElement filterFieldActive" value="showJoined"></div>
                                                                        </div>
                                                                        <div class="overlayPanelElementContainerWide searchPanelEvtMgrFilter">
                                                                            <div><label class="overlayPanelLabel">Show My Created Events</label></div>
                                                                            <div><input type="checkbox" id="evtStatusCreated" name="evtStatus[]" 
                                                                                   class="overlayPanelElement filterFieldActive" value="showCreated"></div>
                                                                        </div>
                                                                        <div class="overlayPanelElementContainerWide searchPanelEvtMgrFilter">
                                                                            <div><label class="overlayPanelLabel">Show Full Events Only</label></div>
                                                                            <div><input type="checkbox" id="evtStatusFull" name="evtStatus[]" 
                                                                                   class="overlayPanelElement filterFieldActive" value="showFull"></div>
                                                                        </div>
                                                                        <div class="overlayPanelElementContainerWide searchPanelCurEvtsFilter">
                                                                            <div><label class="overlayPanelLabel">Show Open Events Only</label></div>
                                                                            <div><input type="checkbox" id="evtStatusOpenOnly" name="evtStatus[]" 
                                                                                   class="overlayPanelElement filterFieldActive" value="openOnly"></div>
                                                                        </div>
                                                                        <div class="overlayPanelElementContainerWide searchPanelEvtMgrFilter">
                                                                            <div><label class="overlayPanelLabel">Show Hidden Events</label></div>
                                                                            <div><input type="checkbox" id="evtStatusHidden" name="evtStatus[]" 
                                                                                   class="overlayPanelElement filterFieldActive" value="showHidden"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="swipeHoverIconContainer"><img id="swipeHoverIcon" src="images/SwipeLeft.png" /></div>
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
				<div id="currentEventsDiv" class="9u jTableContainer">
                                    <section class="box style1">
					<h2>Current Events</h2>
					<span id="totalGamesToJoin" class="byline"></span><br/><br/>
					<div id="currentEventsContent">
                                        </div>
                                    </section><br/>
				</div>
				<div id="manageEventsDiv" class="9u jTableContainer">
                                    <section class="box style1">
                                        <h2>Manage Your Events</h2>
                                        <div class="dashboardTitle">Dashboard</div>
                                        <div class="mobileButtonToolbarContainer">
                                            <button type="button" class="memberHomeBtn" id="btnMobileActivate">
                                                <img src="images/activate.png" />&nbsp;&nbsp;Activate</button>
                                            <button type="button" class="memberHomeBtn" id="btnMobileHide">
                                                <img src="images/deactivate.png" />&nbsp;&nbsp;Hide</button>
                                            <button type="button" class="memberHomeBtn" id="btnMobileLeave">
                                                <img src="images/cancelsignup.png" />&nbsp;&nbsp;Leave</button>
                                            <button type="button" class="memberHomeBtn" id="btnMobileDelete">
                                                <img src="images/delete.png" />&nbsp;&nbsp;Delete</button>
                                            <button type="button" class="memberHomeBtn" onclick="return DisplayCreateEventDialog();" 
                                                    id="btnMobileCreate"><img src="images/calendar.png" />&nbsp;&nbsp;Create</button>
                                            <button type="button" class="memberHomeBtn" id="btnMobileRefresh">
                                                <img src="images/refresh.png" />&nbsp;&nbsp;Refresh</button>
                                        </div>
                                        <div id="manageEventsContent">
                                        </div>
                                    </section>
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
