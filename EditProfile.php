<?php
    $mobileLoginPage = false;
    $sessionRequired = true;
    $sessionAllowed = true;
    
    // header.php retrieves user information from session, storing in $objUser variable
    include "Header.php";
    
    $welcomeHeaderText = "Player Unite | " . $welcomeUserName;
    $editProfileMsg = "<h2>Edit Profile</h2>";

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
        <script src="js/jTable/jquery.jtable.min.js"></script>
		
	<?php if(Constants::$isDebugMode): ?>
            <script src="js/GamerTagViewer.js"></script>
            <script src="js/pStrength.jquery.js"></script>
	<?php else: ?>
            <script src="js/GamerTagViewer.min.js"></script>
            <script src="js/pStrength.jquery.min.js"></script>
	<?php endif; ?>
            
        <link rel="stylesheet" href="css/jTable/lightcolor/blue/jtable.min.css" />
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
                                                    <div class="submitFormDiv desktopButtonDisplay">
                                                        <button type="button" class="controlBtn button icon fa-sitemap" id="gamerTagUpdateBtn">Manage Gamer Tags</button>
                                                        <button type="submit" class="controlBtn button icon fa-calendar" id="eventReminderSettingsBtn">Event Reminders</button>
                                                        <button type="submit" class="controlBtn button icon fa-wrench" id="signupBtn">Update Profile</button>
                                                    </div>
                                                </h2>
                                                <h3>
                                                    <br />
                                                    <div id="editProfileStatus" class="hidden">&nbsp;</div>
                                                </h3>
                                                <?php
                                                    echo $editProfileMsg;
                                                ?>
                                            </header><br/>
                                            <form id="signupForm" name="signupForm" method="POST" action="">
                                                <div class="row double">
                                                    <section class="3u">
                                                        <p><i class="fa fa-tags"></i>&nbsp;&nbsp;Enter a username</p>
                                                        <?php                                                           
                                                            echo $userNameInputHTML;
                                                        ?>
							<br/>
                                                            <div id="userNameTakenIndicator">&nbsp;</div>
							<br/>
                                                        <p><i class="fa fa-user"></i>&nbsp;&nbsp;What is your given name?</p>
                                                        <?php                                                           
                                                            echo $firstNameInputHTML . $lastNameInputHTML;
                                                        ?>
							<br/><br/><br/>
                                                        <p><i class="fa fa-at"></i>&nbsp;&nbsp;Update Your Email Address:</p>
                                                        <?php
                                                            echo $emailAddressInputHTML;
                                                        ?>
                                                        <br/><br/><br/>
                                                        <p><i class="fa fa-male"></i>&nbsp;<i class="fa fa-female"></i>&nbsp;What is your gender?</p>
                                                        <?php
                                                            echo $genderInputHTML;
							?>
                                                        <button type="submit" id="hiddenBtn" style="display:none">Update Profile</button>
                                                    </section>
                                                    <section class="4u">
                                                        <p><i class="fa fa-birthday-cake"></i>&nbsp;&nbsp;When were you born?</p>
                                                        <?php
                                                            echo $dobHTML;
							?>
							<br/><br/><br/>
                                                        <p><i class="fa fa-clock-o"></i>&nbsp;&nbsp;What is your time zone?</p>
                                                        <?php echo $timeZoneHTML; ?>
                                                        <br/><br/><br/>
                                                        <p><i class="fa fa-key"></i>&nbsp;&nbsp;Change Your Password:</p>
                                                        <input id="pwd" name="pwd" type="password" maxlength="50" placeholder=" Password" /><br/>
                                                        <span id="togglePasswordSpan">
                                                            <a href="#" id="togglePassword" 
                                                               onclick="return togglePasswordField('#togglePassword', '#pwd', '#pwd', '#pwdConfirm', 
                                                                                                   ' Password', false, 'passwordStrength');">Show Text</a>
                                                        </span>&nbsp;&nbsp;
                                                        <span id="passwordStrength"></span><br/><br/>
                                                        
                                                        <input id="pwdConfirm" type="password" maxlength="50" placeholder=" Confirm Password" /><br/>
                                                        <span id="togglePasswordConfirmSpan">
                                                            <a href="#" id="togglePasswordConfirm" 
                                                               onclick="return togglePasswordField('#togglePasswordConfirm', '#pwdConfirm', '#pwd', '#pwdConfirm', 
                                                                                                   ' Confirm Password', true);">Show Text</a>
                                                        </span>&nbsp;<br/>
                                                        <img id="passwordMatch" src="images/green_checkmark.gif" />
                                                    </section>
                                                    <section class="4u">
                                                        <p><i class="fa fa-gamepad"></i>&nbsp;&nbsp;Which console(s) do you game on?<br /><label>(check all that apply)</label></p>
                                                        <div class="fixedHeightScrollableContainer">
                                                            <?php echo $gamingHandler->GetPlatformCheckboxList($dataAccess, $objUser->GamePlatforms); ?>
                                                        </div>
                                                        <br/><br/>
                                                        <p><i class="fa fa-comment"></i>&nbsp;&nbsp;Tell us about yourself.</p>
                                                        <?php
                                                            echo $bioHTML;
							?>
                                                        <div class="mobileButtonDisplay">
                                                            <br/><br/>
                                                            <button type="button" class="controlBtn button icon fa-sitemap" id="gamerTagUpdateBtnMobile">Manage Gamer Tags</button>
                                                            <br/><br/>
                                                            <button type="submit" class="controlBtn button icon fa-calendar" id="eventReminderSettingsBtnMobile">Event Reminders</button>
                                                            <br/><br/>
                                                            <button type="submit" class="controlBtn button icon fa-wrench" id="signupBtnMobile">Update Profile</button>
                                                        </div>
                                                    </section>
                                                </div>
                                            </form>
                                            <br />
                                            <div id="editProfileStatusMobile" class="hidden">&nbsp;</div>
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