function MobileLoginOnReady()
{
    $('#mobileLoginBtn').click(function() {
        $('#loginErr').attr('class', 'mobilePreLogin');
        $('#loginErr').html("Logging In...");
        $('#loginErr').fadeIn(200);

        $.ajax({
            type: "POST",
            url: "AJAXHandler.php",
            data: "action=Login&" + $('#mobileLoginForm').serialize(),
            success: function(response){
                if(response === 'true') {
                    window.location.href = "MemberHome.php";
                }
                else {
                    $('#loginErr').attr('class', 'mobileLoginError');
                    $('#loginErr').html(response);
                    
                    $('#loginPassword').val('');

                    setTimeout(function() {
                        $('#loginErr').hide();
                        }, 
                        3000
                    );
                }
            }
        });

        return false;
    });
	
    var curWidthClass = GetCurWidthClass();
    var curHeightClass = GetCurHeightClass();
    var displayContainerPosition = "top";
    var dlgWidth = 600;
    var dlgHeight = 400;
    
    if(curWidthClass == 'mobile') {
        dlgWidth = 400;
        displayContainerPosition = "top+10%";
    }
    if(curWidthClass == 'xtraSmall') {
        dlgWidth = 275;
        displayContainerPosition = "top+10%";
    }
    
    if((curHeightClass == 'mobile') || (curHeightClass == 'xtraSmall')) {
        dlgHeight = 375;
    }
        
    $('#forgotPasswordLink').click(function() {
    	displayJQueryDialog("dlgPasswordRecovery", "Forgot Password", "top", displayContainerPosition, window, false, true, 
                            "AJAXHandler.php?action=PasswordRecoveryDialogLoad", function() {
            PasswordRecoveryDialogOnReady($('#dlgPasswordRecovery').dialog());
        }, dlgWidth, dlgHeight);
		
	return false;
    });
}