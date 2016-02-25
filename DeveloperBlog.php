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
													<h3>Date: 02/26/2016</h3>
													<p>Welcome to Player Unite! After months of writing code, we are thrilled to be pulling the curtain back.
													The main focus of our site is to help unite gamers across the world. We accomplish this by hosting a 
													living event feed where you can search, create, and sign up for gaming events across various platforms.
													Stop in, create a profile, enjoy, and leave us some feedback. <br/> - The Devolopment Team</p>
												</header>
												<header>
													<h3>Date: Cooming soon</h3>
													<p>...More post to follow!</p>
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
