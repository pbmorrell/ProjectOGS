function IndexOnReady()
{
    var getAction = GetURLParamVal('action');
    getAction = (getAction.length > 0) ? getAction : 'Login';
    
    // If viewing device has screen width > 650px, treat as mobile device
    // and redirect user to mobile login page
    if ((getAction != "Signup") && isMobileView()) {
        window.location.replace("MobileLogin.php");
    }
    
    // If this called from a page with signup form
    if($("#signupFormDiv").length) {
        $('#togglePassword').hide();
        $('#togglePasswordConfirm').hide();

        $('#signupPW').keyup(function() {
            evaluateCurrentPWVal('#signupPW', '#signupPWConfirm', '#passwordStrength', '#passwordMatch', '#togglePassword');
        });

        $('#signupPWConfirm').keyup(function() {
            evaluateCurrentPWConfirmVal('#signupPW', '#signupPWConfirm', '#passwordMatch', '#togglePasswordConfirm');
        });

        $('#signupBtn').click(function() {
            var email = $('#signupEmail').val();
            var password = $('#signupPW').val();
            var captcha = $('#captcha_code').val();
            var passwordConf = $('#signupPWConfirm').val();

            return OnSignupButtonClick(email, password, passwordConf, captcha, 'AJAXHandler.php', 'EditProfile.php', 
                                       '#signupPW', '#signupPWConfirm', '#captcha_code', '#captcha', '#passwordMatch', 
                                       '#passwordStrength', '#togglePassword', '#togglePasswordConfirm', '#signupErr');
        });
    }

    $('#loginBtn').click(function() {
        $('#loginErr').attr('class', 'preLogin');
        $('#loginErr').html("Logging In...");
        $('#loginErr').fadeIn(200);

        $.ajax({
            type: "POST",
            url: "AJAXHandler.php",
            data: "action=Login&" + $('#loginForm').serialize(),
            success: function(response){
                if(response === 'true') {
                    window.location.href = "MemberHome.php";
                }
                else {
                    $('#loginErr').attr('class', 'loginError');
                    $('#loginErr').html(response);

                    $('#loginPassword').val('');

                    setTimeout(function() {
                        $('#loginErr').hide();
                        }, 3000
                    );
                }
            }
        });

        return false;
    });
}

function OnSignupButtonClick(email, password, passwordConf, captcha, actionURL, successURL, pwField, 
                             pwConfirmField, captchaCodeField, captchaImage, pwMatchField, pwStrengthField, 
                             pwToggleField, pwConfirmToggleField, errField)
{
    var validEmailRegEx = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;

    if((email.trim().length === 0) || (password.trim().length === 0)) {
        sweetAlert("Opps...", "Unable to create account: The Email Address and Password fields must be filled", "error");
    }
    else if (validEmailRegEx.test(email) === false) {
        sweetAlert("Opps...", "Unable to create account: Please enter a valid email address", "error");
    }
    else if (password !== passwordConf) {
        sweetAlert("Opps...", "Unable to create account: Your Password does not match the Password Confirmation", "error");
    }
    else if (captcha.trim().length === 0) {
        sweetAlert("Opps...", "Unable to create account: Please enter the code displayed in the CAPTCHA image", "error");
    }
    else {
        $.ajax({
            type: "POST",
            url: actionURL,
            data: "action=Signup&signupPW=" + password + "&signupEmail=" + email + "&captcha_code=" + captcha,
            success: function(response){
                if(response === 'true') {
                    window.location.href = successURL;
                }
                else {
                    // Clear input fields, except for email
                    $(pwField).val('');
                    $(pwConfirmField).val('');
                    $(captchaCodeField).val('');

                    // Clear password strength and password-confirm-match indicators
                    $(pwMatchField).html('');
                    $(pwStrengthField).html('');
                    $(pwToggleField).hide();
                    $(pwConfirmToggleField).hide();

                    // Display error message from server
                    $(errField).html(response);

                    // Refresh captcha image
                    $(captchaImage).attr('src', 'securimage/securimage_show.php?' + Math.random());
                }
            }
        });
    }

    return false;
}