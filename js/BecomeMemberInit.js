function BecomeMemberOnReady()
{

}

function SubscribeOnClick(action)
{
    $('#btnSubscribe').click(function(){        
        sweetAlert({
           title: "Under Construction",
           text: "Unable to " + action + ": Paypal functionality not yet implemented",
           type: "info",
           imageUrl: "images/underConstruction.gif"
        });
        
        return false;
    });
}
