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
															Yes! The main components of Player Unite are completly free. This includes a personnel login,
															unique profile, the ability to create and join gaming events, and access to search tools! The site has
															been designed so anyone may use it and not be held back by member fees.</p>
															<p>
															Additional features can be obtained by subscribing.<br/>
															Subscribing members (Premium Members) gain access to additional search tools, a freinds list,
															and the ability to keep your gaming events private.
															</p>
														  </div>
														  <h3>How do the Search Functions work?</h3>
														  <div>
															<p>
															Straight Magic. No seriously, Stephen Giles knows magic.
															</p>
															<ul>
															  <li>I'm still</li>
															  <li>working</li>
															  <li>on this!</li>
															</ul>
														  </div>
														  <h3>Where do my donations go?</h3>
														  <div>
															<p>
															All donations go towards the future of Player Unite. Your money gifts are 
															reinvested into the server hosting cost, additioanl future features, improved integration,
															marketing, and website maintenace.
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
