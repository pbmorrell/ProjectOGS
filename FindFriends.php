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
        <?php echo $headerHTML; ?>
        <!-- Main Wrapper -->
		<div id="main-wrapper">
            <div class="container">
		<div class="row">
                    <div class="12u">
			<div id="main">
                            <div class="row">
				<div class="controlPanelSection">
                                    <div id="content">
					<section class="box ctaz">
                                            <section>
                                                <h2>Control Panel</h2>
						<ul class="style3 contact">
                                                    <li class="icon fa-search-plus">
							<a href="#searchForFriendsDiv" onclick="ToggleControlPanelDisplay(panelEnum.AvailUserSearch);" 
                                                           style="text-decoration:none;font-weight:bold;">User Search</a>
                                                    </li>
                                                    <li class="icon fa-user">
							<a href="#manageFriendsListDiv" onclick="ToggleControlPanelDisplay(panelEnum.MyFriendsViewer);" 
                                                           style="text-decoration:none;font-weight:bold;">Manage Friend List</a>
                                                    </li>
                                                    <li class="icon fa-bullseye">
							<a id="searchFilterLink" href="#" style="text-decoration:none;font-weight:bold;">Filter User List</a>
                                                    </li>

                                                    <div id="modalOverlay" class="overlayPanelModalBackground"></div>
                                                    <div id="searchPanel" class="overlayPanel overlayPanelOrangeBackground">
                                                        <form name="searchForm" method="POST" action="">
                                                            <div class="overlayPanelFixedHeightScrollableContainer">
                                                                <div class="overlayPanelToggleGroup searchPanelAvailUsersFilter searchPanelCurFriendsFilter">
                                                                    <div class="overlayPanelToggleLinkWrap">
                                                                        <a href="#" id="usernameFilterLink" class="fa fa-plus-square overlayPanelToggleElementInactive" 
                                                                           onclick="return ToggleSearchDivDisplay('#usernameFilterDiv', this);">&nbsp;&nbsp;User Name</a>
                                                                    </div>
                                                                    <div class="overlayPanelToggleChkboxWrap">
                                                                        <input id="usernameFilterActiveToggleLabel" class="overlayPanelToggleActiveChk" linkId="usernameFilterLink" 
                                                                               groupId="usernameFilterDiv" lblId="usernameFilterActiveToggleLabel" type="checkbox">
                                                                        <label id="usernameFilterActiveToggleLabel" class="overlayPanelToggleActiveLbl">Activate Filter</label>
                                                                    </div>
                                                                </div>
                                                                <div id="usernameFilterDiv" class="overlayPanelFilterGroup searchPanelAvailUsersFilter searchPanelCurFriendsFilter">
                                                                    <div class="overlayPanelFilterSubGroup">
                                                                        <div class="overlayPanelElementContainerCheckboxListSiblingWide">
                                                                            <input id="usernameFilter" class="overlayPanelElement" name="usernameFilter" type="text" 
                                                                                   maxlength="50" placeholder=" Username Search">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="overlayPanelToggleGroup searchPanelAvailUsersFilter searchPanelCurFriendsFilter">
                                                                    <div class="overlayPanelToggleLinkWrap">
                                                                        <a href="#" id="gamerTagFilterLink" class="fa fa-plus-square overlayPanelToggleElementInactive" 
                                                                           onclick="return ToggleSearchDivDisplay('#gamerTagFilterDiv', this);">&nbsp;&nbsp;Gamer Tag</a>
                                                                    </div>
                                                                    <div class="overlayPanelToggleChkboxWrap">
                                                                        <input id="gamerTagFilterActiveToggleLabel" class="overlayPanelToggleActiveChk" linkId="gamerTagFilterLink" 
                                                                               groupId="gamerTagFilterDiv" lblId="gamerTagFilterActiveToggleLabel" type="checkbox">
                                                                        <label id="gamerTagFilterActiveToggleLabel" class="overlayPanelToggleActiveLbl">Activate Filter</label>
                                                                    </div>
                                                                </div>
                                                                <div id="gamerTagFilterDiv" class="overlayPanelFilterGroup searchPanelAvailUsersFilter searchPanelCurFriendsFilter">
                                                                    <div class="overlayPanelFilterSubGroup">
                                                                        <div class="overlayPanelElementContainerCheckboxListSiblingWide">
                                                                            <input id="gamerTagFilter" class="overlayPanelElement" name="gamerTagFilter" type="text" 
                                                                                   maxlength="50" placeholder=" Gamer Tag Search">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="overlayPanelToggleGroup searchPanelAvailUsersFilter searchPanelCurFriendsFilter">
                                                                    <div class="overlayPanelToggleLinkWrap">
                                                                        <a href="#" id="firstnameFilterLink" class="fa fa-plus-square overlayPanelToggleElementInactive" 
                                                                           onclick="return ToggleSearchDivDisplay('#firstnameFilterDiv', this);">&nbsp;&nbsp;First Name</a>
                                                                    </div>
                                                                    <div class="overlayPanelToggleChkboxWrap">
                                                                        <input id="firstnameFilterActiveToggleLabel" class="overlayPanelToggleActiveChk" linkId="firstnameFilterLink" 
                                                                               groupId="firstnameFilterDiv" lblId="firstnameFilterActiveToggleLabel" type="checkbox">
                                                                        <label id="firstnameFilterActiveToggleLabel" class="overlayPanelToggleActiveLbl">Activate Filter</label>
                                                                    </div>
                                                                </div>
                                                                <div id="firstnameFilterDiv" class="overlayPanelFilterGroup searchPanelAvailUsersFilter searchPanelCurFriendsFilter">
                                                                    <div class="overlayPanelFilterSubGroup">
                                                                        <div class="overlayPanelElementContainerCheckboxListSiblingWide">
                                                                            <input id="firstnameFilter" class="overlayPanelElement" name="firstnameFilter" type="text" 
                                                                                   maxlength="50" placeholder=" Search By First Name">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="overlayPanelToggleGroup searchPanelAvailUsersFilter searchPanelCurFriendsFilter">
                                                                    <div class="overlayPanelToggleLinkWrap">
                                                                        <a href="#" id="lastnameFilterLink" class="fa fa-plus-square overlayPanelToggleElementInactive" 
                                                                           onclick="return ToggleSearchDivDisplay('#lastnameFilterDiv', this);">&nbsp;&nbsp;Last Name</a>
                                                                    </div>
                                                                    <div class="overlayPanelToggleChkboxWrap">
                                                                        <input id="lastnameFilterActiveToggleLabel" class="overlayPanelToggleActiveChk" linkId="lastnameFilterLink" 
                                                                               groupId="lastnameFilterDiv" lblId="lastnameFilterActiveToggleLabel" type="checkbox">
                                                                        <label id="lastnameFilterActiveToggleLabel" class="overlayPanelToggleActiveLbl">Activate Filter</label>
                                                                    </div>
                                                                </div>
                                                                <div id="lastnameFilterDiv" class="overlayPanelFilterGroup searchPanelAvailUsersFilter searchPanelCurFriendsFilter">
                                                                    <div class="overlayPanelFilterSubGroup">
                                                                        <div class="overlayPanelElementContainerCheckboxListSiblingWide">
                                                                            <input id="lastnameFilter" class="overlayPanelElement" name="lastnameFilter" type="text" 
                                                                                   maxlength="50" placeholder=" Search By Last Name">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="overlayPanelToggleGroup searchPanelAvailUsersFilter searchPanelCurFriendsFilter">
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
                                                                <div id="platformFilterDiv" class="overlayPanelFilterGroup searchPanelAvailUsersFilter searchPanelCurFriendsFilter">
                                                                    <div class="overlayPanelFilterSubGroup">
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
                                                                <div class="overlayPanelToggleGroup searchPanelAvailUsersFilter">
                                                                    <div class="overlayPanelToggleLinkWrap">
                                                                        <a href="#" id="genderFilterLink" class="fa fa-plus-square overlayPanelToggleElementInactive" 
                                                                           onclick="return ToggleSearchDivDisplay('#genderFilterDiv', this);">&nbsp;&nbsp;Gender</a>
                                                                    </div>
                                                                    <div class="overlayPanelToggleChkboxWrap">
                                                                        <input id="genderFilterActiveToggle" class="overlayPanelToggleActiveChk" linkId="genderFilterLink" 
                                                                               groupId="genderFilterDiv" lblId="genderFilterActiveToggleLabel" type="checkbox">
                                                                        <label id="genderFilterActiveToggleLabel" class="overlayPanelToggleActiveLbl">Activate Filter</label>
                                                                    </div>
                                                                </div>
                                                                <div id="genderFilterDiv" class="overlayPanelFilterGroup searchPanelAvailUsersFilter">
                                                                    <div class="overlayPanelFilterSubGroup">
                                                                        <div class="overlayPanelElementContainerWide">
                                                                            <div>
                                                                                <input type="radio" name="genderFilter" 
                                                                                       class="overlayPanelElement" value="M">
                                                                                <label class="overlayPanelLabel">Male</label>
                                                                                <br />
                                                                                <input type="radio" name="genderFilter" 
                                                                                       class="overlayPanelElement" value="F">
                                                                                <label class="overlayPanelLabel">Female</label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="overlayPanelToggleGroup searchPanelCurFriendsFilter">
                                                                    <div class="overlayPanelToggleLinkWrap">
                                                                        <a href="#" id="friendTypeFilterLink" class="fa fa-plus-square overlayPanelToggleElementActive" 
                                                                           onclick="return ToggleSearchDivDisplay('#friendTypeFilterDiv', this);">&nbsp;&nbsp;Friend Type</a>
                                                                    </div>
                                                                    <div class="overlayPanelToggleChkboxWrap">
                                                                        <input id="friendTypeFilterActiveToggle" class="overlayPanelToggleActiveChk" linkId="friendTypeFilterLink" 
                                                                               groupId="friendTypeFilterDiv" lblId="friendTypeFilterActiveToggleLabel" type="checkbox" checked="true">
                                                                        <label id="friendTypeFilterActiveToggleLabel" class="overlayPanelToggleActiveLbl">Deactivate Filter</label>
                                                                    </div>
                                                                </div>
                                                                <div id="friendTypeFilterDiv" class="overlayPanelFilterGroup searchPanelCurFriendsFilter">
                                                                    <div class="overlayPanelFilterSubGroup">
                                                                        <div class="overlayPanelElementContainerWide">
                                                                            <div><label class="overlayPanelLabel">Show Invitations To Me</label></div>
                                                                            <div><input type="checkbox" id="friendTypeInvToMe" name="friendTypes[]" 
                                                                                   class="overlayPanelElement filterFieldActive" value="showInvToMe"></div>
                                                                        </div>
                                                                        <div class="overlayPanelElementContainerWide">
                                                                            <div><label class="overlayPanelLabel">Show Invitations From Me</label></div>
                                                                            <div><input type="checkbox" id="friendTypeInvFromMe" name="friendTypes[]" 
                                                                                   class="overlayPanelElement filterFieldActive" value="showInvFromMe"></div>
                                                                        </div>
                                                                        <div class="overlayPanelElementContainerWide">
                                                                            <div><label class="overlayPanelLabel">Show Rejected Invitations</label></div>
                                                                            <div><input type="checkbox" id="friendTypeRejectedInv" name="friendTypes[]" 
                                                                                   class="overlayPanelElement filterFieldActive" value="showRejectedInv"></div>
                                                                        </div>
                                                                        <div class="overlayPanelElementContainerWide">
                                                                            <div><label class="overlayPanelLabel">Show Current Friends</label></div>
                                                                            <div><input type="checkbox" id="friendTypeCurFriends" name="friendTypes[]" 
                                                                                   class="overlayPanelElement filterFieldActive" value="showCurFriends"></div>
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
                                                    </div>
						</ul>
                                            </section>
					</section>
                                    </div>
                                </div>
                                <div id="searchForFriendsDiv" class="9u jTableContainer">
                                    <section class="box style1">
                                        <h2>Search For New Friends</h2>
                                        <div id="searchForFriendsContent">
                                        </div>
                                    </section><br/>
                                </div>
                                <div id="manageFriendsListDiv" class="9u jTableContainer">
                                    <section class="box style1">
                                        <h2>Manage Friends List</h2>
                                        <div id="manageFriendsListContent">
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
        <?php include("Footer.php"); ?>
        <!-- Footer Wrapper -->
    </body>
</html>
