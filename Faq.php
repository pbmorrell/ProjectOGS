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
													<h2>FAQ (Frequently Asked Questions )</h2><br/>
														<div id="accordion">
															<h3>What is Player Unite?</h3>
														  <div>
															<p>
															Player Unite is a "Looking for Group" message board. We have a simple to use interface that allows
															you to find other players to game with.</p>
															<p>
															Sign up is quick and simple.&nbsp<a href="">Get Started Today!</a>
															</p>
														  </div>
														  <h3>Is it free?</h3>
														  <div>
															<p>
															Yes! The main components of Player Unite are completely free. This includes a personnel login,
															unique profile, the ability to create and join gaming events, and access to search tools! The site has
															been designed so anyone may use it and not be held back by member fees.</p>
															<p>
															Additional features can be obtained by subscribing.<br/>
															Subscribing members (Premium Members) gain access to additional search tools, a freinds list,
															and the ability to keep your gaming events private.
															</p>
														  </div>
														  <h3>How do I create an event for others to join?</h3>
														  <div>
															<p>
															Click on Schedule Event. Complete required fields and click Create Event. 
															Your events will be shown on others' event feed for them to join.
															</p>
														  </div>
														  <h3>I made a mistake when creating my event. How can I fix it?</h3>
														  <div>
															<p>
															Click on Manage Events. Select the event you want to change and click edit. 
															Make the needed changes and click update.
															</p>
														  </div>
														  <h3>I created an event but I don't see it under Current Events. Do I need to recreate it?</h3>
														  <div>
															<p>
															No. Your event will show up on others' Current Event feed. If you would like to view your 
															created events, click on Manage Events. From this screen you can view your events, 
															make changes to them, or delete them.
															</p>
														  </div>
														  <h3>How do I see my joined events?</h3>
														  <div>
															<p>
															Click on Filter Events. Scroll down to the bottom of the screen and click Show My Joined Events. 
															Select Search and then Close the Search box. Your joined events will be listed under Current Events.
															</p>
														  </div>
														  <h3>How do I see events for a specific game title or console?</h3>
														  <div>
															<p>
															Click on Filter Events. Select your desired filter(s) and enter your search criteria. 
															Click Activate Filter for each desired filter and then click Search at the bottom of the box. 
															Close the Search box to view your results under current events.
															</p>
														  </div>
														  <h3>How do I get the Event Feed to show all created events after performing a search?</h3>
														  <div>
															<p>
															Click on Filter Events. Make sure previously used search fields are deactivated and click Search. 
															Close the Search box to return to the Event Feed.
															</p>
														  </div>
														  <h3>How do I become a member of Player Unite?</h3>
														  <div>
															<p>
															Click on your User Name at the top right of the screen. Select Become a Member and then click Subscribe.
															</p>
														  </div>
														  <h3>How do I edit my profile?</h3>
														  <div>
															<p>
															Click on your User Name at the top right of the screen and then select Edit Profile. Make your desired 
															changes and click Update Profile.
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
