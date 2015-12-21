<?php
    $mobileLoginPage = false;
    $sessionRequired = true;
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
	<script src="js/jTable/jquery.jtable.min.js"></script>
	<script src="js/jquery.slidereveal.min.js"></script>
	<script src="js/GamerTagViewer.js"></script>
	<link rel="stylesheet" href="css/jTable/lightcolor/green/jtable.min.css" />
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
                                    <div id="content">
					<article class="box style1">
                                            <div id="searchForFriendsDiv" class="9u jTableContainer">
                                                <section class="box style1" style="padding-bottom: 1em !important;">
                                                    <h2>Search For New Friends</h2>
                                                    <div id="searchForFriendsContent">
                                                    </div>
                                                </section><br/>
                                            </div>
					</article>
					<article class="box style1">
                                            <div id="manageFriendsListDiv" class="9u jTableContainer">
                                                <section class="box style1" style="padding-bottom: 1em  !important;">
                                                    <h2>Manage Friends List</h2>
                                                    <div id="manageFriendsListContent">
                                                    </div>
                                                </section><br/>
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
