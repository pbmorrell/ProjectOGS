// Globals
var panelEnum = {
    MyEventViewer: '#manageEventsDiv',
    CurrentEventFeed: '#currentEventsDiv'
};

var activePanel = panelEnum.CurrentEventFeed; 

var eventManagerLoadAction = 'GetUserOwnedEventsForJTable';
var eventManagerJTableDiv = "#manageEventsContent";

var currentEventViewerJTableDiv = "#currentEventsContent";
var currentEventViewerLoadAction = 'GetCurrentEventsForJTable';
var gamerTagViewerDlg = "gamerTagViewerDlg";
var gamerTagViewerJTableDiv = "#viewGamerTagsDiv";

var expandedCurEventsFilterGroups = [];
var expandedEvtMgrFilterGroups = [];

var lastWindowWidth = -1;
var lastWindowHeight = -1;

// Functions
function MemberHomeOnReady()
{
    $(panelEnum.MyEventViewer).hide();
    LoadCurrentEventViewer();
    LoadEventManager();
    
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
    $('.overlayPanelFilterGroup').not('#joinStatusFilterDiv').hide();
	
    // Initialize search filter display to match current view
    DisplaySearchFiltersByCurrentView();
	
    // Set Close button to hide search panel
    $('#closePanelBtn').click(CloseSearchPanel);
    
    // Initialize Game Filter Date datepickers
    $('#gameFilterStartDate').datepicker({
         inline: true,
         changeYear: true,
         yearRange: '-0:+1',
         changeMonth: true,
         constrainInput: true,
         showButtonPanel: true,
         showOtherMonths: true,
         dayNamesMin: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
         dateFormat: 'yy-mm-dd',
         onClose: function(selectedDate) {
             $('#gameFilterEndDate').datepicker('option', 'minDate', selectedDate);
         }
    });
    
    $('#gameFilterEndDate').datepicker({
         defaultDate: "+3",
         inline: true,
         changeYear: true,
         yearRange: '-0:+1',
         changeMonth: true,
         constrainInput: true,
         showButtonPanel: true,
         showOtherMonths: true,
         dayNamesMin: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
         dateFormat: 'yy-mm-dd',
         onClose: function(selectedDate) {
             $('#gameFilterStartDate').datepicker('option', 'maxDate', selectedDate);
         }
    });
    
    // Set date fields to defaults
    $('#gameFilterStartDate').datepicker('setDate', new Date());
    $('#gameFilterEndDate').datepicker('setDate', addDaysToDate(new Date(), 3));
    
    // Initialize Game Filter Time datepickers
    var defaultTimeDate = new Date();
    var nextRoundIntervalValue = 15 - (defaultTimeDate.getMinutes() % 15);
    defaultTimeDate.setMinutes(defaultTimeDate.getMinutes() + nextRoundIntervalValue);

    var optionsTimePicker = {
        disableTextInput: false,
        disableTouchKeyboard: false,
        minTime: defaultTimeDate,
        selectOnBlur: true,
        useSelect: false,
        className: 'overlayPanelElement',
        step: 15
    };
    
    $('#gameFilterStartTime').timepicker(optionsTimePicker);
    $('#gameFilterEndTime').timepicker(optionsTimePicker);   
    
    // Set default start and end times
    $('#gameFilterStartTime').timepicker('setTime', defaultTimeDate);
    $('#gameFilterEndTime').timepicker('setTime', defaultTimeDate);
    
    // Add overlayPanel class to time zone pickers in search panel
    $('#ddlTimeZonesStart').addClass('overlayPanelElement');
    $('#ddlTimeZonesEnd').addClass('overlayPanelElement');
    
    // Add event handlers to mobile control panel buttons
    $('#btnMobileLeave').click(function() {
        LeaveSelectedEvents();
    });

    $('#btnMobileActivate').click(function() {
        ToggleTableEventActivation("1");
    });

    $('#btnMobileHide').click(function() {
        ToggleTableEventActivation("0");
    });

    $('#btnMobileDelete').click(function() {
        DeleteTableEvents();
    });
    
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
    
    // Initialize event status checkbox states
    $('#evtStatusCreated').prop('checked', true);
    
    // Force user to select at least one of (but possibly both of) "Show My Joined Events" and 
    // "Show My Created Events" search panel checkboxes
    $('#evtStatusJoined,#evtStatusCreated').change(function() {        
        var joinedChkBoxIsChecked = $('#evtStatusJoined').is(':checked');
        var createdChkBoxIsChecked = $('#evtStatusCreated').is(':checked');
        
        if(!joinedChkBoxIsChecked && !createdChkBoxIsChecked) {
            $(this).prop('checked', true);
        }
    });
    
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
                if(isMobileView()) displayText = "Deactivate";
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
        var isCurEvtFeed = (activePanel === panelEnum.CurrentEventFeed);
        var searchFieldClass = '.searchPanelCurEvtsFilter';
        if(!isCurEvtFeed) {
            searchFieldClass = '.searchPanelEvtMgrFilter';
        }
        
        var searchEventInfo = ValidateSearchFormFields(searchFieldClass, false);
        if(searchEventInfo.validated) {
            if (isCurEvtFeed) {
                ReloadCurrentEventsTable(true);
            }
            else {
                ReloadUserHostedEventsTable(true);
            }
        }
        
        return false;
    });
}

function CloseSearchPanel()
{
    $('#modalOverlay').slideReveal("hide");
    $('#searchPanel').slideReveal("hide");
    return false;
}

function HandleWidthTransition(curWindowWidth, lastWidthClass, curWidthClass, initTransition)
{
    var isWidthTransition = false;
    var searchPanelWidth = Math.round(curWindowWidth * 0.65);
    if(curWindowWidth > 1000)  searchPanelWidth = Math.round(curWindowWidth * 0.45);
    
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

        // Convert time pickers to plain select lists, for ease of use on mobile devices
        $('#gameFilterStartTime').timepicker('option', { 'selectOnBlur': false, 'useSelect' : true });
        $('#gameFilterEndTime').timepicker('option', { 'selectOnBlur': false, 'useSelect' : true });

        // Force date selection alone on date pickers
        $('#gameFilterStartDate').prop('readonly', true);
        $('#gameFilterEndDate').prop('readonly', true);
        
        // Change text of toggle checkboxes to be shorter, for mobile view
        $('.overlayPanelToggleActiveLbl').filter(function(index) { return $(this).text() === "Activate Filter" }).text('Activate');
        $('.overlayPanelToggleActiveLbl').filter(function(index) { return $(this).text() === "Deactivate Filter" }).text('Deactivate');
		
	// Show event manager mobile control panel if in mobile view
        $('.dashboardTitle').show();
	$('.mobileButtonToolbarContainer').show();
        
        // Collapse events tables by combining text of certain columns and hiding other, unnecessary ones
        if(!initTransition) {
            FormatCurrentEventsTableForCurrentView(true, curWidthClass);
            FormatEventManagerTableForCurrentView(true, curWidthClass);
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
        
        // Convert time pickers back to normal selector type
        $('#gameFilterStartTime').timepicker('option', { 'selectOnBlur': true, 'useSelect' : false });
        $('#gameFilterEndTime').timepicker('option', { 'selectOnBlur': true, 'useSelect' : false });

        // Allow text entry into date picker fields
        $('#gameFilterStartDate').prop('readonly', false);
        $('#gameFilterEndDate').prop('readonly', false);
        
        // Change text of toggle checkboxes to full version, for desktop view
        $('.overlayPanelToggleActiveLbl').filter(function(index) { return $(this).text() === "Activate" }).text('Activate Filter');
        $('.overlayPanelToggleActiveLbl').filter(function(index) { return $(this).text() === "Deactivate" }).text('Deactivate Filter');
		
	// Hide event manager mobile control panel in desktop view
        $('.dashboardTitle').hide();
	$('.mobileButtonToolbarContainer').hide();

        // Restore hidden or combined columns
        if(!initTransition) {
            FormatCurrentEventsTableForCurrentView(false);
            FormatEventManagerTableForCurrentView(false);
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

function LoadEventManager()
{    
    // Initialize jTable on manageEventsContent div
    $(eventManagerJTableDiv).jtable({
        title: "Your Events",
        paging: true,
        pageSize: 10,
        pageSizes: [5, 10, 15, 20, 25],
        pageSizeChangeArea: true,
        pageList: 'minimal',
        sorting: true,
        defaultSorting: 'DisplayDate ASC',
        openChildAsAccordion: false,
        selecting: true,
        multiselect: true,
        selectingCheckboxes: true,
        selectOnRowClick: false,
        toolbar: {
            items:
            [
                {
                    text: 'Refresh',
                    icon: 'images/refresh.png',
                    tooltip: 'Refreshes your event list',
                    click: function(){
			var fullRefresh = false;
                        ReloadUserHostedEventsTable(fullRefresh);
                    }
                },
                {
                    text: 'Create',
                    icon: 'images/calendar.png',
                    tooltip: 'Create a new event',
                    click: function(){
			DisplayCreateEventDialog();
                    }
                },
                {
                    text: 'Activate Selected',
                    icon: 'images/activate.png',
                    tooltip: 'Makes selected events active & visible',
                    click: function(){
                        ToggleTableEventActivation("1");
                    }
                },
                {
                    text: 'Hide Selected',
                    icon: 'images/deactivate.png',
                    tooltip: 'Makes selected events inactive & invisible',
                    click: function(){
                        ToggleTableEventActivation("0");
                    }
                },
                {
                    text: 'Leave Selected',
                    icon: 'images/cancelsignup.png',
                    tooltip: 'Cancels enrollment for selected events',
                    click: function(){
                        LeaveSelectedEvents();
                    }
                },
                {
                    text: 'Delete Selected',
                    icon: 'images/delete.png',
                    tooltip: 'Permanently deletes selected events',
                    click: function(){
                        DeleteTableEvents();
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
            GameTitle: {
                title: 'Game',
                width: '11%',
                sorting: true
            },
            Platform: {
                title: 'Console',
                width: '11%',
                sorting: true
            },
            DisplayDate: {
                title: 'Date',
                width: '14%',
                sorting: true,
                display: function (data) {
                    var displayDateUTC = moment.utc(data.record.EventScheduledForDate, "YYYY-MM-DD HH:mm:ss");
                    var displayDateLocal = displayDateUTC.local();
                    var displayDateLocalText = displayDateLocal.format("MMM D, YYYY");
					
                    var $displayDate = $('<label title="Date in Time Zone where Event Created: ' + data.record.DisplayDate + '">' + displayDateLocalText + '</label>');
                    return $displayDate;
                }
            },
            DisplayTime: {
                title: 'Time',
                width: '12%',
                sorting: false,
                display: function (data) {
                    var displayDateUTC = moment.utc(data.record.EventScheduledForDate, "YYYY-MM-DD HH:mm:ss");
                    var displayDateLocal = displayDateUTC.local();
                    var displayTimeLocalText = displayDateLocal.format("h:mma");
					
                    var $displayTime = $('<label title="Time in Time Zone where Event Created: ' + data.record.DisplayTime + '">' + displayTimeLocalText + '</label>');
                    return $displayTime;
                }
            },
            Notes: {
                title: 'Game Notes',
                width: '21%',
                sorting: false
            },
            PlayersSignedUp: {
                title: 'Players Joined',
                width: '10%',
                display: function (data) {					
                    // Create PlayersSignedUp display object
                    var eventId = data.record.ID;
                    var $expandImage = $('<label>' + data.record.PlayersSignedUp + '&nbsp;&nbsp;<label id="lblEvt' + eventId + '" class="fa fa-plus-square" /></label>');
                    $expandImage.click(function () {
			var tableRow = $(this).closest('tr');
                        var curLblId = "#lblEvt" + eventId;
                        
                        // If we are just toggling the current child table display, close it, change icon back to + (expand), and return
                        if($(eventManagerJTableDiv).jtable('isChildRowOpen', tableRow, true)) {
                            $(eventManagerJTableDiv).jtable('closeChildTable', tableRow, void 0, true);
                            $(curLblId).attr('class', 'fa fa-plus-square');
                            return;
                        }
                        else {
                            ShowChildTableForJoinedPlayers(tableRow, curLblId, eventManagerJTableDiv);
                        }
                    });

                    // Return image for display in jTable
                    return $expandImage;
                },
                sorting: false
            },
            Hidden: {
                title: 'Hidden',
                width: '7%',
                sorting: true
            },
            EventCreator: {
                title: 'Creator',
                width: '7%',
                sorting: true,
                display: function (data) {
                    if(data.record.EventCreator !== 'ME') {
                        var $userNameDetailsPopupLink = $('<a href="#" class="actionLink" id="unDetailsLink' + data.record.ID + '">' + data.record.EventCreator + '</a>');
                        $userNameDetailsPopupLink.click(function () {
                            OpenUserDetailsPopup(data.record.EventCreatorUserID, data.record.EventCreator);
                            return false;
                        });

                        // Return link for display in jTable
                        return $userNameDetailsPopupLink;
                    }
                    else {
			return $('<label>' + data.record.EventCreator + '</label>');
                    }
                }
            },
            Actions: {
                title: 'Actions',
                width: '7%',
                sorting: false,
                columnSelectable: false,
                display: function (data) {
                    // Create EDIT or LEAVE link
                    if((data.record.Actions == 'EDIT') || (data.record.Actions == 'LEAVE')) {
			var $expandLink = $('<a href="#" class="actionLink" id="evtLink' + data.record.ID + '">' + data.record.Actions + '</a>');
				
			if(data.record.Actions === 'EDIT') {
                            $expandLink = $('<img src="images/edit.png" />');
                            
                            $expandLink.click(function () {
                                DisplayEditEventDialog(data.record.ID);
                            });
			}
			else {
                            $expandLink.click(function () {
				var eventIds = [data.record.ID];
				LeaveEvents(eventIds);
				return false;
                            });
			}
							
			// Return image for display in jTable
			return $expandLink;
                    }
                    else {
			return $('<label>' + data.record.Actions + '</label>');
                    }
                }
            }
        },
	recordsLoaded: function(event, data) {
            $(eventManagerJTableDiv + ' .jtable-data-row').each(function() {
		// Store PlayersSignedUpData as custom data attribute on each row, for use in child table expansion
		var id = $(this).attr('data-record-key');
		var dataRecordArray = $.grep(data.records, function (e) {
                    return e.ID === id;
                });
					
		var playerData = dataRecordArray[0].PlayersSignedUpData;
		$(this).attr('data-playersSignedUp', playerData);
									
		// Pre-load each child table, but do not show yet
		OpenChildTableForJoinedPlayers($(this), eventManagerJTableDiv);
                
                // Set forecolor of event row, depending on its attributes
                var isHidden = dataRecordArray[0].Hidden;                
		var isJoined = dataRecordArray[0].Actions;
                var eventCreator = dataRecordArray[0].EventCreator;
                var isPastEvent = IsPastEvent(dataRecordArray[0].EventScheduledForDate);
                
                // If an event is joined by current user, set forecolor to green
		if((isJoined === 'LEAVE') && (eventCreator !== 'ME') && !isPastEvent) {
                    $(this).css('color', 'green');
		}
		// If all required players are signed up for a given event,
		// such that it is "full", set forecolor to red
		else if((eventCreator !== 'ME') && (isJoined === 'FULL') && !isPastEvent) {
                    $(this).css('color', 'red');
		}
                // If a future, non-hidden event is created by current user, set forecolor to blue
                else if((eventCreator === 'ME') && (isHidden !== 'Yes') && !isPastEvent) {
                    $(this).css('color', 'blue');
                }
		// If event occurred in the past, or is a hidden event created by current user, set forecolor to gray
                // For all other cases (namely, other user's future events that are joinable by the current user),
                //  forecolor will be regular black
		else if((isHidden === 'Yes') || isPastEvent) {
                    $(this).css('color', 'gray');
		}
            });
			
            var curWidthClass = GetCurWidthClass();
            if(curWidthClass != 'desktop')  FormatEventManagerTableForCurrentView(true, curWidthClass);
	}
    });

    // Load event list
    var postData = 
        {
            action: eventManagerLoadAction,
            'evtStatus[]': 'showCreated'
        };
		
    $(eventManagerJTableDiv).jtable('load', postData);

    // Execute any post-startup logic
    EventManagerOnReady();
    /* ******************************************************************************************************** */
}

function LoadCurrentEventViewer()
{   
    // Initialize jTable on currentEventsContent div
    $(currentEventViewerJTableDiv).jtable({
        title: 'Browse Events Hosted By Other Users',
        paging: true,
        pageSize: 10,
        pageSizes: [5, 10, 15, 20, 25],
        pageSizeChangeArea: true,
        pageList: 'minimal',
        sorting: true,
        defaultSorting: 'DisplayDate ASC',
        openChildAsAccordion: false,
        selecting: true,
        multiselect: true,
        selectingCheckboxes: true,
        selectOnRowClick: false,
        toolbar: {
            items:
            [
                {
                    text: 'Refresh',
                    icon: 'images/refresh.png',
                    tooltip: 'Refreshes current event list',
                    click: function(){
			var fullRefresh = false;
                        ReloadCurrentEventsTable(fullRefresh);
                    }
                },
                {
                    text: 'Join Selected',
                    icon: 'images/signup.png',
                    tooltip: 'Signs you up for all selected events',
                    click: function(){
                        JoinSelectedEvents();
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
                title: 'User',
                width: '8%',
                sorting: true,
                display: function (data) {
                    var $userNameDetailsPopupLink = $('<a href="#" class="actionLink" id="unDetailsLink' + data.record.ID + '">' + data.record.UserName + '</a>');
                    $userNameDetailsPopupLink.click(function () {
                        OpenUserDetailsPopup(data.record.UserID, data.record.UserName);
                        return false;
                    });
                    
                    // Return link for display in jTable
                    return $userNameDetailsPopupLink;
                }
            },
            GameTitle: {
                title: 'Game',
                width: '15%',
                sorting: true
            },
            Platform: {
                title: 'Console',
                width: '11%',
                sorting: true
            },
            DisplayDate: {
                title: 'Date',
                width: '14%',
                sorting: true,
                display: function (data) {
                    var displayDateUTC = moment.utc(data.record.EventScheduledForDate, "YYYY-MM-DD HH:mm:ss");
                    var displayDateLocal = displayDateUTC.local();
                    var displayDateLocalText = displayDateLocal.format("MMM D, YYYY");
					
                    var $displayDate = $('<label title="Date in Event Creator Time Zone: ' + data.record.DisplayDate + '">' + displayDateLocalText + '</label>');
                    return $displayDate;
                }
            },
            DisplayTime: {
                title: 'Time',
                width: '12%',
                sorting: false,
                display: function (data) {
                    var displayDateUTC = moment.utc(data.record.EventScheduledForDate, "YYYY-MM-DD HH:mm:ss");
                    var displayDateLocal = displayDateUTC.local();
                    var displayTimeLocalText = displayDateLocal.format("h:mma");
					
                    var $displayTime = $('<label title="Time in Event Creator Time Zone: ' + data.record.DisplayTime + '">' + displayTimeLocalText + '</label>');
                    return $displayTime;
                }
            },
            Notes: {
                title: 'Game Notes',
                width: '22%',
                sorting: false,
            },
            Actions: {
                title: 'Actions',
                width: '7%',
                sorting: true,
                display: function (data) {
                    // Create JOIN link
                    if(data.record.Actions == 'JOIN') {
			var $expandLink = $('<a href="#" class="actionLink" id="evtLink' + data.record.ID + '">' + data.record.Actions + '</a>');				
                        $expandLink.click(function () {
                            var eventIds = [data.record.ID];
                            JoinEvents(eventIds);
                            return false;
                        });
							
			// Return image for display in jTable
			return $expandLink;
                    }
                    else {
			return $('<label>' + data.record.Actions + '</label>');
                    }
                }
            },
            PlayersSignedUp: {
                title: 'Players Joined',
                width: '11%',
                display: function (data) {					
                    // Create PlayersSignedUp display object
                    var eventId = data.record.ID;
                    var $expandImage = $('<label>' + data.record.PlayersSignedUp + '&nbsp;&nbsp;<label id="lblCurEvt' + eventId + '" class="fa fa-plus-square" /></label>');
                    $expandImage.click(function () {
			var tableRow = $(this).closest('tr');
                        var curLblId = "#lblCurEvt" + eventId;
                        
                        // If we are just toggling the current child table display, close it, change icon back to + (expand), and return
                        if($(currentEventViewerJTableDiv).jtable('isChildRowOpen', tableRow, true)) {
                            $(currentEventViewerJTableDiv).jtable('closeChildTable', tableRow, void 0, true);
                            $(curLblId).attr('class', 'fa fa-plus-square');
                            return;
                        }
                        else {
                            ShowChildTableForJoinedPlayers(tableRow, curLblId, currentEventViewerJTableDiv);
                        }
                    });

                    // Return image for display in jTable
                    return $expandImage;
                },
                sorting: false
            }
        },
	recordsLoaded: function(event, data) {
            // Set total count of games that need joining
            if((data.records !== null) && (data.records.length > 0)) {
		var totalGameCount = data.records[0].TotalGamesToJoinCount;
		var needText = "need";
		if(totalGameCount == 1) {
                    needText = "needs";
		}
				
		$('#totalGamesToJoin').text('(' + totalGameCount + ') ' + needText + ' joining!');
            }
				
            $(currentEventViewerJTableDiv + ' .jtable-data-row').each(function() {
		// Store PlayersSignedUpData as custom data attribute on each row, for use in child table expansion
		var id = $(this).attr('data-record-key');
		var dataRecordArray = $.grep(data.records, function (e) {
                    return e.ID === id;
		});
										
		var playerData = dataRecordArray[0].PlayersSignedUpData;
		$(this).attr('data-playersSignedUp', playerData);
																
		// Pre-load each child table, but do not show yet
		OpenChildTableForJoinedPlayers($(this), currentEventViewerJTableDiv);
									
		var isJoined = dataRecordArray[0].Actions;
		// If all required players are signed up for a given event,
		// such that it is "full", set forecolor to red
		if((isJoined === 'FULL') && (!IsPastEvent(dataRecordArray[0].EventScheduledForDate))) {
                    $(this).css('color', 'red');
		}
		// If event occurred in the past, set forecolor to gray
		else if(isJoined !== 'JOIN') {
                    $(this).css('color', 'gray');
		}				
            });
				
            var curWidthClass = GetCurWidthClass();
            if(curWidthClass != 'desktop')  FormatCurrentEventsTableForCurrentView(true, curWidthClass);
	}
    });

    // Load event list
    var postData = 
        {
            action: currentEventViewerLoadAction
        };
		
    $(currentEventViewerJTableDiv).jtable('load', postData);

    // Execute any post-startup logic
    CurrentEventViewerOnReady();
    /* ******************************************************************************************************** */
}

function FormatEventManagerTableForCurrentView(isMobile, curWidthClass)
{
    var hiddenClass = "evtMgrHiddenInMobileView";
    if(isMobile) {
	// Temporarily show hidden columns, to allow for column combination to work properly in all cases
	$('.' + hiddenClass).removeClass(hiddenClass);
		
	// Temporarily remove fixed-width scrollable container, to allow for column combination to work properly in all cases
	$(eventManagerJTableDiv + ' .fixedWidthScrollableContainer .jtable').unwrap();
		
        // Collapse Game & Console columns into one vertical column,
        // and Scheduled Date & Scheduled Time columns into another vertical column
        var colsToCombine = ["Game", "Console"];
        var colsToCombineBlankSeparatorLine = {"Game": false, "Console": true};
        CombineTableColumns(colsToCombine, colsToCombineBlankSeparatorLine, eventManagerJTableDiv, hiddenClass);

        colsToCombine = ["Date", "Time"];
        colsToCombineBlankSeparatorLine = {"Date": false, "Time": false};
        CombineTableColumns(colsToCombine, colsToCombineBlankSeparatorLine, eventManagerJTableDiv, hiddenClass);
		
        $(eventManagerJTableDiv + ' .jtable-title-text').text('YOUR EVENTS');

        // Hide all toolbar items (in preparation to show mobile dashboard)
        $(eventManagerJTableDiv + ' .jtable-toolbar-item').hide();
        
        // Hide "Game Notes" and "Hidden" columns
        var gameNotesColHdr = $(eventManagerJTableDiv + ' th:contains("Game Notes")');
        var hiddenColHdr = $(eventManagerJTableDiv + ' th:contains("Hidden")');
        
        var gameNotesColIdx = $(gameNotesColHdr).index();
        var hiddenColIdx = $(hiddenColHdr).index();
        
        $(gameNotesColHdr).addClass(hiddenClass);
        $(hiddenColHdr).addClass(hiddenClass);
                
	$(eventManagerJTableDiv).children('.jtable-main-container').children('.jtable').children('tbody').children('tr').each(function() {
            var gameNotesCol = $(this).children('td').eq(gameNotesColIdx);
            var hiddenCol = $(this).children('td').eq(hiddenColIdx);
            $(gameNotesCol).addClass(hiddenClass);
            $(hiddenCol).addClass(hiddenClass);
        });
        
        // Hide page size change area
        $(eventManagerJTableDiv + ' .jtable-page-size-change').hide();
        
        // Hide go-to-page area
        if(curWidthClass == 'xtraSmall') {
            $(eventManagerJTableDiv + ' .jtable-goto-page').hide();
        }
        
	// Reduce font size of table text to limit need for table overflow
	$(eventManagerJTableDiv + ' table').removeClass('desktopViewFontSize').addClass('mobileViewFontSize');
		
	// Enclose jTable containers in fixed-width scrollable divs
        $(eventManagerJTableDiv + ' .jtable-main-container').children('.jtable').wrap('<div class="fixedWidthScrollableContainer"></div>');
        
        // Adjust fixed-width scrollable container to maximum available width
        if($(eventManagerJTableDiv + ' .jtable-title').is(':visible')) {
            var titleBarWidth = $(eventManagerJTableDiv + ' .jtable-title').width();
            $(eventManagerJTableDiv + ' .fixedWidthScrollableContainer').css('width', (titleBarWidth + 10) + 'px');
        }
    }
    else {        
        // Expand Game & Console columns into two distinct columns again;
        // do same for Scheduled Date & Scheduled Time columns
        var colsToExpand = ["Console"];
        ExpandTableColumn("Game", colsToExpand, eventManagerJTableDiv, hiddenClass);
            
        colsToExpand = ["Time"];
        ExpandTableColumn("Date", colsToExpand, eventManagerJTableDiv, hiddenClass);

        // Change table header text to non-caps version
        $(eventManagerJTableDiv + ' .jtable-title-text').text('Your Events');
        
        // Show toolbar items
        $(eventManagerJTableDiv + ' .jtable-toolbar-item').show();
        
        // Show hidden columns
        $('.' + hiddenClass).removeClass(hiddenClass);
        
        // Display page size change area
        $(eventManagerJTableDiv + ' .jtable-page-size-change').show();
        
        // Display go-to-page area
        $(eventManagerJTableDiv + ' .jtable-goto-page').show();
		
	// Make font size normal again
	$(eventManagerJTableDiv + ' table').removeClass('mobileViewFontSize').addClass('desktopViewFontSize');
		
	// Remove fixed-width scrollable container
	$(eventManagerJTableDiv + ' .fixedWidthScrollableContainer .jtable').unwrap();
    }
}

function FormatCurrentEventsTableForCurrentView(isMobile, curWidthClass)
{
    var hiddenClass = "curEvtsHiddenInMobileView";
	
    if(isMobile) {
	// Temporarily show hidden columns, to allow for column combination to work properly in all cases
	$('.' + hiddenClass).removeClass(hiddenClass);
		
	// Temporarily remove fixed-width scrollable container, to allow for column combination to work properly in all cases
	$(currentEventViewerJTableDiv + ' .fixedWidthScrollableContainer .jtable').unwrap();
		
        // Collapse Game & Console columns into one vertical column,
        // and Scheduled Date & Scheduled Time columns into another vertical column
        var colsToCombine = ["Game", "Console"];
        var colsToCombineBlankSeparatorLine = {"Game": false, "Console": true};
        CombineTableColumns(colsToCombine, colsToCombineBlankSeparatorLine, currentEventViewerJTableDiv, hiddenClass);

        colsToCombine = ["Date", "Time"];
        colsToCombineBlankSeparatorLine = {"Date": false, "Time": false};
        CombineTableColumns(colsToCombine, colsToCombineBlankSeparatorLine, currentEventViewerJTableDiv, hiddenClass);
        
        // Change table header and toolbar text to shorter, mobile-friendly words
        if(curWidthClass == 'mobile')           $(currentEventViewerJTableDiv + ' .jtable-title-text').text('BROWSE USER EVENTS');
        else if(curWidthClass == 'xtraSmall')   $(currentEventViewerJTableDiv + ' .jtable-title-text').text('EVENT LIST');
        
        $(currentEventViewerJTableDiv + ' .jtable-toolbar-item-text:contains("Join Selected")').text('Join');
        
        // Hide "Game Notes" and "Actions" columns
        var gameNotesColHdr = $(currentEventViewerJTableDiv + ' th:contains("Game Notes")');
        
        var actionsColHdr = $(currentEventViewerJTableDiv + ' th').filter(function() {
            return $(this).text() === "Actions";
        }).eq(0);
        
        var gameNotesColIdx = $(gameNotesColHdr).index();
        var actionsColIdx = $(actionsColHdr).index();
        
        $(gameNotesColHdr).addClass(hiddenClass);
        $(actionsColHdr).addClass(hiddenClass);
                
	$(currentEventViewerJTableDiv).children('.jtable-main-container').children('.jtable').children('tbody').children('tr').each(function() {
            var gameNotesCol = $(this).children('td').eq(gameNotesColIdx);
            var actionsCol = $(this).children('td').eq(actionsColIdx);
            $(gameNotesCol).addClass(hiddenClass);
            $(actionsCol).addClass(hiddenClass);
        });
        
        // Hide page size change area
        $(currentEventViewerJTableDiv + ' .jtable-page-size-change').hide();
        
        // Hide go-to-page area
        if(curWidthClass == 'xtraSmall') {
            $(currentEventViewerJTableDiv + ' .jtable-goto-page').hide();
        }
        
	// Reduce font size of table text to limit need for table overflow
	$(currentEventViewerJTableDiv + ' table').removeClass('desktopViewFontSize').addClass('mobileViewFontSize');
		
	// Enclose jTable containers in fixed-width scrollable divs
        $(currentEventViewerJTableDiv + ' .jtable-main-container').children('.jtable').wrap('<div class="fixedWidthScrollableContainer"></div>');
        
        // Adjust fixed-width scrollable container to maximum available width
        if($(currentEventViewerJTableDiv + ' .jtable-title').is(':visible')) {
            var titleBarWidth = $(currentEventViewerJTableDiv + ' .jtable-title').width();
            $(currentEventViewerJTableDiv + ' .fixedWidthScrollableContainer').css('width', (titleBarWidth + 10) + 'px');
        }
    }
    else {
        // Expand Game & Console columns into two distinct columns again;
        // do same for Scheduled Date & Scheduled Time columns
        var colsToExpand = ["Console"];
        ExpandTableColumn("Game", colsToExpand, currentEventViewerJTableDiv, hiddenClass);
            
        colsToExpand = ["Time"];
        ExpandTableColumn("Date", colsToExpand, currentEventViewerJTableDiv, hiddenClass);

        // Change table header and toolbar text to full-length versions
        $(currentEventViewerJTableDiv + ' .jtable-title-text').text('Browse Events Hosted By Other Users');
        $(currentEventViewerJTableDiv + ' .jtable-toolbar-item-text:contains("Join")').text('Join Selected');
        
        // Show hidden columns
        $('.' + hiddenClass).removeClass(hiddenClass);
        
        // Display page size change area
        $(currentEventViewerJTableDiv + ' .jtable-page-size-change').show();
        
        // Display go-to-page area
        $(currentEventViewerJTableDiv + ' .jtable-goto-page').show();
		
	// Make font size normal again
	$(currentEventViewerJTableDiv + ' table').removeClass('mobileViewFontSize').addClass('desktopViewFontSize');
			
	// Remove fixed-width scrollable container
	$(currentEventViewerJTableDiv + ' .fixedWidthScrollableContainer .jtable').unwrap();
    }
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

function CurrentEventViewerOnReady()
{

}

function EventManagerOnReady()
{

}

function IsPastEvent(eventScheduledForDate)
{
    var curMoment = moment().utc();
	
    // Event date already in UTC, and is in 24-hr format
    var gameTimeMoment = moment(eventScheduledForDate + " +0000", "YYYY-MM-DD HH:mm:ss Z");
    if (gameTimeMoment.isBefore(curMoment)) {
	return true;
    }

    return false;
}

function DeselectAllJTableRows(jTableContainer)
{
    // Deselect rows (remove highlight)
    $(jTableContainer).find(".jtable-row-selected").each(function() {
        $(this).removeClass('jtable-row-selected');
    });

    // Deselect selecting checkboxes, if any
    $(jTableContainer).find(".jtable-selecting-column > input").each(function() {
	$(this).prop("checked", false);
    });
	
    // Deselect Select/Deselect All checkbox in header of table
    $(jTableContainer).find(".jtable-command-column-header.jtable-column-header-selecting > input").each(function() {
        $(this).prop("checked", false);
    });
}

function DeselectJTableRowsByKey(jTableContainer, keys)
{
    for(var i = 0; i < keys.length; i++) {
        var curRowKey = keys[i];
        
        $(jTableContainer).find(".jtable-row-selected[data-record-key='" + curRowKey + "']").each(function() {
            // Deselect rows (remove highlight)
            $(this).removeClass('jtable-row-selected');
            
            // Deselect selecting checkboxes, if any
            $(this).find(".jtable-selecting-column > input").each(function() {
                $(this).prop("checked", false);
            });
        });
    }
}

function ToggleTableEventActivation(isActive)
{
    var $selectedRows = $(eventManagerJTableDiv).jtable('selectedRows');

    if($selectedRows.length === 0) {
        sweetAlert("No events selected");
        return;
    }
    
    var selectedEventIds = [];
    var eventsToDeselect = [];
    $selectedRows.each(function() {
        var id = $(this).data('record').ID;
        var eventCreator = $(this).data('record').EventCreator;
            
        // Only try to toggle hidden status for events created by this user
        if(eventCreator === 'ME') {
            selectedEventIds.push(id);
        }
        else {
            eventsToDeselect.push(id);
        }
    });

    if(selectedEventIds.length === 0) {
        sweetAlert("ERROR", "No selected events may be edited: you can only change event status for events you own", "error");
        DeselectJTableRowsByKey(eventManagerJTableDiv, eventsToDeselect);
    }
    else if(!ToggleEventVisibility(selectedEventIds, isActive)) {
        // Reject event activation request (de-select rows)
        DeselectAllJTableRows(eventManagerJTableDiv);
    }   
}

function DeleteTableEvents()
{
    var $selectedRows = $(eventManagerJTableDiv).jtable('selectedRows');
    if($selectedRows.length === 0) {
        sweetAlert("No events selected");
        return;
    }

    var selectedEventIds = [];
    var eventsToDeselect = [];
    $selectedRows.each(function() {
        var id = $(this).data('record').ID;
        var eventCreator = $(this).data('record').EventCreator;
            
        // Only try to delete events created by this user
        if(eventCreator === 'ME') {
            selectedEventIds.push(id);
        }
        else {
            eventsToDeselect.push(id);
        }
    });

    if(selectedEventIds.length === 0) {
        sweetAlert("ERROR", "No selected events may be deleted: you can only delete events you have created", "error");
        DeselectJTableRowsByKey(eventManagerJTableDiv, eventsToDeselect);
    }
    else if(!DeleteEvents(selectedEventIds)) {
        // Reject event deletion request (de-select rows)
        DeselectAllJTableRows(eventManagerJTableDiv);
    }
}

function JoinSelectedEvents()
{
    var $selectedRows = $(currentEventViewerJTableDiv).jtable('selectedRows');
    if($selectedRows.length === 0) {
        sweetAlert("No events selected");
        return;
    }

    var selectedEventIds = [];
    var eventsToDeselect = [];
    $selectedRows.each(function() {
        var id = $(this).data('record').ID;
        var isJoined = $(this).data('record').Joined;
            
        // Only try to join events that this user has not already joined
        if(isJoined === 'JOIN') {
            selectedEventIds.push(id);
        }
        else {
            eventsToDeselect.push(id);
        }
    });

    if(selectedEventIds.length === 0) {
        sweetAlert("No selected events may be joined: you can only join future events which are not full, and which you haven't already joined");
        DeselectJTableRowsByKey(currentEventViewerJTableDiv, eventsToDeselect);
    }
    else if(!JoinEvents(selectedEventIds)) {
        // De-select any rows for events the user has already joined
        DeselectJTableRowsByKey(currentEventViewerJTableDiv, eventsToDeselect);
    }
}

function LeaveSelectedEvents()
{
    var $selectedRows = $(eventManagerJTableDiv).jtable('selectedRows');
    if($selectedRows.length === 0) {
        sweetAlert("No events selected");
        return;
    }

    var selectedEventIds = [];
    var eventsToDeselect = [];
    $selectedRows.each(function() {
        var id = $(this).data('record').ID;
        var action = $(this).data('record').Actions;
            
        // Only try to leave events that this user has already joined
        if(action === 'LEAVE') {
            selectedEventIds.push(id);
        }
        else {
            eventsToDeselect.push(id);
        }
    });

    if(selectedEventIds.length === 0) {
        sweetAlert("No selected events may be left: you can only leave events scheduled for the future, and which you have already joined");
        DeselectJTableRowsByKey(eventManagerJTableDiv, eventsToDeselect);
    }
    else if(!LeaveEvents(selectedEventIds)) {
        // De-select any rows for events for which the user is not a member
        DeselectJTableRowsByKey(eventManagerJTableDiv, eventsToDeselect);
    }
}

function OpenChildTableForJoinedPlayers(tableRow, jTableDiv)
{        
    $(jTableDiv).jtable('openChildTable', tableRow,
        {
            title: "",
            childTableNoReloadOnOpen: true,
            actions: {
                listAction: function() {
                    return GetChildDataForRow(tableRow);
                }
            },
            fields: {
                ID: {
                    key: true,
                    list: false
                },
                PlayerName: {
                    title: 'Player Name',
                    width: '55%',
                    sorting: true,
                    display: function (data) {
                        var $userNameDetailsPopupLink = $('<a href="#" class="actionLink" id="unDetailsLink' + data.record.ID + '">' + data.record.PlayerName + '</a>');
                        $userNameDetailsPopupLink.click(function () {
                            OpenUserDetailsPopup(data.record.UserID, data.record.PlayerName);
                            return false;
                        });

                        // Return link for display in jTable
                        return $userNameDetailsPopupLink;
                    }
                },
                GamerTags: {
                    title: 'View Gamer Tags',
                    width: '45%',
                    sorting: false,
                    display: function (data) {
			var $tagViewerLink = $('<a href="#" class="actionLink" id="tagsLink' + data.record.ID + '">Show Tags</a>');

                        $tagViewerLink.click(function () {
                            OpenGamerTagViewer(gamerTagViewerDlg, gamerTagViewerJTableDiv.substring(1), "Gamer Tag Viewer", 
                                               "Gamer Tags For: " + data.record.PlayerName, true, true, data.record.UserID);
                            return false;
                        });
							
			// Return link for display in jTable
			return $tagViewerLink;
                    }
                }
            },
            recordsLoaded: function() {
                // Customize child table appearance
                $(this).find('table.jtable > tbody > tr')
                    .each(function() {
                        $(this).addClass('customTheme');
                    });

                    // Do not let child table expand to fill container
                    $(this).find('table.jtable')
                        .each(function() {
                            $(this).addClass('jTableChild');
                        });
            }
        },
        function(data) {
            data.childTable.jtable('load', {});
        });
}

function ShowChildTableForJoinedPlayers(tableRow, curLblId, jTableDiv)
{    
    // Close all other child tables (accordion-style)
    tableRow.siblings(jTableDiv + ' .jtable-data-row').each(function () {
        $(jTableDiv).jtable('closeChildTable', $(this), void 0, true);
    });
    
    // Change all event row child table toggle icons to "+" (expand)
    $(jTableDiv + ' .fa.fa-minus-square').each(function() {
            $(this).attr('class', 'fa fa-plus-square')
        }
    );
    
    $(jTableDiv).jtable('showChildTable', tableRow);
	
    // Change expand icon to collapse icon for this child table's parent row
    $(curLblId).attr('class', 'fa fa-minus-square');    
}

function GetChildDataForRow(tableRow)
{
    var signedUpPlayersText = tableRow.attr('data-playersSignedUp');
    var signedUpPlayerArray = signedUpPlayersText.split(',');
    var playerCount = signedUpPlayerArray.length;
    var records = [];
	
    for(var i = 0; i < signedUpPlayerArray.length; i++) {
	var playerDataArray = signedUpPlayerArray[i].split('|');
	records.push({ "ID": playerDataArray[0], "PlayerName": playerDataArray[1], "UserID": playerDataArray[2]});
    }
	
    return {
	"Result": "OK",
	"Records": records,
	"TotalRecordCount": playerCount
    };
}

function ReloadGameTitleSelector(eventId)
{
    var eventIdSuffix = "";
    if(eventId > -1) {
        eventIdSuffix = eventId.toString();
    }
    
    // Make AJAX call to refresh game title selector
    $.ajax({
        type: "POST",
        url: "AJAXHandler.php",
        data: "action=ReloadGameTitleSelector",
        success: function(response){
            $('#gameSelectorDiv' + eventIdSuffix).html(response);
            
            // Convert reloaded game selector dropdown list to a jQuery-powered comboBox for game title selection or entry
            PrepareAutocompleteComboBox("selGameTitle" + eventIdSuffix);
            $('#ddlGameTitles' + eventIdSuffix).combobox();
        },
        error: function() {
            var dfltSelectHtml =    '<select id="ddlGameTitles' + eventIdSuffix + '" name="ddlGameTitles' + eventIdSuffix + '">' +
                                        '<option value="-1" class="globalGameOption">' + 
                                            'Unable to reload game title selector...please try again later' + 
                                        '</option>' +
                                    '</select>';
								 
            $('#gameSelectorDiv' + eventIdSuffix).html(dfltSelectHtml);
        }
    });
}

function ToggleEventVisibility(selectedEventIds, isActive)
{
    var eventText = 'all selected events';
    if(selectedEventIds.length === 1) {
        eventText = 'this event';
    }
    
    var confirmMsg = 'Are you sure you want to hide ' + eventText + '?';
    if(isActive === '1') {
        confirmMsg = 'Are you sure you want to make ' + eventText + ' visible?';
    }
    
    sweetAlert({
      title: "Confirm Event Change",
      text: confirmMsg,
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Yes, do it!",
      closeOnConfirm: false,
      closeOnCancel: false,
      showLoaderOnConfirm: true
   },
   function(isConfirm) {
      if(isConfirm) {
        // Serialize array of selected event IDs for POST Ajax call
	var eventIdsForPost = [];
	for(var i = 0; i < selectedEventIds.length; i++) {
            eventIdsForPost.push({"name":"eventIds[]", "value": selectedEventIds[i].toString()});
	}
		
	// Make AJAX call to update Active status for given events
	$.ajax({
            type: "POST",
            url: "AJAXHandler.php",
            data: "action=EventEditorToggleEventVisibility&" + $.param({'eventIds': selectedEventIds}) + "&isActive=" + isActive,
            success: function(response){
                var fullRefresh = false;
                ReloadUserHostedEventsTable(fullRefresh);
                
                if(response.match("^SYSTEM ERROR")) {
                    sweetAlert("Events Not Changed", response, "error");
                }
                else {
                    // Show success message
                    sweetAlert("Events Changed!", response, "success");
                }
            },
            error: function() {
		sweetAlert("Events Not Changed", "Unable to change visibility for selected events: server error. Please try again later.", "error");
            }
        });
      }
      else {
         // Show cancel message
         sweetAlert("Event Change Canceled", "Your events' visibility has not been changed", "info");
      }
   });
}

function DeleteEvents(selectedEventIds)
{ 
    sweetAlert({
      title: "Confirm Delete",
      text: "Are you sure you want to delete all selected events?",
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Yes, do it!",
      closeOnConfirm: false,
      closeOnCancel: false,
      showLoaderOnConfirm: true
   },
   function(isConfirm) {
      if(isConfirm) {
         // Make AJAX call to update Active status for given events
	$.ajax({
            type: "POST",
            url: "AJAXHandler.php",
            data: "action=EventEditorDeleteEvents&" + $.param({'eventIds': selectedEventIds}),
            success: function(response){
                var fullRefresh = false;
                ReloadUserHostedEventsTable(fullRefresh);
                
                if(response.match("^SYSTEM ERROR")) {
                    sweetAlert("Events Not Deleted", response, "error");
                }
                else {
                    // Show success message
                    sweetAlert("Events Deleted!", response, "success");
                }
            },
            error: function() {
		sweetAlert("Events Not Deleted", "Unable to delete events: server error. Please try again later.", "error");
            }
        });
      }
      else {
         // Show cancel message
         sweetAlert("Events Deletion Canceled", "Your events have not been deleted", "info");
      }
   });
}
		
function JoinEvents(selectedEventIds)
{
    sweetAlert({
      title: "Confirm Join",
      text: "Join this Event?",
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "You Bet!",
      closeOnConfirm: false,
      closeOnCancel: false,
      showLoaderOnConfirm: true
   },
   function(isConfirm) {
      if(isConfirm) {
         // Make AJAX call to sign current user up for selected events
	$.ajax({
            type: "POST",
            url: "AJAXHandler.php",
            data: "action=EventViewerJoinEvents&" + $.param({'eventIds': selectedEventIds}),
            success: function(response){
		var fullRefresh = false;
		ReloadCurrentEventsTable(fullRefresh);
                ReloadUserHostedEventsTable(fullRefresh);
		
                if(response.match("^SYSTEM ERROR")) {
                    sweetAlert("Events Not Joined", response, "error");
                }
                else {
                    // Show success message
                    sweetAlert("Events Joined!", response, "success");
                }
            },
            error: function() {
		sweetAlert("Events Not Joined", "Unable to join events: server error. Please try again later.", "error");
            }
        });
      }
      else {
         // Show cancel message
         sweetAlert("Events Not Joined", "Canceled join events", "info");
      }
   });
}

function LeaveEvents(selectedEventIds)
{
    sweetAlert({
      title: "Confirm Leave",
      text: "Leave this Event?",
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Yep, I want out!",
      closeOnConfirm: false,
      closeOnCancel: false,
      showLoaderOnConfirm: true
   },
   function(isConfirm) {
      if(isConfirm) {
         // Make AJAX call to remove current user from selected events
	$.ajax({
            type: "POST",
            url: "AJAXHandler.php",
            data: "action=EventViewerLeaveEvents&" + $.param({'eventIds': selectedEventIds}),
            success: function(response){
		var fullRefresh = false;
		ReloadCurrentEventsTable(fullRefresh);
                ReloadUserHostedEventsTable(fullRefresh);
                
                if(response.match("^SYSTEM ERROR")) {
                    sweetAlert("Events Not Left", response, "error");
                }
                else {
                    sweetAlert("Events Left!", response, "success");
                }
            },
            error: function() {
		sweetAlert("Events Not Left", "Unable to leave events: server error. Please try again later.", "error");
            }
        });
      }
      else {
         // Show cancel message
         sweetAlert("Events Not Left", "Canceled leave events", "info");
      }
   });
}

function ReloadUserHostedEventsTable(fullRefresh)
{    
    if(fullRefresh) {
        var searchFormData = ValidateSearchFormFields('.searchPanelEvtMgrFilter', true);
        var postData = ('action=' + eventManagerLoadAction) + searchFormData.postData;
        $(eventManagerJTableDiv).jtable('load', postData);
    }
    else {
        // Reload event list with same POST arguments
        $(eventManagerJTableDiv).jtable('reload');
    }
}

function ReloadCurrentEventsTable(fullRefresh)
{    
    if(fullRefresh) {
        var searchFormData = ValidateSearchFormFields('.searchPanelCurEvtsFilter', true);
        var postData = ('action=' + currentEventViewerLoadAction) + searchFormData.postData;
        $(currentEventViewerJTableDiv).jtable('load', postData);   
    }
    else {
        // Reload event list with same POST arguments
        $(currentEventViewerJTableDiv).jtable('reload');
    }
}

function DisplayCreateEventDialog()
{
    var curWidthClass = GetCurWidthClass();
    var curHeightClass = GetCurHeightClass();
    var displayContainerPosition = "top";
    var dlgWidth = 600;
    var dlgHeight = 700;
    
    if(curWidthClass == 'mobile') {
        dlgWidth = 400;
        displayContainerPosition = "top+10%";
    }
    if(curWidthClass == 'xtraSmall') {
        dlgWidth = 275;
        displayContainerPosition = "top+10%";
    }
    
    if((curHeightClass == 'mobile') || (curHeightClass == 'xtraSmall')) {
        dlgHeight = 450;
    }
    
    displayJQueryDialog("dlgCreateEvt", "Create Event", "top", displayContainerPosition, window, false, true, 
                        "AJAXHandler.php?action=EventEditorLoad", function() {
        var eventId = -1;
        EventSchedulerDialogOnReady(eventId, $('#dlgCreateEvt').dialog(), curWidthClass);
    }, dlgWidth, dlgHeight);
}

function DisplayEditEventDialog(eventId)
{
    var curWidthClass = GetCurWidthClass();
    var curHeightClass = GetCurHeightClass();
    var displayContainerPosition = "top";
    var dlgWidth = 600;
    var dlgHeight = 700;
    
    if(curWidthClass == 'mobile') {
        dlgWidth = 400;
        displayContainerPosition = "top+10%";
    }
    if(curWidthClass == 'xtraSmall') {
        dlgWidth = 275;
        displayContainerPosition = "top+10%";
    }
    
    if((curHeightClass == 'mobile') || (curHeightClass == 'xtraSmall')) {
        dlgHeight = 450;
    }
    
    displayJQueryDialog("dlgEditEvent" + eventId, "Edit Event", "top", displayContainerPosition, window, false, true, 
                        "AJAXHandler.php?action=EventEditorLoad&EventID=" + eventId, function() {
        EventSchedulerDialogOnReady(eventId, $('#dlgEditEvent' + eventId).dialog(), curWidthClass);
    }, dlgWidth, dlgHeight);
}

function EventSchedulerDialogOnReady(eventId, $dialog, curWidthClass)
{
    var eventIdSuffix = "";
    var gameTime = "";
    
    if(eventId > -1) {
        eventIdSuffix = eventId.toString();
        
        // Attach event handler to Edit Event button
        $('#editEventBtn' + eventIdSuffix).click(function() {
            return EditEvent(eventIdSuffix, $dialog);
        });
        
        // Attach event handler to Toggle Event Visibility button
        var eventVisibilityBtn = '#toggleEventVisibilityBtn' + eventIdSuffix;
        $(eventVisibilityBtn).click(function() {
            var action = $(eventVisibilityBtn).attr('myAction');
            var eventIds = [ eventId ];
            ToggleEventVisibility(eventIds, action);
            $dialog.dialog('destroy').remove();
            return false;
        });
        
        // Attach event handler to Delete Event button
        $('#deleteEventBtn' + eventIdSuffix).click(function() {
            var eventIds = [ eventId ];
            DeleteEvents(eventIds);
            $dialog.dialog('destroy').remove();
            return false;
        });
        
        // Create jQuery-powered comboBox for game title selection or entry
        PrepareAutocompleteComboBox("selGameTitle" + eventIdSuffix);
        $('#ddlGameTitles' + eventIdSuffix).combobox();
        
        // Change dialog title to Editing Event: 'game' @ 'datetime'
        var selGameTitle = $('#selGameTitle' + eventIdSuffix).val();
        var gameDate = $('#gameDate' + eventIdSuffix).val();
        gameTime = $('#gameTime' + eventIdSuffix).val();

        var title = 'Editing Event: "' + selGameTitle + '" @ ' + gameDate + ' ' + gameTime;
        $dialog.dialog('option', 'title', title);
    }
    else {
        // Create jQuery-powered comboBox for game title selection or entry
        PrepareAutocompleteComboBox("selGameTitle");
        $('#ddlGameTitles').combobox();
        
        // Attach event handler to Create Event button
        $('#createEventBtn').click(function() {
            return CreateEvent($dialog);
        });
    }

    // Attach event handler to Cancel Event Creation/Update button
    $('#cancelEventBtn' + eventIdSuffix).click(function() {
        $dialog.dialog('destroy').remove();
        return false;
    });
    
    // Initialize Game Scheduled Date datepicker
    $('#gameDate' + eventIdSuffix).datepicker({
         inline: true,
         yearRange: '-0:+1',
         changeYear: true,
         changeMonth: true,
         constrainInput: true,
         showButtonPanel: true,
         showOtherMonths: true,
         dayNamesMin: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
         dateFormat: 'yy-mm-dd'
    });
    
    // Initialize Game Scheduled Time datepicker
    var minTime = null;
    if(gameTime.length === 0) {
        var defaultTimeDate = new Date();
        var nextRoundIntervalValue = 15 - (defaultTimeDate.getMinutes() % 15);
        defaultTimeDate.setMinutes(defaultTimeDate.getMinutes() + nextRoundIntervalValue);
        minTime = defaultTimeDate;
    }
    else {
        minTime = gameTime;
    }

    var isMobile = isMobileView();

    var options = {
        disableTextInput: false,
        disableTouchKeyboard: false,
        minTime: minTime,
        selectOnBlur: !isMobile,
        useSelect: isMobile,
        step: 15
    };
    
    $('#gameTime' + eventIdSuffix).timepicker(options);
	
    // If user selects Private Event checkbox, enable friend list selection
    $('#privateEvent' + eventIdSuffix).click(function() {
	var checkedVal = this.checked;
			
	if(checkedVal) {
            $("input[name='pvtEventFriends" + eventIdSuffix + "[]']").removeProp('disabled');
            $("#selectAllFriends" + eventIdSuffix).removeProp('disabled');
	}
	else {
            $("input[name='pvtEventFriends" + eventIdSuffix + "[]']").prop('disabled', true);
            $("input[name='pvtEventFriends" + eventIdSuffix + "[]']").prop('checked', false);
            
            $("#selectAllFriends" + eventIdSuffix).prop('disabled', true);
            $("#selectAllFriends" + eventIdSuffix).prop('checked', false);
	}
    });
    
    if(curWidthClass == 'xtraSmall') {
        $("#selectAllFriends" + eventIdSuffix).removeClass('selectAllCheckbox').addClass('xtraSmallSelectAllCheckbox');
    }
    
    $("#selectAllFriends" + eventIdSuffix).click(function() {
        var checkedVal = this.checked;
        
        if(checkedVal) {
            $("input[name='pvtEventFriends" + eventIdSuffix + "[]']").prop('checked', true);
        }
        else {
            $("input[name='pvtEventFriends" + eventIdSuffix + "[]']").prop('checked', false);
        }
    });
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

        if(activePanel == panelEnum.CurrentEventFeed) {
            var titleBarWidth = $(currentEventViewerJTableDiv + ' .jtable-title').width();
            $(currentEventViewerJTableDiv + ' .fixedWidthScrollableContainer').css('width', (titleBarWidth + 10) + 'px');
        }
        else {
            var titleBarWidth = $(eventManagerJTableDiv + ' .jtable-title').width();
            $(eventManagerJTableDiv + ' .fixedWidthScrollableContainer').css('width', (titleBarWidth + 10) + 'px');            
        }
    }
	
    // Close search panel, if open
    CloseSearchPanel();
	
    DisplaySearchFiltersByCurrentView();
    return false;
}

function DisplaySearchFiltersByCurrentView()
{
    var $evtMgrFilters = $('#searchPanel .overlayPanelToggleGroup.searchPanelEvtMgrFilter,#searchPanel .overlayPanelToggleGroup.searchPanelCurEvtsFilter');
    var $exclusivelyEvtMgrFilters = $evtMgrFilters.not('.searchPanelCurEvtsFilter');
    var $exclusivelyCurEvtFilters = $evtMgrFilters.not('.searchPanelEvtMgrFilter');
    
    $evtMgrFilters = $('#searchPanel .overlayPanelFilterSubGroup .searchPanelEvtMgrFilter,#searchPanel .overlayPanelFilterSubGroup .searchPanelCurEvtsFilter');
    var $exclusivelyEvtMgrFilterFlds = $evtMgrFilters.not('.searchPanelCurEvtsFilter');
    var $exclusivelyCurEvtFilterFlds = $evtMgrFilters.not('.searchPanelEvtMgrFilter');
    
    if(activePanel === panelEnum.CurrentEventFeed) {
	// Change search panel style to reflect current view
	$('#searchPanel').removeClass('overlayPanelGreenBackground').addClass('overlayPanelOrangeBackground');
        
	// Cache, then hide, any expanded filter input groups exclusively from old view
	expandedEvtMgrFilterGroups = [];
	$('#searchPanel .overlayPanelFilterGroup.searchPanelEvtMgrFilter.overlayPanelGroupExpanded').not('.searchPanelCurEvtsFilter').each(function() {
            expandedEvtMgrFilterGroups.push($(this).attr('id'));
            $(this).removeClass('overlayPanelGroupBorder');
		$(this).removeClass('overlayPanelGroupExpanded');
		$(this).slideUp('slow');
            }
	);
			
	// Hide any search filters that are not associated with this particular view
	$exclusivelyEvtMgrFilters.hide();
	$exclusivelyCurEvtFilters.show();
        
        // Hide any fields within a search filter that are not associated with this particular view (when others in the same filter might be)
	$exclusivelyEvtMgrFilterFlds.hide();
	$exclusivelyCurEvtFilterFlds.show();
        
	// Reveal any filter input groups in new view that were previously expanded
	var i;
	for(i = 0; i < expandedCurEventsFilterGroups.length; i++) {
            var $curDiv = $('#' + expandedCurEventsFilterGroups[i]);
            $curDiv.removeClass('overlayPanelGroupBorder').addClass('overlayPanelGroupBorder');
            $curDiv.removeClass('overlayPanelGroupExpanded').addClass('overlayPanelGroupExpanded');
            $curDiv.slideDown('slow');
	}
    }
    else {
	// Change search panel style to reflect current view
	$('#searchPanel').removeClass('overlayPanelOrangeBackground').addClass('overlayPanelGreenBackground');
        
	// Cache, then hide, any expanded filter input groups exclusively from old view
	expandedCurEventsFilterGroups = [];
	$('#searchPanel .overlayPanelFilterGroup.searchPanelCurEvtsFilter.overlayPanelGroupExpanded').not('.searchPanelEvtMgrFilter').each(function() {
            expandedCurEventsFilterGroups.push($(this).attr('id'));
            $(this).removeClass('overlayPanelGroupBorder');
		$(this).removeClass('overlayPanelGroupExpanded');
		$(this).slideUp('slow');
            }
	);
				
	// Hide any search filter fields that are not associated with this particular view
	$exclusivelyCurEvtFilters.hide();
	$exclusivelyEvtMgrFilters.show();
        
        // Hide any fields within a search filter that are not associated with this particular view (when others in the same filter might be)
	$exclusivelyCurEvtFilterFlds.hide();
	$exclusivelyEvtMgrFilterFlds.show();
        
	// Reveal any filter input groups in new view that were previously expanded
	var i;
	for(i = 0; i < expandedEvtMgrFilterGroups.length; i++) {
            var $curDiv = $('#' + expandedEvtMgrFilterGroups[i]);
            $curDiv.removeClass('overlayPanelGroupBorder').addClass('overlayPanelGroupBorder');
            $curDiv.removeClass('overlayPanelGroupExpanded').addClass('overlayPanelGroupExpanded');
            $curDiv.slideDown('slow');
	}
    }
}

function ValidateEventFormFields(eventId)
{
    var alertTextType = "create";
    var eventIdSuffix = "";
    if(eventId > -1) {
        alertTextType = "update";
        eventIdSuffix = eventId.toString();
    }
  
    var numPlayersNeeded = $('#gamePlayersNeeded' + eventIdSuffix).val();
    var numPlayersAllowed = $("input[name='pvtEventFriends" + eventIdSuffix + "[]']:checked").length;
    var isPrivateEvent = $('#privateEvent' + eventIdSuffix).is(':checked');
    var gameDate = $('#gameDate' + eventIdSuffix).val();
    var gameTime = $('#gameTime' + eventIdSuffix).val();
    var gameTimezone = $('#ddlTimeZones'  + eventIdSuffix + ' option:selected').text();
    var displayDatetime = gameDate + " " + gameTime + " " + gameTimezone;
    
    var comments = $('#message' + eventIdSuffix).val();
    var isGlobalGame = $('#ddlGameTitles' + eventIdSuffix + ' option:selected').attr('class') == "globalGameOption" ? "true" : "false";
    var isExistingGame = (($('#ddlGameTitles' + eventIdSuffix).val() != null) && ($('#ddlGameTitles' + eventIdSuffix).val().length > 0));
	
    var curMoment = moment().utc();
    var eventInfo = new EventFormInfo(displayDatetime, comments, isGlobalGame, isExistingGame, curMoment, $('#selGameTitle' + eventIdSuffix).val());
        
    // Regular expression for time format
    var regexTime = /^\d{1,2}:\d{2}([apAP][mM])?$/;

    // Verify that required fields are filled out and have valid data
    if(gameDate.length === 0) {
        sweetAlert("Oops...", "Unable to " + alertTextType + " event: Please select a date", "error");
    } else if (gameTime.length === 0) {
        sweetAlert("Oops...", "Unable to " + alertTextType + " event: Please select a time", "error");
    } else if (!gameTime.match(regexTime)) {
        sweetAlert("Oops...", "Unable to " + alertTextType + " event: Please enter a valid time", "error");
    } else if (comments.trim().length === 0) {
        sweetAlert("Oops...", "Unable to " + alertTextType + " event: Please enter notes about your event", "error");
    } else if (isPrivateEvent && (numPlayersNeeded > (numPlayersAllowed + 1))) { // Event creator is implicitly allowed to join event
        sweetAlert("Oops...", "Unable to " + alertTextType + " event: New set of allowed friends is smaller than the number of members required for this event", "error");
    } else {
        var gameDateWithTZ = moment.tz(gameDate + " " + gameTime, "YYYY-MM-DD h:mmA", gameTimezone);
        var gameTimeMoment = moment(gameDateWithTZ).utc();
        eventInfo.gameTimeMoment = gameTimeMoment;

        if (gameTimeMoment.isBefore(curMoment)) {
            sweetAlert("Oops...", "Unable to " + alertTextType + " event: Scheduled game time '" + displayDatetime + "' is in the past", "error");
        }
        else {
            eventInfo.validated = true;
        }
    }
    
    return eventInfo;
}

function ValidateSearchFormFields(searchFieldClass, suppressAlerts)
{
    var searchEventsInfo = new SearchEventsFormInfo();
	
    // Search fields are considered validated if not included in current active search filters
    var dateFieldsValidated = true;
	
    // Date filters
    if(($('#dateRangeFilterLink').length) && ($('#dateRangeFilterLink').hasClass('overlayPanelToggleElementActive'))) {
	dateFieldsValidated = false;
	var gameStartDate = $('#gameFilterStartDate').val();
	var gameStartTime = $('#gameFilterStartTime').val();
	var gameStartTimezone = $('#ddlTimeZonesStart option:selected').text();
			
        var gameEndDate = $('#gameFilterEndDate').val();
        var gameEndTime = $('#gameFilterEndTime').val();
        var gameEndTimezone = $('#ddlTimeZonesEnd option:selected').text();

        // Regular expression for time format
        var regexTime = /^\d{1,2}:\d{2}([apAP][mM])?$/;

        // Verify that required fields are filled out and have valid data
        if(gameStartDate.length === 0) {
            if(!suppressAlerts)  sweetAlert("Unable to filter by date range: Must select a start date");
        } else if (gameStartTime.length === 0) {
            if(!suppressAlerts)  sweetAlert("Unable to filter by date range: Must select a start time");
        } else if (!gameStartTime.match(regexTime)) {
            if(!suppressAlerts)  sweetAlert("Unable to filter by date range: Must enter a valid start time");
        } else if(gameEndDate.length === 0) {
            if(!suppressAlerts)  sweetAlert("Unable to filter by date range: Must select a end date");
        } else if (gameEndTime.length === 0) {
            if(!suppressAlerts)  sweetAlert("Unable to filter by date range: Must select a end time");
        } else if (!gameEndTime.match(regexTime)) {
            if(!suppressAlerts)  sweetAlert("Unable to filter by date range: Must enter a valid end time");
        } else {
            // Cannot add date range filters directly to post data via serialize() call -- 
            //  we must convert them to UTC first, then manually add to post data
            var gameStartDateTimeWithTZ = moment.tz(gameStartDate + " " + gameStartTime, "YYYY-MM-DD h:mmA", gameStartTimezone);
            var gameStartDateTimeMoment = moment(gameStartDateTimeWithTZ).utc();
            var gameEndDateTimeWithTZ = moment.tz(gameEndDate + " " + gameEndTime, "YYYY-MM-DD h:mmA", gameEndTimezone);
            var gameEndDateTimeMoment = moment(gameEndDateTimeWithTZ).utc();        

            searchEventsInfo.gameStartDateTimeMoment = gameStartDateTimeMoment;
            searchEventsInfo.gameEndDateTimeMoment = gameEndDateTimeMoment;
            searchEventsInfo.postData += ('&gameFilterStartDateTime=' + gameStartDateTimeMoment.toISOString() + 
                                          '&gameFilterEndDateTime=' + gameEndDateTimeMoment.toISOString());
            dateFieldsValidated = true;
        }
    }
    
    var filterFields = $(searchFieldClass).find('*[name]').filter('.filterFieldActive');
    if(filterFields && filterFields.length) {
        searchEventsInfo.postData += ('&' + (filterFields.serialize()));
    }
    
    searchEventsInfo.validated = dateFieldsValidated;
    return searchEventsInfo;
}

function EventFormInfo(displayDatetime, comments, isGlobalGame, isExistingGame, gameTimeMoment, selGameTitle)
{
    this.displayDatetime = displayDatetime;
    this.comments = comments;
    this.isGlobalGame = isGlobalGame;
    this.isExistingGame = isExistingGame;
    this.gameTimeMoment = gameTimeMoment;
    this.selGameTitle = selGameTitle;
    this.validated = false;
}

function SearchEventsFormInfo()
{
    this.gameStartDateTimeMoment = '';
    this.gameEndDateTimeMoment = '';
    this.validated = false;
    this.postData = '';
}

function EditEvent(eventId, $dialog)
{
    var eventInfo = ValidateEventFormFields(eventId);
    
    if(eventInfo.validated) {
        var postData = "action=EventEditorUpdateEvent&isGlobalGame=" + eventInfo.isGlobalGame + "&gameTitle=" + eventInfo.selGameTitle + 
                       "&eventId=" + eventId + "&gameDateUTC=" + eventInfo.gameTimeMoment.toISOString() + "&" + $('#eventEditForm' + eventId).serialize();

        $.ajax({
            type: "POST",
            url: "AJAXHandler.php",
            data: postData,
            success: function(response){
                if(response === 'true') {
                    // Reload event manager
                    var fullRefresh = false;
                    ReloadUserHostedEventsTable(fullRefresh);

                    // Reload game title dropdown, if user entered a new game
                    if(!eventInfo.isExistingGame) {
                        ReloadGameTitleSelector(eventId);
                    }

                    sweetAlert('Success', 'Updated event for game "' + eventInfo.selGameTitle + '" at ' + eventInfo.displayDatetime + '!', 'success');
                    $dialog.dialog('destroy').remove();
                }
                else {
                    sweetAlert(response);
                }
            }
        });
    }

    return false;
}

function CreateEvent($dialog)
{
    var eventId = -1;
    var eventInfo = ValidateEventFormFields(eventId);

    if(eventInfo.validated) {
        // Create event
        var postData = "action=EventEditorCreateEvent&isGlobalGame=" + eventInfo.isGlobalGame + "&gameTitle=" + eventInfo.selGameTitle + 
                       "&gameDateUTC=" + eventInfo.gameTimeMoment.toISOString() + "&" + $('#eventCreateForm').serialize();

        $.ajax({
            type: "POST",
            url: "AJAXHandler.php",
            data: postData,
            success: function(response){
                if(response === 'true') {
                    // Reload event manager
                    var fullRefresh = false;
                    ReloadUserHostedEventsTable(fullRefresh);
		
                    // Reload game title dropdown, if user entered a new game
                    if(!eventInfo.isExistingGame) {
                        ReloadGameTitleSelector(eventId);
                    }
                
                    sweetAlert('Success', 'Created event for game "' + eventInfo.selGameTitle + '" at ' + eventInfo.displayDatetime + '!', 'success');
                    $dialog.dialog('destroy').remove();
                }
                else {
                    sweetAlert(response);
                }
            }
        });
    }
    
    return false;
}

