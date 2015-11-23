function AccountManagementOnReady()
{
    $('#btnCancelMembership').click(function(event) { CancelOnClick(event); });
}

function SubscribeOnClick(action)
{      
//    sweetAlert({
//        title: "Under Construction",
//    	text: "Unable to " + action + ": Paypal functionality not yet implemented",
//    	type: "info",
//    	imageUrl: "images/underConstruction.gif"
//    });
	
//    return false;
	
    //sweetAlert({
    //    title: "Please note:",
    //	text: "To " + action + ", must redirect you to PayPal site, where you'll be asked to log in and confirm the transaction. After completing payment, you will be returned to this site to view the transaction results.",
    //	type: "info"
    //});
    return true;
}

function CancelOnClick(event)
{
    event.stopPropagation();
    event.preventDefault();
    
    sweetAlert({
      title: "Confirm Cancellation",
      text: "Are you sure you want to cancel? You'll lose your friends list, and have access only to basic search and event creation functions.",
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Yes, do it!",
      closeOnConfirm: false,
      closeOnCancel: false,
      showLoaderOnConfirm: true
    },
    function(isConfirm) {
        if(!isConfirm) {
            // Show cancel message
            sweetAlert("Membership Not Changed", "You are still a premium member!", "info");
        }
        else {            
            // Make AJAX call to PayPal API to cancel this user's recurring subscription
            $.ajax({
                type: "POST",
                url: "AJAXHandler.php",
                data: "action=CancelPayPalSubscription",
                success: function(response){
                    if(response.match("^SYSTEM ERROR")) {
                        sweetAlert("Subscription Not Cancelled", response, "error");
                    }
                    else {
                        sweetAlert("Subscription Cancelled", response, "success");
                    }
                },
                error: function() {
                    sweetAlert("Subscription Not Cancelled", "Error communicating with PayPal: could not cancel subscription. " + 
                               "If this issue continues, try cancelling this subscription directly from your PayPal account page.", "error");
                }
            });

//            sweetAlert({
//                title: "Under Construction",
//                type: "info",
//                text: "Unable to cancel: Paypal functionality not yet implemented",
//                imageUrl: "images/underConstruction.gif"
//            });
          }
    });
}
