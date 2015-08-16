// Globals
var panelEnum = {
    None: 'None',
    EventScheduler: '#scheduleEventDiv',
    MyEventViewer: '#viewEventsDiv',
    CurrentEventFeed: '#currentEventsDiv'
};

var activePanel = panelEnum.CurrentEventFeed; 

// Functions
function MemberHomeOnReady()
{
    $(panelEnum.EventScheduler).addClass('hidden');
    $(panelEnum.MyEventViewer).addClass('hidden');
	
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
    
    // Add new mask definitions
    $.mask.definitions['#'] = '[01]';
    $.mask.definitions['@'] = '[012345]';
    $.mask.definitions['~'] = '[apAP]';
    
    // Apply input mask to fields
    $('#gameDate').mask('9999-99-99');
    $('#gameTime').mask('#9:@9 ~m');
	
    // If user selects Private Event checkbox, enable friend list selection
    $('#privateEvent').click(function() {
	var checkedVal = this.checked;
			
	if(checkedVal) {
            $("input[name='pvtEventFriends[]']").removeProp('disabled');
	}
	else {
            $("input[name='pvtEventFriends[]']").prop('disabled', true);
            $("input[name='pvtEventFriends[]']").prop('checked', false);
	}
    });
	
    // Attach event handler to Create Event button
    $('#createEventBtn').click(function() {
	CreateEvent();
        return false;
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

function CreateEvent()
{
    var curDateTimestamp = new Date();
    var curDateString = curDateTimestamp.getFullYear() + "-" + curDateTimestamp.getMonth() + "-" + curDateTimestamp.getDate();
    var curTimeString = curDateTimestamp.getHours() + ":" + curDateTimestamp.getMinutes() + ":" + curDateTimestamp.getSeconds();
    curTimeString = curTimeString + (curDateTimestamp.getHours() > 11 ? " pm" : " am");
    
    var gameDate = $('#gameDate').val();
    var gameTime = $('#gameTime').val();
    var comments = $('#message').val();
    
    // Verify that required fields are filled out and have valid data
    if ((gameDate < curDateString) || ((gameDate == curDateString) && (gameTime < curTimeString))) {
        alert("Unable to create event: Specified game date or time is in the past");
    } else if (comments.trim().length === 0) {
        alert("Unable to create event: Please enter notes about your event");
    } else {    
        // Create event
        $.ajax({
            type: "POST",
            url: "AJAXHandler.php",
            data: "action=EventEditorCreateEvent&" + $('#eventCreateForm').serialize(),
            success: function(response){
                if(response === 'true') {
                    alert('Success - Created event for game "' + $('#selGameTitle').val() + '" at ' + 
                          $('#gameDate').val() + ' ' + $('#gameTime').val() + '!');
                }
                else {
                    alert(response);
                }
            }
        });
    }
}