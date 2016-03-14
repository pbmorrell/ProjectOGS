// Globals
var panelEnum = {
    AvailUserSearch: '#searchForFriendsDiv',
    MyFriendsViewer: '#manageFriendsListDiv'
};

var activePanel = panelEnum.AvailUserSearch;

var availUsersTableLoadAction = 'GetFriendInviteAvailUsersForJTable';
var availUsersJTableDiv = "#searchForFriendsContent";

var manageFriendsListTableLoadAction = 'GetCurrentFriendsListForJTable';
var manageFriendsListJTableDiv = "#manageFriendsListContent";

var gamerTagViewerDlg = "gamerTagViewerDlg";
var gamerTagViewerJTableDiv = "#viewGamerTagsDiv";

var expandedAvailUserFilterGroups = [];
var expandedCurFriendFilterGroups = [];

var lastWindowWidth = -1;
var lastWindowHeight = -1;

function FindFriendsOnReady()
{
    $(panelEnum.MyFriendsViewer).hide();
    
    // Set up search filter panel
    $('#modalOverlay').slideReveal({
        position: "right",
        push: false
    });	
	
    $('#searchPanel').slideReveal({
        position: "right",
        push: false
    });

    $('#searchFilterLink').on('touchend', function(event) {
	event.preventDefault();
	if (!$('#modalOverlay').data("slide-reveal")) { // Show
            $('#modalOverlay').slideReveal("show");
            $('#searchPanel').slideReveal("show");
	} else { // Hide
            CloseSearchPanel();
	}
    });
    
    $('#searchFilterLink').click( function(event) {
	event.preventDefault();
	if (!$('#modalOverlay').data("slide-reveal")) { // Show
            $('#modalOverlay').slideReveal("show");
            $('#searchPanel').slideReveal("show");
	} else { // Hide
            CloseSearchPanel();
	}
    });

    // Hide all filters initially, showing only filter name and toggle in un-expanded state
    $('.overlayPanelFilterGroup').hide();
	
    // Initialize search filter display to match current view
    DisplaySearchFiltersByCurrentView();
	
    // Initialize friend type search checkbox states
    $('#friendTypeInvToMe').prop('checked', true);
    $('#friendTypeRejectedInv').prop('checked', true);
    $('#friendTypeCurFriends').prop('checked', true);
	
    // Set Close button to hide search panel
    $('#closePanelBtn').click(CloseSearchPanel);
    
    // Add click/touch handler to search panel swipe icon
    $('#swipeHoverIcon').on('touchend', function(event) {
        event.preventDefault();
        if($('.overlayPanelControlElementGroup').children('button').first().is(':visible')) {
            $('#swipeHoverIcon').attr('src', 'images/SwipeRight.png');
        }
        else {
            $('#swipeHoverIcon').attr('src', 'images/SwipeLeft.png');
        }
        
        $('.overlayPanelControlElementGroup').children('button').toggle('slide');
    });
    
    $('#swipeHoverIcon').click(function(event) {
        event.preventDefault();
        if($('.overlayPanelControlElementGroup').children('button').first().is(':visible')) {
            $('#swipeHoverIcon').attr('src', 'images/SwipeRight.png');
        }
        else {
            $('#swipeHoverIcon').attr('src', 'images/SwipeLeft.png');
        }
        
        $('.overlayPanelControlElementGroup').children('button').toggle('slide');
    });
    
    // On startup, we want to simulate a viewport size transition, in order to format and size
    // the current layout to best fit the current browser dimensions...we initialized everything
    // as if it was in desktop view, so now we run a transition from 'desktop' to whatever the current view class is
    var curWindowHeight = $(window).height();
    lastWindowHeight = curWindowHeight;
    var curWindowWidth = $(window).width();
    lastWindowWidth = curWindowWidth;
    var initTransition = true;
    
    OnViewportSizeChanged(curWindowWidth, curWindowHeight, 'desktop', GetCurWidthClass(), 'desktop', GetCurHeightClass(), initTransition);
    
    // Add checked handler to filter checkboxes
    $('#searchPanel .overlayPanelToggleActiveChk').each(function() {
	$(this).change(function() {
            var toggleLinkId = '#' + $(this).attr('linkId');
            var groupId = '#' + $(this).attr('groupId');
            var lblId = '#' + $(this).attr('lblId');
            if($(toggleLinkId).hasClass('overlayPanelToggleElementInactive')) {
		$(toggleLinkId).removeClass('overlayPanelToggleElementInactive').addClass('overlayPanelToggleElementActive');
		$(groupId).find('.overlayPanelElement').removeClass('filterFieldActive').addClass('filterFieldActive');
		
                var displayText = "Deactivate Filter";
                if(isMobileView())  displayText = "Deactivate";
                $(lblId).text(displayText);
            }
            else {
		$(toggleLinkId).removeClass('overlayPanelToggleElementActive').addClass('overlayPanelToggleElementInactive');
		$(groupId).find('.overlayPanelElement').removeClass('filterFieldActive');
                
                var displayText = "Activate Filter";
                if(isMobileView()) displayText = "Activate";
                $(lblId).text(displayText);                
            }
	});
    });
	
    // Attach event handler to search button
    $('#searchBtn').click(function() {
        var isAvailUserFeed = (activePanel === panelEnum.AvailUserSearch);
        var searchFieldClass = '.searchPanelAvailUsersFilter';
        if(!isAvailUserFeed) {
            searchFieldClass = '.searchPanelCurFriendsFilter';
        }
        
        var searchEventInfo = ValidateSearchFormFields(searchFieldClass);
        if(searchEventInfo.validated) {
            if (isAvailUserFeed) {
                ReloadAvailUsersTable(true);
            }
            else {
                ReloadManageFriendsTable(true);
            }
        }
        
        return false;
    });
	
    LoadAvailUsersForFriendInvite();
    LoadCurrentFriendListForUser();
}

function OnViewportSizeChanged(curWindowWidth, curWindowHeight, lastWidthClass, curWidthClass, lastHeightClass, curHeightClass, initTransition)
{
    HandleWidthTransition(curWindowWidth, lastWidthClass, curWidthClass, initTransition);
    HandleHeightTransition(curWindowHeight, lastHeightClass, curHeightClass);
    
    if((curWidthClass == "desktop") && (curHeightClass == "desktop")) {
        // Adjust padding for filter div to account for Skel's removal of mobile header/toolbar
        $('.overlayPanelFixedHeightScrollableContainer').css('padding-top', "2%");
        
        // Show search panel button div toggle icon
        $('.overlayPanelControlElementGroup').children('button').show();
        $('.swipeHoverIconContainer').hide();
    }
    else {           
        // Adjust padding for filter div to account for Skel's addition of mobile header/toolbar
        $('.overlayPanelFixedHeightScrollableContainer').css('padding-top', "8%");
        
        // Show search panel button div toggle icon
        $('.swipeHoverIconContainer').show();
    }
}

function HandleWidthTransition(curWindowWidth, lastWidthClass, curWidthClass, initTransition)
{
    var isWidthTransition = false;
    var searchPanelWidth = Math.round(curWindowWidth * 0.65);
    
    // Begin logic flow for viewport width changes that result in a class change
    if(((lastWidthClass == 'desktop')  || initTransition) && ((curWidthClass == 'mobile') || (curWidthClass == 'xtraSmall'))) {
        // If moving from desktop view to mobile view
        if(($('#' + gamerTagViewerDlg).length) && ($('#' + gamerTagViewerDlg + ' .jtable').length)) {
            // Hide page size change area in gamerTagViewer table
            $(gamerTagViewerJTableDiv + ' .jtable-page-size-change').hide();

            // Decrease width of gamer tag viewer dialog
            $('#' + gamerTagViewerDlg).dialog('option', 'width', (curWindowWidth < 400) ? (curWindowWidth - 25) : 400);
        }

        // Adjust width of search panel
        if(curWidthClass == 'mobile')  searchPanelWidth = Math.round(curWindowWidth * 0.8);
        else                           searchPanelWidth = Math.round(curWindowWidth * 0.95);
        
        // Change text of toggle checkboxes to be shorter, for mobile view
        $('.overlayPanelToggleActiveLbl').filter(function(index) { return $(this).text() === "Activate Filter" }).text('Activate');
        $('.overlayPanelToggleActiveLbl').filter(function(index) { return $(this).text() === "Deactivate Filter" }).text('Deactivate');
        
        // Collapse events tables by combining text of certain columns and hiding other, unnecessary ones
        if(!initTransition) {
            FormatAvailUsersTableForCurrentView(true, curWidthClass);
            FormatCurFriendsTableForCurrentView(true, curWidthClass);
        }
        
        isWidthTransition = true;
    }
    
    if(((lastWidthClass == 'mobile') || (lastWidthClass == 'xtraSmall') || initTransition) && (curWidthClass == 'desktop')) {
        if(($('#' + gamerTagViewerDlg).length) && ($('#' + gamerTagViewerDlg + ' .jtable').length)) {
            // Show page size change area in gamerTagViewer table
            $(gamerTagViewerJTableDiv + ' .jtable-page-size-change').show();

            // Increase width of gamer tag viewer dialog
            $('#' + gamerTagViewerDlg).dialog('option', 'width', (curWindowWidth < 600) ? (curWindowWidth - 25) : 600);
        }
        
        // Change text of toggle checkboxes to full version, for desktop view
        $('.overlayPanelToggleActiveLbl').filter(function(index) { return $(this).text() === "Activate" }).text('Activate Filter');
        $('.overlayPanelToggleActiveLbl').filter(function(index) { return $(this).text() === "Deactivate" }).text('Deactivate Filter');

        // Restore hidden or combined columns
        if(!initTransition) {
            FormatAvailUsersTableForCurrentView(false);
            FormatCurFriendsTableForCurrentView(false);
        }
        
        isWidthTransition = true;
    }
    
    // Do any required adjustments for viewport width changes that don't result in a class change
    // If the resize event was triggered by scrolling or a minimal resize, don't need to hide search panel
    if((Math.abs(curWindowWidth - lastWindowWidth) > 50) || initTransition) {
        CloseSearchPanel();

        $('#modalOverlay').slideReveal({
            changeWidth: true,
            width: curWindowWidth + 20
        });
        
        if(isWidthTransition) {
            $('#searchPanel').slideReveal({
                changeWidth: true,
                width: searchPanelWidth
            });
        }
    }

    lastWindowWidth = curWindowWidth;
}

function HandleHeightTransition(curWindowHeight, lastHeightClass, curHeightClass)
{
    // Do required adjustments for viewport height changes that don't result in a class change
    var filterDivHeightPct = 0.9;
    if(curHeightClass != "desktop") {
        filterDivHeightPct = 0.7;
    }
    
    var filterDivHeight = filterDivHeightPct * curWindowHeight;
    $('.overlayPanelFixedHeightScrollableContainer').css('height', filterDivHeight.toString() + 'px');
    
    // If the resize event was triggered by scrolling or a minimal resize, don't need to hide search panel
    if(Math.abs(curWindowHeight - lastWindowHeight) > 100) {
        CloseSearchPanel();
    }
    
    lastWindowHeight = curWindowHeight;
}

function FormatAvailUsersTableForCurrentView(isMobile, curWidthClass)
{
    var hiddenClass = "curEvtsHiddenInMobileView";
    if(isMobile) {
	// Temporarily show hidden columns, to allow for column combination to work properly in all cases
	$('.' + hiddenClass).removeClass(hiddenClass);
		
	// Temporarily remove fixed-width scrollable container, to allow for column combination to work properly in all cases
	$(availUsersJTableDiv + ' .fixedWidthScrollableContainer .jtable').unwrap();
        
        // Change table header/toolbar to be more mobile-friendly		
        if(curWidthClass == 'mobile') {
            $(availUsersJTableDiv + ' .jtable-title-text').text('Available Users');
	}
        else if(curWidthClass == 'xtraSmall') {
            $(availUsersJTableDiv + ' .jtable-title-text').text('User List');
	}
        
        $(availUsersJTableDiv + ' .jtable-toolbar-item-text:contains("Invite Selected")').text('Invite');
        
        // Hide "Gender" column
        if(curWidthClass == 'xtraSmall') {
            var genderColHdr = $(availUsersJTableDiv + ' th:contains("Gender")');
            var genderColIdx = $(genderColHdr).index();
            $(genderColHdr).addClass(hiddenClass);

            $(availUsersJTableDiv).children('.jtable-main-container').children('.jtable').children('tbody').children('tr').each(function() {
                var genderCol = $(this).children('td').eq(genderColIdx);
                $(genderCol).addClass(hiddenClass);
            });
        }
        
        // Hide page size change area
        $(availUsersJTableDiv + ' .jtable-page-size-change').hide();
        
        // Hide go-to-page area
        if(curWidthClass == 'xtraSmall') {
            $(availUsersJTableDiv + ' .jtable-goto-page').hide();
        }
        
	// Reduce font size of table text to limit need for table overflow
	$(availUsersJTableDiv + ' table').removeClass('desktopViewFontSize').addClass('mobileViewFontSize');
		
	// Enclose jTable containers in fixed-width scrollable divs
        $(availUsersJTableDiv + ' .jtable-main-container').children('.jtable').wrap('<div class="fixedWidthScrollableContainer"></div>');
        
        // Adjust fixed-width scrollable container to maximum available width
        if($(availUsersJTableDiv + ' .jtable-title').is(':visible')) {
            var titleBarWidth = $(availUsersJTableDiv + ' .jtable-title').width();
            $(availUsersJTableDiv + ' .fixedWidthScrollableContainer').css('width', (titleBarWidth + 10) + 'px');
        }
    }
    else {        
        // Change table header and toolbar text to full-length versions
        $(availUsersJTableDiv + ' .jtable-title-text').text('Available Users');
        $(availUsersJTableDiv + ' .jtable-toolbar-item-text:contains("Invite")').text('Invite Selected');
        
        // Show hidden columns
        $('.' + hiddenClass).removeClass(hiddenClass);
        
        // Display page size change area
        $(availUsersJTableDiv + ' .jtable-page-size-change').show();
        
        // Display go-to-page area
        $(availUsersJTableDiv + ' .jtable-goto-page').show();
		
	// Make font size normal again
	$(availUsersJTableDiv + ' table').removeClass('mobileViewFontSize').addClass('desktopViewFontSize');
		
	// Remove fixed-width scrollable container
	$(availUsersJTableDiv + ' .fixedWidthScrollableContainer .jtable').unwrap();
    }
}

function FormatCurFriendsTableForCurrentView(isMobile, curWidthClass)
{
    var hiddenClass = "evtMgrHiddenInMobileView";
    if(isMobile) {
	// Temporarily show hidden columns, to allow for column combination to work properly in all cases
	$('.' + hiddenClass).removeClass(hiddenClass);
		
	// Temporarily remove fixed-width scrollable container, to allow for column combination to work properly in all cases
	$(manageFriendsListJTableDiv + ' .fixedWidthScrollableContainer .jtable').unwrap();
		
        // Reveal "Full Name" column (containing 'firstName lastName')
        $('.' + hiddenClass).removeClass(hiddenClass);
                
        // Hide First Name & Last Name columns
        var firstNameColHdr = $(manageFriendsListJTableDiv + ' th:contains("First")');
        var lastNameColHdr = $(manageFriendsListJTableDiv + ' th:contains("Last")');
        
        var firstNameColIdx = $(firstNameColHdr).index();
        var lastNameColIdx = $(lastNameColHdr).index();
        
        $(firstNameColHdr).addClass(hiddenClass);
        $(lastNameColHdr).addClass(hiddenClass);
                
	$(manageFriendsListJTableDiv).children('.jtable-main-container').children('.jtable').children('tbody').children('tr').each(function() {
            var firstNameCol = $(this).children('td').eq(firstNameColIdx);
            var lastNameCol = $(this).children('td').eq(lastNameColIdx);
            $(firstNameCol).addClass(hiddenClass);
            $(lastNameCol).addClass(hiddenClass);
        });
        
        // Change table header/toolbar to be more mobile-friendly		
        if(curWidthClass == 'mobile') {
            $(manageFriendsListJTableDiv + ' .jtable-title-text').text('Current Friends');
	}
        else if(curWidthClass == 'xtraSmall') {
            $(manageFriendsListJTableDiv + ' .jtable-title-text').text('Friends');
	}
        
        $(manageFriendsListJTableDiv + ' .jtable-toolbar-item-text:contains("Accept Selected")').text('Accept');
        $(manageFriendsListJTableDiv + ' .jtable-toolbar-item-text:contains("Remove Selected")').text('Remove');
        
        // Hide page size change area
        $(manageFriendsListJTableDiv + ' .jtable-page-size-change').hide();
        
        // Hide go-to-page area
        if(curWidthClass == 'xtraSmall') {
            $(manageFriendsListJTableDiv + ' .jtable-goto-page').hide();
        }
        
	// Reduce font size of table text to limit need for table overflow
	$(manageFriendsListJTableDiv + ' table').removeClass('desktopViewFontSize').addClass('mobileViewFontSize');
		
	// Enclose jTable containers in fixed-width scrollable divs
        $(manageFriendsListJTableDiv + ' .jtable-main-container').children('.jtable').wrap('<div class="fixedWidthScrollableContainer"></div>');
        
        // Adjust fixed-width scrollable container to maximum available width
        if($(manageFriendsListJTableDiv + ' .jtable-title').is(':visible')) {
            var titleBarWidth = $(manageFriendsListJTableDiv + ' .jtable-title').width();
            $(manageFriendsListJTableDiv + ' .fixedWidthScrollableContainer').css('width', (titleBarWidth + 10) + 'px');
        }
    }
    else {        
        // Change table header and toolbar text to full-length versions
        $(manageFriendsListJTableDiv + ' .jtable-title-text').text('Current Friends');
        $(manageFriendsListJTableDiv + ' .jtable-toolbar-item-text:contains("Accept")').text('Accept Selected');
        $(manageFriendsListJTableDiv + ' .jtable-toolbar-item-text:contains("Remove")').text('Remove Selected');
        
        // Display page size change area
        $(manageFriendsListJTableDiv + ' .jtable-page-size-change').show();
        
        // Display go-to-page area
        $(manageFriendsListJTableDiv + ' .jtable-goto-page').show();
		
	// Make font size normal again
	$(manageFriendsListJTableDiv + ' table').removeClass('mobileViewFontSize').addClass('desktopViewFontSize');
		
	// Remove fixed-width scrollable container
	$(manageFriendsListJTableDiv + ' .fixedWidthScrollableContainer .jtable').unwrap();
        
        // Show hidden columns
        $('.' + hiddenClass).removeClass(hiddenClass);
        
        // Hide FullName column
        var fullNameColHdr = $(manageFriendsListJTableDiv + ' th:contains("Full Name")');
        var fullNameColIdx = $(fullNameColHdr).index();
        $(fullNameColHdr).addClass(hiddenClass);
                
	$(manageFriendsListJTableDiv).children('.jtable-main-container').children('.jtable').children('tbody').children('tr').each(function() {
            var fullNameCol = $(this).children('td').eq(fullNameColIdx);
            $(fullNameCol).addClass(hiddenClass);
        });
    }
}

function LoadAvailUsersForFriendInvite()
{
    // Initialize jTable on availUsersJTableDiv div
    $(availUsersJTableDiv).jtable({
        title: "Available Users",
        paging: true,
        pageSize: 10,
        pageSizes: [5, 10, 15, 20, 25],
        pageSizeChangeArea: true,
        pageList: 'minimal',
        sorting: true,
        defaultSorting: 'UserName ASC',
        openChildAsAccordion: false,
        selecting: true,
        multiselect: true,
        selectingCheckboxes: true,
        selectOnRowClick: false,
        toolbar: {
            items:
            [
                {
                    text: 'Invite Selected',
                    icon: 'images/envelope_closed.png',
                    tooltip: 'Invite selected users to be your friend',
                    click: function(){
                        IssueFriendInviteToSelectedUsers();
                    }
                },
                {
                    text: 'Refresh',
                    icon: 'images/refresh.png',
                    tooltip: 'Refreshes list of available users',
                    click: function(){
			var fullRefresh = false;
                        ReloadAvailUsersTable(fullRefresh);
                    }
                }
            ]
        },
        actions: {
            listAction: "AJAXHandler.php"
        },
        fields: {
            ID: {
                key: true,
                list: false
            },
            UserName: {
                title: 'Username',
                width: '20%',
                sorting: true,
                display: function (data) {
                    var $userNameDetailsPopupLink = $('<a href="#" class="actionLink" id="unDetailsLink' + data.record.ID + '">' + data.record.UserName + '</a>');
                    $userNameDetailsPopupLink.click(function () {
                        OpenUserDetailsPopup(data.record.ID, data.record.UserName);
                        return false;
                    });
                    
                    // Return link for display in jTable
                    return $userNameDetailsPopupLink;
                }
            },
            FirstName: {
                title: 'First Name',
                width: '17%',
                sorting: true
            },
            LastName: {
                title: 'Last Name',
                width: '23%',
                sorting: true
            },
            Gender: {
                title: 'Gender',
                width: '10%',
                sorting: true
            },
            GamerTags: {
                title: 'View Gamer Tags',
                width: '15%',
                sorting: false,
                display: function (data) {
                    var $tagViewerLink = $('<a href="#" class="actionLink" id="tagsLink' + data.record.ID + '">Show Tags</a>');

                    $tagViewerLink.click(function () {
                        OpenGamerTagViewer(gamerTagViewerDlg, gamerTagViewerJTableDiv.substring(1), "Gamer Tag Viewer", 
                                           "Gamer Tags For: " + (data.record.FirstName + " " + data.record.LastName), true, true, data.record.ID);
                        return false;
                    });

                    // Return link for display in jTable
                    return $tagViewerLink;
                }
            },
            SendInvite: {
                title: 'Send Invite',
                width: '15%',
                display: function (data) {
                    var $inviteImage = $('<img alt="Invite" title="Invite this user to be your friend" src="images/envelope_closed.png" />');
                    $inviteImage.click(function () {
                        var userIds = [data.record.ID];
                        SendFriendInviteToUsers(userIds);
                    });

                    // Return image for display in jTable
                    return $inviteImage;
                },
                sorting: false,
                columnSelectable: false
            }
        },
	recordsLoaded: function(event, data) {			
            var curWidthClass = GetCurWidthClass();
            if(curWidthClass != 'desktop')  FormatAvailUsersTableForCurrentView(true, curWidthClass);
	}
    });

    // Load available user list
    ReloadAvailUsersTable(true);
}

function LoadCurrentFriendListForUser()
{
    // Initialize jTable on manageFriendsListJTableDiv div
    $(manageFriendsListJTableDiv).jtable({
        title: "Current Friends",
        paging: true,
        pageSize: 10,
        pageSizes: [5, 10, 15, 20, 25],
        pageSizeChangeArea: true,
        pageList: 'minimal',
        sorting: true,
        defaultSorting: 'UserName ASC',
        openChildAsAccordion: false,
        selecting: true,
        multiselect: true,
        selectingCheckboxes: true,
        selectOnRowClick: false,
        toolbar: {
            items:
            [
                {
                    text: 'Accept Selected',
                    icon: 'images/activate.png',
                    tooltip: 'Accepts any active invitations from selected users',
                    click: function(){
                        AcceptUserFriendInvites();
                    }
                },
                {
                    text: 'Remove Selected',
                    icon: 'images/delete.png',
                    tooltip: 'Removes selected users from your friend list, or cancels any active invitations',
                    click: function(){
                        RemoveUsersFromFriendList();
                    }
                },
                {
                    text: 'Refresh',
                    icon: 'images/refresh.png',
                    tooltip: 'Refreshes friend list',
                    click: function(){
			var fullRefresh = false;
                        ReloadManageFriendsTable(fullRefresh);
                    }
                }
            ]
        },
        actions: {
            listAction: "AJAXHandler.php"
        },
        fields: {
            ID: {
                key: true,
                list: false
            },
            UserName: {
                title: 'Username',
                width: '17%',
                sorting: true,
                display: function (data) {
                    var $userNameDetailsPopupLink = $('<a href="#" class="actionLink" id="unDetailsLink' + data.record.ID + '">' + data.record.UserName + '</a>');
                    $userNameDetailsPopupLink.click(function () {
                        OpenUserDetailsPopup(data.record.ID, data.record.UserName);
                        return false;
                    });
                    
                    // Return link for display in jTable
                    return $userNameDetailsPopupLink;
                }
            },
            FirstName: {
                title: 'First Name',
                width: '14%',
                sorting: true
            },
            LastName: {
                title: 'Last Name',
                width: '21%',
                sorting: true
            },
            FullName: {
                title: 'Full Name',
                width: '35%',
                sorting: true
            },
            InviteType: {
                title: 'Invite?',
                width: '8%',
                sorting: true
            },
            InviteReply: {
                title: 'Invite Reply',
                width: '10%',
                sorting: true
            },
            AnswerInvite: {
                title: 'Manage Invite',
                width: '9%',
                sorting: false,
                columnSelectable: false,
                display: function (data) {
                    if((data.record.InviteType == 'To Me') && (data.record.InviteReply != 'Rejected')) {
                        var $answerImage = $('<label><img alt="Accept" id="acc' + data.record.ID + '" class="acceptIcon" title="Accept this invite" src="images/activate.png" />&nbsp;&nbsp;' + 
                                                    '<img alt="Reject" id="rej' + data.record.ID + '" class="rejectIcon" title="Reject this invite" src="images/deactivate.png" /></label>');

                        // Return HTML element for display in jTable
                        return $answerImage;
                    }
                    else if((data.record.InviteType == 'From Me') && (data.record.InviteReply != 'Rejected')) {
                        var $cancelImage = $('<img alt="Cancel" id="cancel' + data.record.ID + '" title="Cancel this invite" src="images/deactivate.png" />');
                        
                        $cancelImage.click(function () {
                            var userIds = [data.record.ID];
                            SendInviteCancel(userIds);
                        });
                    
                        // Return HTML element for display in jTable
                        return $cancelImage;
                    }
                    else {
                        return $('<label>&nbsp;</label>');
                    }
                }
            },
            GamerTags: {
                title: 'View Gamer Tags',
                width: '13%',
                sorting: false,
                display: function (data) {
                    var $tagViewerLink = $('<a href="#" class="actionLink" id="tagsLink' + data.record.ID + '">Show Tags</a>');

                    $tagViewerLink.click(function () {
                        OpenGamerTagViewer(gamerTagViewerDlg, gamerTagViewerJTableDiv.substring(1), "Gamer Tag Viewer", 
                                           "Gamer Tags For: " + (data.record.FirstName + " " + data.record.LastName), true, true, data.record.ID);
                        return false;
                    });

                    // Return link for display in jTable
                    return $tagViewerLink;
                }
            },
            RemoveUser: {
                title: 'Remove',
                width: '8%',
                sorting: false,
                columnSelectable: false,
                display: function (data) {
                    if(data.record.InviteType == 'No') {
                        var $removeImage = $('<img alt="Remove" id="remove' + data.record.ID + '" title="Remove this user from friend list" src="images/delete.png" />');
                        
                        $removeImage.click(function () {
                            var userIds = [data.record.ID];
                            SendInviteReject(userIds);
                        });
                    
                        // Return HTML element for display in jTable
                        return $removeImage;
                    }
                    else {
                        return $('<label>&nbsp;</label>');
                    }
                }
            }
        },
	recordsLoaded: function(event, data) {
            $(manageFriendsListJTableDiv + ' .jtable-data-row').each(function() {
		var id = $(this).attr('data-record-key');
		var dataRecordArray = $.grep(data.records, function (e) {
                    return e.ID === id;
                });
                                
                $(this).find('.acceptIcon').each(function() {
                    $(this).click(function () {
                        var userIds = [id];
                        SendInviteAccept(userIds);
                    });
		});
				
                $(this).find('.rejectIcon').each(function() {
                    $(this).click(function () {
                        var userIds = [id];
                        SendInviteReject(userIds);
                    });
		});
                
                var inviteType = dataRecordArray[0].InviteType;
		if(inviteType !== 'No') {
                    $(this).css('color', 'gray');
		}
            });
			
            var curWidthClass = GetCurWidthClass();
            if(curWidthClass != 'desktop')  FormatCurFriendsTableForCurrentView(true, curWidthClass);
	}
    });

    // Load friend list
    ReloadManageFriendsTable(true);
}

function ToggleSearchDivDisplay(curDiv, curToggleLink)
{
    var isExpand = $(curToggleLink).hasClass('fa-plus-square');
    
    if(isExpand) {
        $(curDiv).removeClass('overlayPanelGroupBorder').addClass('overlayPanelGroupBorder');
	$(curDiv).removeClass('overlayPanelGroupExpanded').addClass('overlayPanelGroupExpanded');
        $(curDiv).slideDown('slow');
        $(curToggleLink).removeClass('fa-plus-square').addClass('fa-minus-square');
    }
    else {
        $(curDiv).removeClass('overlayPanelGroupBorder');
	$(curDiv).removeClass('overlayPanelGroupExpanded');
        $(curDiv).slideUp('slow');
        $(curToggleLink).removeClass('fa-minus-square').addClass('fa-plus-square');
    }
    
    return false;
}

function ReloadAvailUsersTable(fullRefresh)
{
    if(fullRefresh) {
        var searchFormData = ValidateSearchFormFields('.searchPanelAvailUsersFilter');
        var postData = ('action=' + availUsersTableLoadAction) + searchFormData.postData;
        $(availUsersJTableDiv).jtable('load', postData);
    }
    else {
        // Reload user list with same POST arguments
        $(availUsersJTableDiv).jtable('reload');
    }
}

function ReloadManageFriendsTable(fullRefresh)
{
    if(fullRefresh) {
        var searchFormData = ValidateSearchFormFields('.searchPanelCurFriendsFilter');
        var postData = ('action=' + manageFriendsListTableLoadAction) + searchFormData.postData;
        $(manageFriendsListJTableDiv).jtable('load', postData);
    }
    else {
        // Reload user list with same POST arguments
        $(manageFriendsListJTableDiv).jtable('reload');
    }
}

function IssueFriendInviteToSelectedUsers()
{
    var $selectedRows = $(availUsersJTableDiv).jtable('selectedRows');
    if($selectedRows.length === 0) {
        sweetAlert("No users selected");
        return;
    }

    var selectedUserIds = [];
    $selectedRows.each(function() {
            var id = $(this).data('record').ID;
            selectedUserIds.push(id);
        }
    );

    if(!SendFriendInviteToUsers(selectedUserIds)) {
        // Reject friend invitation request (de-select rows)
        DeselectAllJTableRows(availUsersJTableDiv);
    }
}

function AcceptUserFriendInvites()
{
    var $selectedRows = $(manageFriendsListJTableDiv).jtable('selectedRows');
    if($selectedRows.length === 0) {
        sweetAlert("No users selected");
        return;
    }

    var selectedUserIds = [];
    $selectedRows.each(function() {
            var id = $(this).data('record').ID;
            selectedUserIds.push(id);
        }
    );

    if(!SendInviteAccept(selectedUserIds)) {
        // Reject invitation accept request (de-select rows)
        DeselectAllJTableRows(manageFriendsListJTableDiv);
    }
}

function RemoveUsersFromFriendList()
{
    var $selectedRows = $(manageFriendsListJTableDiv).jtable('selectedRows');
    if($selectedRows.length === 0) {
        sweetAlert("No users selected");
        return;
    }

    var selectedUserIds = [];
    $selectedRows.each(function() {
            var id = $(this).data('record').ID;
            selectedUserIds.push(id);
        }
    );

    if(!SendInviteReject(selectedUserIds)) {
        // Reject invitation rejection request (de-select rows)
        DeselectAllJTableRows(manageFriendsListJTableDiv);
    }
}

function SendFriendInviteToUsers(userIDs)
{
    sweetAlert({
      title: "Confirm Invite",
      text: "Are you sure you want to send a friend invite to all selected users?",
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Yes, do it!",
      closeOnConfirm: false,
      closeOnCancel: false,
      showLoaderOnConfirm: true
   },
   function(isConfirm) {
      if(isConfirm) {
         // Make AJAX call to send friend invites to selected users
	$.ajax({
            type: "POST",
            url: "AJAXHandler.php",
            data: "action=SendFriendInviteToUsers&" + $.param({'userIds': userIDs}),
            success: function(response){                
                if(response.match("^SYSTEM ERROR")) {
                    sweetAlert("Invitations Not Sent", response, "error");
                }
                else {
                    var fullRefresh = false;
                    ReloadManageFriendsTable(fullRefresh);
                    ReloadAvailUsersTable(fullRefresh);
                    
                    // Show success message
                    sweetAlert("Invitations Sent!", response, "success");
                }
            },
            error: function() {
		sweetAlert("Invitations Not Sent", "Unable to send friend invites to selected users: server error. Please try again later.", "error");
            }
        });
      }
      else {
         // Show cancel message
         sweetAlert("Friend Invitations Canceled", "Your invitations have not been sent", "info");
      }
   });
}

function SendInviteAccept(userIDs)
{
    sweetAlert({
      title: "Confirm Acceptance",
      text: "Are you sure you want to accept the friend invitation from all selected users?",
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Yes, do it!",
      closeOnConfirm: false,
      closeOnCancel: false,
      showLoaderOnConfirm: true
   },
   function(isConfirm) {
      if(isConfirm) {
         // Make AJAX call to accept invitations from selected users
	$.ajax({
            type: "POST",
            url: "AJAXHandler.php",
            data: "action=AcceptUserFriendInvites&" + $.param({'userIds': userIDs}),
            success: function(response){
                if(response.match("^SYSTEM ERROR")) {
                    sweetAlert("Could Not Accept Invitations", response, "error");
                }
                else {
                    var fullRefresh = false;
                    ReloadManageFriendsTable(fullRefresh);
                    
                    // Show success message
                    sweetAlert("Invitations Accepted!", response, "success");
                }
            },
            error: function() {
		sweetAlert("Could Not Accept Invitations", "Unable to accept invitations from selected users: server error. Please try again later.", "error");
            }
        });
      }
      else {
         // Show cancel message
         sweetAlert("Invitation Acceptance Canceled", "Your Friends List Has Not Been Changed", "info");
      }
   });
}

function SendInviteReject(userIDs)
{
    sweetAlert({
      title: "Confirm Removal",
      text: "Are you sure you want to remove all selected users from your friends list, including invites from users who want to be your friend?",
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Yes, do it!",
      closeOnConfirm: false,
      closeOnCancel: false,
      showLoaderOnConfirm: true
   },
   function(isConfirm) {
      if(isConfirm) {
         // Make AJAX call to remove selected users from friends list
	$.ajax({
            type: "POST",
            url: "AJAXHandler.php",
            data: "action=RemoveUsersFromFriendList&" + $.param({'userIds': userIDs}),
            success: function(response){                             
                if(response.match("^SYSTEM ERROR")) {
                    sweetAlert("Could Not Remove Users", response, "error");
                }
                else {
                    var fullRefresh = false;
                    ReloadManageFriendsTable(fullRefresh);
                    ReloadAvailUsersTable(fullRefresh);
                    
                    // Show success message
                    sweetAlert("Users Removed", response, "success");
                }
            },
            error: function() {
		sweetAlert("Could Not Remove Users", "Unable to remove selected users from friends list: server error. Please try again later.", "error");
            }
        });
      }
      else {
         // Show cancel message
         sweetAlert("Users Not Removed", "Your friends list has not been changed", "info");
      }
   });
}

function SendInviteCancel(userIDs)
{
    sweetAlert({
      title: "Confirm Cancellation",
      text: "Are you sure you want to cancel pending friend invites to all selected users?",
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Yes, do it!",
      cancelButtonText: "No, don't cancel",
      closeOnConfirm: false,
      closeOnCancel: false,
      showLoaderOnConfirm: true
   },
   function(isConfirm) {
      if(isConfirm) {
         // Make AJAX call to cancel invites to all selected users
	$.ajax({
            type: "POST",
            url: "AJAXHandler.php",
            data: "action=RemoveUsersFromFriendList&" + $.param({'userIds': userIDs}),
            success: function(response){                             
                if(response.match("^SYSTEM ERROR")) {
                    sweetAlert("Could Not Cancel Invites To Selected Users", response, "error");
                }
                else {
                    var fullRefresh = false;
                    ReloadManageFriendsTable(fullRefresh);
                    ReloadAvailUsersTable(fullRefresh);
                    
                    // Show success message
                    sweetAlert("Success", "Invitations Cancelled", "success");
                }
            },
            error: function() {
		sweetAlert("Could Not Cancel Invites To Selected Users", "Unable to cancel invitations to selected users: server error. Please try again later.", "error");
            }
        });
      }
      else {
         // Show cancel message
         sweetAlert("Invitations Not Cancelled", "Your invitations are still active", "info");
      }
   });
}

function CloseSearchPanel()
{
    $('#modalOverlay').slideReveal("hide");
    $('#searchPanel').slideReveal("hide");
    return false;
}

function ToggleControlPanelDisplay(panelToToggle)
{
    // Toggle active view
    if($(panelToToggle).css('display') === 'none')
    {
	// Hide active panel
        $(activePanel).hide();
			
	// Fade in desired panel
	activePanel = panelToToggle;
	$(panelToToggle).fadeIn("slow", function() {});

        if(activePanel == panelEnum.AvailUserSearch) {
            var titleBarWidth = $(availUsersJTableDiv + ' .jtable-title').width();
            $(availUsersJTableDiv + ' .fixedWidthScrollableContainer').css('width', (titleBarWidth + 10) + 'px');
        }
        else {
            var titleBarWidth = $(manageFriendsListJTableDiv + ' .jtable-title').width();
            $(manageFriendsListJTableDiv + ' .fixedWidthScrollableContainer').css('width', (titleBarWidth + 10) + 'px');            
        }
    }
	
    // Close search panel, if open
    CloseSearchPanel();
	
    DisplaySearchFiltersByCurrentView();
    return false;
}

function DisplaySearchFiltersByCurrentView()
{
    var $availUserToggles = $('#searchPanel .overlayPanelToggleGroup.searchPanelCurFriendsFilter,#searchPanel .overlayPanelToggleGroup.searchPanelAvailUsersFilter');
    var $exclusivelyCurFriendToggles = $availUserToggles.not('.searchPanelAvailUsersFilter');
    var $exclusivelyAvailUserToggles = $availUserToggles.not('.searchPanelCurFriendsFilter');
    
    var $availUsersFilterFlds = $('#searchPanel .overlayPanelFilterSubGroup .searchPanelCurFriendsFilter,#searchPanel .overlayPanelFilterSubGroup .searchPanelAvailUsersFilter');
    var $exclusivelyCurFriendFilterFlds = $availUsersFilterFlds.not('.searchPanelAvailUsersFilter');
    var $exclusivelyAvailUserFilterFlds = $availUsersFilterFlds.not('.searchPanelCurFriendsFilter');
    
    if(activePanel === panelEnum.AvailUserSearch) {
	// Change search panel style to reflect current view
	$('#searchPanel').removeClass('overlayPanelGreenBackground').addClass('overlayPanelOrangeBackground');
				
	// Cache, then hide, any expanded filter input groups exclusively from old view
	expandedCurFriendFilterGroups = [];
	$('#searchPanel .overlayPanelFilterGroup.searchPanelCurFriendsFilter.overlayPanelGroupExpanded').not('.searchPanelAvailUsersFilter').each(function() {
            expandedCurFriendFilterGroups.push($(this).attr('id'));
            $(this).removeClass('overlayPanelGroupBorder');
		$(this).removeClass('overlayPanelGroupExpanded');
		$(this).slideUp('slow');
            }
	);
				
	// Hide any search filter toggles that are not associated with this particular view
        $exclusivelyCurFriendToggles.addClass('hidden');
        $exclusivelyAvailUserToggles.removeClass('hidden');
			
	// Hide any fields within a search filter that are not associated with this particular view (when others in the same filter might be)
        $exclusivelyCurFriendFilterFlds.addClass('hidden');
        $exclusivelyAvailUserFilterFlds.removeClass('hidden');
		
	// Reveal any filter input groups in new view that were previously expanded
	var i;
	for(i = 0; i < expandedAvailUserFilterGroups.length; i++) {
            var $curDiv = $('#' + expandedAvailUserFilterGroups[i]);
            $curDiv.removeClass('overlayPanelGroupBorder').addClass('overlayPanelGroupBorder');
            $curDiv.removeClass('overlayPanelGroupExpanded').addClass('overlayPanelGroupExpanded');
            $curDiv.slideDown('slow');
	}
    }
    else {
	// Change search panel style to reflect current view
	$('#searchPanel').removeClass('overlayPanelOrangeBackground').addClass('overlayPanelGreenBackground');
					
	// Cache, then hide, any expanded filter input groups exclusively from old view
	expandedAvailUserFilterGroups = [];
	$('#searchPanel .overlayPanelFilterGroup.searchPanelAvailUsersFilter.overlayPanelGroupExpanded').not('.searchPanelCurFriendsFilter').each(function() {
            expandedAvailUserFilterGroups.push($(this).attr('id'));
            $(this).removeClass('overlayPanelGroupBorder');
            $(this).removeClass('overlayPanelGroupExpanded');
            $(this).slideUp('slow');
	});
					
	// Hide any search filter toggles that are not associated with this particular view
        $exclusivelyAvailUserToggles.addClass('hidden');
        $exclusivelyCurFriendToggles.removeClass('hidden');
			
	// Hide any fields within a search filter that are not associated with this particular view (when others in the same filter might be)
        $exclusivelyAvailUserFilterFlds.addClass('hidden');
        $exclusivelyCurFriendFilterFlds.removeClass('hidden');
		
	// Reveal any filter input groups in new view that were previously expanded
	var i;
	for(i = 0; i < expandedCurFriendFilterGroups.length; i++) {
            var $curDiv = $('#' + expandedCurFriendFilterGroups[i]);
            $curDiv.removeClass('overlayPanelGroupBorder').addClass('overlayPanelGroupBorder');
            $curDiv.removeClass('overlayPanelGroupExpanded').addClass('overlayPanelGroupExpanded');
            $curDiv.slideDown('slow');
	}
    }
}

function ValidateSearchFormFields(searchFieldClass)
{
    var searchEventsInfo = new SearchEventsFormInfo();    
    var filterFields = $(searchFieldClass).find('*[name]').filter('.filterFieldActive');
    
    if(filterFields && filterFields.length) {
        searchEventsInfo.postData += ('&' + (filterFields.serialize()));
    }
    
    searchEventsInfo.validated = true;
    return searchEventsInfo;
}

function SearchEventsFormInfo()
{
    this.validated = false;
    this.postData = '';
}