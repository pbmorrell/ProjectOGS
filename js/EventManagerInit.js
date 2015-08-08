function EventManagerOnReady()
{
    $('#datepicker').datepicker({
         inline: true,
         yearRange: '-125:-2',
         changeYear: true,
         constrainInput: true,
         showButtonPanel: true,
         showOtherMonths: true,
         dayNamesMin: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
         dateFormat: 'yy-mm-dd'
    });
}