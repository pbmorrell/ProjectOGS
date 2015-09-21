// Globals
var panelEnum = {
    None: 'None',
    MyEventViewer: '#manageEventsDiv',
    CurrentEventFeed: '#currentEventsDiv'
};

var activePanel = panelEnum.CurrentEventFeed; 

var eventManagerLoadAction = 'GetUserOwnedEventsForJTable';
var eventManagerJTableDiv = "#manageEventsContent";
var eventManagerShowHiddenEvents = 0;
var eventManagerShowPastEventsInDays = 0;

var currentEventViewerJTableDiv = "#currentEventsContent";
var currentEventViewerLoadAction = 'GetCurrentEventsForJTable';

// Functions
function MemberHomeOnReady()
{
    $(panelEnum.MyEventViewer).hide();
    LoadCurrentEventViewer();
    LoadEventManager();
}

function LoadEventManager()
{
    var isMobile = isMobileView();
    
    if(isMobile) {
        $('#mobileEventsTableToolbar').removeClass('hidden');
        $('#toolbarSpacer').removeClass('hidden');
        $('#refreshEventsBtn').click(function() {
            var fullRefresh = false;
            ReloadUserHostedEventsTable(fullRefresh);
            return false;
        });
        
        $('#activateEventsBtn').click(function() {
            ToggleTableEventActivation("1");
            return false;
        });
        
        $('#hideEventsBtn').click(function() {
            ToggleTableEventActivation("0");
            return false;
        });
        
        $('#deleteEventsBtn').click(function() {
            DeleteTableEvents();
            return false;
        });        
    }
    
    // Initialize jTable on manageEventsContent div
    $(eventManagerJTableDiv).jtable({
        title: "Events Hosted By You",
        paging: true,
        pageSize: 10,
        pageSizes: [5, 10, 15, 20, 25],
        pageSizeChangeArea: !isMobile,
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
                    text: 'Refresh Events',
                    icon: 'images/refresh.png',
                    tooltip: 'Refreshes your event list',
                    cssClass: (isMobile) ? ('hidden') : (''),
                    click: function(){
			var fullRefresh = false;
                        ReloadUserHostedEventsTable(fullRefresh);
                    }
                },
                {
                    text: 'Activate Selected',
                    icon: 'images/activate.png',
                    tooltip: 'Makes selected events active & visible',
                    cssClass: (isMobile) ? ('hidden') : (''),
                    click: function(){
                        ToggleTableEventActivation("1");
                    }
                },
                {
                    text: 'Hide Selected',
                    icon: 'images/deactivate.png',
                    tooltip: 'Makes selected events inactive & invisible',
                    cssClass: (isMobile) ? ('hidden') : (''),
                    click: function(){
                        ToggleTableEventActivation("0");
                    }
                },
                {
                    text: 'Delete Selected',
                    icon: 'images/delete.png',
                    tooltip: 'Permanently deletes selected events',
                    cssClass: (isMobile) ? ('hidden') : (''),
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
                sorting: true
            },
            DisplayTime: {
                title: 'Time',
                width: '12%',
                sorting: false
            },
            Notes: {
                title: 'Game Notes',
                width: '25%',
                sorting: false,
                list: !isMobile
            },
            PlayersSignedUp: {
                title: 'Players Joined',
                width: '11%',
                display: function (data) {					
                    // Create PlayersSignedUp display object
                    var $expandImage = $('<label>' + data.record.PlayersSignedUp + '&nbsp;&nbsp;<label id="lblEvt' + data.record.ID + '" class="fa fa-plus-square" /></label>');
                    $expandImage.click(function () {
                        var eventId = data.record.ID;
			var tableRow = $(this).closest('tr');
                        var curLblId = "#lblEvt" + data.record.ID;
                        
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
                sorting: true,
                list: !isMobile
            },
            Edit: {
                title: 'Edit',
                width: '5%',
                display: function (data) {
                    var $editImage = $('<img src="images/edit.png" />');
                    $editImage.click(function () {
                        DisplayEditEventDialog(data.record.ID);
                    });

                    // Return image for display in jTable
                    return $editImage;
                },
                sorting: false,
                columnSelectable: false
            }
        },
	recordsLoaded: function(event, data) {
            $(eventManagerJTableDiv + ' .jtable-data-row').each(function() {
		// Store PlayersSignedUpData as custom data attribute on each row, for use in child table expansion
		var id = $(this).attr('data-record-key');
		var dataRecordArray = $.grep(data.records, function (e) {
                        return e.ID === id;
                    }
		);
					
		var playerData = dataRecordArray[0].PlayersSignedUpData;
		$(this).attr('data-playersSignedUp', playerData);
					
		// Pre-load each child table, but do not show yet
		OpenChildTableForJoinedPlayers($(this), id, eventManagerJTableDiv);
                
                // If in mobile view, and an event is hidden, set forecolor to red rather than show "Hidden" column
                var isHidden = dataRecordArray[0].Hidden;
                if((isMobile) && (isHidden === 'Yes')) {
                    $(this).css('color', 'red');
                }
            });
	}
    });

    // Load event list
    var postData = 
        {
            action: eventManagerLoadAction,
            showHidden: eventManagerShowHiddenEvents,
            showPastEventsInDays: eventManagerShowPastEventsInDays
        };
		
    $(eventManagerJTableDiv).jtable('load', postData);

    // Execute any post-startup logic
    EventManagerOnReady();
    /* ******************************************************************************************************** */
}

function LoadCurrentEventViewer()
{
    var isMobile = isMobileView();
    
    // Initialize jTable on currentEventsContent div
    $(currentEventViewerJTableDiv).jtable({
        title: (isMobile) ? ('Browse User Events') : ('Browse Events Hosted By Other Users'),
        paging: true,
        pageSize: 10,
        pageSizes: [5, 10, 15, 20, 25],
        pageSizeChangeArea: !isMobile,
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
                    text: (isMobile) ? ('Refresh') : ('Refresh Events'),
                    icon: 'images/refresh.png',
                    tooltip: 'Refreshes current event list',
                    click: function(){
			var fullRefresh = false;
                        ReloadCurrentEventsTable(fullRefresh);
                    }
                },
                {
                    text: (isMobile) ? ('Join') : ('Join Selected'),
                    icon: 'images/signup.png',
                    tooltip: 'Signs you up for all selected events',
                    click: function(){
                        JoinSelectedEvents();
                    }
                },
                {
                    text: (isMobile) ? ('Leave') : ('Leave Selected'),
                    icon: 'images/cancelsignup.png',
                    tooltip: 'Cancels enrollment for selected events',
                    click: function(){
                        LeaveSelectedEvents();
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
                sorting: true				
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
                sorting: true
            },
            DisplayTime: {
                title: 'Time',
                width: '12%',
                sorting: false
            },
            Notes: {
                title: 'Game Notes',
                width: '22%',
                sorting: false,
                list: !isMobile
            },
            Joined: {
                title: 'Joined',
                width: '7%',
                sorting: true,
                list: !isMobile,
                display: function (data) {
                    // Create JOIN or LEAVE link
                    var $expandLink = $('<a href="#" class="actionLink" id="evtLink' + data.record.ID + '">' + data.record.Joined + '</a>');
                    
                    if(data.record.Joined === 'JOIN') {
                        $expandLink.click(function () {
                            var eventIds = [data.record.ID];
                            JoinEvents(eventIds);
                            return false;
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
            },
            PlayersSignedUp: {
                title: 'Players Joined',
                width: '11%',
                display: function (data) {					
                    // Create PlayersSignedUp display object
                    var $expandImage = $('<label>' + data.record.PlayersSignedUp + '&nbsp;&nbsp;<label id="lblCurEvt' + data.record.ID + '" class="fa fa-plus-square" /></label>');
                    $expandImage.click(function () {
                        var eventId = data.record.ID;
			var tableRow = $(this).closest('tr');
                        var curLblId = "#lblCurEvt" + data.record.ID;
                        
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
            $(currentEventViewerJTableDiv + ' .jtable-data-row').each(function() {
		// Store PlayersSignedUpData as custom data attribute on each row, for use in child table expansion
		var id = $(this).attr('data-record-key');
		var dataRecordArray = $.grep(data.records, function (e) {
                    return e.ID === id;
		});
							
		var playerData = dataRecordArray[0].PlayersSignedUpData;
		$(this).attr('data-playersSignedUp', playerData);
							
		// Pre-load each child table, but do not show yet
		OpenChildTableForJoinedPlayers($(this), id, currentEventViewerJTableDiv);
                
                // If in mobile view, and an event is joined by current user, set forecolor to green rather than show "Joined" column
                var isJoined = dataRecordArray[0].Joined;
                if((isMobile) && (isJoined === 'LEAVE')) {
                    $(this).css('color', 'green');
                }
            });
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

function CurrentEventViewerOnReady()
{
	
}

function EventManagerOnReady()
{
    $('#toggleHiddenEvents').change(function() {
        eventManagerShowHiddenEvents = ($(this).is(':checked')) ? 1 : 0;
        var fullRefresh = true;
        ReloadUserHostedEventsTable(fullRefresh);
    });
	
    $('#toggleShowPastEvents').change(function() {
        eventManagerShowPastEventsInDays = $(this).val();
        var fullRefresh = true;
        ReloadUserHostedEventsTable(fullRefresh);
    });
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
        alert("No events selected");
        return;
    }

    var selectedEventIds = [];
    $selectedRows.each(function() {
            var id = $(this).data('record').ID;
            selectedEventIds.push(id);
        }
    );

    if(!ToggleEventVisibility(selectedEventIds, isActive)) {
        // Reject event activation request (de-select rows)
        DeselectAllJTableRows(eventManagerJTableDiv);
    }   
}

function DeleteTableEvents()
{
    var $selectedRows = $(eventManagerJTableDiv).jtable('selectedRows');
    if($selectedRows.length === 0) {
        alert("No events selected");
        return;
    }

    var selectedEventIds = [];
    $selectedRows.each(function() {
            var id = $(this).data('record').ID;
            selectedEventIds.push(id);
        }
    );

    if(!DeleteEvents(selectedEventIds)) {
        // Reject event deletion request (de-select rows)
        DeselectAllJTableRows(eventManagerJTableDiv);
    }
}

function JoinSelectedEvents()
{
    var $selectedRows = $(currentEventViewerJTableDiv).jtable('selectedRows');
    if($selectedRows.length === 0) {
        alert("No events selected");
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
        alert("You are already a member of all selected events");
        DeselectJTableRowsByKey(currentEventViewerJTableDiv, eventsToDeselect);
    }
    else if(!JoinEvents(selectedEventIds)) {
        // De-select any rows for events the user has already joined
        DeselectJTableRowsByKey(currentEventViewerJTableDiv, eventsToDeselect);
    }
}

function LeaveSelectedEvents()
{
    var $selectedRows = $(currentEventViewerJTableDiv).jtable('selectedRows');
    if($selectedRows.length === 0) {
        alert("No events selected");
        return;
    }

    var selectedEventIds = [];
    var eventsToDeselect = [];
    $selectedRows.each(function() {
        var id = $(this).data('record').ID;
        var isJoined = $(this).data('record').Joined;
            
        // Only try to leave events that this user has already joined
        if(isJoined === 'LEAVE') {
            selectedEventIds.push(id);
        }
        else {
            eventsToDeselect.push(id);
        }
    });

    if(selectedEventIds.length === 0) {
        alert("You haven't joined any of the selected events yet");
        DeselectJTableRowsByKey(currentEventViewerJTableDiv, eventsToDeselect);
    }
    else if(!LeaveEvents(selectedEventIds)) {
        // De-select any rows for events for which the user is not a member
        DeselectJTableRowsByKey(currentEventViewerJTableDiv, eventsToDeselect);
    }
}

function OpenChildTableForJoinedPlayers(tableRow, eventId, jTableDiv)
{        
    $(jTableDiv).jtable('openChildTable', tableRow,
        {
            title: "",
            childTableNoReloadOnOpen: true,
            actions: {
                listAction: function(postData, jtParams) {
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
                    width: '100%',
                    sorting: true
                }
            },
            recordsLoaded: function(event, data) {
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
	records.push({ "ID": playerDataArray[0], "PlayerName": playerDataArray[1]});
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
	
    if(confirm(confirmMsg)) {
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
                ReloadUserHostedEventsTable(true);
		alert(response);
		return true;
            }
        });
    }
    else {
        return false;
    }
}

function DeleteEvents(selectedEventIds)
{
    var confirmMsg = 'Are you sure you want to delete all selected events?';
    if(selectedEventIds.length === 1) {
        confirmMsg = 'Are you sure you want to delete this event?';
    }
    
    if(confirm(confirmMsg)) {
        // Serialize array of selected event IDs for POST Ajax call
	var eventIdsForPost = [];
	for(var i = 0; i < selectedEventIds.length; i++) {
            eventIdsForPost.push({"name":"eventIds[]", "value": selectedEventIds[i].toString()});
	}
		
	// Make AJAX call to update Active status for given events
	$.ajax({
            type: "POST",
            url: "AJAXHandler.php",
            data: "action=EventEditorDeleteEvents&" + $.param({'eventIds': selectedEventIds}),
            success: function(response){
                ReloadUserHostedEventsTable(true);
		alert(response);
		return true;
            }
        });
    }
    else {
        return false;
    }
}

function JoinEvents(selectedEventIds)
{
    var confirmMsg = 'Are you sure you want to join all selected events?';
    if(selectedEventIds.length === 1) {
        confirmMsg = 'Are you sure you want to join this event?';
    }
    
    if(confirm(confirmMsg)) {
        // Serialize array of selected event IDs for POST Ajax call
	var eventIdsForPost = [];
	for(var i = 0; i < selectedEventIds.length; i++) {
            eventIdsForPost.push({"name":"eventIds[]", "value": selectedEventIds[i].toString()});
	}
		
	// Make AJAX call to sign current user up for selected events
	$.ajax({
            type: "POST",
            url: "AJAXHandler.php",
            data: "action=EventViewerJoinEvents&" + $.param({'eventIds': selectedEventIds}),
            success: function(response){
		var fullRefresh = false;
                ReloadCurrentEventsTable(fullRefresh);
		alert(response);
		return true;
            }
        });
    }
    else {
        return false;
    }
}

function LeaveEvents(selectedEventIds)
{
    var confirmMsg = 'Are you sure you want to leave all selected events?';
    if(selectedEventIds.length === 1) {
        confirmMsg = 'Are you sure you want to leave this event?';
    }
    
    if(confirm(confirmMsg)) {
        // Serialize array of selected event IDs for POST Ajax call
	var eventIdsForPost = [];
	for(var i = 0; i < selectedEventIds.length; i++) {
            eventIdsForPost.push({"name":"eventIds[]", "value": selectedEventIds[i].toString()});
	}
		
	// Make AJAX call to remove current user up from selected events
	$.ajax({
            type: "POST",
            url: "AJAXHandler.php",
            data: "action=EventViewerLeaveEvents&" + $.param({'eventIds': selectedEventIds}),
            success: function(response){
		var fullRefresh = false;
                ReloadCurrentEventsTable(fullRefresh);
		alert(response);
		return true;
            }
        });
    }
    else {
        return false;
    }
}

function ReloadUserHostedEventsTable(fullRefresh)
{
    if(fullRefresh) {
        var postData = 
            {
                action: eventManagerLoadAction,
                showHidden: eventManagerShowHiddenEvents,
		showPastEventsInDays: eventManagerShowPastEventsInDays
            };
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
        var postData = 
            {
                action: currentEventViewerLoadAction
            };
        $(currentEventViewerJTableDiv).jtable('load', postData);   
    }
    else {
        // Reload event list with same POST arguments
        $(currentEventViewerJTableDiv).jtable('reload');
    }
}

function DisplayCreateEventDialog()
{
    displayJQueryDialog("dlgCreateEvt", "Create Event", "top", "top", window, false, true, 
                        "AJAXHandler.php?action=EventEditorLoad", function() {
        var eventId = -1;
        EventSchedulerDialogOnReady(eventId, $('#dlgCreateEvt').dialog());
    });
}

function DisplayEditEventDialog(eventId)
{
    displayJQueryDialog("dlgEditEvent" + eventId, "Edit Event", "top", "top", window, false, true, 
                        "AJAXHandler.php?action=EventEditorLoad&EventID=" + eventId, function() {
        EventSchedulerDialogOnReady(eventId, $('#dlgEditEvent' + eventId).dialog());
    });
}

function EventSchedulerDialogOnReady(eventId, $dialog)
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
            $dialog.dialog('close');
            return false;
        });
        
        // Attach event handler to Delete Event button
        $('#deleteEventBtn' + eventIdSuffix).click(function() {
            var eventIds = [ eventId ];
            DeleteEvents(eventIds);
            $dialog.dialog('close');
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

    var options = {
        disableTextInput: true,
        disableTouchKeyboard: true,
        minTime: minTime,
        selectOnBlur: true,
        step: 15
    };
    
    $('#gameTime' + eventIdSuffix).timepicker(options);
    
    // Apply input mask to date field
    $('#gameDate' + eventIdSuffix).mask('9999-99-99');
    
    // Switch comments section to mobile width, if needed
    if(isMobileView()) {
        $('#message' + eventIdSuffix).addClass('textareaMobile');
        $('#eventDialogToolbar' + eventIdSuffix).addClass('mobileDlgToolbarContainer');
    }
    else {
        $('#eventDialogToolbar' + eventIdSuffix).addClass('dlgToolbarContainer');
    }
	
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
    if($(panelToToggle).css('display') === 'none')
    {
	// Hide active panel
	if(activePanel !== panelEnum.None) {
            $(activePanel).hide();
	}
		
	// Fade in desired panel
	activePanel = panelToToggle;
	$(panelToToggle).fadeIn("slow", function() {});
    }
    else {
	activePanel = panelEnum.None;
	$(panelToToggle).fadeOut("fast", function() {});
    }
	
    return false;
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
        alert("Unable to " + alertTextType + " event: Must select a date");
    } else if (gameTime.length === 0) {
        alert("Unable to " + alertTextType + " event: Must select a time");
    } else if (!gameTime.match(regexTime)) {
        alert("Unable to " + alertTextType + " event: Must enter a valid time");
    } else if (comments.trim().length === 0) {
        alert("Unable to " + alertTextType + " event: Please enter notes about your event");
    } else if (isPrivateEvent && (numPlayersNeeded > numPlayersAllowed)) {
        alert("Unable to " + alertTextType + " event: New set of allowed friends is smaller than the number of members required for this event");
    } else {
        var curDateMoment = moment().utc();
        var gameDateWithTZ = moment.tz(gameDate + " " + gameTime, "YYYY-MM-DD h:mmA", gameTimezone);
        var gameTimeMoment = moment(gameDateWithTZ).utc();
        eventInfo.gameTimeMoment = gameTimeMoment;

        if (gameTimeMoment.isBefore(curDateMoment)) {
            alert("Unable to " + alertTextType + " event: Scheduled game time '" + displayDatetime + "' is in the past");
        }
        else {
            eventInfo.validated = true;
        }
    }
    
    return eventInfo;
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

                    alert('Success - Updated event for game "' + eventInfo.selGameTitle + '" at ' + eventInfo.displayDatetime + '!');
                    $dialog.dialog('destroy').remove();
                }
                else {
                    alert(response);
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
                
                    alert('Success - Created event for game "' + eventInfo.selGameTitle + '" at ' + eventInfo.displayDatetime + '!');
                    $dialog.dialog('destroy').remove();
                }
                else {
                    alert(response);
                }
            }
        });
    }
    
    return false;
}