<?php
    include_once 'classes/DataAccess.class.php';
    include_once 'classes/SecurityHandler.class.php';
    include_once 'classes/DBSessionHandler.class.php';
    include_once 'classes/Logger.class.php';
    include_once 'classes/User.class.php';
    
    $dataAccess = new DataAccess();
    $logger = new Logger($dataAccess);
    $securityHandler = new SecurityHandler();
    
    $sessionDataAccess = new DataAccess();
    $sessionHandler = new DBSessionHandler($sessionDataAccess);
    session_set_save_handler($sessionHandler, true);
    session_start();
    
    $objUser = User::constructDefaultUser();
    
    // If user not logged in or unauthorized to view this page, redirect to login page
    if($securityHandler->UserCanAccessThisPage($dataAccess, $logger, "EditProfile", "Login.php")) {
        $objUser = $_SESSION['WebUser'];
        $welcomeHeaderText = "Project OGS | " . $objUser->UserName;
	$editProfileMsg = "<p>Update your profile...</p>";
        $_SESSION['lastActivity'] = time();
		
	$userNameReadOnly = "readonly ";
		
	if(isset($_SESSION['JustCreatedAccount'])) {
            if($_SESSION['JustCreatedAccount'] == true) {
		$welcomeHeaderText = "Welcome " . $objUser->EmailAddress;
		$userNameReadOnly = "";
		$editProfileMsg = "<p>Let's finish filling out your profile. It only takes a moment!&nbsp;&nbsp;<a href='MemberHome.php' " . 
                                  "id='upgradeLater' class='deferOptionLinkStyle' onclick='return DeferFullAccountCreation();'>I'll do this later</a></p>";
		$_SESSION['JustCreatedAccount'] = false;
            }
	}
	
        // If username has not been set yet, allow it to be set now
        if(strlen(trim($objUser->UserName)) == 0) {
            $userNameReadOnly = "";
        }
        
	/*** Pre-load form fields, if user has already updated their account before ***/
		
	// UserName
	$userNameInputHTML = "<input id='userName' name='userName' type='text' maxlength='50' placeholder=' Username' " . 
                             $userNameReadOnly . "/>";

	if(strlen($objUser->UserName) > 0) {
            $userNameInputHTML = "<input id='userName' name='userName' type='text' maxlength='50' placeholder=' Username' " . 
				 $userNameReadOnly . " value='" . $objUser->UserName . "' />";
        }
		
	// Name
        $firstNameInputHTML = "First Name<input type='text' id='firstName' name='firstName' placeholder=' First name'><br/><br/>";
        $lastNameInputHTML = "Last Name<input type='text' id='lastName' name='lastName' placeholder=' Last name'>";
                                                            
        if(strlen($objUser->FirstName) > 0) {
            $firstNameInputHTML = "First Name&nbsp;<input type='text' id='firstName' name='firstName' placeholder=' First name' value='" .
            $objUser->FirstName . "'><br/><br/>";
        }
        if(strlen($objUser->LastName) > 0) {
            $lastNameInputHTML = "Last Name&nbsp;<input type='text' id='lastName' name='lastName' placeholder=' Last name' value='" .
            $objUser->LastName . "'>";
        }
		
	// Gender
	$genderInputHTML = "<input type='radio' id='fm' name='gender' value='F'>Female <br/>" . 
                           "<input type='radio' id='m' name='gender' value='M'>Male";
        
	if(strlen($objUser->Gender) > 0) {
            $selValIdx = strpos($genderInputHTML, "value='" . $objUser->Gender);
            if($selValIdx !== false) {
		$genderInputHTML = substr($genderInputHTML, 0, $selValIdx) . "checked='checked' " . 
				   substr($genderInputHTML, $selValIdx, strlen($genderInputHTML) - $selValIdx);
            }
	}
	else {
            $genderInputHTML = "<input type='radio' id='fm' name='gender' checked='checked' value='F'>Female <br/>" . 
                               "<input type='radio' id='m' name='gender' value='M'>Male";
	}
	
        // Email
        $emailAddressInputHTML = "<input id='emailAddress' name='emailAddress' type='text' maxlength='100' placeholder=' Email Address'>";
        if(strlen($objUser->EmailAddress) > 0) {
            $emailAddressInputHTML = "<input id='emailAddress' name='emailAddress' type='text' maxlength='100' placeholder=' Email Address' value='" . 
                                     $objUser->EmailAddress . "'>";
        }
        
	// Birthday
	$dobHTML = "<input type='text' id='DOBDatePicker' name='DOBDatePicker'>";
	if(strlen($objUser->Birthdate) > 0) {
            $dobHTML = "<input type='text' id='DOBDatePicker' name='DOBDatePicker' value='" . $objUser->Birthdate . "'>";
	}
		
	// Time zone
	$selectedTimeZoneID = 15; // Default to EST
	if($objUser->TimezoneID > 0) {
            $selectedTimeZoneID = $objUser->TimezoneID;
	}
		
	// Biography
	$bioHTML = "<textarea name='message' id='message' placeholder='This is your bio! What are your favorite games? When do you do most of your online gaming? etc..' " . 
		   "rows='6' required></textarea>";
	if(strlen($objUser->Autobiography) > 0) {
            $bioHTML = "<textarea name='message' id='message' placeholder='This is your bio! What are your favorite games? When do you do most of your online gaming? etc..' " . 
                       "rows='6' required>" . $objUser->Autobiography . "</textarea>";
	}
		
	session_write_close();
    }
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>Project OGS | Welcome</title>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <meta name="description" content="" />
        <meta name="keywords" content="" />
        <!--[if lte IE 8]><script src="css/ie/html5shiv.js"></script><![endif]-->
        <script src="js/jquery.min.js"></script>
        <script src="js/jquery.dropotron.min.js"></script>
        <script src="js/skel.min.js"></script>
        <script src="js/skel-layers.min.js"></script>
        <script src="js/init.js"></script>
        <script src="js/main.js"></script>
        <script src="js/ajax.js"></script>
        <script src="js/jquery-1.10.2.js"></script>
        <script src="js/jquery-ui-1.10.4.custom.js"></script>
	
        <!-- For Skel framework -->
	<noscript>
            <link rel="stylesheet" href="css/skel.css" />
            <link rel="stylesheet" href="css/style.css" />
            <link rel="stylesheet" href="css/style-desktop.css" />
	</noscript>
	<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css" />
        <script>
            // JQuery functionality
            $(document).ready(function($) {
                $('#DOBDatePicker').datepicker({
                    inline: true,
                    yearRange: '-125:-2',
                    changeYear: true,
                    constrainInput: true,
                    showButtonPanel: true,
                    showOtherMonths: true,
                    dayNamesMin: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                    defaultDate: '-2y',
                    dateFormat: 'yy-mm-dd'
		});
		
                $('#togglePassword').hide();
                $('#togglePasswordConfirm').hide();
                
                $('#pwd').keyup(function() {
                    evaluateCurrentPWVal('#pwd', '#pwdConfirm', '#passwordStrength', '#passwordMatch', '#togglePassword');
                });
                    
                $('#pwdConfirm').keyup(function() {
                    evaluateCurrentPWConfirmVal('#pwd', '#pwdConfirm', '#passwordMatch', '#togglePasswordConfirm');
                });
				
		$('#userName').blur(function() {
                    evaluateUserNameAvailability('#userName', '#userNameTakenIndicator', 'CheckUsernameAvailability.php');
		});
                
                $('#signupBtn').click(function() {
                    var validEmailRegEx = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
                    
                    var email = $('#emailAddress').val();
                    var password = $('#pwd').val();
                    var passwordConf = $('#pwdConfirm').val();
                    var userName = $('#userName').val();
                    var dob = $('#DOBDatePicker').val();
                    var bio = $('#message').val();
                    var checkedPlatforms = $("input[name='platforms[]']:checked").length;

                    if (validEmailRegEx.test(email) === false) {
                        alert("Unable to update account: Please enter a valid email address");
                    }
                    else if (password !== passwordConf) {
                        alert("Unable to update account: Your Password does not match the Password Confirmation");
                    }
                    else if (userName.trim().length === 0) {
                        alert("Unable to update account: The Username field must be filled");
                    }
                    else if((email.trim().length === 0) || (dob.trim().length === 0) || (bio.trim().length === 0)) {
			alert("Unable to update account: The Email, Birthdate and Autobiography fields must be filled");
                    } 
                    else if ((checkedPlatforms > 0) || ((checkedPlatforms === 0) && (confirm("No game platforms selected...proceed with update?")))) {
                        $('#editProfileStatus').attr('class', 'preEditProfile');
                        $('#editProfileStatus').html("Updating Account...");
                        $('#editProfileStatus').fadeIn(200);
            
                        // Make AJAX call to update user account
			$.ajax({
                            type: "POST",
                            url: "UpdateAccount.php",
                            data: $('#signupForm').serialize(),
                            success: function(response){
				if(response === 'true') {
                                    window.location.href = "MemberHome.php";
				}
				else {
                                    $('#editProfileStatus').attr('class', 'editProfileErr');
                                    $('#editProfileStatus').html(response);
                                    
                                    setTimeout(function() {
                                        $('#editProfileStatus').hide();
                                        }, 3000
                                    );
				}
                            }
			});
                    }
                            
                    return false;
                });
            });
        </script>
    </head>
    <body class="">
        <!-- Navigation Wrapper -->
        <div id="header-wrapper">
            <div class="container">
                <div class="row">
                    <div class="12u">				
			<!-- Header -->
			<header id="header">		
                            <!-- Logo -->
                            <h1><a href="#" id="logo">Project OGS</a></h1>				
                            <!-- Nav -->
                            <nav id="nav">
                                <ul>
                                    <li><a href="MemberHome.php">Home</a></li>
                                    <li><a href="ExecuteLogout.php">Log Out</a></li>
				</ul>
                            </nav>
			</header>
                    </div>
		</div>
            </div>
	</div>
	<!-- Navigation Wrapper -->
	<!-- Main Wrapper -->
	<div id="main-wrapper">
            <div class="container">
		<div class="row">
                    <div class="12u">
			<div id="main">
                            <div class="row">			
				<div class="12u">							
                                    <!-- Content -->
                                    <div id="content">
                                        <article class="box main">
                                            <header>
                                                <h2>
                                                    <?php 
                                                        echo $welcomeHeaderText;
                                                    ?>&nbsp;
                                                    <div class="submitFormDiv">
                                                        <button type="submit" class="button icon fa-wrench" id="signupBtn">Update Profile</button>
                                                    </div>
                                                </h2>
                                                <h3>
                                                    <div id="editProfileStatus" class="hidden">&nbsp;</div>
                                                </h3>
                                                <?php
                                                    echo $editProfileMsg;
                                                ?>
                                            </header><br/>
                                            <form id="signupForm" name="signupForm" method="POST" action="">
                                                <div class="row double">
                                                    <section class="3u">
                                                        <p><i class="fa fa-terminal"></i>&nbsp;&nbsp;Pick a username</p>
                                                        <?php                                                           
                                                            echo $userNameInputHTML;
                                                        ?>
							<br/>
                                                            <div id="userNameTakenIndicator">&nbsp;</div>
							<br/>
                                                        <p><i class="fa fa-at"></i>&nbsp;&nbsp;Edit Your Email Address:</p>
                                                        <?php
                                                            echo $emailAddressInputHTML;
                                                        ?>
                                                        <br/><br/><br/>
                                                        <p><i class="fa fa-key"></i>&nbsp;&nbsp;Change Your Password:</p>
                                                        <input id="pwd" name="pwd" type="password" maxlength="50" placeholder=" Password" /><br/>
                                                        <span id="togglePasswordSpan">
                                                            <a href="#" id="togglePassword" 
                                                               onclick="return togglePasswordField('#togglePassword', '#pwd', '#pwd', '#pwdConfirm', 
                                                                                            ' Password', false);">Show Password</a>
                                                        </span>&nbsp;&nbsp;
                                                        <span id="passwordStrength" class="passwordNone"></span><br/><br/>
                                                        
                                                        <input id="pwdConfirm" type="password" maxlength="50" placeholder=" Confirm Password" /><br/>
                                                        <span id="togglePasswordConfirmSpan">
                                                            <a href="#" id="togglePasswordConfirm" 
                                                               onclick="return togglePasswordField('#togglePasswordConfirm', '#pwdConfirm', '#pwd', '#pwdConfirm', 
                                                                                            ' Confirm Password', true);">Show Password</a>
                                                        </span>&nbsp;<br/>
                                                        <span id="passwordMatch" class="passwordWeak"></span>
                                                        <button type="submit" id="hiddenBtn" style="display:none">Update Profile</button>
                                                    </section>
                                                    <section class="3u">
                                                        <p><i class="fa fa-user"></i>&nbsp;&nbsp;What is your name?</p>
                                                        <?php                                                           
                                                            echo $firstNameInputHTML . $lastNameInputHTML;
                                                        ?>
							<br/><br/><br/>
                                                        <p><i class="fa fa-male"></i>&nbsp;<i class="fa fa-female"></i>&nbsp;What is your gender?</p>
                                                        <?php
                                                            echo $genderInputHTML;
							?>
							<br/><br/>
                                                        <p><i class="fa fa-birthday-cake"></i>&nbsp;&nbsp;When were you born?</p>
                                                        <?php
                                                            echo $dobHTML;
							?>
							<br/><br/>
                                                        <p><i class="fa fa-clock-o"></i>&nbsp;&nbsp;What is your time zone?</p>
                                                        <?php
                                                            $errors = $dataAccess->CheckErrors();
                                                            $ddlTimeZonesHTML = "";
                                                            $ddlTimeZonesErrorHTML = "<select id='ddlTimeZones' name='ddlTimeZones'><option value='-1'>Cannot load time zones, please try later</option></select><br/><br/>";

                                                            if(strlen($errors) == 0) {
                                                                $timeZoneQuery = "SELECT `ID`, `Abbreviation` FROM `Configuration.TimeZones` ORDER BY `SortOrder`;";
                                                                if($dataAccess->BuildQuery($timeZoneQuery)){
                                                                    $results = $dataAccess->GetResultSet();
                                                                    if($results != null){
                                                                        $ddlTimeZonesHTML = $ddlTimeZonesHTML . "<select id='ddlTimeZones' name='ddlTimeZones'>";
                                                                        foreach($results as $row){
                                                                            if($row['ID'] == $selectedTimeZoneID) {
                                                                                $ddlTimeZonesHTML = $ddlTimeZonesHTML . "<option value='" . $row['ID'] . "' selected='true'>" . $row['Abbreviation'] . "</option>";
                                                                            }
                                                                            else {
                                                                                $ddlTimeZonesHTML = $ddlTimeZonesHTML . "<option value='" . $row['ID'] . "'>" . $row['Abbreviation'] . "</option>";
                                                                            }
                                                                        }
                                                                        $ddlTimeZonesHTML = $ddlTimeZonesHTML . "</select>";
                                                                    }
                                                                }
                                                            }

                                                            $errors = $dataAccess->CheckErrors();
                                                            if(strlen($errors) == 0) {
                                                                echo $ddlTimeZonesHTML;
                                                            }
                                                            else { 
                                                                echo $ddlTimeZonesErrorHTML;
                                                            }
                                                        ?>
                                                    </section>
                                                    <section class="6u">
                                                        <p><i class="fa fa-gamepad"></i>&nbsp;&nbsp;Which console(s) do you game on? (check all that apply)</p>
                                                        <?php
                                                            $errors = $dataAccess->CheckErrors();
                                                            $ddlPlatformsHTML = "";
                                                            $ddlPlatformsErrorHTML = "<p>Cannot load console list, please try later</p>";

                                                            if(strlen($errors) == 0) {
                                                                $platformQuery = "SELECT `ID`, `Name` FROM `Configuration.Platforms` ORDER BY `Name`;";
                                                                if($dataAccess->BuildQuery($platformQuery)){
                                                                    $results = $dataAccess->GetResultSet();
                                                                    if($results != null){
                                                                        foreach($results as $row){
                                                                            $selected = "";
                                                                            if(in_array($row['ID'], $objUser->GamePlatforms)) {
										$selected = "checked='checked'";
                                                                            }
																	
                                                                            $ddlPlatformsHTML = $ddlPlatformsHTML . "<input type='checkbox' name='platforms[]' " . 
                                                                                                $selected . " value='" . $row['ID'] . "'>" . $row['Name'] . "</input><br/>";
                                                                        }
                                                                    }
                                                                }
                                                            }

                                                            $errors = $dataAccess->CheckErrors();
                                                            if(strlen($errors) == 0) {
                                                                echo $ddlPlatformsHTML;
                                                            }
                                                            else { 
                                                                echo $ddlPlatformsErrorHTML;
                                                            }
                                                        ?><br/><br/>
                                                        <p><i class="fa fa-comment"></i>&nbsp;&nbsp;Tell us about yourself.</p>
                                                        <?php
                                                            echo $bioHTML;
							?>
							<br/><br/>
                                                    </section>
                                                </div>
                                            </form>
					</article>
					<!-- Lower Article Wrapper -->							
					<!-- Lower Article Wrapper -->
                                    </div>						
				</div>
                            </div>
			</div>
                    </div>
		</div>
            </div>
	</div>
	<!-- Footer Wrapper -->		
	<div class="container">
            <div class="row">
                <div class="12u">
                    <!-- Footer -->
                    <!-- <footer id="footer">
                        <div class="row">
                            <!-- <div class="6u">									
                                <section>
                                    <h2>Membership</h2>
                                    <p>Want additional features? Tired of seeing ads? For just a dollar a month you can become a premium member.</p>
                                    <a href="#" class="button icon fa-sign-in">Sign Up!</a>
                                </section>							
                            </div> 
                            <div class="6u">								
                                <section>
                                    <h2>Quick Links</h2>
                                    <ul class="style3">
                                        <li>
                                            <a href="" target="" style="text-decoration:none;">Recent News</a>
                                        </li>
                                        <li>
                                            <a href="" target="" style="text-decoration:none;">Developer Blog</a>
                                        </li>
                                        <li>
                                            <a href="" target="" style="text-decoration:none;">About Us</a>
                                        </li>
                                    </ul>
                                </section>								
                            </div>
                            <div class="6u">								
                                <section>
                                    <h2>Contact Us</h2>
                                    <ul class="contact">
                                        <li class="icon fa-envelope">
                                            <a href="" target="" style="text-decoration:none;">Email</a>
                                        </li>
                                        <li class="icon fa-youtube">
                                            <a href="" target="" style="text-decoration:none;">YouTube</a>
                                        </li>
                                        <li class="icon fa-twitch">
                                            <a href="" target="" style="text-decoration:none;">Twitch</a>
                                        </li>
                                    </ul>
                                </section>								
                            </div>
                        </div>
                    </footer> -->
                    <!-- Copyright -->
                    <div id="copyright">
			&copy; <script>document.write(new Date().getFullYear());</script> Project OGS<br/> All Rights Reserved.<br/>
			<a href="" target="" style="text-decoration:none;">Developed by<br/>Stephen Giles and Paul Morrell</a>&nbsp<i class="fa fa-cogs"></i>
                    </div>			
		</div>
            </div>
	</div>		
	<!-- Footer Wrapper -->
    </body>
</html>