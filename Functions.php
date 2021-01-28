<?php 
/********************************
* Project: Auto Tax Reciept PDF Generator with LGL Custom Integration
* Code Version: 2.0
* Author: Benjamin Sommer (BenSommer.net) for The Berman Consulting Group (BermanGroup.com)
* Theme: `Hypothesis` by AJ at Pixelarity.com
* #FlattenTheCurve #COVID-19
***************************************************************************************/


	/*
	* Functions and Variables
	*/

	/* A little extra hash to the admin panel security [Highly Recommended] */
		$SecretHash = "Your Secret Hash Here";
		
	/* Admin Panel Login Info (For Manual Donation) [Extremely Recommended] */
		$Username = "admin";
		$Password = "admin1234";

	/* Company Info */
		$CompanyName = "Sample Company";	// [Recommended]
		$TaxID = "XX-XXXXXX";				// [Recommended]

	/* Path to store RecieptS */
		$PublicRecieptsPath = 'https://example.com/donations/Reciepts';		// [Required]
		$LocalRecieptsPath = 'Reciepts';									// [Optional, based on above]

	/* How each Reciept starts (I.E. Donation_Receipt_11232.pdf */
		$RecieptPrefix = 'Donation_Receipt_';								// [Optional]

	/* Last Reciept ID Location (TXT) File on server */
		$LastRecieptLocation = dirname(__FILE__) . '/Assets/LastRecieptID.txt';		// [Don't change unless you are sure]

	/* Enable or Disable LGL Integration */
		DEFINE('LGL_INTEGRATION_ENABLED', TRUE);	// [Optional]							

	/* Since LGL only handles USD we need to convert any other rates */
		$LGL_RATE_CONV_CURRCODE = 2;						// [Optional]
		$LGL_RATE_CONV_CURRCODE_OPR = "Multiplication";		// [Required if using another currency]

	/* LGL IPN URL (See: https://help.littlegreenlight.com/article/456-use-custom-integrations-to-send-data-via-webhooks-to-lgl) */
		$LGLIPNURL = "";							// [Required if LGL Enabled]

	/* SMTP Information */							// [Required, always, unless you don't want emails]
		$SMTP_Host = "";
		$SMTP_Auth = true;
		$SMTP_Username = "";
		$SMTP_Password = "";
		$SMTP_Security = "ssl";
		$SMTP_Port = "465";
		$FromEmail = "";
		
		/* Additional addresses to be cc'ed (besides buyer email) [Optional] */
			$AdditionalEmails = array("example@example.com");


	/* System Functions (Don't touch) */

		/* 
		* Get Next Reciept ID 
		* @Return (int) next ID number to use
		*/
		function GetNextRecieptID(){
			
			global $LastRecieptLocation;

			/* Get the ID */
			$RecieptID = 0;
		    $fh = fopen($LastRecieptLocation,'r');
			while ($line = fgets($fh)) {
			  $RecieptID = $line;
			}
			fclose($fh);

			/* Increment the ID */
			file_put_contents($LastRecieptLocation, $RecieptID+1);

			/* Return the ID */
			return $RecieptID+1;

		}
