<?php
    $mobileLoginPage = false;
    $sessionRequired = false;
    $sessionAllowed = true;
    include "Header.php";
?>
<!DOCTYPE HTML>
<!--
	Project OGS
	by => Stephen Giles and Paul Morrell
-->
<html>
    <head>
        <?php echo $pageHeaderHTML; ?>
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
				<!-- About -->
                                    <div id="content">
					<article class="box style1">
                                            <header>
						<h2>FAQ (Frequently Asked Questions)</h2><br/>
						<div id="accordion">
                                                    <h3>What is Player Unite?</h3>
                                                    <div>
							<p>Player Unite is a "Looking for Group" message board. We have a simple-to-use interface that allows
							you to find other players to game with.</p>
							<p>Sign up is quick and simple.&nbsp;<a href="Index.php">Get Started Today!</a></p>
                                                    </div>
                                                    <h3>Is it free?</h3>
                                                    <div>
                                                        <p>
                                                            Yes! The main components of Player Unite are completely free. This includes a personal login,
                                                            unique profile, the ability to create and join gaming events, and access to search tools! The site has
                                                            been designed so anyone may use it, without being held back by member fees.
                                                        </p>
                                                        <p>
                                                            Additional features can be obtained by subscribing.
                                                            Subscribing members (Premium Members) gain access to additional search tools, a friends list,
                                                            and the ability to keep your gaming events private.
                                                        </p>
                                                    </div> 
                                                    <h3>Where do my donations go?</h3>
                                                    <div>
							<p>
                                                        All donations go towards the future of Player Unite. Your money gifts are 
							reinvested into the server hosting cost, additional future features, improved integration,
							marketing, and website maintenance.
							</p>
                                                    </div>
                                                    <h3>How do I become a member of Player Unite?</h3>
                                                    <div>
							<p>
                                                        Click on your User Name at the top right of the screen. Select "Become a Member" and then click "Subscribe".
                                                        You will be automatically redirected to the PayPal website, where you'll be asked to log in.
                                                        After logging in, you'll be asked to agree to the terms and conditions of the PlayerUnite subscription.
                                                        If you do, you'll then be automatically sent back to the PlayerUnite site, and the completed transaction
                                                        details will be displayed. If all went well, you will now have full Premium Member access!
                                                        </p>
                                                        <p>
                                                        You can cancel your membership at anytime with ease. From the "Home" page, click on "Manage Your Account"
                                                        from within the Control Panel on the left. Click "Unsubscribe" from the Cancel Your Membership tab and confirm
                                                        your request from the pop up dialog box.
							</p>
                                                    </div>
                                                    <h3>How do I manage my friends list</h3>
                                                    <div>
                                                        <p>
                                                         Please keep in mind, the Friends feature is for premium members. Sign up to gain access!
                                                        </p>
                                                        <p>
                                                         Once a premium member, you can click on a user's name anywhere in the site to view their profile. 
                                                        </p>
                                                        <p>
                                                         Start by clicking on your user name at the top right of the screen. Select "Find Friends". This
                                                         will take you to the Friends section where you can search for friends and manage all current friends.
                                                        </p>
                                                        <p>
                                                         Click on "User Search" within the control panel on the left. This will bring up a table with all of the site's
                                                         active users. From here you can organize the users by clicking the appropriate field, i.e. First Name, Last Name,
                                                         Gender, etc. Click the envelope to send the user a "Friend Request". The selected user now has the option to
                                                         accept or decline your invitation.
                                                        </p>
                                                        <p>
                                                         Click on "Manage Friend List" to see your current friends. From here you can remove any unwanted friends. You
                                                         also have the option to remove pending invitations. 
                                                        </p>
                                                        <p>
                                                         Click on "Filter User List". This brings up the search tools. Click on the desired filter and be sure to click 
                                                         "Activate Filter". Once you have finished with your desired filters, click "Search" then "Close". Your 
                                                         search results will be displayed. If you would like to reverse the search, simply reclick "Filter User List",
                                                         click "Deactivate Filter(s)", "Search", and then finish by clicking "Close".
                                                        </p>
                                                    </div>
                                                    <h3>How do I edit my profile?</h3>
                                                    <div>
                                                        <p>
							Click on your User Name at the top right of the screen, then select "Edit Profile". Make your desired 
							changes and click "Update Profile".
							</p>
                                                    </div>
                                                    <h3>How do I add gamer tags to my profile?</h3>
                                                    <div>
                                                        <p>
							Click on your User Name at the top right of the screen, then select "Edit Profile".
                                                        Click the "Update Gamer Tags" button on the upper right of the screen. In the popup window,
                                                        you'll be able to easily add, remove, or edit your gamer tags.
                                                        </p>
                                                        <p>
                                                        After doing this, other users will be able to quickly bring up a list of all your gamer tags just by 
                                                        clicking a link from any events you've created. They will also be able to search for you based on your 
                                                        gamer tags.
							</p>
                                                    </div>
                                                    <h3>How do I create an event for others to join?</h3>
                                                    <div>
							<p>
							Click on "Schedule Event". Complete required fields and click "Create Event". 
							Your events will be shown on other gamers' event feeds for them to join.
							</p>
                                                    </div>
                                                    <h3>I made a mistake when creating my event. How can I fix it?</h3>
                                                    <div>
							<p>
							Click on Manage Events. Find the event you want to change and click the icon in the "Edit" column. 
							Make the needed changes and click "Update Event".
							</p>
                                                    </div>
                                                    <h3>I created an event but I don't see it under Current Events. Do I need to re-create it?</h3>
                                                    <div>
                                                        <p>
							No. Your event will show up on other gamers' Current Event feeds. If you would like to view your 
							created events, click on Manage Events. From this screen you can view your events, 
							make changes to them, hide them from other's feeds, or delete them.
							</p>
                                                    </div>
                                                    <h3>How do I see my joined events?</h3>
                                                    <div>
                                                        <p>
							Click on "Filter Events". Scroll down to the bottom of the screen and click "Show My Joined Events". 
							Click the "Search" button and then close the Search box. Your joined events will be listed under Current Events.
							</p>
                                                    </div>
                                                    <h3>How do I see events for a specific game title or console?</h3>
                                                    <div>
							<p>
							Click on "Filter Events". Select your desired filter(s) and enter your search criteria. 
							Check "Activate Filter" for each desired filter and then click "Search". 
							Close the Search box to view your results under Current Events.
							</p>
                                                    </div>
                                                    <h3>How do I get the Event Feed to show all the events, with no filters?</h3>
                                                    <div>
                                                        <p>
							Click on "Filter Events". Make sure previously used search fields are deactivated and click "Search". 
							Close the Search box to return to the Event Feed.
							</p>
                                                    </div>
						</div>
                                            </header>
					</article>
                                    </div>
				</div>
                            </div>
			</div>
                    </div>
		</div>
            </div>
	</div>
        <!-- Footer Wrapper -->
        <?php include("Footer.php"); ?>
        <!-- Footer Wrapper -->
    </body>
</html>
