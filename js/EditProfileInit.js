var gamerTagManagerDlg = "gamerTagManagerDlg";
var gamerTagManagerJTableDiv = "#manageGamerTagsDiv";
var cachedPlatformList = undefined;

function EditProfileOnReady()
{
    $('#DOBDatePicker').datepicker({
        inline: true,
        yearRange: '-125:-2',
        changeYear: true,
        changeMonth: true,
        constrainInput: true,
        showButtonPanel: true,
        showOtherMonths: true,
        dayNamesMin: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        defaultDate: '-2y',
        dateFormat: 'yy-mm-dd'
    });

    $('#togglePassword').hide();
    $('#togglePasswordConfirm').hide();

    $('#pwd').keyup(function() {
        evaluateCurrentPWVal('#pwd', '#pwdConfirm', '#passwordStrength', '#passwordMatch', '#togglePassword');
    });

    $('#pwdConfirm').keyup(function() {
        evaluateCurrentPWConfirmVal('#pwd', '#pwdConfirm', '#passwordMatch', '#togglePasswordConfirm');
    });

    $('#userName').blur(function() {
        evaluateUserNameAvailability('#userName', '#userNameTakenIndicator', 'AJAXHandler.php');
    });

    // Attach delegated event handler to Edit Profile mobile button, for when/if it becomes visible
    $('.mobileButtonDisplay').on('click', '#signupBtnMobile', function() {
        return OnEditProfile('#editProfileStatusMobile');
    });

    // Attach delegated event handler to Edit Profile desktop button, for when/if it becomes visible
    $('.submitFormDiv').on('click', '#signupBtn', function() {
        return OnEditProfile('#editProfileStatus');
    });
    
    // Attach delegated event handler to Manage Gamer Tags mobile button, for when/if it becomes visible
    $('.mobileButtonDisplay').on('click', '#gamerTagUpdateBtnMobile', function() {
        return OnGamerTagUpdateClick();
    });

    // Attach delegated event handler to Manage Gamer Tags desktop button, for when/if it becomes visible
    $('.submitFormDiv').on('click', '#gamerTagUpdateBtn', function() {
        return OnGamerTagUpdateClick();
    });
}

function OnGamerTagUpdateClick()
{
    var dialogHTML = '<div id="' + gamerTagManagerDlg + '">' +
			'<div class="box style3 paddingOverride">' +
                            '<p><i class="fa fa-gamepad"></i> &nbsp; Add, update, or delete gamer tags tied to your user ID</p>' +
                            '<div class="fixedHeightScrollableContainerJumbo">' +
                                '<div id="manageGamerTagsDiv" class="jTableContainer">' +
                                '</div>' + 
                            '</div><br />' +
                            '<div id="gamerTagDialogToolbar">' +
                                '<button class="gamerTagManagerBtn icon fa-thumbs-o-down" id="cancelBtn">Cancel</button>' +
                            '</div>' +
			'</div>' +
                     '</div>';

    if(($('#' + gamerTagManagerDlg).length) && ($('#' + gamerTagManagerDlg + ' .jtable').length)) {
        $('#' + gamerTagManagerDlg).dialog('open');
    }
    else {
        displayJQueryDialogFromDiv(dialogHTML, "Gamer Tag Management", "top", "top", window, false, true, 600, "auto");
        GamerTagManagerDialogOnReady($('#' + gamerTagManagerDlg).dialog());
    }
}

function GamerTagManagerDialogOnReady($dialog)
{
    // Initialize jTable on currentEventsContent div
    $(gamerTagManagerJTableDiv).jtable({
        title: 'Your Gamer Tags',
        paging: true,
        pageSize: 10,
        //pageSizes: [5, 10, 15, 20, 25],
        pageSizeChangeArea: false,
        sorting: true,
        defaultSorting: 'GamerTagName ASC',
        openChildAsAccordion: false,
        actions: {
            listAction: "AJAXHandler.php?action=GetCurrentGamerTagsForUser",
            createAction: "AJAXHandler.php?action=AddGamerTagForUser",
            updateAction: "AJAXHandler.php?action=UpdateGamerTagsForUser",
            deleteAction: "AJAXHandler.php?action=DeleteGamerTagsForUser"
        },
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
            // Nothing to do here yet
	}
    });

    // Load event list
    $(gamerTagManagerJTableDiv).jtable('load');
    
    // Attach event handler to Cancel Event Creation/Update button
    $('#cancelBtn').click(function() {
        $dialog.dialog('close');
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

//function OnViewportWidthChanged(newViewType)
//{	
//    switch(newViewType) {
//        case "xtraSmall":
//        case "mobile":
//
//            break;
//        case "desktop":
//
//            break;
//    }
//}

function OnEditProfile(editProfileStatusId)
{
    var validEmailRegEx = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;

    var email = $('#emailAddress').val();
    var password = $('#pwd').val();
    var passwordConf = $('#pwdConfirm').val();
    var userName = $('#userName').val();
    var dob = $('#DOBDatePicker').val();
    var bio = $('#message').val();
    var checkedPlatforms = $("input[name='platforms[]']:checked").length;

    if (validEmailRegEx.test(email) === false) {
        sweetAlert("Oops...", "Unable to update account: Please enter a valid email address", "error");
    }
    else if (password !== passwordConf) {
        sweetAlert("Oops...", "Unable to update account: Your Password does not match the Password Confirmation", "error");
    }
    else if (userName.trim().length === 0) {
        sweetAlert("Oops...", "Unable to update account: The Username field must be filled", "error");
    }
    else if((email.trim().length === 0) || (dob.trim().length === 0) || (bio.trim().length === 0)) {
        sweetAlert("Oops...", "Unable to update account: The Email, Birthdate and Autobiography fields must be filled", "error");
    } 
    else if ((checkedPlatforms > 0) || ((checkedPlatforms === 0) && (confirm("No game platforms selected...proceed with update?")))) {
        $(editProfileStatusId).attr('class', 'preEditProfile');
        $(editProfileStatusId).html("Updating Account...");
        $(editProfileStatusId).fadeIn(200);

        // Make AJAX call to update user account
        $.ajax({
            type: "POST",
            url: "AJAXHandler.php",
            data: "action=UpdateAccount&" + $('#signupForm').serialize(),
            success: function(response){
                if(response === 'true') {
                    window.location.href = "MemberHome.php";
                }
                else {
                    $(editProfileStatusId).attr('class', 'editProfileErr');
                    $(editProfileStatusId).html(response);

                    setTimeout(function() {
                        $(editProfileStatusId).hide();
                        }, 3000
                    );
                }
            }
        });
    }

    return false;
}