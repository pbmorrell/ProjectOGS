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
							<h3>Section 1</h3>
                                                             <div>
								<p>
								Mauris mauris ante, blandit et, ultrices a, suscipit eget, quam. Integer
								ut neque. Vivamus nisi metus, molestie vel, gravida in, condimentum sit
								amet, nunc. Nam a nibh. Donec suscipit eros. Nam mi. Proin viverra leo ut
								odio. Curabitur malesuada. Vestibulum a velit eu ante scelerisque vulputate.
								</p>
                                                             </div>
							<h3>Section 2</h3>
                                                             <div>
								<p>
								Sed non urna. Donec et ante. Phasellus eu ligula. Vestibulum sit amet
								purus. Vivamus hendrerit, dolor at aliquet laoreet, mauris turpis porttitor
								velit, faucibus interdum tellus libero ac justo. Vivamus non quam. In
								suscipit faucibus urna.
								</p>
                                                            </div>
							<h3>Section 3</h3>
                                                            <div>
								<p>
								Nam enim risus, molestie et, porta ac, aliquam ac, risus. Quisque lobortis.
								Phasellus pellentesque purus in massa. Aenean in pede. Phasellus ac libero
								ac tellus pellentesque semper. Sed ac felis. Sed commodo, magna quis
								lacinia ornare, quam ante aliquam nisi, eu iaculis leo purus venenatis dui.
								</p>
								<ul>
								 <li>List item one</li>
								 <li>List item two</li>
								<li>List item three</li>
								</ul>
								 </div>
							<h3>Section 4</h3>
                                                            <div>
								<p>
								Cras dictum. Pellentesque habitant morbi tristique senectus et netus
								et malesuada fames ac turpis egestas. Vestibulum ante ipsum primis in
								faucibus orci luctus et ultrices posuere cubilia Curae; Aenean lacinia
								mauris vel est.
								</p>
								<p>
								Suspendisse eu nisl. Nullam ut libero. Integer dignissim consequat lectus.
								Class aptent taciti sociosqu ad litora torquent per conubia nostra, per
								inceptos himenaeos.
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
