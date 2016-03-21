function PasswordRecoveryOnReady()
{
    if($("#resetPasswordFormDiv").length) {
        $('#forgotPasswordLink').hide();
	$('#forgotPasswordLinkMobile').hide();
        $('#togglePassword').hide();
        $('#togglePasswordConfirm').hide();
        $('#passwordMatch').hide();

        $('#newPW').pStrength({
            'changeBackground' : false,
            'onPasswordStrengthChanged' : function(passwordStrength, strengthPercentage) {
                evaluateCurrentPWStrength('#newPW', '#passwordStrength', passwordStrength, strengthPercentage);
            }
        });

        $('#newPW').keyup(function() {
            evaluateCurrentPWVal('#newPW', '#newPWConfirm', '#passwordMatch', '#togglePassword');
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
	
    // On startup, we want to simulate a viewport size transition, in order to format and size
    // the current layout to best fit the current browser dimensions...we initialized everything
    // as if it was in desktop view, so now we run a transition from 'desktop' to whatever the current view class is
    var curWindowHeight = $(window).height();
    lastWindowHeight = curWindowHeight;
    var curWindowWidth = $(window).width();
    lastWindowWidth = curWindowWidth;
    var initTransition = true;
    
    OnViewportSizeChanged(curWindowWidth, curWindowHeight, 'desktop', GetCurWidthClass(), 'desktop', GetCurHeightClass(), initTransition);
}

function OnResetPwdButtonClick(password, passwordConf, actionURL, successURL, pwField, pwConfirmField, pwMatchField, 
                               pwStrengthField, pwToggleField, pwConfirmToggleField, userId)
{
    var curPwdStrengthLevel = $("#passwordStrength").text();
    
    if(password.trim().length === 0) {
        sweetAlert("Oops...", "Unable to reset password: The Password field must be filled", "error");
    } else if (password !== passwordConf) {
        sweetAlert("Oops...", "Unable to reset password: Your new Password does not match the Password Confirmation", "error");
    } else if ((curPwdStrengthLevel == 'Very Weak') || (curPwdStrengthLevel == 'Weak')) {
	sweetAlert("Oops...", "Unable to reset password: The strength rating for your new password must at least be 'Fair'", "error");
    } else {
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
                    $(pwMatchField).attr('src', 'images/green_checkmark.gif');
                    $(pwMatchField).attr('title', 'Passwords match');
                    $(pwMatchField).hide();
					
                    $(pwStrengthField).html('');
                    $.fn.pStrength('resetStyle', $(pwField));
                    
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

function OnViewportSizeChanged(curWindowWidth, curWindowHeight, lastWidthClass, curWidthClass, lastHeightClass, curHeightClass, initTransition)
{   
    if((curWidthClass == "desktop") || ($("#resetPasswordFormDiv").length)) {
        $('#forgotPasswordLinkMobile').hide();
    }
    else {           
        $('#forgotPasswordLinkMobile').show();
    }
}