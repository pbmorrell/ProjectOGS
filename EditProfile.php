<?php
    $mobileLoginPage = false;
    $sessionRequired = true;
    $sessionAllowed = true;
    
    // header.php retrieves user information from session, storing in $objUser variable
    include "Header.php";
    
    $welcomeHeaderText = "Project OGS | " . $welcomeUserName;
    $editProfileMsg = "<p>Update your profile...</p>";

    $userNameReadOnly = "readonly ";

    if($justCreatedSession) {
        $welcomeHeaderText = "Welcome " . $welcomeUserName;
        $userNameReadOnly = "";
        $editProfileMsg = "<p>Let's finish filling out your profile. It only takes a moment!&nbsp;&nbsp;<a href='MemberHome.php' " . 
                          "id='upgradeLater' class='deferOptionLinkStyle' onclick='return DeferFullAccountCreation();'>I'll do this later</a></p>";
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
    
    $dataAccess = new DataAccess();
    $gamingHandler = new GamingHandler();
    $timeZoneHTML = $gamingHandler->GetTimezoneList($dataAccess, $selectedTimeZoneID);
    
    // Biography
    $bioHTML = "<textarea name='message' id='message' placeholder='This is your bio! What are your favorite games? When do you do most of your online gaming? etc..' " . 
               "rows='6' required></textarea>";
    if(strlen($objUser->Autobiography) > 0) {
        $bioHTML = "<textarea name='message' id='message' placeholder='This is your bio! What are your favorite games? When do you do most of your online gaming? etc..' " . 
                   "rows='6' required>" . $objUser->Autobiography . "</textarea>";
    }
?>
<!DOCTYPE HTML>
<!--
	Project OGS
	by => Stephen Giles and Paul Morrell
-->
<html>
    <head>
        <?php echo $pageHeaderHTML; ?>
        <script>
            // JQuery functionality
            $(document).ready(function($) {
                displayHiddenAdsByBrowsingDevice();
                EditProfileOnReady();
            });
        </script>
    </head>
    <body class="">
        <?php echo $headerHTML; ?>
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
                                        <article class="box style3">
                                            <header>
                                                <h2>
                                                    <?php 
                                                        echo $welcomeHeaderText;
                                                    ?>&nbsp;
                                                    <div class="submitFormDiv">
                                                        <button type="submit" class="controlBtn button icon fa-wrench" id="signupBtn">Update Profile</button>
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
                                                    <section class="4u">
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
                                                        <?php echo $timeZoneHTML; ?>
                                                    </section>
                                                    <section class="4u">
                                                        <p><i class="fa fa-gamepad"></i>&nbsp;&nbsp;Which console(s) do you game on?<br /><label>(check all that apply)</label></p>
                                                        <?php echo $gamingHandler->GetPlatformCheckboxList($dataAccess, $objUser->GamePlatforms); ?><br/><br/>
                                                        <p><i class="fa fa-comment"></i>&nbsp;&nbsp;Tell us about yourself.</p>
                                                        <?php
                                                            echo $bioHTML;
							?>
							<br/><br/>
                                                        <button type="submit" class="controlBtn button icon fa-wrench" id="signupBtnMobile">Update Profile</button>
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
        <?php include 'Footer.php'; ?>
	<!-- Footer Wrapper -->
    </body>
</html>