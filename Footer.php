<!-- Footer Wrapper -->
<div id="footer-wrapper">
    <div class="container">
        <div class="row">
            <div class="12u">
                <!-- Footer -->
                <footer id="footer">
                    <div class="row">
                        <div class="4u">
                            <section class="newsletter">
                                <h2>Your Feedback is Important</h2>
                                <p>We want to make Player Unite your home. How would you improve the site? Drop us a line below.</p>
                                <a href="mailto:playerunite@gmail.com?subject=Player Unite Feedback" class="button icon fa-commenting">Comment</a>
                            </section>
                        </div>
			<div class="4u">
                            <section class="newsletter">
                                <h2>Help Expand Player Unite</h2>
                                <p>Your donations will help cover our server hosting and future expansion cost.</p>
				<form action="<?php echo $payPalButtonFormUrl; ?>" method="post" target="_top">
                                    <input type="hidden" name="cmd" value="_s-xclick">
                                    <input type="hidden" name="hosted_button_id" value="<?php echo $payPalDonationButtonId; ?>">
                                    <table style="margin-bottom:0px;">
					<tr><td><input type="hidden" name="on0" value="Donation Amounts">Donation Amounts</td></tr>
					<tr>
                                            <td>
						<select style="width: 10em;" name="os0">
                                                    <option value="1:">1: $25.00 USD</option>
                                                    <option value="2:">2: $5.00 USD</option>
                                                    <option value="3:">3: $10.00 USD</option>
                                                    <option value="4:">4: $50.00 USD</option>
                                                    <option value="5:">5: $100.00 USD</option>
                                                    <option value="6:">6: $200.00 USD</option>
                                                    <option value="7:">7: $500.00 USD</option>
						</select>
                                            </td>
					</tr>
                                    </table>
                                    <input type="hidden" name="currency_code" value="USD">
                                    <input type="image" style="width: 150px; background:#191d23;" src="<?php echo $payPalDonationButtonImgUrl; ?>" border="0" name="submit" 
					   alt="PayPal - The safer, easier way to pay online!" height="55" onclick="return DonateOnClick();">
                                    <img alt="" border="0" src="<?php echo $payPalPixelImgUrl; ?>" width="1" height="1">
				</form>
                            </section>
                       </div>
                       <div class="2u">
                            <section>
                                <h2>Quick Links</h2>
                                <ul class="style3">
                                    <li class="icon fa-question-circle">
                                        <a href="Faq.php" style="text-decoration:none;">&nbsp;FAQ</a>
                                    </li>
                                    <li class="icon fa-cubes">
                                        <a href="About.php" style="text-decoration:none;">&nbsp;About Us</a>
                                    </li>
                                </ul>
                            </section>
                       </div>
                       <div class="2u">
                            <section>
                                <h2>Contact Us</h2>
                                <ul class="style3">
                                    <li class="icon fa-envelope">
                                        <a href="mailto:playerunite@gmail.com?subject=General Support" style="text-decoration:none;">&nbsp;Email</a>
                                    </li>
                                    <li class="icon fa-twitter">
                                        <a href="https://twitter.com/PlayerUnite" target="_blank" style="text-decoration:none;">&nbsp;@PlayerUnite</a>
                                    </li>
                                </ul>
                            </section>
                       </div>
                    </div>
               </footer>
                <!-- Footer -->
                <!-- Copyright -->
                <div id="copyright">
                    &copy; <script>document.write(new Date().getFullYear());</script> Player Unite<br/> All Rights Reserved<br/>
                    <a href="TermsPri.php" style="text-decoration:none;">Terms & Conditions | Privacy Policy</a>
                </div>
                <!-- Copyright -->
            </div>
        </div>
    </div>
</div>
<!-- Footer Wrapper -->