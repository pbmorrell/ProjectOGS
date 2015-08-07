<!DOCTYPE HTML>
<!--
	Project OGS
	by => Stephen Giles and Paul Morrell
-->
<html>
	<head>
	<meta charset="UTF-8">
	<title>Project OGS | Event Manager</title>
			<meta http-equiv="content-type" content="text/html; charset=utf-8" />
			<meta name="description" content="" />
			<meta name="keywords" content="" />
			<!--[if lte IE 8]><script src="css/ie/html5shiv.js"></script><![endif]-->
			<script src="js/jquery.min.js"></script>
			<script src="js/jquery.dropotron.min.js"></script>
			<script src="js/skel.min.js"></script>
			<script src="js/skel-layers.min.js"></script>
			<script src="js/init.js"></script>
			<script src="js/main.js"></script>
			<script src="js/ajax.js"></script>
			<script src="js/jquery-1.10.2.js"></script>
			<script src="js/jquery-ui-1.10.4.custom.js"></script>
			<noscript>
				<link rel="stylesheet" href="css/skel.css" />
				<link rel="stylesheet" href="css/style.css" />
				<link rel="stylesheet" href="css/style-desktop.css" />
			</noscript>
			<!--[if lte IE 8]><link rel="stylesheet" href="css/ie/v8.css" /><![endif]-->
			<script>
				 $(function(){
					$('#datepicker').datepicker({
						inline: true,
						changeYear: true,
						showButtonPanel: true,
						showOtherMonths: true,
						dayNamesMin: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
					});
				});
			</script>
	</head>
	<body class="">

		<!-- Navigation Wrapper -->
			<?php include("Header.php"); ?>
		<!-- Navigation Wrapper -->


		<!-- Main Wrapper -->
			<div id="main-wrapper">
				<div class="container">
					<div class="row">
						<div class="12u">
							<div id="main">
								<div class="row">
									<div class="6u">
										
										<!-- Content -->
											<div id="content">
											
												<section class="box"> <!-- *remember to add class "main" to easily increase the font mark up if needed-->
													<section>
														<p>Create an Event</p>
																	<div class="inputLine">
																		<p><i class="fa fa-gamepad"></i> &nbsp What game do you wish to schedule?<br/>
																			<input type="text" maxlength="50" placeholder="exp: Rocket League" ></p>
																		<p><i class="fa fa-calendar-o"></i> &nbsp Tell us a date<br/>
																			<input type="text" id ="datepicker" maxlength="50" placeholder=" Date"></p>
																		<p><i class="fa fa-clock-o"></i> &nbsp Time you want to play<br/>
																			<input type="time" maxlength="50" placeholder=" Time"></p>
																		<p><i class="fa fa-user"></i> &nbsp Number of players needed<br/>
																			<input type="number" min="1" max="64"></p>
																		<p><i class="fa fa-comments-o"></i> &nbsp Notes about your event<br/>
																			<textarea name="message" id="message" placeholder=" exp: Looking for some new team mates to play through Rocket Leagues 3v3 mode. Must have a mic!" rows="6" required></textarea></p>
																		<button class="button icon fa-cogs" id="signupBtn">Create Event!</button>
																	</div>
													</section>
												</section>
											</div>
									</div>
								
									<div class="6u">
									
										<!-- Sidebar 1 -->
											<div id="content">

												<section class="box style1"> <!-- *remember to add class "main" to easily increase the font mark up if needed-->
													<p>Edit Upcoming Event(s)</p>
																	<div id="createNewEvent">
																		<p><i class="fa fa-pencil"></i> &nbsp Click on your events to perform edits</p>
																			<ul>
																				<li><a href="">Diablo 3 Reaper of Souls 11-17-2015 7:30 PM EST</a></li>
																				<li><a href="">Splatoon 11-22-2015 11:34 AM EST</a></li>
																				<li><a href="">Street Fighter V 08-30-2015 6:45 PM EST</a></li>
																				<li><a href="">Destiny 09-01-2015 7:45 PM EST</a></li>
																				<li><a href="">Rocket League 09-11-2015 6:45 PM EST</a></li>
																			</ul>
																	</div>
												</section><br/>

											</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

		<!-- Footer Wrapper -->
			<?php include("footer.php"); ?>
		<!-- Footer Wrapper -->

	</body>
</html>