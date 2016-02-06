<?php
class Constants
{	    
    // Database configuration
    public static $dbType = "mysql";
    public static $dbHost = "localhost";
    public static $dbUser = "raiden01_webuser";
    public static $dbPassword = "t3\$t*useR";
    public static $dbName = "raiden01_ProjectOGS";
	
    // Security configuration
    public static $authFailureRedirectPage = "Index.php";
    public static $pwdRecoveryPage = "http://www.morrellweb.com/ogs/PasswordRecovery.php";
    public static $pwdRecoveryEmailSenderEmail = "playerunite.donotreply@gmail.com";
    public static $pwdRecoveryEmailSenderFriendlyName = "PlayerUnite - DoNotReply";
    public static $pwdRecoverySessionExpTimeInMinutes = "12";
    public static $pwdRecoverySessionEmailSubject = "PlayerUnite Password Reset Instructions";
    public static $emailSMTPServer = "smtp.gmail.com";
    public static $emailSMTPPort = 587;
    public static $emailSMTPUser = "playerunite.donotreply@gmail.com";
    public static $emailSMTPPassword = "t3\$t*useR";
	
    /* ************************************** PAYPAL CONFIGURATION *******************************************/
    public static $isPayPalTest = true; // Change this to false when ready to move to production
    //public static $subscriptionOptionNames = ["Monthly" => "6.50", "Yearly" => "39.95"];
    public static $subscriptionOptionNames = ["Monthly" => "6.50"];
    public static $premiumMemberRoleName = "PremiumMember";
    public static $basicMemberRoleName = "BasicMember";
	
    // Sandbox testing variables
    public static $payPalTestMerchantId = "stephengiles2011@comcast.net";
    public static $payPalTestButtonFormUrl = "https://www.sandbox.paypal.com/cgi-bin/webscr";
    public static $payPalTestMakeSubscriptionButtonId = "ZHAXXJD9UND5W";
    public static $payPalTestDonationButtonId = "CR9CAS34T5BZ2";
    public static $payPalTestSubscribeButtonImgUrl = "https://www.sandbox.paypal.com/en_US/i/btn/btn_subscribe_LG.gif";
    public static $payPalTestUnsubscribeButtonImgUrl = "https://www.sandbox.paypal.com/en_US/i/btn/btn_unsubscribe_LG.gif";
    public static $payPalTestDonationButtonImgUrl = "https://www.sandbox.paypal.com/en_US/i/btn/btn_paynow_LG.gif";
    public static $payPalTestPixelImgUrl = "https://www.sandbox.paypal.com/en_US/i/scr/pixel.gif";
    public static $payPalTestPostIdentityToken = "NyoAhK2wqNWZZ53U4B96QvQhBwxqEGAeiyhxTRPbAS4HEvyP-28LUC51zMe";
    public static $payPalTestAPIURL = "https://api-3t.sandbox.paypal.com/nvp";
    public static $payPalTestAPIUsername = "stephengiles2011_api1.comcast.net";
    public static $payPalTestAPIPassword = "GWFEUGDRJHMXMP5F";
    public static $payPalTestAPISignature = "An5ns1Kso7MWUdW4ErQKJJJ4qi4-AsiEpTILBNuocaRuASZDvMYMDNuB";

    // Production 
    public static $payPalProdMerchantId = "admin@morrellweb.com";
    public static $payPalProdButtonFormUrl = "https://www.paypal.com/cgi-bin/webscr";
    public static $payPalProdMakeSubscriptionButtonId = "9H4QGFRL3F7HS";
    public static $payPalProdDonationButtonId = "KPJMEYCDVH4XW";
    public static $payPalProdSubscribeButtonImgUrl = "https://www.paypalobjects.com/en_US/i/btn/btn_subscribe_LG.gif";
    public static $payPalProdUnsubscribeButtonImgUrl = "https://www.paypalobjects.com/en_US/i/btn/btn_unsubscribe_LG.gif";
    public static $payPalProdDonationButtonImgUrl = "https://www.paypalobjects.com/en_US/i/btn/btn_paynow_LG.gif";
    public static $payPalProdPixelImgUrl = "https://www.paypalobjects.com/en_US/i/scr/pixel.gif";
    public static $payPalProdPostIdentityToken = "YJ0q__STdxwVpTQg5X6-JMLqZnppTTEMBRmg6Iao0kyqSQEsYOjDoxoyv3e";
    public static $payPalProdAPIURL = "https://api-3t.paypal.com/nvp";
    public static $payPalProdAPIUsername = "admin_api1.morrellweb.com";
    public static $payPalProdAPIPassword = "D4BEWPEQ6792PQBT";
    public static $payPalProdAPISignature = "AN-TgTMVCpYHCc3KCiFcKast2SyHAVzcTpuUlSXVhndDVTV-VWeG03cB";
    /* ************************************ END PAYPAL CONFIGURATION *****************************************/
}

