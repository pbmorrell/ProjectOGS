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
	
    /* ************************************** PAYPAL CONFIGURATION *******************************************/
    public static $isPayPalTest = true; // Change this to false when ready to move to production
    public static $subscriptionOptionNames = ["Month-by-month" => "3.95", "Full Year" => "39.95"];
    public static $premiumMemberRoleName = "PremiumMember";
    public static $basicMemberRoleName = "BasicMember";
	
    // Sandbox testing variables
    public static $payPalTestMerchantId = "stephengiles2011@comcast.net";
    public static $payPalTestButtonFormUrl = "https://www.sandbox.paypal.com/cgi-bin/webscr";
    public static $payPalTestCancelSubscriptionUrl = "https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_subscr-find&alias=8PF58UQ6LLAZA";
    public static $payPalTestMakeSubscriptionButtonId = "ZHAXXJD9UND5W";
    public static $payPalTestRenewSubscriptionButtonId = "6NXXK8S52LWJS";
    public static $payPalTestDonationButtonId = "CR9CAS34T5BZ2";
    public static $payPalTestSubscribeButtonImgUrl = "https://www.sandbox.paypal.com/en_US/i/btn/btn_subscribe_LG.gif";
    public static $payPalTestUnsubscribeButtonImgUrl = "https://www.sandbox.paypal.com/en_US/i/btn/btn_unsubscribe_LG.gif";
    public static $payPalTestDonationButtonImgUrl = "https://www.sandbox.paypal.com/en_US/i/btn/btn_paynow_LG.gif";
    public static $payPalTestPixelImgUrl = "https://www.sandbox.paypal.com/en_US/i/scr/pixel.gif";
    public static $payPalTestPostIdentityToken = "NyoAhK2wqNWZZ53U4B96QvQhBwxqEGAeiyhxTRPbAS4HEvyP-28LUC51zMe";

    // Production 
    public static $payPalProdMerchantId = "admin@morrellweb.com";
    public static $payPalProdButtonFormUrl = "https://www.paypal.com/cgi-bin/webscr";
    public static $payPalProdCancelSubscriptionUrl = "https://www.paypal.com/cgi-bin/webscr?cmd=_subscr-find&alias=A92KZXWFK8REW";
    public static $payPalProdMakeSubscriptionButtonId = "9H4QGFRL3F7HS";
    public static $payPalProdRenewSubscriptionButtonId = "NW9MDQLDJS8D6";
    public static $payPalProdDonationButtonId = "KPJMEYCDVH4XW";
    public static $payPalProdSubscribeButtonImgUrl = "https://www.paypalobjects.com/en_US/i/btn/btn_subscribe_LG.gif";
    public static $payPalProdUnsubscribeButtonImgUrl = "https://www.paypalobjects.com/en_US/i/btn/btn_unsubscribe_LG.gif";
    public static $payPalProdDonationButtonImgUrl = "https://www.paypalobjects.com/en_US/i/btn/btn_paynow_LG.gif";
    public static $payPalProdPixelImgUrl = "https://www.paypalobjects.com/en_US/i/scr/pixel.gif";
    public static $payPalProdPostIdentityToken = "YJ0q__STdxwVpTQg5X6-JMLqZnppTTEMBRmg6Iao0kyqSQEsYOjDoxoyv3e";
    /* ************************************ END PAYPAL CONFIGURATION *****************************************/
}

