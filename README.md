# Auto Tax Reciept PDF Generator with LGL Custom Integration

- Project: Auto Tax Reciept PDF Generator with LGL Custom Integration
- Code Version: 1.2
- Author: Benjamin Sommer (BenSommer.net) for The Berman Consulting Group (BermanGroup.com)
- GitHub: https://github.com/remmosnimajneb
- Theme Design by: `Hypothesis` by AJ at Pixelarity.com
- Licensing Information: CC BY-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)

## Table of Contents:
1. Overview
2. Requirements & Install Instructions
3. PayPal Setup
4. LGL Setup

## SECTION 1 - OVERVIEW

So this is a cool little script that is an Auto Tax Reciept PDF Generator for PayPal (and other donations) that also pops the info straight to LGL (Little Green Light) using DomPDF to generate a PDF and then PHPMailer to email it to the donor.

It also integrates with LGL (Little Green Light) to push the donation info there as well.

I'd recommend changing the Email template (I used Stripo - Stripo.email). The actual PDF itself I would recommend just changing it as the HTML is pretty simple and easy enough to change (Also it's preset with the right stuff for A4 paper etc.)

`#FlattenTheCurve / #Covid-19`

## SECTION 2 - REQUIRMENTS & INSTALL INSTRUCTIONS
	
Requirments:

- A web server (Local only access is ok) with PDO type PHP Extention (Important!)
	- You also will need ability to connect to External SMTP via PHPMailer as well as ability to use file_get_contents which many Hosts disable
- PHP
- That's it

Aight, let's go! Let's install this thing already!!

1. Open the Config file (File: Config.php) in your favorite text editor (h/t to mine Sublime Text 3) and fill out all the info, it's pretty straight forward
4. Move all the files to your public directory on the server (Can exclude this file, everything else required)
5. That's it! Open your browser to the directory you stuck this in and use admin, admin1234 as the defualt login

## SECTION 3 - PayPal Integration

So for PayPal you just need to set your IPN - Instant Payment Notifier to the URL of the PayPalIPNListener.php file (example.com/ipn/PayPalIPNListener.php), you may want to simply look at https://developer.paypal.com/docs/ipn/integration-guide/IPNSetup/#setting-up-ipn-notifications-on-paypal for more info.

## SECTION 4 - LGL Integrations

So your gonna need to make a new Custom Integration on LGL (Settings -> Integration Settings, Add new Integration)

Then for the custom fields - use these (You can add more, but these are preset):

- record_id
- Title
- FirstName
- LastName
- Email
- Phone
- StreetAddress
- City
- State
- ZIP
- Country
- PaymentAmount
- GiftDate
- DepositDate
- PaymentMethod
- PaymentRefNumber
- PaymentNotes
- CampaignName

Then grab the URL it gives you and stick it in the Config.php file.

Then as they come in, you need to manaully "accept" them by going to the Integration Queue and approving the donations