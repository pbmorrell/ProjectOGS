<?php
    $curPageName = "About";
    $mobileLoginPage = false;
    $sessionRequired = false;
    $sessionAllowed = true;
    $authFailureRedirectPage = "Index.php";
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
                                <div class="12u">
                                    <!-- Content -->
                                    <div id="content">
                                        <article class="box">
                                                <div class="row double">
                                                    <section class="8u">
                                                        <h2>Our Goal</h2>
                                                        <p>To help gamers everywhere, meet and engage with other like-minded gamers to enrich their online game experience.</p><br/>
                                                        <h2>What our site offers</h2>
                                                        <p>
                                                            - The ability to schedule games that your friends can join<br/>
                                                            - When creating an event, select from an array of game titles, or enter your own<br/>
                                                            - An event feed that allows you to view and sign up for as many games as you desire<br/>
                                                            - Subscribe to events created for particular game titles, platforms, etc.<br/>
                                                            - Defeat your archnemesis in any game<br/>
                                                            - OK, that last one is still in the works...<br/>
                                                        </p><br/>
                                                        <h2>Why?</h2>
                                                        <p>Have you ever started a new game only to wind up in a lobby with unfamiliar gamer tags?<br/>Are your current friends still playing a game you no longer have
                                                           interest in?<br/>Do you need help with a particular game, but are unsure where to turn?<br/><br/>...Then this site is for you.</p>
                                                    </section>
                                                    <!-- Second Main Div -->
                                                    <section class="4u box style3">
                                                        <br/>
                                                        <h2>Stephen Giles</h2>
                                                            <div class="image">
                                                                <img src="images/author1.jpg"  />
                                                            </div>
                                                            <p>The master of all things programming, Stephen has graciously given much of his time to make this site a reality. When not hammering away on a keyboard, Stephen enjoys ping-pong, football, studying the Bible, and (occasionally) playing Halo and various racing games.</p>
                                                        <br />
                                                        <h2>Paul Morrell</h2>
                                                            <div class="image">
                                                                <img src="images/author2.png"  />
                                                            </div>
                                                            <p>The mold was broken when Paul entered this world. All joking aside, Paul enjoys all types of video games. From the FPS genre to an involving RPG. Lets not forget the indies! Have you played Axiom Verge?!</p>
                                                    </section>
                                                </div>
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
