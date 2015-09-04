// Globals
var panelEnum = {
    None: 'None',
    EventScheduler: '#scheduleEventDiv',
    MyEventViewer: '#manageEventsDiv',
    CurrentEventFeed: '#currentEventsDiv'
};

var activePanel = panelEnum.CurrentEventFeed; 
var eventManagerLoadAction = 'GetUserOwnedEventsForJTable';
var eventManagerShowHiddenEvents = 0;

// Functions
function MemberHomeOnReady()
{
    $(panelEnum.EventScheduler).addClass('hidden');
    $(panelEnum.MyEventViewer).addClass('hidden');
	
    LoadEventEditor();
    LoadEventManager();
    
}

function LoadEventEditor()
{
    // Make AJAX call to initialize Event Scheduler
    $.ajax({
        type: "POST",
        url: "AJAXHandler.php",
        data: "action=EventEditorLoad&EventID=0",
        success: function(response){
            $('#scheduleEventContent').html(response);
            EventSchedulerOnReady();
        },
        error: function() {
            $('#scheduleEventContent').html('Unable to load Event Scheduler...please try again later');
        }
    });
}

function LoadEventManager()
{	
    // Initialize jTable on manageEventsContent div
    $('#manageEventsContent').jtable({
        title: "Events Hosted By You",
        paging: true,
        pageSize: 10,
        pageSizes: [5, 10, 15, 20, 25],
        sorting: true,
        defaultSorting: 'DisplayDate ASC',
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
                width: '9%',
                sorting: true
            },
            DisplayDate: {
                title: 'Scheduled Date',
                width: '15%',
                sorting: true
            },
            DisplayTime: {
                title: 'Scheduled Time',
                width: '12%',
                sorting: false
            },
            Notes: {
                title: 'Game Notes',
                width: '25%',
                sorting: false
            },
            PlayersSignedUp: {
                title: 'Players Joined',
                width: '11%',
                sorting: false
            },
            Edit: {
                title: 'Edit',
                width: '6%',
                display: function (data) {
                    var $editImage = $('<img src="images/edit.png" rel="' + data.record.ID + '" />');
                    $editImage.click(function () {
                        var eventId = $(this).attr('rel');
                        DisplayEditEventDialog(eventId);
                    });

                    // Return image for display in jTable
                    return $editImage;
                },
                sorting: false
            },
            Hidden: {
                title: 'Hidden',
                width: '7%',
                display: function (data) {
                    var checkedVal = "";
                    if(data.record.Hidden === 'hidden') {
			checkedVal = " checked='checked'";
                    }
					
                    var $hiddenCheckbox = $('<input type="checkbox" id="hiddenEvent' + data.record.ID + '"' + checkedVal + ' rel="' + data.record.ID + '" />');
                    $hiddenCheckbox.change(function () {
                        var eventId = $(this).attr('rel');
			var isActive = $(this).is(':checked') ? '0' : '1';
                        var result = ToggleEventVisibility(eventId, isActive);
                        
                        if(!result) {
                            // Reject checked changed event (restore previous checked state)
                            if(isActive) {
                                $(this).prop('checked', true);
                            }
                            else {
                                $(this).prop('checked', false);
                            }
                        }
                    });

                    // Return checkbox HTML for display in jTable
                    return $hiddenCheckbox;
                },
                sorting: false
            }
        }
    });

    // Load event list
    var postData = 
        {
            action: eventManagerLoadAction,
            showHidden: eventManagerShowHiddenEvents
        };
    $('#manageEventsContent').jtable('load', postData);

    // Execute any post-startup logic
    EventManagerOnReady();
    /* ******************************************************************************************************** */
}

function EventManagerOnReady()
{
    $('#toggleHiddenEvents').change(function() {
            eventManagerShowHiddenEvents = ($(this).is(':checked')) ? 1 : 0;
            var fullRefresh = true;
            ReloadUserHostedEventsTable(fullRefresh);
        }
    );
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

function ToggleEventVisibility(eventId, isActive)
{
    var actionText = "hide this event";
    if(isActive === '1') {
        actionText = "make this event visible";
    }
	
    if(confirm('Are you sure you want to ' + actionText + '?')) {
	// Make AJAX call to set event Active status to false
	$.ajax({
            type: "POST",
            url: "AJAXHandler.php",
            data: "action=EventEditorToggleEventVisibility&eventId=" + eventId + "&isActive=" + isActive,
            success: function(response){
                alert(response);
            }
        });
        
        return true;
    }
    else {
        // Restore checkbox to previous state
        return false;
    }
}

function ReloadUserHostedEventsTable(fullRefresh)
{
    if(fullRefresh) {
        var postData = 
            {
                action: eventManagerLoadAction,
                showHidden: eventManagerShowHiddenEvents
            };
        $('#manageEventsContent').jtable('load', postData);   
    }
    else {
        // Reload event list with same POST arguments
        $('#manageEventsContent').jtable('reload');
    }
}

function DisplayEditEventDialog(eventId)
{
    var $dialog = $('<div></div>').load('AJAXHandler.php?action=EventEditorLoad&EventID=' + eventId, 
                                        function() { EventSchedulerDialogOnReady(eventId, $dialog); }).dialog({
            autoOpen: false,
            title: 'Edit Event',
            width: 600,
            height: 700,
            modal: true
        }
    );
    
    $dialog.dialog('option', 'position', {
        my: 'top',
        at: 'top',
        of: window
    });

    $dialog.dialog('open');
}

function EventSchedulerOnReady()
{
    // Create jQuery-powered comboBox for game title selection or entry
    PrepareAutocompleteComboBox("selGameTitle");
    $('#ddlGameTitles').combobox();
	
    // Initialize Game Scheduled Date datepicker
    $('#gameDate').datepicker({
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
    var defaultTimeDate = new Date();
    var nextRoundIntervalValue = 15 - (defaultTimeDate.getMinutes() % 15);
    defaultTimeDate.setMinutes(defaultTimeDate.getMinutes() + nextRoundIntervalValue);
    $('#gameTime').timepicker(
        {
            defaultTime: defaultTimeDate,
            timeFormat: 'h:mmp',
            startTime: defaultTimeDate,
            interval: 15 // 15 minutes
        }
    );
    
    // Define new mask characters
    //$.mask.definitions['~']='[ap]';
    
    // Apply input mask to date field
    $('#gameDate').mask('9999-99-99');
    
    // Apply input mask to time field
    //$('#gameTime').mask('99:99 ~m');
	
    // If user selects Private Event checkbox, enable friend list selection
    $('#privateEvent').click(function() {
	var checkedVal = this.checked;
			
	if(checkedVal) {
            $("input[name='pvtEventFriends[]']").removeProp('disabled');
            $("#selectAllFriends").removeProp('disabled');
	}
	else {
            $("input[name='pvtEventFriends[]']").prop('disabled', true);
            $("input[name='pvtEventFriends[]']").prop('checked', false);
            
            $("#selectAllFriends").prop('disabled', true);
            $("#selectAllFriends").prop('checked', false);
	}
    });
    
    $("#selectAllFriends").click(function() {
        var checkedVal = this.checked;
        
        if(checkedVal) {
            $("input[name='pvtEventFriends[]']").prop('checked', true);
        }
        else {
            $("input[name='pvtEventFriends[]']").prop('checked', false);
        }
    });

    // If viewing device has screen width > 650px, treat as mobile device
    if (window.matchMedia("(max-width: 650px)").matches) {
        $('#createEventBtn').hide();
        $('#createEventBtnMobile').show();

        // Attach event handler to Create Event mobile button
        $('#createEventBtnMobile').click(function() {
            CreateEvent();
        });
    }
    else {
        $('#createEventBtn').show();
        $('#createEventBtnMobile').hide();
        
        // Attach event handler to Create Event desktop button
        $('#createEventBtn').click(function() {
            CreateEvent();
        });
    }
}

function EventSchedulerDialogOnReady(eventId, $dialog)
{
    var eventIdSuffix = "";
    if(eventId > -1) {
        eventIdSuffix = eventId.toString();
    }
	
    // Create jQuery-powered comboBox for game title selection or entry
    PrepareAutocompleteComboBox("selGameTitle" + eventId);
    $('#ddlGameTitles' + eventId).combobox();
	
    // Initialize Game Scheduled Date datepicker
    $('#gameDate' + eventId).datepicker({
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
    var defaultTimeDate = new Date();
    var nextRoundIntervalValue = 15 - (defaultTimeDate.getMinutes() % 15);
    defaultTimeDate.setMinutes(defaultTimeDate.getMinutes() + nextRoundIntervalValue);
    $('#gameTime' + eventId).timepicker(
        {
            timeFormat: 'h:mmp',
            startTime: defaultTimeDate,
            interval: 15 // 15 minutes
        }
    );
    
    // Apply input mask to date field
    $('#gameDate' + eventId).mask('9999-99-99');
	
    // If user selects Private Event checkbox, enable friend list selection
    $('#privateEvent' + eventId).click(function() {
	var checkedVal = this.checked;
			
	if(checkedVal) {
            $("input[name='pvtEventFriends" + eventId + "[]']").removeProp('disabled');
            $("#selectAllFriends" + eventId).removeProp('disabled');
	}
	else {
            $("input[name='pvtEventFriends" + eventId + "[]']").prop('disabled', true);
            $("input[name='pvtEventFriends" + eventId + "[]']").prop('checked', false);
            
            $("#selectAllFriends" + eventId).prop('disabled', true);
            $("#selectAllFriends" + eventId).prop('checked', false);
	}
    });
    
    $("#selectAllFriends" + eventId).click(function() {
        var checkedVal = this.checked;
        
        if(checkedVal) {
            $("input[name='pvtEventFriends" + eventId + "[]']").prop('checked', true);
        }
        else {
            $("input[name='pvtEventFriends" + eventId + "[]']").prop('checked', false);
        }
    });

    // Attach event handler to Edit Event button
    $('#editEventBtn' + eventId).click(function() {
        return EditEvent(eventId, $dialog);
    });
	
    // Change dialog title to Editing Event: 'game' @ 'datetime'
    var selGameTitle = $('#selGameTitle' + eventIdSuffix).val();
    var gameDate = $('#gameDate' + eventIdSuffix).val();
    var gameTime = $('#gameTime' + eventIdSuffix).val();
	
    var title = 'Editing Event: "' + selGameTitle + '" @ ' + gameDate + ' ' + gameTime;
    $dialog.dialog('option', 'title', title);
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
    } else if (numPlayersNeeded > numPlayersAllowed) {
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
        $.ajax({
            type: "POST",
            url: "AJAXHandler.php",
            data: "action=EventEditorUpdateEvent&isGlobalGame=" + eventInfo.isGlobalGame + "&gameTitle=" + eventInfo.selGameTitle + 
                  "&eventId=" + eventId + "&gameDateUTC=" + eventInfo.gameTimeMoment.toISOString() + "&" + $('#eventEditForm' + eventId).serialize(),
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
                    $dialog.dialog('close');
                }
                else {
                    alert(response);
                }
            }
        });
    }

    return false;
}

function CreateEvent()
{
    var eventId = -1;
    var eventInfo = ValidateEventFormFields(eventId);

    if(eventInfo.validated) {
        // Create event
        $.ajax({
            type: "POST",
            url: "AJAXHandler.php",
            data: "action=EventEditorCreateEvent&isGlobalGame=" + eventInfo.isGlobalGame + "&gameTitle=" + eventInfo.selGameTitle + 
                  "&gameDateUTC=" + eventInfo.gameTimeMoment.toISOString() + "&" + $('#eventCreateForm').serialize(),
            success: function(response){
                if(response === 'true') {
                    // Reload event manager
                    var fullRefresh = false;
                    ReloadUserHostedEventsTable(fullRefresh);

                    // Reload game title dropdown, if user entered a new game
                    /* *** This will not be needed when Create Event is moved to modal popup from main jTable screen *** */
                    if(!eventInfo.isExistingGame) {
			ReloadGameTitleSelector(eventId);
                    }
					
                    alert('Success - Created event for game "' + eventInfo.selGameTitle + '" at ' + eventInfo.displayDatetime + '!');
                }
                else {
                    alert(response);
                }
            }
        });
    }
    
    return false;
}