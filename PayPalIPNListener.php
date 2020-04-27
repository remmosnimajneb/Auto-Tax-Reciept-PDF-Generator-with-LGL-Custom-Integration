<?php 
/********************************
* Project: Auto Tax Reciept PDF Generator with LGL Custom Integration
* Code Version: 1.2
* Author: Benjamin Sommer (BenSommer.net) for The Berman Consulting Group (BermanGroup.com)
* Theme: `Hypothesis` by AJ at Pixelarity.com
* @Description: Allow a manual add of a donation via HTML Form and automatically generate and send a Tax Rcpt and send donation to LGL
* #FlattenTheCurve #COVID-19
***************************************************************************************/

require_once("Config.php");

// 0. Use PayPal Listener Namespace and start a new IPN
namespace Listener;
require('PaypalIPN.php');
use PaypalIPN;
$ipn = new PaypalIPN();


// 1. Include files for the PDF Generator
	// PDF Generater Autoload
	require_once 'dompdf/autoload.inc.php';

	// Reference the Dompdf namespace
	use Dompdf\Dompdf;
	use Dompdf\Options;

	// Instantiate and use the dompdf class
	$options = new Options();
	$options->setIsRemoteEnabled(true);
	$dompdf = new Dompdf($options);

	// I'll explain after
	$sendEmail = false;

// Get the Last ID of the Reciept
	// We store the last ID in a TXT file and then increment it each run
	$rcptid = 0;
    $fh = fopen('LastRecieptID.txt','r');
	while ($line = fgets($fh)) {
	  $rcptid = $line;
	}
	fclose($fh);

// 2. Generate the PDF!

//$ipn->useSandbox(); // Uncomment if using the Sandbox Testing!
$verified = $ipn->verifyIPN();

if ($verified) {

	//New Rcpt

		// 1. Generate PDF

		// If First Name is blank, use Buissness Name
	    if($_POST['first_name'] == ""){
				$_POST['first_name'] = $_POST['payer_business_name'];
		}

		// If Address is empty, we skip putting the fields in (otherwise it would output random ",")
		$AddressField = "";
		if($_POST['address_street'] != ""){
			$AddressField =  ucwords($_POST['address_street']) . ' <br />' . ucwords($_POST['address_city']) . ', ' . ucwords($_POST['address_state']) . ' ' . $_POST['address_zip'] . '<br />';
		}

		$RecieptHTML = '<!DOCTYPE HTML>
		  <html>
		    <head>
		      <style type="text/css">
		        body {
		          margin: 60px;
		          padding: 0;
		          background-color: white;
		          font-size: 11pt;
		          font-family: Times New Roman,Candara,Segoe,Segoe UI,Optima,Arial,sans-serif; 
		          margin-top:0px;
		        }

		        * {
		          box-sizing: border-box;
		          -moz-box-sizing: border-box;
		        }

		        .page {
		          width: 21cm;
		          min-height: 29.7cm;
		          padding: 2cm;
		          margin: 1cm auto;
		          border-radius: 5px;
		          background: white;
		        }

		        
		        @page {
		          size: A4;
		          margin: 0;
		        }

		        @media print {
		          .page {
		            margin: 0;
		            border: initial;
		            border-radius: initial;
		            width: initial;
		            min-height: initial;
		            box-shadow: initial;
		            background: initial;
		            page-break-after: always;
		          }
		        }
		      </style>
		    </head>
		    <body>
		      <div class="">
		        <div style="text-align: left;">
		        </div>
		        <div>
		          <br />
		          <br />
		          <br />
		          <br />
		          <p>
		            ' . ucwords($_POST['FirstName']) . ' ' . ucwords($_POST['LastName']) . ' <br />
		            ' . $AddressField . '
		          </p>
		          <br />
					<p style="float:right">' . date('F d, Y') . '</p>
		          <br />
		          <br />
		          <p>Dear ' . ucwords($_POST['FirstName']) . ',</p>
		          <p>
					Thank you so much for your very generous donation of $' . $_POST['Amount'] . ' to ' . $CompanyName . '. 		          
		          <br /><br />
		            Sincerely,
		          </p>
		          <p>
		            <b>' . $CompanyName . '</b>
		          </p>
		          <hr style="height:3px;color:black;background-color:black;" />
		          <p style="text-align: center;"><strong><u>DONATION RECEIPT</strong></u></p>
		          <p><b>Date:</b> ' . date_format(date_create($_POST['GiftDate']), "m/d/Y") . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Amount:</b> $' . $_POST['Amount'] . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Receipt number:</b> ' . $rcptid . '</p>
		          <p><b>Recieved From:</b><br />' . ucwords($_POST['FirstName']) . ' ' . ucwords($_POST['LastName']) . '<br>' . $AddressField . '</p>		            
		          <br /><br />
		          <p style="text-align: center;font-size:12px;">
		          	' . $CompanyName . '<br />
		          	Federal ID Number: ' . $TaxID . '<br />
		          	Contributions are tax deductible<br />
		          	<b>No goods or services have been provided for this contribution</b><br />
		          	<b>Please retain this receipt for your tax records.</b>
		          </p>
		        </div>
		      </div>
		    </body>
		  </html>';

		  // 2. Make PDF
		$dompdf->set_option('isHtml5ParserEnabled', true);
	    $dompdf->setPaper('A4', 'portrait');

	   	$dompdf->load_html($RecieptHTML);

	    $dompdf->render();
	    $output = $dompdf->output();

	  // 3. Dump PDF
	    file_put_contents($LocalRecieptsPath . '/' .  $RecieptPrefix . '/' .  $rcptid . '.pdf', $output);

	  
	  // Finally - we send the email
	  	// Now this flag was added since the USE for PHPMailer needs to be OUTSIDE all if's and funct() 
	  	// so we set it as true AFTER generating PDF to ensure if an error goes here we don't send a blank email
	    $sendEmail = true;

}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if($sendEmail){

	$msg = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
			<html style=\"width:100%;font-family:arial, 'helvetica neue', helvetica, sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;padding:0;Margin:0;\">
			 <head> 
			  <meta charset=\"UTF-8\"> 
			  <meta content=\"width=device-width, initial-scale=1\" name=\"viewport\"> 
			  <meta name=\"x-apple-disable-message-reformatting\"> 
			  <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\"> 
			  <meta content=\"telephone=no\" name=\"format-detection\"> 
			  <title>Drisha Tax Rcpt</title> 
			  <!--[if (mso 16)]>
			    <style type=\"text/css\">
			    a {text-decoration: none;}
			    </style>
			    <![endif]--> 
			  <!--[if gte mso 9]><style>sup { font-size: 100% !important; }</style><![endif]--> 
			  <style type=\"text/css\">
			@media only screen and (max-width:600px) {p, ul li, ol li, a { font-size:16px!important; line-height:150%!important } h1 { font-size:30px!important; text-align:center; line-height:120%!important } h2 { font-size:26px!important; text-align:center; line-height:120%!important } h3 { font-size:20px!important; text-align:center; line-height:120%!important } h1 a { font-size:30px!important } h2 a { font-size:26px!important } h3 a { font-size:20px!important } .es-menu td a { font-size:16px!important } .es-header-body p, .es-header-body ul li, .es-header-body ol li, .es-header-body a { font-size:16px!important } .es-footer-body p, .es-footer-body ul li, .es-footer-body ol li, .es-footer-body a { font-size:16px!important } .es-infoblock p, .es-infoblock ul li, .es-infoblock ol li, .es-infoblock a { font-size:12px!important } *[class=\"gmail-fix\"] { display:none!important } .es-m-txt-c, .es-m-txt-c h1, .es-m-txt-c h2, .es-m-txt-c h3 { text-align:center!important } .es-m-txt-r, .es-m-txt-r h1, .es-m-txt-r h2, .es-m-txt-r h3 { text-align:right!important } .es-m-txt-l, .es-m-txt-l h1, .es-m-txt-l h2, .es-m-txt-l h3 { text-align:left!important } .es-m-txt-r img, .es-m-txt-c img, .es-m-txt-l img { display:inline!important } .es-button-border { display:block!important } a.es-button { font-size:20px!important; display:block!important; border-width:10px 0px 10px 0px!important } .es-btn-fw { border-width:10px 0px!important; text-align:center!important } .es-adaptive table, .es-btn-fw, .es-btn-fw-brdr, .es-left, .es-right { width:100%!important } .es-content table, .es-header table, .es-footer table, .es-content, .es-footer, .es-header { width:100%!important; max-width:600px!important } .es-adapt-td { display:block!important; width:100%!important } .adapt-img { width:100%!important; height:auto!important } .es-m-p0 { padding:0px!important } .es-m-p0r { padding-right:0px!important } .es-m-p0l { padding-left:0px!important } .es-m-p0t { padding-top:0px!important } .es-m-p0b { padding-bottom:0!important } .es-m-p20b { padding-bottom:20px!important } .es-mobile-hidden, .es-hidden { display:none!important } .es-desk-hidden { display:table-row!important; width:auto!important; overflow:visible!important; float:none!important; max-height:inherit!important; line-height:inherit!important } .es-desk-menu-hidden { display:table-cell!important } table.es-table-not-adapt, .esd-block-html table { width:auto!important } table.es-social { display:inline-block!important } table.es-social td { display:inline-block!important } }
			#outlook a {
				padding:0;
			}
			.ExternalClass {
				width:100%;
			}
			.ExternalClass,
			.ExternalClass p,
			.ExternalClass span,
			.ExternalClass font,
			.ExternalClass td,
			.ExternalClass div {
				line-height:100%;
			}
			.es-button {
				mso-style-priority:100!important;
				text-decoration:none!important;
			}
			a[x-apple-data-detectors] {
				color:inherit!important;
				text-decoration:none!important;
				font-size:inherit!important;
				font-family:inherit!important;
				font-weight:inherit!important;
				line-height:inherit!important;
			}
			.es-desk-hidden {
				display:none;
				float:left;
				overflow:hidden;
				width:0;
				max-height:0;
				line-height:0;
				mso-hide:all;
			}
			</style> 
			 </head> 
			 <body style=\"width:100%;font-family:arial, 'helvetica neue', helvetica, sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;padding:0;Margin:0;\"> 
			  <div class=\"es-wrapper-color\" style=\"background-color:#F6F6F6;\"> 
			   <!--[if gte mso 9]>
						<v:background xmlns:v=\"urn:schemas-microsoft-com:vml\" fill=\"t\">
							<v:fill type=\"tile\" color=\"#f6f6f6\"></v:fill>
						</v:background>
					<![endif]--> 
			   <table class=\"es-wrapper\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;padding:0;Margin:0;width:100%;height:100%;background-repeat:repeat;background-position:center top;\"> 
			     <tr style=\"border-collapse:collapse;\"> 
			      <td valign=\"top\" style=\"padding:0;Margin:0;\"> 
			       <table class=\"es-content\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;\"> 
			         <tr style=\"border-collapse:collapse;\"> 
			          <td align=\"center\" style=\"padding:0;Margin:0;\"> 
			           <table class=\"es-content-body\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:transparent;\" width=\"600\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\"> 
			             <tr style=\"border-collapse:collapse;\"> 
			              <td align=\"left\" style=\"Margin:0;padding-top:20px;padding-bottom:20px;padding-left:20px;padding-right:20px;\"> 
			               <!--[if mso]><table width=\"560\" cellpadding=\"0\" cellspacing=\"0\"><tr><td width=\"356\" valign=\"top\"><![endif]--> 
			               <table class=\"es-left\" cellspacing=\"0\" cellpadding=\"0\" align=\"left\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;float:left;\"> 
			                 <tr style=\"border-collapse:collapse;\"> 
			                  <td class=\"es-m-p0r es-m-p20b\" width=\"356\" valign=\"top\" align=\"center\" style=\"padding:0;Margin:0;\"> 
			                   <table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;\"> 
			                     <tr style=\"border-collapse:collapse;\"> 
			                      <td style=\"padding:0;Margin:0;display:none;\" align=\"center\"></td> 
			                     </tr> 
			                   </table></td> 
			                 </tr> 
			               </table> 
			               <!--[if mso]></td><td width=\"20\"></td><td width=\"184\" valign=\"top\"><![endif]--> 
			               <table cellspacing=\"0\" cellpadding=\"0\" align=\"right\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;\"> 
			                 <tr style=\"border-collapse:collapse;\"> 
			                  <td width=\"184\" align=\"left\" style=\"padding:0;Margin:0;\"> 
			                   <table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;\"> 
			                     <tr style=\"border-collapse:collapse;\"> 
			                      <td style=\"padding:0;Margin:0;display:none;\" align=\"center\"></td> 
			                     </tr> 
			                   </table></td> 
			                 </tr> 
			               </table> 
			               <!--[if mso]></td></tr></table><![endif]--></td> 
			             </tr> 
			           </table></td> 
			         </tr> 
			       </table> 
			       <table class=\"es-content\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;\"> 
			         <tr style=\"border-collapse:collapse;\"> 
			          <td align=\"center\" style=\"padding:0;Margin:0;\"> 
			           <table class=\"es-content-body\" width=\"600\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#ffffff\" align=\"center\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:#FFFFFF;\"> 
			             <tr style=\"border-collapse:collapse;\"> 
			              <td align=\"left\" style=\"padding:0;Margin:0;padding-top:20px;padding-left:20px;padding-right:20px;\"> 
			               <table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;\"> 
			                 <tr style=\"border-collapse:collapse;\"> 
			                  <td width=\"560\" valign=\"top\" align=\"center\" style=\"padding:0;Margin:0;\"> 
			                   <table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;\"> 
			                     <tr style=\"border-collapse:collapse;\"> 
			                     </tr> 
			                     <tr style=\"border-collapse:collapse;\"> 
			                      <td align=\"left\" style=\"padding:0;Margin:0;padding-top:20px;\"><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333;\">Dear " . ucwords($_POST['FirstName']) . ",</p><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333;\"><br>Thank you so much for your very generous donation to " . $CompanyName . ".</p><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333;\"><br></p><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333;\"><br>Attached is the receipt for your donation.</p><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333;\"><br></p><p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-size:14px;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333;\">Sincerely,</p></td> 
			                     </tr> 
			                   </table></td> 
			                 </tr> 
			                 <tr style=\"border-collapse:collapse;\"> 
			                  <td class=\"es-m-p0r es-m-p20b\" width=\"560\" valign=\"top\" align=\"center\" style=\"padding:0;Margin:0;\"> 
			                   <table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;\"> 
			                     <tr style=\"border-collapse:collapse;\"> 
			                     </tr> 
			                     <tr style=\"border-collapse:collapse;\"> 
			                     </tr> 
			                   </table></td> 
			                 </tr> 
			               </table></td> 
			             </tr> 
			           </table></td> 
			         </tr> 
			       </table> 
			       <table class=\"es-footer\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;background-color:transparent;background-repeat:repeat;background-position:center top;\"> 
			         <tr style=\"border-collapse:collapse;\"> 
			          <td align=\"center\" style=\"padding:0;Margin:0;\"> 
			           <table class=\"es-footer-body\" width=\"600\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:transparent;\"> 
			             <tr style=\"border-collapse:collapse;\"> 
			              <td align=\"left\" style=\"Margin:0;padding-top:20px;padding-bottom:20px;padding-left:20px;padding-right:20px;\"> 
			               <table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;\"> 
			                 <tr style=\"border-collapse:collapse;\"> 
			                  <td width=\"560\" valign=\"top\" align=\"center\" style=\"padding:0;Margin:0;\"> 
			                   <table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;\"> 
			                     <tr style=\"border-collapse:collapse;\"> 
			                      <td align=\"center\" style=\"padding:0;Margin:0;display:none;\"></td> 
			                     </tr> 
			                   </table></td> 
			                 </tr> 
			               </table></td> 
			             </tr> 
			           </table></td> 
			         </tr> 
			       </table> 
			       <table class=\"es-content\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;\"> 
			         <tr style=\"border-collapse:collapse;\"> 
			          <td align=\"center\" style=\"padding:0;Margin:0;\"> 
			           <table class=\"es-content-body\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:transparent;\" width=\"600\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\"> 
			             <tr style=\"border-collapse:collapse;\"> 
			              <td align=\"left\" style=\"padding:0;Margin:0;padding-left:20px;padding-right:20px;padding-bottom:30px;\"> 
			               <table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;\"> 
			                 <tr style=\"border-collapse:collapse;\"> 
			                  <td width=\"560\" valign=\"top\" align=\"center\" style=\"padding:0;Margin:0;\"> 
			                   <table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" style=\"mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;\"> 
			                     <tr style=\"border-collapse:collapse;\"> 
			                      <td style=\"padding:0;Margin:0;display:none;\" align=\"center\"></td> 
			                     </tr> 
			                   </table></td> 
			                 </tr> 
			               </table></td> 
			             </tr> 
			           </table></td> 
			         </tr> 
			       </table></td> 
			     </tr> 
			   </table> 
			  </div>  
			 </body>
			</html>";

	// Send Email
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

        //Recipients
        $mail->setFrom($FromEmail, $CompanyName);                         
        
        //Content
        $mail->isHTML(true); // Set email format to HTML
        
        $mail->Subject   = 'Thank you for your contribution!';
        $mail->addAddress($_POST['Email']);
        
        $mail->Body      = $msg;

        $mail->addStringAttachment( file_get_contents($LocalRecieptsPath . '/' .  $RecieptPrefix . $rcptid . ".pdf"), $RecieptPrefix . $rcptid . '.pdf' );

        $mail->Send();

        } catch (Exception $e) {
          //echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo . '<br />';
      };

      // Now increment the ID in the TXT file for next time
	file_put_contents('lastrcptid.txt', $rcptid+1);


	// Now we need to push to LGL
	
	// Format the Date
	$PaymentDate = date("Y-m-d", strtotime($_POST['payment_date']));

		
	$data = array(
		'Title' => $_POST['Title'],
		'FirstName' => $_POST['FirstName'],
		'LastName' => $_POST['LastName'],
		'Email' => $_POST['Email'],
		'Phone' => $_POST['Phone'],
		'StreetAddress' => $_POST['StreetAddr'],
		'City' => $_POST['City'],
		'State' => $_POST['State'],
		'ZIP' => $_POST['ZIP'],
		'Country' => $_POST['Country'],
		'PaymentAmount' => $_POST['Amount'],
		'GiftDate' => $_POST['GiftDate'],
		'DepositDate' => $_POST['DepositDate'],
		'PaymentMethod' => $_POST['PaymentMethod'],
		'PaymentNotes' => $_POST['PaymentNotes'] . "\nGenerated Tax Reciept URL: " . $PublicRecieptsPath . '/' . $LocalRecieptsPath . '/' .  $RecieptPrefix . $rcptid . ".pdf",
		'PaymentRefNumber' => $_POST['PaymentRefNumber'],
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

// Reply with an empty 200 response to indicate to paypal the IPN was received correctly.
header("HTTP/1.1 200 OK");
?>