<?php 
/********************************
* Project: Auto Tax Reciept PDF Generator with LGL Custom Integration
* Code Version: 1.2
* Author: Benjamin Sommer (BenSommer.net) for The Berman Consulting Group (BermanGroup.com)
* Theme: `Hypothesis` by AJ at Pixelarity.com
* @Description: Allow a manual add of a donation via HTML Form and automatically generate and send a Tax Rcpt and send donation to LGL
* #FlattenTheCurve #COVID-19
***************************************************************************************/

/* Note -This utilizes: DomToPDF to make the PDF, and PHPMailer to email it, you should look into those for further options and customizations*/


/* Config File */

		// A little extra hash to the admin panel security
	$SecretHash = "20eea14472886b8ef3c8f9d9c2e061ff1ff40af93d08b39ea467307020ee8d6a";
		
		// Admin Panel Login Info
	$Username = "admin";
	$Password = "admin1234";

		// Company Info
	$CompanyName = "Sample Company Name";
	$TaxID = "";

		// Path to store RecieptS
	$PublicRecieptsPath = 'https://example.com';
	$LocalRecieptsPath = 'Reciepts';
		// How each Reciept starts (I.E. Donation_Receipt_11232.pdf)
	$RecieptPrefix = 'Donation_Receipt_';


		// LGL IPN URL
	$LGLIPNURL = "";

		// SMTP Info - See PHPMailer for more info!
	$SMTP_Host = "";
	$SMTP_Auth = true;
	$SMTP_Username = "";
	$SMTP_Password = "";
	$SMTP_Security = "";
	$SMTP_Port = "";
	$FromEmail = "";
	
?>