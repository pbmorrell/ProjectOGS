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
									<div class="6u">
										<!-- About -->
										<div id="content">
											<article class="box features">
												<header>
													<h2>About Us</h2>
													<span class="byline"></span>
												</header>
													<img src="images/PlayerFind.jpg" class="image rnd left" alt="Player Find"/>
												<p>Have you ever started a new game only to wind up in a lobby with unfamiliar gamer tags? 
													Are your current friends still playing a game you no longer have interest in? Do you need 
													help with a particular game, but are unsure where to turn?</p>
												<p>You've come to the right place!</p>
												<p>Player Unite is dedicated to growing a community of gamers. Our site is your go-to place 
													for meeting other gamers and setting up events to play with friends, old and new.</p>
													<img src="images/HighFive.jpg" class="image rnd right" alt="Snake & Samus Team Up" />
												<p>Player Unite was created by Paul Morrell and Stephen Giles. We are a two man team dedicated 
													to providing a simple way to unite video game players everywhere.</p>
												<p>Sign up is quick and easy. Did we mention a standard account is free? <a href="Index.php" style="text-decoration:none;">
													What are you waiting for?</a></p>
												<p>Have questions? Be sure to check out our <a href="Faq.php" style="text-decoration:none;">FAQ</a> for additinal information.</p>
										</article>
										</div>
									</div>
									<div class="6u">
										<!-- Features -->
										<div id="content">
											<section class="box style1">
												<h2>Features</h2>
													<p>
														<ul><li class="icon fa-user">&nbsp Find new friends to play your favorite games with online.</li></ul>
													</p>
													<p>
														<ul><li class="icon fa-rss-square">&nbsp Create an event that your friends or random users can sign up for. You decide who can join!</li></ul>
													</p>
													<p>
														<ul><li class="icon fa-search">&nbsp A user generated event feed that allows you to search for games you are interested in.</li></ul>
													</p>
												<h2>The Team</h2>
												<h3>Stephen Giles</h3>
													<p>Stephen is well versed in multiple programming languages such as .NET, PHP, AJAX, and JS. 
														When not stiching together backend databases, he enjoys a wide range of activties. Which include ping-pong, football, studying the Bible, 
														playing the Halo series, and endless nights with Forza</p>
												<h3>Paul Morrell</h3>
													<p>During the day Paul is an Automotive Consultant for a number of body shops across the United States. 
													In his free time he enjoys coding front end websites and gaming on the PS4 and Steam. He enjoys all types 
													of video games. From the FPS genre, to an involving RPG, and lets not forget the indies! Have you played Axiom Verge?!
													Paul is currently falling prey to the people of Yharnam in Bloodborne.
													Follow Paul on Twitter <a href="https://twitter.com/pbmorrell" target="_blank" style="text-decoration:none;">@pbmorrell</a></p>
											</section>
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
