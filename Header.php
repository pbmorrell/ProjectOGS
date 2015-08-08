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
    $justCreatedSession = false;
    // If user not logged in or unauthorized to view this page, redirect to login page
    if($securityHandler->UserCanAccessThisPage($dataAccess, $logger, $curPageName, $authFailureRedirectPage)) {
        $objUser = $_SESSION['WebUser'];
        $_SESSION['lastActivity'] = time();
        
        if(isset($_SESSION['JustCreatedAccount'])) {
            if($_SESSION['JustCreatedAccount'] == true) {
                $justCreatedSession = true;
                $_SESSION['JustCreatedAccount'] = false;
            }
        }
        
	session_write_close();
        
        // Construct HTML Header string, which will be echoed by all including pages
        $welcomeUserName = (strlen($objUser->FirstName) > 0) ? $objUser->FirstName . " " . $objUser->LastName : 
                                                               (strlen($objUser->UserName) > 0) ? $objUser->UserName : $objUser->EmailAddress;
        
        // Construct page JS init file name for script include
        $pageInitJSScriptName = $curPageName . "Init.js";

        $pageHeaderHTML = 
            '<meta charset="UTF-8">'.
            '<title>Project OGS | ' . $welcomeUserName . '</title>'.
            '<meta http-equiv="content-type" content="text/html; charset=utf-8" />'.
            '<meta name="description" content="" />'.
            '<meta name="keywords" content="" />'.

            '<!-- For Skel framework -->'.
            '<noscript>'.
                '<link rel="stylesheet" href="css/skel.css" />'.
                '<link rel="stylesheet" href="css/style.css" />'.
                '<link rel="stylesheet" href="css/style-desktop.css" />'.
            '</noscript>'.
            '<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css" />'.

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
            '<!--[if lte IE 8]><link rel="stylesheet" href="css/ie/v8.css" /><![endif]-->';
        
        // Construct user navigation menu
        $basicMemberNavOptions =    array('<li><a href="#">Become a Premium Member!</a></li>');
        
        $premiumMemberNavOptions =  array('<li><a href="#">Find Friends</a></li>');
        
        $homeNavOption =            array('<li><a href="MemberHome.php">Home</a></li>');
        
        $commonNavOptions =         array('<li><a href="EditProfile.php">Edit Profile</a></li>',
                                          '<li><a href="ExecuteLogout.php">Log Out</a></li>');
        
        $curNavOptions = array();
        //$premiumMemberBenefitAd = '<h2><a href="#" id="upgradeAd">Want to create your own events? Become a Premium member today!</a></h2>';
        $premiumMemberBenefitAdDesktop = 
            '<li>'.
                '<div class="hiddenDesktopAds">'.
                    '<a href="#" title="Upgrade to Premium">'.
                        '<img src="images/big-power-text-layered.jpg" alt="Upgrade Membership" />'.
                    '</a>'.
                '</div>'.
            '</li>';
        
        $premiumMemberBenefitAdMobile = 
            '<div class="mobileAdStyle">'.
                '<a href="#" title="Upgrade to Premium">'.
                    '<img src="images/big-power-text-layered.jpg" alt="Upgrade Membership" />'.
                '</a>'.
            '</div>';
        
        if($objUser->IsPremiumMember) {
            $curNavOptions = array_merge($curNavOptions, $premiumMemberNavOptions);
            $premiumMemberBenefitAdDesktop = '';
            $premiumMemberBenefitAdMobile = '';
        }
        else {
            $curNavOptions = array_merge($curNavOptions, $basicMemberNavOptions);
        }
        
        if(!$justCreatedSession) {
            $curNavOptions = array_merge($curNavOptions, $homeNavOption);
        }
        
        $curNavOptions = array_merge($curNavOptions, $commonNavOptions);
        
        // Now build the header HTML string, which will be echoed on all including pages
        $headerHTML = 
            '<div id="header-wrapper">'.
                '<div class="container">'.
                    '<div class="row">'.
                        '<div class="12u">'.
                            '<header id="header">'.						
                                '<!-- Logo -->'.
                                '<h1><a href="#" id="logo">Project OGS</a></h1>' .
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
                                                '<li><a href="#">Recent News</a></li>'.
                                                '<li><a href="#">Developer Blog</a></li>'.
                                                '<li><a href="#">About Us</a></li>'.
                                                '<li><a href="#">Contact Us</a></li>'.
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
?>