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
}