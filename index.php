<?php 
/********************************
* Project: Auto Tax Reciept PDF Generator with LGL Custom Integration
* Code Version: 2.0
* Author: Benjamin Sommer (BenSommer.net) for The Berman Consulting Group (BermanGroup.com)
* Theme: `Hypothesis` by AJ at Pixelarity.com
* #FlattenTheCurve #COVID-19
***************************************************************************************/

/*
* Manually Run the Payment
*/

	/* Include Functions */
		require_once('Functions.php');

	/* 1. Start Session */
	session_start();
	
	/* 2. Now check login */
	if(isset($_POST['Username']) && isset($_POST['Password'])){
		if($_POST['Username'] == $Username && $_POST['Password'] == $Password){
			$_SESSION['IsLoggedIn'] = true;
			$_SESSION['LoggedInUserAuth'] = $SecretHash;
		} else {
			$_SESSION['IsLoggedIn'] == false;
			$_SESSION['LoggedInUserAuth'] == null;
		}
	}



	/* 3. If running, run */
	if($_POST['RunPayment'] == "true" && $_SESSION['LastPaymentHash'] != $_POST['PaymentHash'] && ($_SESSION['IsLoggedIn'] == true && $_SESSION['LoggedInUserAuth'] == $SecretHash)){

		require_once('Payment.php');

		$Message = "Payment Run Successful";

	}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title><?php echo $CompanyName; ?></title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<link rel="stylesheet" href="../assets/css/main.css?ver=8">
	</head>
	<body class="is-preload">
		<!-- Wrapper -->
			<div id="wrapper">
				<!-- Header -->
					<header id="header">
						<!-- Logo -->
							<div class="logo">
								<a href="#"><strong><?php echo $CompanyName; ?></strong></span></a>
							</div>
					</header>
					<section id="one" class="main alt">

						<div class="inner alt">
							<div class="content">
									<?php 
										// If User didn't login, we force a login, otherwise show the add form
										if($_SESSION['IsLoggedIn'] != true && $_SESSION['LoggedInUserAuth'] != $SecretHash){
									?>
										<h2>Please Login!</h2>
										<form action="index.php" method="POST">
											Username: <input type="text" name="Username" required="required"><br>
											Password: <input type="password" name="Password" required="required"><br>
											<input type="submit" name="submit" value="Login">
										</form>
									<?php
										} else {
											if(isset($Message)){
												echo $Message;
											}
									?>
										<form action="index.php" method="POST">
											<input type="hidden" name="RunPayment" value="true">
											<input type="hidden" name="PaymentHash" value="<?php echo bin2hex(random_bytes(32)); ?>">
											<h3>Constituent Information</h3>
											Title:
												<select name="Title">
													<option value="">Ignore</option>													
													<option value="Mr">Mr</option>
													<option value="Mrs">Mrs</option>
													<option value="Ms">Ms</option>
													<option value="Dr">Dr</option>
												</select><br>
											First Name:
												<input type="text" name="first_name"><br>
											Last Name:
												<input type="text" name="last_name"><br>
											Email:
												<input type="email" name="payer_email"><br>
											Phone:
												<input type="text" name="Phone"><br>
											Street Address:
												<input type="text" name="address_street"><br>
											City:
												<input type="text" name="address_city"><br>
											State:
												<input type="text" name="address_state"><br>
											ZIP:
												<input type="text" name="address_zip"><br>
											Country:
												<input type="text" name="address_country"><br>
											<hr>
											<h3>Payment Information</h3>
											Payment Amount:
											<br><i>Do NOT insert a $ or other currency markings, simply insert the amount as DDDD.CC (I.E. 50.00), Only insert in USD Amounts!</i>
												<input type="text" name="mc_gross"><br>
												<input type="hidden" name="mc_currency" value="USD">
											Gift Date:
											<br><i>Leave blank to use today's date</i>
												<input type="date" name="payment_date" value="<?php echo date("Y-m-d"); ?>"><br><br>
											Deposit Date:
											<i>Leave blank to use today's date</i>
												<input type="date" name="DepositDate" value="<?php echo date("Y-m-d"); ?>"><br>
											Payment Method:
												<input type="text" name="PaymentMethod"><br>
											Payment Ref #:
											<br><i>Check #'s or PayPal Confirmation ID's go here!</i>
												<input type="text" name="txn_id"><br>
											Payment Notes: 
												<textarea name="PaymentNotes"></textarea><br>
											Campaign:
												<input type="text" name="CampaignName">
												<br>
											Generate Letter Type:
												<select name="GenerateLetterType">
													<option value="TaxRcpt" selected="selected">Tax Receipt</option>
													<option value="AckLetter">Acknolegment Letter</option>
												</select><br>
											<input type="Submit" name="Submit" value="Run">
										</form>
									<?php
										}
									?>
							</div>
					</section>

				<footer id="footer">

						<div class="copyright">
							<p>&copy; <?php echo $CompanyName; ?>. All rights reserved.</p>
								<hr />
						</div>
					</footer>
			</div>
		<!-- Scripts -->
			<script src="../assets/js/jquery.min.js"></script>
			<script src="../assets/js/jquery.dropotron.min.js"></script>
			<script src="../assets/js/jquery.selectorr.min.js"></script>
			<script src="../assets/js/jquery.scrollex.min.js"></script>
			<script src="../assets/js/jquery.scrolly.min.js"></script>
			<script src="../assets/js/browser.min.js"></script>
			<script src="../assets/js/breakpoints.min.js"></script>
			<script src="../assets/js/util.js"></script>
			<script src="../assets/js/main.js"></script>
	</body>
</html>