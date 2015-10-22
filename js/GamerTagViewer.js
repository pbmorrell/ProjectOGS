var gamerTagManagerDlg = "gamerTagManagerDlg";
var gamerTagManagerJTableDiv = "#manageGamerTagsDiv";
var thisDialogTitle = "Gamer Tag Management";
var thisGamerTagTableTitle = "Your Gamer Tags";
var isReadOnlyMode = false;
var destroyThisDialogOnClose = false;
var cachedPlatformList = undefined;

function OpenGamerTagViewer(gamerTagManagerDlgId, gamerTagManagerJTableDivId, dialogTitle, tableTitle, isReadOnly, destroyDialogOnClose)
{
    gamerTagManagerDlg = gamerTagManagerDlgId;
    gamerTagManagerJTableDiv = gamerTagManagerJTableDivId;
    thisDialogTitle = dialogTitle;
    thisGamerTagTableTitle = tableTitle;
    isReadOnlyMode = isReadOnly;
    destroyThisDialogOnClose = destroyDialogOnClose;
	
    var dialogDescriptor = '<p><i class="fa fa-gamepad"></i> &nbsp; Add, update, or delete gamer tags tied to your user ID</p>';
    if(isReadOnlyMode) {
	dialogDescriptor = '<br />';
    }
	
    var dialogHTML = '<div id="' + gamerTagManagerDlg + '">' + 
			'<div class="box style3 paddingOverride">' + dialogDescriptor +
                            '<div id="manageGamerTagsDiv" class="jTableContainer">' +
                            '</div><br />' + 
                            '<div id="gamerTagDialogToolbar" style="text-align: center;">' +
                                '<button class="gamerTagManagerBtn icon fa-close" id="cancelBtn">Close</button>' +
                            '</div>' +
			'</div>' +
                     '</div>';

    if(($('#' + gamerTagManagerDlg).length) && ($('#' + gamerTagManagerDlg + ' .jtable').length)) {
        $('#' + gamerTagManagerDlg).dialog('open');
    }
    else {
        displayJQueryDialogFromDiv(dialogHTML, thisDialogTitle, "top", "top", window, false, true, 600, "auto");
        GamerTagManagerDialogOnReady($('#' + gamerTagManagerDlg).dialog());
    }
}

function GamerTagManagerDialogOnReady($dialog)
{
    var managerActions = {
        listAction: "AJAXHandler.php?action=GetCurrentGamerTagsForUser",
        createAction: "AJAXHandler.php?action=AddGamerTagForUser",
        updateAction: "AJAXHandler.php?action=UpdateGamerTagsForUser",
        deleteAction: "AJAXHandler.php?action=DeleteGamerTagsForUser"
    };
	
    var readOnlyActions = { listAction: "AJAXHandler.php?action=GetCurrentGamerTagsForUser" };
	
    // Initialize jTable on requested div
    $(gamerTagManagerJTableDiv).jtable({
        title: thisGamerTagTableTitle,
        paging: true,
        pageSize: 10,
        pageSizes: [5, 10, 15, 20, 25],
        pageSizeChangeArea: true,
	gotoPageArea: 'none',
        sorting: true,
        defaultSorting: 'GamerTagName ASC',
        openChildAsAccordion: false,
        actions: isReadOnlyMode ? readOnlyActions : managerActions,
        fields: {
            ID: {
                key: true,
                list: false
            },
            GamerTagName: {
                title: 'Tag Name',
                width: '65%',
                sorting: true
            },
            PlatformName: {
                title: 'Platform',
                width: '35%',
		display: function(data) {
                    // When a record is updated, the (numeric) value of the platform dropdown list is sent to the server for update, and this number
                    // is returned as "PlatformName" after the update completes. So, have to look up the platform name by its select list value here,
                    // if we're listing records after an update
                    return ($.isNumeric(data.record.PlatformName)) ? FindPlatformNameByID(data.record.PlatformName) : data.record.PlatformName;
		},
                input: function (data) {
                    // Get list of platforms
                    var selectorHTML = '<select id="ddlPlatforms" name="PlatformName"><option value="-1">DB error: could not load consoles</option></select>';
                    if(!cachedPlatformList) {
                        $.ajax({
                            type: 'POST',
                            url: 'AJAXHandler.php',
                            data: 'action=GetPlatformDropdownListForEditor&selectorFieldName=PlatformName',
                            async: false,
                            success: function(response){
                                if(response != 'ERROR') {
                                    cachedPlatformList = response;
                                }
                            },
                            error: function () {
                                return selectorHTML;
                            }
                        });
                    }
                    
                    return ConstructPlatformOptionList(data);

                },
                sorting: true
            }
        },
	recordsLoaded: function(event, data) {
            // Enclose jTable container in fixed-height scrollable div
            $(gamerTagManagerJTableDiv + ' .jtable-main-container').children('.jtable').wrap('<div class="fixedHeightScrollableContainerJumbo"></div>');
			
            if(isMobileView()) {
		// Hide page size change area
		$(gamerTagManagerJTableDiv + ' .jtable-page-size-change').hide();
            }
	}
    });

    // Load event list
    $(gamerTagManagerJTableDiv).jtable('load');
    
    // Attach event handler to Cancel Event Creation/Update button
    $('#cancelBtn').click(function() {
	if(destroyThisDialogOnClose)   $dialog.dialog('destroy').remove();
	else                           $dialog.dialog('close');
        return false;
    });
}

function ConstructPlatformOptionList(data)
{
    // Construct option list
    var selectorHTML = cachedPlatformList;
    if(data.formType == 'edit') {
        var selectedPlatformId = data.record.PlatformID;
        var selectedOptionIdx = cachedPlatformList.indexOf("<option value='" + selectedPlatformId + "'");
        if(selectedOptionIdx >= 0) {
            selectorHTML = cachedPlatformList.replace("<option value='" + selectedPlatformId + "'", "<option value='" + selectedPlatformId + "' selected='true'");
        }
    }

    return selectorHTML;
}

function FindPlatformNameByID(platformID)
{
    var platformName = platformID;
    if(cachedPlatformList) {
	// Search option list
	var platformIdx = cachedPlatformList.indexOf("value='" + platformID + "'");
	if(platformIdx >= 0) {
            var platformNameStartIdx = cachedPlatformList.indexOf(">", platformIdx) + 1;
            var platformNameEndIdx = cachedPlatformList.indexOf("</option>", platformNameStartIdx);
            platformName = cachedPlatformList.substring(platformNameStartIdx, platformNameEndIdx);
	}
    }

    return platformName;
}
