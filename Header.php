<?php
    include_once 'classes/Constants.class.php';
    include_once 'classes/DataAccess.class.php';
    include_once 'classes/SecurityHandler.class.php';
    include_once 'classes/GamingHandler.class.php';
    include_once 'classes/DBSessionHandler.class.php';
    include_once 'classes/Logger.class.php';
    include_once 'classes/User.class.php';
    include_once 'classes/Constants.class.php';
    include_once 'classes/PayPalTxnMsg.class.php';
    
    $welcomeUserName = "Welcome";
    $curPageName = basename($_SERVER['PHP_SELF'], ".php");
	
    // Construct page JS init file name for script include
    $pageInitJSScriptName = $curPageName . "Init.js";
    
    $onloadPopupJSCode = "";
    if(isset($_GET['redirectMsg'])){
        $onloadPopupJSCode = "window.history.pushState('Index', '', 'Index.php'); alert('" . filter_var($_GET['redirectMsg'], FILTER_SANITIZE_STRING) . "');";
        unset($_GET['redirectMsg']);
    }
    
    // Now build the page header HTML (nav) string, which will be echoed on all including pages and will be the login code
    // if no logged-in user
    
    // If this is the signup page, do not include "Signup" button in header
    $signupBtn = '<button type="button" class="button icon fa-cogs" id="signupBtnLoginForm">Sign Up</button>';
    if(strtolower($curPageName) === 'index') {
        $signupBtn = "";
    }
    
    $pageHeaderLoginForm =
        '<div id="login">
            <form id="loginForm" name="loginForm" method="POST" action="">
                <input id="loginUsername" name="loginUsername" type="text" maxlength="100" placeholder=" Username">
                <input id="loginPassword" name="loginPassword" type="password" maxlength="50" placeholder=" Password">
                <button type="submit" class="button icon fa-sign-in" id="loginBtn">Log In</button>&nbsp;' . $signupBtn .
            '</form>
        </div>';
    
    $loginErrorDiv = '<div id="loginErr" class="preLogin">&nbsp;</div>';
    
    if($mobileLoginPage) {
        $pageHeaderLoginForm = "";
        $loginErrorDiv = "";
    }
    
    $headerHTML =
        '<!-- Navigation Wrapper -->'.
	'<div id="header-wrapper">'.
            '<div class="container">'.
                '<div class="row">'.
                    '<div class="12u">'.
                        '<!-- Header -->'.
                        '<header id="header">'.
                            '<!-- Logo -->'.
                            '<h1>'.
                                '<a href="#" id="logo">Player Unite</a>'.
                                $pageHeaderLoginForm .
                            '</h1>'.
                            '<!-- Nav -->'.
                            '<nav id="nav" style="display:none;">'.
                                '<ul>'.
                                    '<li><a href="MobileLogin.php">Log In</a></li>'.
                                    '<li><a href="Index.php?action=Signup">Sign Up</a></li>'.
                                '</ul>'.
                            '</nav>'.
                        '</header>' . $loginErrorDiv.
                    '</div>'.
                '</div>'.
            '</div>'.
        '</div>';
		
    // Set PayPal donation button image (appearing in Footer.php) based on whether we are hitting the sandbox or production PayPal environment
    $payPalButtonFormUrl 	= Constants::$isPayPalTest ? Constants::$payPalTestButtonFormUrl 	: Constants::$payPalProdButtonFormUrl;
    $payPalDonationButtonImgUrl = Constants::$isPayPalTest ? Constants::$payPalTestDonationButtonImgUrl : Constants::$payPalProdDonationButtonImgUrl;
    $payPalDonationButtonId 	= Constants::$isPayPalTest ? Constants::$payPalTestDonationButtonId 	: Constants::$payPalProdDonationButtonId;
    $payPalPixelImgUrl 		= Constants::$isPayPalTest ? Constants::$payPalTestPixelImgUrl 		: Constants::$payPalProdPixelImgUrl;
	
    $customSessionVars = [];
    if($sessionAllowed) {
        $dataAccess = new DataAccess();
        $logger = new Logger($dataAccess);
        $securityHandler = new SecurityHandler();

        $sessionDataAccess = new DataAccess();
        $sessionHandler = new DBSessionHandler($sessionDataAccess);
        session_set_save_handler($sessionHandler, true);
        session_start();
    }
	
    if($sessionRequired || (($sessionAllowed) && (isset($_SESSION['WebUser'])))) {
        $objUser = User::constructDefaultUser();
        $justCreatedSession = false;
        // If user not logged in or unauthorized to view this page, redirect to login page
        if($securityHandler->UserCanAccessThisPage($dataAccess, $logger, $curPageName, Constants::$authFailureRedirectPage)) {
            $objUser = $_SESSION['WebUser'];
            $_SESSION['lastActivity'] = time();

            if(isset($_SESSION['JustCreatedAccount'])) {
                if($_SESSION['JustCreatedAccount'] == true) {
                    $justCreatedSession = true;
                    $_SESSION['JustCreatedAccount'] = false;
                }
            }

            // Retrieve any custom session variables requested by the current including page
            if(isset($customSessionVarsToRetrieve)) {
		if(count($customSessionVarsToRetrieve) > 0) {
                    foreach($customSessionVarsToRetrieve as $customSessionVarToRetrieve) {
			if(isset($_SESSION[$customSessionVarToRetrieve])) {
                            $customSessionVars[$customSessionVarToRetrieve] = $_SESSION[$customSessionVarToRetrieve];
			}
                    }
		}
            }
			
            session_write_close();

            // Build string that will be used to refer to the current user
            $welcomeUserName = (strlen($objUser->FirstName) > 0) ? ($objUser->FirstName . " " . $objUser->LastName) : 
                                                                   ((strlen($objUser->UserName) > 0) ? $objUser->UserName : $objUser->EmailAddress);

            // Construct user navigation menu
            $basicMemberNavOptions =    array('<li><a href="AccountManagement.php">Become a Member</a></li>');

            $premiumMemberNavOptions =  array('<li><a href="FindFriends.php">Find Friends</a></li>');

            $homeNavOption =            array('<li><a href="MemberHome.php">Home</a></li>');

            $commonNavOptions =         array('<li><a href="EditProfile.php">Edit Profile</a></li>',
                                              '<li><a href="AJAXHandler.php?action=Logout">Log Out</a></li>');

            $curNavOptions = array();

            $premiumMemberBenefitAdDesktop = '';
//                '<li>'.
//                    '<div class="hiddenDesktopAds">'.
//                        '<a href="#" title="Upgrade to Premium">'.
//                            '<img src="images/big-power-text-layered.jpg" alt="Upgrade Membership" />'.
//                        '</a>'.
//                    '</div>'.
//                '</li>';

            $premiumMemberBenefitAdMobile = '';
//                '<div class="mobileAdStyle">'.
//                    '<a href="#" title="Upgrade to Premium">'.
//                        '<img src="images/big-power-text-layered.jpg" alt="Upgrade Membership" />'.
//                    '</a>'.
//                '</div>';

            if($objUser->IsPremiumMember) {
                $curNavOptions = array_merge($curNavOptions, $premiumMemberNavOptions);
                $premiumMemberBenefitAdDesktop = '';
                $premiumMemberBenefitAdMobile = '';
            }
            else {
                $curNavOptions = array_merge($curNavOptions, $basicMemberNavOptions);
            }

            if(!$justCreatedSession) {
                $curNavOptions = array_merge($homeNavOption, $curNavOptions);
            }

            $curNavOptions = array_merge($curNavOptions, $commonNavOptions);

            // Now revise the header HTML string for logged-in users
            $headerHTML = 
                '<div id="header-wrapper">'.
                    '<div class="container">'.
                        '<div class="row">'.
                            '<div class="12u">'.
                                '<header id="header">'.						
                                    '<!-- Logo -->'.
                                    '<h1><a href="#" id="logo">Player Unite</a></h1>' .
                                    '<!-- Nav -->'.
                                    '<nav id="nav">'.
                                        '<ul>' . $premiumMemberBenefitAdDesktop .
                                            '<li>'.
                                                '<a href="" class="arrow">' . $welcomeUserName . '</a>'.
                                                '<ul>'.
                                                    implode('', $curNavOptions) .
                                                '</ul>'.
                                            '</li>'.
                                            '<li>'.
                                                '<a href="" class="arrow">Site Links</a>'.
                                                '<ul>'.
                                                    '<li><a href="Faq.php">FAQ</a></li>'.
                                                    '<li><a href="DeveloperBlog.php">Developer Blog</a></li>'.
                                                    '<li><a href="About.php">About Us</a></li>'.
                                                '</ul>'.
                                            '</li>'.
                                        '</ul>'.
                                    '</nav>'.
                                '</header>'.
                            '</div>'.
                        '</div>'.
                    '</div>'.
                '</div>' . $premiumMemberBenefitAdMobile;
        }
    }
    
    // Construct HTML Header string, which will be echoed by all including pages
    $jQueryUiCssPath = '<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css" />';
    if(isset($customJqueryUiCssPath)) {
        $jQueryUiCssPath = $customJqueryUiCssPath;
    }
    
    $pageHeaderHTML = 
        '<meta charset="UTF-8">'.
        '<title>Player Unite | ' . $welcomeUserName . '</title>'.
        '<meta http-equiv="content-type" content="text/html; charset=utf-8" />'.
        '<meta name="description" content="" />'.
        '<meta name="keywords" content="" />'.

        '<!-- For Skel framework -->'.
        '<noscript>'.
            '<link rel="stylesheet" href="css/skel.css" />'.
            '<link rel="stylesheet" href="css/style.css" />'.
            '<link rel="stylesheet" href="css/style-desktop.css" />'.
        '</noscript>' . $jQueryUiCssPath.
        '<link rel="stylesheet" href="css/jquery.timepicker.css" />'.
            
        '<!-- For SweetAlert framework -->'.
        '<script src="js/sweetalert.min.js"></script>'.
        '<link rel="stylesheet" type="text/css" href="css/sweetalert.css" />'.

        '<script src="js/jquery.min.js"></script>'.
        '<script src="js/jquery.dropotron.min.js"></script>'.
        '<script src="js/skel.min.js"></script>'.
        '<script src="js/skel-layers.min.js"></script>'.
        '<script src="js/init.js"></script>'.
        '<script src="js/main.js"></script>'.
        '<script src="js/' . $pageInitJSScriptName . '"></script>'.
        '<script src="js/ajax.js"></script>'.
        '<script src="js/jquery-1.10.2.js"></script>'.
        '<script src="js/jquery-ui-1.10.4.custom.js"></script>'.
        '<script src="js/jquery.maskedinput.min.js"></script>'.
        '<script src="js/jquery.timepicker.min.js"></script>'.
        '<!--[if lte IE 8]><link rel="stylesheet" href="css/ie/v8.css" /><![endif]-->'.
        '<script>'.
            '$(document).ready(function($) { '.
                'GlobalStartupActions(); '.
                $curPageName . 'OnReady(); '.
                // Display auth failure redirection message, if present
                $onloadPopupJSCode.
            '});'.
        '</script>';
?>
