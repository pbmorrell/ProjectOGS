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
                                            <h2>Developer Blog</h2><br/>
                                            <header>
						<h3>Date: 4/18/2016</h3>
						<p>New feature added! We have implemented an automatic email to be sent to the user at a specified time, 
                                                    to remind you of your scheduled/joined events. Of course, you have the option to enable or disable 
                                                    this feature. Once logged in, you will find the option under "Edit Profile" of your account. Keep sending
                                                    us feedback! Thank you to all of our users.</p>
                                            </header>
                                            <header>
						<h3>Date: 03/01/2016</h3>
                                                <p>Welcome to Player Unite! After months of writing code, we are thrilled to be pulling the curtain back.
                                                    The main focus of our site is to help unite gamers across the world. We accomplish this by hosting a 
                                                    living event feed where you can search, create, and sign up for gaming events across various platforms.
                                                    Stop in, create a profile, enjoy, and leave us some feedback. <br/> - The Development Team</p>
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
