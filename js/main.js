// Shared functions
function _(x) {
    return document.getElementById(x);	
}

function togglePasswordField(targetToggle, targetPWField, pwField, pwConfirmField, targetPWPlaceholder, thisIsConfirmField) {
    var $input = $(targetPWField);
    var change = "password";
    if ($(targetToggle).html() === 'Show Password'){
        change = "text";
        $(targetToggle).html('Hide Password');
    } else {
        $(targetToggle).html('Show Password');
    }
    
    var rep = $("<input type='" + change + "' maxlength='50' placeholder='" + targetPWPlaceholder + "' />")
                .attr("id", $(targetPWField).attr("id"))
                .attr('class', $(targetPWField).attr('class'))
                .val($(targetPWField).val())
                .insertBefore($(targetPWField));
    
    $input.remove();
    $input = rep;
	
    if(thisIsConfirmField) {
	$input.keyup(function() {
            evaluateCurrentPWConfirmVal(pwField, pwConfirmField, '#passwordMatch', targetToggle);
	});
    }
    else {
	$input.keyup(function() {
            evaluateCurrentPWVal(pwField, pwConfirmField, '#passwordStrength', '#passwordMatch', targetToggle);
	});
    }
    
    return false;
}

function evaluateCurrentPWVal(pwField, pwConfirmField, pwStrengthField, pwMatchField, pwToggleLink) {
    var strongRegex = new RegExp("^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\\W).*$", "g");
    var mediumRegex = new RegExp("^(?=.{7,})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*$", "g");
    var enoughRegex = new RegExp("(?=.{6,}).*", "g");
	
    var curPWVal = $(pwField).val();
    var curPWConfVal = $(pwConfirmField).val();
	 
    if(curPWVal.length === 0) {
	$(pwStrengthField).attr('class', 'passwordNone');
	$(pwStrengthField).html('');
	$(pwMatchField).html('');
	$(pwToggleLink).hide();
    } else {
	if(curPWVal !== curPWConfVal) {
            $(pwMatchField).attr('class', 'passwordWeak');
            $(pwMatchField).html('Passwords do not match');
            $(pwToggleLink).show();
	} else {
            $(pwMatchField).attr('class', 'passwordStrong');
            $(pwMatchField).html('Passwords match!');
            $(pwToggleLink).show();
	}
		
	if (false === enoughRegex.test(curPWVal)) {
            $(pwStrengthField).attr('class', 'passwordWeak');
            $(pwStrengthField).html('More Characters');
            $(pwToggleLink).show();
	} else if (strongRegex.test(curPWVal)) {
            $(pwStrengthField).attr('class', 'passwordStrong');
            $(pwStrengthField).html('Strong!');
            $(pwToggleLink).show();
	} else if (mediumRegex.test(curPWVal)) {
            $(pwStrengthField).attr('class', 'passwordOK');
            $(pwStrengthField).html('Medium');
            $(pwToggleLink).show();
	} else {
            $(pwStrengthField).attr('class', 'passwordWeak');
            $(pwStrengthField).html('Weak');
            $(pwToggleLink).show();
	}
    }
    
    return true;
}

function evaluateCurrentPWConfirmVal(pwField, pwConfirmField, pwMatchField, pwToggleLink) {
    var curPWVal = $(pwField).val();
    var curPWConfirmVal = $(pwConfirmField).val();
	
    if((curPWConfirmVal.length === 0) && 
       (curPWVal.length === 0)) {
	$(pwMatchField).html('');
	$(pwToggleLink).hide();
    } else if(curPWConfirmVal !== curPWVal) {
	$(pwMatchField).attr('class', 'passwordWeak');
	$(pwMatchField).html('Passwords do not match');
	$(pwToggleLink).show();
    } else {
	$(pwMatchField).attr('class', 'passwordStrong');
	$(pwMatchField).html('Passwords match!');
	$(pwToggleLink).show();
    }	
}

function DeferFullAccountCreation() {
    var response = confirm('Skipping this stage will default your username to your email address. Proceed?');
	
    if(response){
	// Make AJAX call to update username to email address
	$.ajax({
            type: "POST",
            url: "UpdateUsername.php",
            data: "",
            success: function(response){
		if(response === 'true') {
                    return true;
                }
                else {
                    alert('SYSTEM ERROR:\n' + response);
                }
            }
        });
    }
    else  return false;
}

function OnSignupButtonClick(email, password, passwordConf, captcha, actionURL, successURL, pwField, 
                             pwConfirmField, captchaCodeField, captchaImage, pwMatchField, pwStrengthField, 
                             pwToggleField, pwConfirmToggleField, errField)
{
    var validEmailRegEx = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;

    if((email.trim().length === 0) || (password.trim().length === 0)) {
        alert("Unable to create account: The Email Address and Password fields must be filled");
    }
    else if (validEmailRegEx.test(email) === false) {
        alert("Unable to create account: Please enter a valid email address");
    }
    else if (password !== passwordConf) {
        alert("Unable to create account: Your Password does not match the Password Confirmation");
    }
    else if (captcha.trim().length === 0) {
        alert("Unable to create account: Please enter the code displayed in the CAPTCHA image");
    }
    else {
        $.ajax({
            type: "POST",
            url: actionURL,
            data: "signupPW=" + password + "&signupEmail=" + email + "&captcha_code=" + captcha,
            success: function(response){
                if(response === 'true') {
                    window.location.href = successURL;
                }
                else {
                    // Clear input fields, except for email
                    $(pwField).val('');
                    $(pwConfirmField).val('');
                    $(captchaCodeField).val('');

                    // Clear password strength and password-confirm-match indicators
                    $(pwMatchField).html('');
                    $(pwStrengthField).html('');
                    $(pwToggleField).hide();
                    $(pwConfirmToggleField).hide();

                    // Display error message from server
                    $(errField).html(response);

                    // Refresh captcha image
                    $(captchaImage).attr('src', 'securimage/securimage_show.php?' + Math.random());
                }
            }
        });
    }

    return false;
}

function evaluateUserNameAvailability(userNameField, availIndicatorField, actionURL)
{
    if((!$(userNameField).is('[readonly]')) && ($(userNameField).val().trim().length > 0))
    {
        $.ajax({
            type: "POST",
            url: actionURL,
            data: "action=CheckUsernameAvailability&userName=" + $(userNameField).val(),
            success: function(response){
		if(response === 'avail') {
                    $(availIndicatorField).attr('class', 'usernameStatusAvail');
                    $(availIndicatorField).html('Username Available!');
                }
                else {
                    $(availIndicatorField).attr('class', 'usernameStatusTaken');
                    $(availIndicatorField).html('Username Already Taken');
                }
            }
        });
    }
    else {
        $(availIndicatorField).html('&nbsp;');
    }

    return false;
}

function displayHiddenAdsByBrowsingDevice()
{   
    // If viewing device has screen width > 650px, treat as desktop device
    if (!window.matchMedia("(max-width: 650px)").matches)
    {
        $('.mobileAdStyle').attr('class', 'hiddenMobileAds');
        $('.hiddenDesktopAds').attr('class', 'desktopAdStyle');
    }
}

function PrepareAutocompleteComboBox(textboxId)
{
    $.widget( "custom.combobox", {
	_create: function() {
            this.wrapper = $( "<span>" )
                .addClass( "custom-combobox" )
                .insertAfter( this.element );

            this.element.hide();
            this._createAutocomplete();
            this._createShowAllButton();
        },
        _createAutocomplete: function() {
            var selected = this.element.children( ":selected" ), value = selected.val() ? selected.text() : "";

            this.input = $( "<input id='" + textboxId + "'>" )
                .appendTo( this.wrapper )
                .val( value )
                .attr( "title", "" )
                .addClass( "custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left" )
                .autocomplete({
                    delay: 0,
                    minLength: 0,
                    source: $.proxy( this, "_source" )
                })
                .tooltip({
                    tooltipClass: "ui-state-highlight"
                });

            this._on( this.input, {
                autocompleteselect: function( event, ui ) {
                    ui.item.option.selected = true;
                    this._trigger( "select", event, {
                        item: ui.item.option
                    });
                },
                autocompletechange: "_updateUnderlyingElementVal"
            });
        },
        _createShowAllButton: function() {
            var input = this.input, wasOpen = false;

            $( "<a>" )
                .attr( "tabIndex", -1 )
                .attr( "title", "Show All Items" )
                .tooltip()
                .appendTo( this.wrapper )
                .button({
                    icons:  {
                                primary: "ui-icon-triangle-1-s"
                            },
                    text: false
                })
                .removeClass( "ui-corner-all" )
                .addClass( "custom-combobox-toggle ui-corner-right" )
                .mousedown(function() {
                    wasOpen = input.autocomplete( "widget" ).is( ":visible" );
                })
                .click(function() {
                    input.focus();

                    // Close if already visible
                    if ( wasOpen ) {
                        return;
                    }

                    // Pass empty string as value to search for, displaying all results
                    input.autocomplete( "search", "" );
                });
        },
        _source: function( request, response ) {
            var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
            response( this.element.children( "option" ).map(function() {
                var text = $( this ).text();
                var className = $(this).attr('class');

                if ( this.value && ( !request.term || matcher.test(text) ) ) {
                    return {
                        label: text,
                        value: text,
                        option: this,
                        cName: className
                    };
                }
            }) );
        },
        _updateUnderlyingElementVal: function( event, ui ) {
            // Selected an item, nothing to do
            if ( ui.item ) {
                return;
            }

            // Search for a match (case-insensitive)
            var value = this.input.val(), valueLowerCase = value.toLowerCase(), valid = false;

            this.element.children( "option" ).each(function() {
                if ( $( this ).text().toLowerCase() === valueLowerCase ) {
                    this.selected = valid = true;
                    return false;
                }
            });

            // Found a match, nothing to do
            if ( valid ) {
                return;
            }

            // Otherwise, clear selection from underlying element
            this.element.val("");
            this.input.autocomplete( "instance" ).term = "";
        },
        _destroy: function() {
            this.wrapper.remove();
            this.element.show();
        }
    });
	
    $.extend($.ui.autocomplete.prototype, {
        _renderItem: function(ul, item) {
            return $("<li class='" + item.cName + "'></li>")
                .data("item.autocomplete", item)
                .append($("<a></a>")["text"](item.label))
                .appendTo(ul);
            }
        }
    );
}