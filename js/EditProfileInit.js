var editProfileGamerTagManagerDlg = "gamerTagManagerDlg";
var editProfileGamerTagManagerJTableDiv = "#manageGamerTagsDiv";
var selReminderEmailTimeInterval = "hr";

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
    $('#passwordMatch').hide();

    $('#pwd').pStrength({
        'changeBackground' : false,
        'onPasswordStrengthChanged' : function(passwordStrength, strengthPercentage) {
            evaluateCurrentPWStrength('#pwd', '#passwordStrength', passwordStrength, strengthPercentage);
        }
    });

    $('#pwd').keyup(function() {
        evaluateCurrentPWVal('#pwd', '#pwdConfirm', '#passwordMatch', '#togglePassword');
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
    
    // Attach delegated event handler to Event Reminders mobile button, for when/if it becomes visible
    $('.mobileButtonDisplay').on('click', '#eventReminderSettingsBtnMobile', function() {
        return OnEventReminderSettingsClick();
    });

    // Attach delegated event handler to Event Reminders desktop button, for when/if it becomes visible
    $('.submitFormDiv').on('click', '#eventReminderSettingsBtn', function() {
        return OnEventReminderSettingsClick();
    });
}

function OnGamerTagUpdateClick()
{
    OpenGamerTagViewer(editProfileGamerTagManagerDlg, editProfileGamerTagManagerJTableDiv.substring(1), "Gamer Tag Management", 
                       "Your Gamer Tags", false, false, -1);
    return false;
}

function OnEventReminderSettingsClick()
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
    
    displayJQueryDialog("dlgEvtReminderSettings", "Event Reminder Settings", "top", displayContainerPosition, window, false, true, 
                        "AJAXHandler.php?action=EventReminderSettingsLoad", function() {
        EventReminderDialogOnReady($('#dlgEvtReminderSettings').dialog(), curWidthClass);
    }, dlgWidth, dlgHeight);
    
    return false;
}

function OnViewportSizeChanged(curWindowWidth, curWindowHeight, lastWidthClass, curWidthClass, lastHeightClass, curHeightClass)
{
    if((lastWidthClass == 'desktop') && ((curWidthClass == 'mobile') || (curWidthClass == 'xtraSmall'))) {
        if(($('#' + editProfileGamerTagManagerDlg).length) && ($('#' + editProfileGamerTagManagerDlg + ' .jtable').length)) {
            // Hide page size change area in gamerTagManager table
            $(editProfileGamerTagManagerJTableDiv + ' .jtable-page-size-change').hide();

            // Decrease width of gamer tag manager dialog
            $('#' + editProfileGamerTagManagerDlg).dialog('option', 'width', (curWindowWidth < 400) ? (curWindowWidth - 25) : 400);
        }
    }
    
    if(((lastWidthClass == 'mobile') || (lastWidthClass == 'xtraSmall')) && (curWidthClass == 'desktop')) {
        if(($('#' + editProfileGamerTagManagerDlg).length) && ($('#' + editProfileGamerTagManagerDlg + ' .jtable').length)) {
            // Show page size change area in gamerTagManager table
            $(editProfileGamerTagManagerJTableDiv + ' .jtable-page-size-change').show();

            // Increase width of gamer tag manager dialog
            $('#' + editProfileGamerTagManagerDlg).dialog('option', 'width', (curWindowWidth < 600) ? (curWindowWidth - 25) : 600);
        }
    }
}

function OnEditProfile(editProfileStatusId)
{
    var validEmailRegEx = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;

    var email = $('#emailAddress').val();
    var password = $('#pwd').val();
    var passwordConf = $('#pwdConfirm').val();
    var curPwdStrengthLevel = $("#passwordStrength").text();
    var userName = $('#userName').val();
    var dob = $('#DOBDatePicker').val();
    var bio = $('#message').val();
    var checkedPlatforms = $("input[name='platforms[]']:checked").length;

    if (validEmailRegEx.test(email) === false) {
        sweetAlert("Oops...", "Unable to update account: Please enter a valid email address", "error");
    } else if (password !== passwordConf) {
        sweetAlert("Oops...", "Unable to update account: Your Password does not match the Password Confirmation", "error");
    } else if ((password.length > 0) && ((curPwdStrengthLevel == 'Very Weak') || (curPwdStrengthLevel == 'Weak'))) {
	sweetAlert("Oops...", "Unable to update account: The strength rating for your new password must at least be 'Fair'", "error");
    } else if (userName.trim().length === 0) {
        sweetAlert("Oops...", "Unable to update account: The Username field must be filled", "error");
    } else if((email.trim().length === 0) || (dob.trim().length === 0) || (bio.trim().length === 0)) {
        sweetAlert("Oops...", "Unable to update account: The Email, Birthdate and Autobiography fields must be filled", "error");
    } else if ((checkedPlatforms > 0) || ((checkedPlatforms === 0) && (confirm("No game platforms selected...proceed with update?")))) {
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

function EventReminderDialogOnReady($dialog, curWidthClass)
{
    // Attach event handler to Cancel button
    $('#cancelBtn').click(function() {
        $dialog.dialog('destroy').remove();
        return false;
    });
    
    // Attach event handler to Update Event Reminder Settings button
    $('#submitBtn').click(function() {
        return OnApplyEventReminderChanges($dialog);
    });    
    
    // Attach selection change handler to time interval dropdown
    $('#timeIntervalSelector').change(function() {
        var selTimeInterval = $('#timeIntervalSelector').val();
        if(selReminderEmailTimeInterval == selTimeInterval)  return false;
        
        selReminderEmailTimeInterval = selTimeInterval;
        $('.timeIntervalSelect').addClass('hidden');
        if(selTimeInterval == 'min') {
            $('#minuteSelector').removeClass('hidden');
        } else if (selTimeInterval == 'hr') {
            $('#hourSelector').removeClass('hidden');
        } else {
            $('#daySelector').removeClass('hidden');
        }
    });
    
    
}

function OnApplyEventReminderChanges($dialog)
{
    
    
    sweetAlert('Success', 'Updated event reminder settings!', 'success');
    $dialog.dialog('destroy').remove();
    return false;
}