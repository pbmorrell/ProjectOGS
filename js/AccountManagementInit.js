function AccountManagementOnReady()
{
    $('#btnCancelMembership').click(function(event) { CancelOnClick(event); });
	
    // Format subscription date in Transaction Details table in user's local time (if shown)
    if($("#subscrDateColumn").length) {
        var subscrDateUTC = moment.utc($('#subscrDateColumn').text().trim(), "YYYY-MM-DD H:mm:ss");
        var subscrDateLocal = subscrDateUTC.local();
        $('#subscrDateColumn').text(subscrDateLocal.format("MMM Do, YYYY, [a]t h:mma"));
    }

    // Format membership expiration date in user's local time (if shown)
    if($("#expDateSpan").length) {
        var membershipExpDateUTC = moment.utc($('#expDateSpan').text().trim(), "YYYY-MM-DD H:mm:ss");
        var membershipExpDateLocal = membershipExpDateUTC.local();
        $('#expDateSpan').text(membershipExpDateLocal.format("MMM Do, YYYY"));
    }
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
			RefreshMembershipInfo();
                    }
                },
                error: function() {
                    sweetAlert("Subscription Not Cancelled", "Error communicating with PayPal: could not cancel subscription. " + 
                               "If this issue continues, try cancelling this subscription directly from your PayPal account page.", "error");
                }
            });
          }
    });
}

function RefreshMembershipInfo()
{   
    // Get current PayPal user information, and display to user
    $.ajax({
        type: "POST",
        url: "AJAXHandler.php",
        data: "action=GetPayPalUserExtendedMembershipDays",
        success: function(response){            
            if(response.length) {
                // Change bill date label to expiration date label
                $('#expDateLabel').text("Your membership will expire on:");

                // Display updated membership exp. date
                var nextBillDate = $('#expDateSpan').text().trim();
                var membershipExpDateLocal = moment(nextBillDate, "MMM Do, YYYY");
                
                var extMembershipDays = parseInt(response);
                if(!isNaN(extMembershipDays)) {
                    membershipExpDateLocal.add(extMembershipDays, 'days');
                }
                
                $('#expDateSpan').text(membershipExpDateLocal.format("MMM Do, YYYY"));    

                // Update memberActionsArticle to permit re-subscribe action rather than cancel action
                $('#memberActionsArticleRecurring').addClass('hidden');
                $('#memberActionsArticleExtend').removeClass('hidden');
            }
            else {
                sweetAlert("Reload Page", "Unable to retrieve updated user info - please reload the page", "info");
            }
        },
        error: function() {
            sweetAlert("Reload Page", "Unable to retrieve updated user info - please reload the page", "info");
        }
    });
}