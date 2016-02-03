function PasswordRecoveryOnReady()
{
    if($("#resetPasswordFormDiv").length) {
        $('#forgotPasswordLink').hide();
        $('#togglePassword').hide();
        $('#togglePasswordConfirm').hide();

        $('#newPW').keyup(function() {
            evaluateCurrentPWVal('#newPW', '#newPWConfirm', '#passwordStrength', '#passwordMatch', '#togglePassword');
        });

        $('#newPWConfirm').keyup(function() {
            evaluateCurrentPWConfirmVal('#newPW', '#newPWConfirm', '#passwordMatch', '#togglePasswordConfirm');
        });

        $('#resetPwdBtn').click(function() {
            var password = $('#newPW').val();
            var passwordConf = $('#newPWConfirm').val();
            var userId = $('#userId').val();

            return OnResetPwdButtonClick(password, passwordConf, 'AJAXHandler.php', 'MemberHome.php', '#newPW', '#newPWConfirm', 
                                         '#passwordMatch', '#passwordStrength', '#togglePassword', '#togglePasswordConfirm', userId);
        });
    }
}

function OnResetPwdButtonClick(password, passwordConf, actionURL, successURL, pwField, pwConfirmField, pwMatchField, 
                               pwStrengthField, pwToggleField, pwConfirmToggleField, userId)
{
    if(password.trim().length === 0) {
        sweetAlert("Oops...", "Unable to reset password: The Password field must be filled", "error");
    }
    else if (password !== passwordConf) {
        sweetAlert("Oops...", "Unable to reset password: Your new Password does not match the Password Confirmation", "error");
    }
    else {
        $.ajax({
            type: "POST",
            url: actionURL,
            data: "action=ResetUserPassword&resetPW=" + password + "&userId=" + userId,
            success: function(response){
                if(response === 'true') {
                    sweetAlert({
                        title: "Success!",
                        text: "Successfully reset password...redirecting to member home page",
                        type: "success"
                    },
                    function(){
                        window.location.href = successURL;
                    });
                }
                else {
                    // Clear input fields
                    $(pwField).val('');
                    $(pwConfirmField).val('');

                    // Clear password strength and password-confirm-match indicators
                    $(pwMatchField).html('');
                    $(pwStrengthField).html('');
                    $(pwToggleField).hide();
                    $(pwConfirmToggleField).hide();

                    // Display error message from server
                    sweetAlert("Oops...", response, "error");
                }
            }
        });
    }

    return false;    
}