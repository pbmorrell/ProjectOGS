function EditProfileOnReady(){$("#DOBDatePicker").datepicker({inline:!0,yearRange:"-125:-2",changeYear:!0,changeMonth:!0,constrainInput:!0,showButtonPanel:!0,showOtherMonths:!0,dayNamesMin:["Sun","Mon","Tue","Wed","Thu","Fri","Sat"],defaultDate:"-2y",dateFormat:"yy-mm-dd"}),$("#togglePassword").hide(),$("#togglePasswordConfirm").hide(),$("#pwd").keyup(function(){evaluateCurrentPWVal("#pwd","#pwdConfirm","#passwordStrength","#passwordMatch","#togglePassword")}),$("#pwdConfirm").keyup(function(){evaluateCurrentPWConfirmVal("#pwd","#pwdConfirm","#passwordMatch","#togglePasswordConfirm")}),$("#userName").blur(function(){evaluateUserNameAvailability("#userName","#userNameTakenIndicator","AJAXHandler.php")}),$(".mobileButtonDisplay").on("click","#signupBtnMobile",function(){return OnEditProfile("#editProfileStatusMobile")}),$(".submitFormDiv").on("click","#signupBtn",function(){return OnEditProfile("#editProfileStatus")}),$(".mobileButtonDisplay").on("click","#gamerTagUpdateBtnMobile",function(){return OnGamerTagUpdateClick()}),$(".submitFormDiv").on("click","#gamerTagUpdateBtn",function(){return OnGamerTagUpdateClick()})}function OnGamerTagUpdateClick(){return OpenGamerTagViewer(editProfileGamerTagManagerDlg,editProfileGamerTagManagerJTableDiv.substring(1),"Gamer Tag Management","Your Gamer Tags",!1,!1,-1),!1}function OnViewportSizeChanged(a,b,c,d,e,f){"desktop"!=c||"mobile"!=d&&"xtraSmall"!=d||$("#"+editProfileGamerTagManagerDlg).length&&$("#"+editProfileGamerTagManagerDlg+" .jtable").length&&($(editProfileGamerTagManagerJTableDiv+" .jtable-page-size-change").hide(),$("#"+editProfileGamerTagManagerDlg).dialog("option","width",400>a?a-25:400)),"mobile"!=c&&"xtraSmall"!=c||"desktop"!=d||$("#"+editProfileGamerTagManagerDlg).length&&$("#"+editProfileGamerTagManagerDlg+" .jtable").length&&($(editProfileGamerTagManagerJTableDiv+" .jtable-page-size-change").show(),$("#"+editProfileGamerTagManagerDlg).dialog("option","width",600>a?a-25:600))}function OnEditProfile(a){var b=/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i,c=$("#emailAddress").val(),d=$("#pwd").val(),e=$("#pwdConfirm").val(),f=$("#userName").val(),g=$("#DOBDatePicker").val(),h=$("#message").val(),i=$("input[name='platforms[]']:checked").length;return b.test(c)===!1?sweetAlert("Oops...","Unable to update account: Please enter a valid email address","error"):d!==e?sweetAlert("Oops...","Unable to update account: Your Password does not match the Password Confirmation","error"):0===f.trim().length?sweetAlert("Oops...","Unable to update account: The Username field must be filled","error"):0===c.trim().length||0===g.trim().length||0===h.trim().length?sweetAlert("Oops...","Unable to update account: The Email, Birthdate and Autobiography fields must be filled","error"):(i>0||0===i&&confirm("No game platforms selected...proceed with update?"))&&($(a).attr("class","preEditProfile"),$(a).html("Updating Account..."),$(a).fadeIn(200),$.ajax({type:"POST",url:"AJAXHandler.php",data:"action=UpdateAccount&"+$("#signupForm").serialize(),success:function(b){"true"===b?window.location.href="MemberHome.php":($(a).attr("class","editProfileErr"),$(a).html(b),setTimeout(function(){$(a).hide()},3e3))}})),!1}var editProfileGamerTagManagerDlg="gamerTagManagerDlg",editProfileGamerTagManagerJTableDiv="#manageGamerTagsDiv";