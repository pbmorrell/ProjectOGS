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
        <script>
            // JQuery functionality
            $(document).ready(function($) {
                displayHiddenAdsByBrowsingDevice();
                IndexOnReady();
                
		// Display auth failure redirection message, if present and valid
		<?php echo $onloadPopupJSCode; ?>
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
									<div class="6u">
										<!-- About -->
											<div id="content">
												<article class="box post">
													<header>
														<h2>About Us</h2>
														<span class="byline"></span>
													</header>
													<a href="#" class="image left"><img src="images/pic01.jpg" alt="" /></a>
													<p>"Player Unite" is dedicated to growing a community of gamers. We want our site to be your go-to place for meeting other gamers and setting up events together.</p>
													<p>Have you ever started a new game only to wind up in a lobby with unfamiliar gamer tags? Are your current friends still playing a game you no longer have interest in? Do you need help with a particular game, but are unsure where to turn?</p>
													<p>...Then hopefully our site can assist you.</p>
													
													<h2>Our Goal</h2>
													<p>To help gamers everywhere, meet and engage with other like-minded gamers to enrich their online game experience.</p>
													<footer>
														<a href="mailto:ProjectOGS@placeholder.com" class="button icon fa-envelope">Share your Feedback</a>
													</footer>
												</article>
											</div>
									</div>
									<div class="3u">
									
										<!-- Features -->
											<div id="sidebar1">

												<section class="box features">
													<h2>Features</h2>
													<article>
														<ul><li class="icon fa-user">&nbsp Find new friends to play your favorite games with online.</li></ul>
													</article>
													<article>
														<img src="images/pic03.jpg" alt="" />
														<h3><a href="#"></a></h3>
														<ul><li class="icon fa-rss-square">&nbsp Create an event that your friends or random users can sign up for. You decide who can join!</li></ul>
			
													</article>
													<article>
														<img src="images/pic04.jpg" alt="" />
														<h3><a href="#"></a></h3>
														<ul><li class="icon fa-search">&nbsp A user generated event feed that allows you to search for games you are interested in.</li></ul>
													</article>
												</section>

											</div>
									
									</div>
									<div class="3u">
									
										<!-- Staff -->
											<div id="sidebar2">

												<section class="box style1">
													<h2>Stephen Giles</h2>
														<div class="image">
															<img src="images/author1.jpg" class="image full" alt=""/>
                                                        </div>
														<p>The master of all things programming, Stephen has graciously given much of his time to make this site a reality. When not hammering away on a keyboard, Stephen enjoys ping-pong, football, studying the Bible, and (occasionally) playing Halo and various racing games.</p>
												</section>
												
												<section class="box style2">
													<h2>Paul Morrell</h2>
														<div class="image">
															<img src="images/author2.png" class="image full" alt=""/>
														</div>
														<p>The mold was broken when Paul entered this world. All joking aside, Paul enjoys all types of video games. From the FPS genre to an involving RPG. Lets not forget the indies! Have you played Axiom Verge?!</p>
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
