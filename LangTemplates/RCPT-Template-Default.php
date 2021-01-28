<?php 
/********************************
* Project: Auto Tax Reciept PDF Generator with LGL Custom Integration
* Code Version: 2.0
* Author: Benjamin Sommer (BenSommer.net) for The Berman Consulting Group (BermanGroup.com)
* Theme: `Hypothesis` by AJ at Pixelarity.com
* #FlattenTheCurve #COVID-19
***************************************************************************************/

	/* Default USD ($) Acknowledgement Template */


		/* Handle Variables */
 		
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
		            ' . ucwords($_POST['first_name']) . ' ' . ucwords($_POST['last_name']) . ' <br />
		            ' . $AddressField . '
		          </p>
		          <br />
					<p style="float:right">' . date('F d, Y') . '</p>
		          <br />
		          <br />
		          <p>Dear ' . ucwords($_POST['first_name']) . ',</p>
		          <p>
					Thank you so much for your very generous donation of $' . $_POST['mc_gross'] . ' to ' . $CompanyName . '. 		          
		          <br /><br />
		            Sincerely,
		          </p>
		          <p>
		            <b>' . $CompanyName . '</b>
		          </p>
		          <hr style="height:3px;color:black;background-color:black;" />
		          <p style="text-align: center;"><strong><u>DONATION RECEIPT</strong></u></p>
		          <p><b>Date:</b> ' . date_format(date_create($_POST['payment_date']), "m/d/Y") . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Amount:</b> $' . $_POST['mc_gross'] . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Receipt number:</b> ' . $rcptid . '</p>
		          <p><b>Recieved From:</b><br />' . ucwords($_POST['first_name']) . ' ' . ucwords($_POST['last_name']) . '<br>' . $AddressField . '</p>		            
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