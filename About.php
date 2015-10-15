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
									<div class="3u">
										<!-- Features -->
										<div id="sidebar">
											<section class="box style2">
												<h2>Stephen Giles</h2>
													<div class="image">
														<img src="images/author1.jpg" class="image full" alt="Stephen Giles"/>
                                                    </div>
													<p>The master of all things programming, Stephen has graciously given much of his time to make this site a reality. When not hammering away on a keyboard, Stephen enjoys ping-pong, football, studying the Bible, and (occasionally) playing Halo and various racing games.</p>
											</section>
										</div>
									</div>
									<div class="6u">
										<!-- About -->
										<div id="content">
											<article class="box features">
												<header>
													<h2>About Us</h2>
													<span class="byline"></span>
												</header>
												<img src="images/PlayerFind.jpg" class="image left" alt="Player Find"/>
												<p>Have you ever started a new game only to wind up in a lobby with unfamiliar gamer tags? Are your current friends still playing a game you no longer have interest in? Do you need help with a particular game, but are unsure where to turn?</p>
												<p>You've come to the right place!</p>
												<p>Player Unite is dedicated to growing a community of gamers. Our site is your go-to place for meeting other gamers and setting up events to play with friends, old and new.</p>
												<img src="images/HighFive.jpg" class="image right" alt="Snake & Samus Team Up" />
												<p>Player Unite was created by Paul Morrell and Stephen Giles. We are a two man team dedicated to providing a simple way to unite video game players everywhere.</p>
												<h2>Features</h2>
													<article>
														<ul><li class="icon fa-user">&nbsp Find new friends to play your favorite games with online.</li></ul>
													</article>
													<article>
														<ul><li class="icon fa-rss-square">&nbsp Create an event that your friends or random users can sign up for. You decide who can join!</li></ul>
													</article>
													<article>
														<ul><li class="icon fa-search">&nbsp A user generated event feed that allows you to search for games you are interested in.</li></ul>
													</article>
											</article>
										</div>
									</div>
									<div class="3u">
										<!-- Staff -->
										<div id="sidebar2">
											<section class="box style2">
												<h2>Paul Morrell</h2>
													<div class="image">
														<img src="images/author2.jpg" class="image full" alt="Paul Morrell"/>
													</div>
													<p>The mold was broken when Paul entered this world. All joking aside, Paul put together the front end of the site. He enjoys all types of video games. From the FPS genre, to an involving RPG, and lets not forget the indies! Have you played Axiom Verge?!</p>
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
