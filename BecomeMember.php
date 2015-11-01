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
						<h2>Become a Member</h2><br/>
						<p>By upgrading your free account to a membership, you are helping us cover our hosting cost and paving a way for future expansions of the site.</p>
						<h2>What is included in the membership?</h2><br/>
													
						<ul><li class="icon fa-search">&nbsp Additional search tools for quickly pinpointing the information you need</li></ul>
													
						<ul><li class="icon fa-user">&nbsp A dedicated friends section that allows you to see other users information.</li></ul>
													
						<ul><li class="icon fa-flask">&nbsp The ability to keep your events open to only your friends, keeping random users out.</li></ul>
													
						<article>
						<a href="#" class="button icon fa-credit-card">Upgrade to a Membership</a>
						</article>
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
