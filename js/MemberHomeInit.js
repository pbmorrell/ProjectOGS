// Globals
var panelEnum = {
    None: 'None',
    EventScheduler: '#scheduleEventDiv',
    MyEventViewer: '#manageEventsDiv',
    CurrentEventFeed: '#currentEventsDiv'
};

var activePanel = panelEnum.CurrentEventFeed; 

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
    /* ****************************** OLD WAY (event manager in straight HTML div) *******************************
    // Make AJAX call to initialize Event Manager
     $.ajax({
        type: "POST",
        url: "AJAXHandler.php",
        data: "action=EventManagerLoad",
        success: function(response){
            $('#manageEventsContent').html(response);
            EventManagerOnReady();
        },
        error: function() {
            $('#manageEventsContent').html('Unable to load Event Manager...please try again later');
        }
    });
    *********************************************************************************************************** */
	
    /* ******************************* NEW WAY (use jTable plugin for event management) *********************** */
	
    // Initialize jTable on manageEventsContent div
    $('#manageEventsContent').jtable({
        title: "Events Hosted By You",
        paging: true,
        pageSize: 10,
        pageSizes: [5, 10, 15, 20, 25],
        sorting: true,
        defaultSorting: 'DisplayDate ASC',
        actions: {
            listAction: 'AJAXHandler.php?action=GetUserOwnedEventsForJTable'
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
                width: '10%',
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
                width: '13%',
                sorting: false
            },
            Edit: {
                title: 'Edit Event',
                width: '10%',
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
            }
        }
    });

    // Load event list
    $('#manageEventsContent').jtable('load');

    /* ******************************************************************************************************** */
}

function EventManagerOnReady()
{
    
}

function ReloadGameTitleSelector()
{
    // Make AJAX call to refresh game title selector
    $.ajax({
        type: "POST",
        url: "AJAXHandler.php",
        data: "action=ReloadGameTitleSelector",
        success: function(response){
            $('#gameSelectorDiv').html(response);
            
            // Convert reloaded game selector dropdown list to a jQuery-powered comboBox for game title selection or entry
            PrepareAutocompleteComboBox("selGameTitle");
            $('#ddlGameTitles').combobox();
        },
        error: function() {
            var dfltSelectHtml =    '<select id="ddlGameTitles" name="ddlGameTitles">' +
                                        '<option value="-1" class="globalGameOption">' + 
                                            'Unable to reload game title selector...please try again later' + 
                                        '</option>' +
                                    '</select>';
								 
            $('#gameSelectorDiv').html(dfltSelectHtml);
        }
    });
}

function ReloadUserHostedEventsTable()
{
    // Reload event list
    $('#manageEventsContent').jtable('load');
}

function DisplayEditEventDialog(eventId)
{
    var $dialog = $('<div></div>').load('AJAXHandler.php?action=EventEditorLoad&EventID=' + eventId, 
                                        function() { EventSchedulerDialogOnReady(eventId); }).dialog({
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

function EventSchedulerDialogOnReady(eventId)
{
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
        return EditEvent(eventId);
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

function EditEvent(eventId)
{
    alert('Editing event ' + eventId);
    return false;
}

function CreateEvent()
{
    var gameDate = $('#gameDate').val();
    var gameTime = $('#gameTime').val();
    var gameTimezone = $('#ddlTimeZones option:selected').text();
    var displayDatetime = gameDate + " " + gameTime + " " + gameTimezone;
    
    var comments = $('#message').val();
    var isGlobalGame = $('#ddlGameTitles option:selected').attr('class') == "globalGameOption" ? "true" : "false";
	var isExistingGame = (($('#ddlGameTitles').val() != null) && ($('#ddlGameTitles').val().length > 0));
	
    // Regular expression for time format
    var regexTime = /^\d{1,2}:\d{2}([apAP][mM])?$/;

    // Verify that required fields are filled out and have valid data
    if(gameDate.length === 0) {
        alert("Unable to create event: Must select a date");
    } else if (gameTime.length === 0) {
        alert("Unable to create event: Must select a time");
    } else if (!gameTime.match(regexTime)) {
        alert("Unable to create event: Must enter a valid time");
    } else if (comments.trim().length === 0) {
        alert("Unable to create event: Please enter notes about your event");
    } else {
        var curDateMoment = moment().utc();
        var gameDateWithTZ = moment.tz(gameDate + " " + gameTime, "YYYY-MM-DD h:mmA", gameTimezone);
        var gameTimeMoment = moment(gameDateWithTZ).utc();

        if (gameTimeMoment.isBefore(curDateMoment)) {
            alert("Unable to create event: Scheduled game time '" + displayDatetime + "' is in the past");
            return false;
        }

        // Create event
        $.ajax({
            type: "POST",
            url: "AJAXHandler.php",
            data: "action=EventEditorCreateEvent&isGlobalGame=" + isGlobalGame + "&gameTitle=" + $('#selGameTitle').val() + 
                  "&gameDateUTC=" + gameTimeMoment.toISOString() + "&" + $('#eventCreateForm').serialize(),
            success: function(response){
                if(response === 'true') {
                    // Reload event manager
                    //LoadEventManager();
                    ReloadUserHostedEventsTable();

                    // Reload game title dropdown, if user entered a new game
                    if(!isExistingGame) {
			ReloadGameTitleSelector();
                    }
					
                    alert('Success - Created event for game "' + $('#selGameTitle').val() + '" at ' + displayDatetime + '!');
                }
                else {
                    alert(response);
                }
            }
        });
    }
    
    return false;
}