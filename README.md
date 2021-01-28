
# Auto Tax Reciept PDF Generator with LGL Custom Integration

- Project: Auto Tax Reciept PDF Generator with LGL Custom Integration
- Code Version: 2.0
- Author: Benjamin Sommer (BenSommer.net) for The Berman Consulting Group (BermanGroup.com)
- GitHub: https://github.com/remmosnimajneb
- Theme Design by: `Hypothesis` by AJ at Pixelarity.com
- Licensing Information: CC BY-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)

## Table of Contents:
1. Overview
2. Requirements & Install Instructions
3. Multi-Currency Handling/Setup
4. PayPal Setup
5. LGL Setup
6. Change Log

## SECTION 1 - OVERVIEW

**New for version 2.0!**
Version 2.0, besides some nice code cleanup and changes, now includes support for non-USD donations, including per-currency templates!
Also has FULL support for PHP Version 8.0 - with upgrades to PHPMailer and DomPDF!

So this is a cool little script that is an Auto Tax Reciept PDF Generator for PayPal (and other donations) that also pops the info straight to LGL (Little Green Light) using DomPDF to generate a PDF and then PHPMailer to email it to the donor.

It also integrates with LGL (Little Green Light) to push the donation info there as well.

I'd recommend changing the Email template (I used Stripo - Stripo.email). The actual PDF itself I would recommend just changing it as the HTML is pretty simple and easy enough to change (Also it's preset with the right stuff for A4 paper etc.)

`#FlattenTheCurve / #Covid-19`

## SECTION 2 - REQUIREMENTS & INSTALL INSTRUCTIONS
	
Requirments:

- A web server (Local only access is ok, if your only running manual entries) with PDO type PHP Extention (Important!)
	- You also will need ability to connect to External SMTP via PHPMailer as well as ability to use file_get_contents which many Hosts disable
- PHP
- That's it

Aight, let's go! Let's install this thing already!!

1. Open the Config file (File: Functions.php) in your favorite text editor (h/t to mine Sublime Text 3) and fill out all the info, it's pretty straight forward
2. Move all the files to your public directory on the server (Can exclude this file, everything else required)
3. That's it! Open your browser to the directory you stuck this in and use admin, admin1234 as the default login

## SECTION 3 - Multi-Currency Handling/Setup
US Dollars are the default currency we assume. If you want to setup handling for other currencies you have two steps to take.
1. You need to add templates to the templates:
	A. /LangTemplates/RCPT-Template-CURRCODE.php for Receipts
	B.  /LangTemplates/ACK-Template-CURRCODE.php for Acknowledgments
Where the CURRCODE is the code for the currency 
(See codes here: https://developer.paypal.com/docs/api/reference/currency-codes/)

You can either just leave the template as is and or have completely different templates for each currency.
(If you DON'T want to change the templates per, you CAN leave the Default and it will fall back to that.)

2. The next step is handling the conversion rate. This is *mostly* for LGL if enabled, as LGL can ONLY handle payments in USD, so if we pay in Canadian Dollars we need to change that to USD. 
This can be handled in Functions.php

    $LGL_RATE_CONV_CURRCODE = 2;
    $LGL_RATE_CONV_CURRCODE_OPR = "Multiplication";	// Or "Division"

Where you change CURRCODE to the Codes as above.
Changing from Multiplication or Division for the OPR will allow for values more or less than 1 USD.

## SECTION 4 - PayPal Integration

So for PayPal you just need to set your IPN - Instant Payment Notifier to the URL of the PayPalIPNListener.php file (example.com/ipn/IPNNotifier/ipn-Listener.php), you may want to simply look at https://developer.paypal.com/docs/ipn/integration-guide/IPNSetup/#setting-up-ipn-notifications-on-paypal for more info.

## SECTION 5 - LGL Integrations

So your gonna need to make a new Custom Integration on LGL (Settings -> Integration Settings, Add new Integration)

Then for the custom fields - use these (You can add more, but these are preset):

- record_id
- Title
- first_name
- last_name
- payer_email
- Phone
- address_street
- address_city
- address_state
- address_zip
- address_country
- mc_gross
- payment_date
- DepositDate
- PaymentMethod
- txn_id
- PaymentNotes
- CampaignName

Then grab the URL it gives you and stick it in the Functions.php file.

Then as they come in, you need to manually "accept" them by going to the Integration Queue and approving the donations

## SECTION 6 - Change log

- Version 1.0 - Initial Code
- Version 1.2 - Cleanup for Github, initial commit
- Version 2.0 - Code cleanup, merge Manual Add and IPN to same function calls. Add support for Multi-Currency handling. Support for PHP Version 8.0.