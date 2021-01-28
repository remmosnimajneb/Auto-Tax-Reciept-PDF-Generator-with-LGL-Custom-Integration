<?php 
/********************************
* Project: Auto Tax Reciept PDF Generator with LGL Custom Integration
* Code Version: 2.0
* Author: Benjamin Sommer (BenSommer.net) for The Berman Consulting Group (BermanGroup.com)
* Theme: `Hypothesis` by AJ at Pixelarity.com
* #FlattenTheCurve #COVID-19
***************************************************************************************/


	/*
	* IPN Listener - Handles a new IPN, verfies it and then runs Payment.php
	*/

	/* 1. Use PayPal Listener Namespace and start a new IPN */
		namespace Listener;
		require('PaypalIPN.php');
		use PaypalIPN;
		$ipn = new PaypalIPN();
		//$ipn->use_curl = FALSE; 
		$ipn->usePHPCerts();


	/* 2. Check if we're verified */
		//$ipn->useSandbox(); // Uncomment if using the Sandbox Testing!
		$verified = $ipn->verifyIPN();

	/* 3. If verified, let's generate the reciept and send it! */

		if($verified){
			require_once('../Payment.php');

		}

	/* 4. Reply with an empty 200 response to indicate to paypal the IPN was received correctly. */
	header("HTTP/1.1 200 OK");