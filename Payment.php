<?php 
/********************************
* Project: Auto Tax Reciept PDF Generator with LGL Custom Integration
* Code Version: 2.0
* Author: Benjamin Sommer (BenSommer.net) for The Berman Consulting Group (BermanGroup.com)
* Theme: `Hypothesis` by AJ at Pixelarity.com
* #FlattenTheCurve #COVID-19
***************************************************************************************/


	/* 
	* Actually make a reciept and send it 
	*/

	/* 0. Include Functions */
		require_once('Functions.php');

	/* 1A. Include DomPDF */
		require 'vendor/autoload.php';

		// Reference the Dompdf namespace
		use Dompdf\Dompdf;
		use Dompdf\Options;

		// Instantiate and use the DomPDF class
		$options = new Options();
		$options->setIsRemoteEnabled(true);
		$DomPDF = new Dompdf($options);


	/* 1B. Get next Reciept ID */
		$RecieptIDNumber = GetNextRecieptID();

	/* 2. Now - let's setup the Tax Reciept or Ack Template - in the LangTemplates/ Directory */

		if(isset($_POST['GenerateLetterType']) && $_POST['GenerateLetterType'] == "AckLetter"){

			/* ACK Template */
				if(!file_exists(dirname(__FILE__) . '/LangTemplates/ACK-Template-' . $_POST['mc_currency'] . '.php')){
					require_once(dirname(__FILE__) . '/LangTemplates/ACK-Template-Default.php');
				} else {
					require_once(dirname(__FILE__) . '/LangTemplates/ACK-Template-' . $_POST['mc_currency'] . '.php');
				}

		} else {

			/* Reciept */
			if(!file_exists(dirname(__FILE__) . '/LangTemplates/RCPT-Template-' . $_POST['mc_currency'] . '.php')){
				require_once(dirname(__FILE__) . '/LangTemplates/RCPT-Template-Default.php');
			} else {
				require_once(dirname(__FILE__) . '/LangTemplates/RCPT-Template-' . $_POST['mc_currency'] . '.php');
			}

		}


	/* 3. Make the PDF */
		$DomPDF->set_option('isHtml5ParserEnabled', true);
	    $DomPDF->setPaper('A4', 'portrait');
	    $DomPDF->load_html($RecieptHTML, 'UTF-8');
	    $DomPDF->render();
	    $Output = $DomPDF->output();
	    file_put_contents(dirname(__FILE__) . '/' . $LocalRecieptsPath . '/' . $RecieptPrefix . $RecieptIDNumber . '.pdf', $Output);

	/* 4. Send the email */

		/* PHPMailer Headers */
		use PHPMailer\PHPMailer\PHPMailer;
		use PHPMailer\PHPMailer\Exception;
		require 'PHPMailer/src/Exception.php';
		require 'PHPMailer/src/PHPMailer.php';
		require 'PHPMailer/src/SMTP.php';

		/* Get the email Template */
			require_once('EmailTemplate.php');

		/* Send Email */
		try{
	        $mail = new PHPMailer();
	        $mail->SMTPDebug = 0;
	        $mail->isSMTP();
	        $mail->Host = $SMTP_Host;                     
	        $mail->SMTPAuth = $SMTP_Auth;
	        $mail->Username = $SMTP_Username;
	        $mail->Password = $SMTP_Password;
	        $mail->SMTPSecure = $SMTP_Security;
	        $mail->Port = $SMTP_Port;

	        /* Recipients */
	        $mail->setFrom($FromEmail, $CompanyName);                         
	        $mail->addAddress($_POST['payer_email']);
	        
	        /* Add Addtl emails */
	        foreach ($AdditionalEmails as $Email) {
	        	 $mail->addCC($Email);
	        }

	        $mail->isHTML(true); // Set email format to HTML

	        $mail->Subject = 'Thank you for your contribution!';
	        $mail->Body = $EmailTemplate;
	        $mail->addStringAttachment( file_get_contents(dirname(__FILE__) . '/' . $LocalRecieptsPath . '/' . $RecieptPrefix . $RecieptIDNumber . '.pdf'), $RecieptPrefix . $RecieptIDNumber . '.pdf' );
	        $mail->Send();
	        } catch (Exception $e) {
	          //echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo . '<br />';
	      };

	/* Finally, handle LGL */

		$PaymentNotes = "";

		/* Check we are sending to LGL */
		if(LGL_INTEGRATION_ENABLED){

			/* If not USD we need to convert to USD for LGL */
				if($_POST['mc_currency'] != "USD"){

					/* Add Notes for conversion currency */
					$PaymentNotes .= "Original Donation Currency: " . $_POST['mc_currency'] . "."; 
					$PaymentNotes .= "Original Donation Amount: " . $_POST['mc_gross'] . ".";

					/* Convert */
					$Rate = "LGL_RATE_CONV_" . $_POST['mc_currency'];
					$Operator = "LGL_RATE_CONV_" . $_POST['mc_currency'] . "_OPR";

					if($$Operator == "Divide"){
						$_POST['mc_gross'] = $_POST['mc_gross'] / $$Rate;
					} else if($$Operator == "Multiplication"){
						$_POST['mc_gross'] = $_POST['mc_gross'] * $$Rate;
					}


					/* More notes */
					$PaymentNotes .= "Exchange Rate used to convert to USD: " . $$Rate . ".";
					$PaymentNotes .= "Donation Amount converted to USD: " . $_POST['mc_gross'] . ".";

				}

			// Format the Date
			$PaymentDate = date("Y-m-d", strtotime($_POST['payment_date']));

			/* 
			* Configure fields 
			* Most of the fields in Manual Run have the same name attr as PayPal so we can double fields
			* SOME fields in Manual Run don't exist in PayPal (Title), so:
			* -> Some we can just add, and if it's PayPal, it will NULL out and whatever
			* -> Some we need to check, well if this then otherwise this....
			*/

				if(empty($_POST['DepositDate'])){
					$DepositDate = $PaymentDate;
				} else {
					$DepositDate = $_POST['DepositDate'];
				}

				if(empty($_POST['PaymentMethod'])){
					$PaymentMethod = "PayPal - Currency: " . $_POST['mc_currency'];
				} else {
					$PaymentMethod = $_POST['PaymentMethod'];
				}

				if(empty($_POST['PaymentNotes'])){
					$PaymentNotes .= "Generated Tax Reciept URL: " . $PublicRecieptsPath . '/' . $RecieptPrefix . $RecieptIDNumber . '.pdf';
				} else {
					$PaymentNotes .= $_POST['PaymentNotes'] . ". Generated Tax Reciept URL: " . $PublicRecieptsPath . '/' . $RecieptPrefix . $RecieptIDNumber . '.pdf';
				}

			$data = array(
				'Title' => $_POST['Title'],
				'FirstName' => $_POST['first_name'],
				'LastName' => $_POST['last_name'],
				'Email' => $_POST['payer_email'],
				'Phone' => $_POST['Phone'],
				'StreetAddr' => $_POST['address_street'],
				'City' => $_POST['address_city'],
				'State' => $_POST['address_state'],
				'ZIP' => $_POST['address_zip'],
				'Country' => $_POST['address_country'],
				'PaymentAmount' => $_POST['mc_gross'],
				'GiftDate' => $PaymentDate,
				'DepositDate' => $DepositDate,
				'PaymentMethod' => $PaymentMethod,
				'PaymentNotes' => $PaymentNotes,
				'PaymentRefNumber' => $_POST['txn_id'],
				'CampaignName' => $_POST['CampaignName']
			);
				
				$options = array(
				    'http' => array(
				        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				        'method'  => 'POST',
				        'content' => http_build_query($data)
				    )
				);

			$context  = stream_context_create($options);
			$result = file_get_contents($LGLIPNURL, false, $context);
		}