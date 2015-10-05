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

    // If viewing device has screen width < 650px, treat as mobile device
    if (isMobileView()) {
        $('#signupBtn').hide();
        $('#signupBtnMobile').show();
        
        // Attach event handler to Edit Profile mobile button
        $('#signupBtnMobile').click(function() {
            return OnEditProfile('#editProfileStatusMobile');
        });
    }
    else {
        $('#signupBtn').show();
        $('#signupBtnMobile').hide();
        
        // Attach event handler to Edit Profile desktop button
        $('#signupBtn').click(function() {
            return OnEditProfile('#editProfileStatus');
        });
    }
}

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
        alert("Unable to update account: Please enter a valid email address");
    }
    else if (password !== passwordConf) {
        alert("Unable to update account: Your Password does not match the Password Confirmation");
    }
    else if (userName.trim().length === 0) {
        alert("Unable to update account: The Username field must be filled");
    }
    else if((email.trim().length === 0) || (dob.trim().length === 0) || (bio.trim().length === 0)) {
        alert("Unable to update account: The Email, Birthdate and Autobiography fields must be filled");
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