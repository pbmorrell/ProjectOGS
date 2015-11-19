function BecomeMemberOnReady()
{

}

function SubscribeOnClick(action)
{      
    sweetAlert({
        title: "Under Construction",
    	text: "Unable to " + action + ": Paypal functionality not yet implemented",
    	type: "info",
    	imageUrl: "images/underConstruction.gif"
    });
	
    return false;
	
    //sweetAlert({
    //    title: "Please note:",
    //	text: "To " + action + ", must redirect you to PayPal site, where you'll be asked to log in and confirm the transaction. After completing payment, you will be returned to this site to view the transaction results.",
    //	type: "info"
    //});
    //return true;
}
