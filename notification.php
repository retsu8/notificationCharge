<?php
//Connect to database for cahrgebacks
$mariadb = new mysqli("127.0.0.1", "druportal", "5v\"Z2.'J&F3aL^.&vOX", "druporta_tss_data");

//setup variables for messaging
$to = "wjp@frontlineprocessing.com";
$subject = "Dispute Notification(s) ".date('m/d/Y')." - MID:";
$body = "";
$headers = "From: donotreply@frontlineprocessing.com\r\n";
$headers .= "BCC: chargebacks@frontlineprocessing.com\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8";
$headers .= 'X-Mailer: PHP/' .phpversion();
$invalid_characters = array("$",",", "%", "#", "<", ">", "|");

// check if connected
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: (" . mysqli_connect_errno() . ") ";
    mysqli_free_result($result);
    mysqli_close($mariadb);
}
$builder = array("Processing Date", "MID","Merchant-Name","Tran-type","tran-identifier","amount","case-number","card-type","dbcr-indicator","reason-code","reason-desc","record-type","auth-code","card-number","reference-number","bin-ica","transaction-date","acquirer-reference");

//setup sql to grab needed information
$sql = 'select * from druporta_tss_data.notifications where notified != true';
$profile = mysqli_query($mariadb, "select * from druporta_tss_data.mrchprofile");
$result=mysqli_query($mariadb,$sql);
$notification = mysqli_fetch_all($result, MYSQLI_BOTH);
$result2 = mysqli_query($mariadb, 'SHOW COLUMNS FROM chargebacks') or die('cannot show columns from '.$table);
$tableHeader = mysqli_fetch_all($result2, MYSQLI_BOTH);
$mrchprofile = mysqli_fetch_all($profile, MYSQLI_BOTH);
$count = 0;

//build table variables for getting information
$table = "notifications";

//chargeback codes for all magor credit companies and there explanation
$chargebackCodes= array(
  "Visa"=> array("30"=>"SERVICES NOT PROVIDED OR MERCHANDISE NOT RECEIVED",
                  "41"=>"CANCELLED RECURRING TRANSACTION",
                  "53"=>"NOT AS DESCRIBED OF DEFECTIVE MERCHANDISE",
                  "57"=>"FRAUDULENT MULTIPLE TRANSACTIONS",
                  "60"=>"REQUESTED COPY ILLEGIBLE OR INVALID",
                  "62"=>"COUNTERFEIT TRANSACTION",
                  "70"=>"ACCOUNT NUMBER ON EXCEPTION FILE",
                  "71"=>"DECLINED AUTHORIZATION",
                  "72"=>"NO AUTHORIZATION",
                  "73"=>"EXPIRED CARD",
                  "74"=>"LATE PRESENTMENT",
                  "75"=>"CARDHOLDER DOES NOT RECOGNIZE TRANSACTION",
                  "76"=>"INCORRECT TRANSACTION CODE",
                  "77"=>"NON-MATCHING ACCOUNT NUMBER",
                  "79"=>"REQUESTED TRANSACTION INFORMATION NOT RECEIVED",
                  "80"=>"INCORRECT TRANSACTION AMOUNT OR ACCOUNT NUMBER",
                  "81"=>"FRAUDULENT TRANSACTION – CARD-PRESENT ENVIRONMENT",
                  "82"=>"DUPLICATE PROCESSING",
                  "83"=>"FRAUDULENT TRANSACTION – CARD-ABSENT ENVIRONMENT",
                  "85"=>"CREDIT NOT PROCESSED",
                  "86"=>"PAID BY OTHER MEANS",
                  "90"=>"SERVICES NOT RENDERED",
                  "93"=>"RISK INDENTIFICATION SERVICE",
                  "96"=>"TRANSACTION EXCEEDS LIMITED AMOUNT"),
  "MasterCard"=>array(
                  "1"=>"REQUESTED TRANSACTION DATA NOT RECEIVED",
                  "2"=>"REQUESTED INFORMATION ILLEGIBLE OR MISSING",
                  "7"=>"WARNING BULLETIN FILE",
                  "8"=>"REQUESTED/REQUIRED AUTHORIZATION NOT OBTAINED",
                  "12"=>"ACCOUNT NUMBER NOT ON FILE",
                  "31"=>"TRANSACTION AMOUNT DIFFERS",
                  "34"=>"DUPLICATE PROCESSING",
                  "35"=>"CARD NOT VALID OR EXPIRED",
                  "37"=>"NO CARDHOLDER AUTHORIZATION",
                  "40"=>"FRAUDULENT PROCESSING OF TRANSACTION",
                  "41"=>"CANCELED RECURRING TRANSCATION",
                  "42"=>"LATE PRESENTMENT",
                  "46"=>"CORRECT TRANSACTION CURRENCY CODE WAS NOT PROVIDED",
                  "47"=>"REQUESTED/REQUIRED AUTHORIZATION NOT OBTAINED/ FRAUD TRANSACTION",
                  "49"=>"QUESTIONABLE MERCHANT ACTIVITY",
                  "50"=>"CREDIT POSTED AS PURCHASE",
                  "53"=>"CARDHOLDER DISPUTE – DEFECTIVE AND/OR NOT AS DESCRIBED",
                  "54"=>"CARDHOLDER DISPUTE NOT ELSEWHERE CLASSIFIED (US ONLY)",
                  "55"=>"NONRECEIPT OF MERCHANDISE",
                  "57"=>"CARD-ACTIVATED PHONE TRANSACTION",
                  "59"=>"SERVICES NOT RENDERED",
                  "60"=>"CREDIT NOT PROCESSED",
                  "62"=>"COUNTERFIET TRANSACTION; MAGNETIC STRIPE POI FRAUD",
                  "63"=>"CARDHOLDER DOES NOT RECOGNIZE-POTENTIAL FRAUD"),
  "Discover"=>array(
                  "4762"=>"GOOD FAITH INVESTIGATION",
                  "4752"=>"DOES NOT RECOGNIZE",
                  "4541"=>"RECURRING PAYMENT",
                  "4863"=>"AUTHORIZATION NON COMPLIANCE",
                  "4586"=>"ALTERED AMOUNT",
                  "4751"=>"CASH ADVANCE DISPUTE",
                  "4550"=>"CREDIT / DEBIT POSTED INCORRECTLY",
                  "4594"=>"CANCELLED RESERVATION",
                  "4534"=>"DUPLICATE PROCESSING",
                  "4502"=>"ILLEGIBLE SALES DATA",
                  "4753"=>"INVALID CARDHOLDER NUMBER",
                  "4584"=>"MISSING SIGNATURE",
                  "4542"=>'LATE PRESENTATION',
                  "4554"=>"NOT CLASSIFIED",
                  "4563"=>"NON RESPONSE TO A TICKET REQUEST",
                  "4755"=>"NON-RECEIPT OF GOODS OR SERVICE",
                  "4553"=>"CARDHOLDER DISPUTES QUALITY OF GOODS OR SERVICE",
                  "8002"=>"CREDIT NOT PROCESSED",
                  "4757"=>"VIOLATION OF OPERATING REGULATIONS",
                  "7010"=>"FRAUD CARD PRESENT TRANSACTION",
                  "7030"=>"FRAUD CARD NOT PRESENT TRANSACTION",
                  "4754"=>"FRAUD NON RESPONSE TO A TICKET RETRIEVAL REQUEST"),
"Amex"=>array(
		  "A01"=>"CHARGE AMOUNT EXCEEDS AUTH. AMOUNT",
		  "A02"=>"NO VALID AUTH.",
		  "A08"=>"AUTH. APPROVAL EXPIRED",
		  "C02"=>"CREDIT NOT PROCESSED",
		  "C04"=>"GOODS/SERVICES RETURNED/REFUSED",
		  "C05"=>"GOODS/SERVICES CANCELLED",
		  "C08"=>"GOODS/SERVICES NOT RECEIVED/ONLY PARTIALLY RECEIVED",
		  "C14"=>"PAID BY OTHER MEANS",
		  "C18"=>"NO SHOW/ CARDEPOSIT CANCELLED",
		  "C28"=>"CANCELLED RECURRING BILLING",
		  "C31"=>"GOODS/SERVICES NOT AS DESCRIBED",
		  "C32"=>"GOODS/SERVICES DAMAGED/DEFECTIVE",
		  "F10"=>"MISSING IMPRINT",
		  "F14"=>"MISSING SIGNATURE",
		  "F24"=>"NO CARD MEMBER AUTH.",
		  "F29"=>"CARD NOT PRESENT",
		  "F30"=>"EMV-COUNTERFEIT",
		  "F31"=>"LOST/STOLEN",
		  "R03"=>"INSUFFICIENT REPLY",
		  "R13"=>"NO REPLY",
		  "M01"=>"CHARGEBACK AUTH.",
		  "P01"=>"UNASSIGNED CARD NUMBER",
		  "P03"=>"CREDIT PROCESSED AS CHARGE",
		  "P04"=>"CHARGE PROCESSED AS CREDIT",
		  "P05"=>"INCORRECT CHARGE AMOUNT",
		  "P07"=>"LATE SUBMISSION",
		  "P08"=>"DUPLICATE CHARGE",
		  "P22"=>"NON-MATCHING ACCOUNT NUMBER",
		  "P23"=>"CURRENT DISCREPANCY",
		  "FR2"=>"FRAUD FULL RECOURSE PROGRAM",
		  "FR4"=>"IMMEDIATE CHARGEBACK PROGRAM",
	     "FR6"=>"PARTIAL IMMEDIATE CHARGEBACK PROGRAM"
),
"RecCode"=>array(
                  "1"=>"VISA PENDING CB",
                  "15"=>"MASTERCARD PENDING CB",
                  "27"=>"DEBIT",
                  "22"=>"CREDIT",
                  "2"=>"VISA MERCHANT REPRESENTMENT",
                  "25"=>"MASTERCARD MERCHANT REPRESENTMENT")
);

//build an array to hold all notifications for multiple notifications per merchant, so they get a table instead of a ton of emails
$simpleArray=array();
$email= array();
$i=0;

chdir('pdfParser');
echo "My current dir is".getcwd();
foreach( $notification as $notify ) {
  $merchant ="";
  //print_r($notify);
  $reason = explode(",",$notify[block]);
  foreach($mrchprofile as $mrch){
    if(array_search($notify[MID], $mrch)){
      $merchant = $mrch;
      print_r($merchant);
      break;
    }
  }
  if(in_array($notify[MID], $simpleArray)){
    array_push($simpleArray[$notify[MID]][$i], $reason);
    $i++;
  }
  else{
    $email[$notify[email]] = $notify[MID];
    $simpleArray[$notify[MID]][$i] = $reason;
    $i++;}
  $merchant['street'] = str_replace($invalid_characters, " ", $merchant['street']);
  $buildRuby = '"'.$reason[7].'","'.$reason[5].'","'.$merchant['merchant-name'].'","'.$merchant['street'].'","'.$merchant['city'].'","'.$merchant['state'].'","'.$merchant['zip'].'","'.$reason[5].'","'.$reason[5].'","'.$reason[11].'","'.$chargebackCodes[$reason[7]][$reason[9]].'","'.''.'","'.$reason[sizeof($reason)-2].'","'.$reason[13].'","'.$reason[5].'","'.$reason[sizeof($reason)-1].'","'.''.'","'.$reason[4].'","'.$reason[11].'"';
  echo $buildRuby;
  shell_exec('ruby pdfAddition.rb '.$buildRuby);
}$i=0;

//print_r($email);

//print_r($simpleArray);



//build message for mail and send it

echo "Going to add things to mail.";

foreach( $simpleArray as $notify ) {

  //setup interactive bootstrap email enviorment for email compatability across browsers

  //---NOTICE:DO NOT ADD JAVASCRIPT TO THIS IT WILL BE MARKED UNDER SPAM AND KILLED

  $body='<!DOCTYPE html><meta name="viewport" content="width=device-width, initial-scale=1"><html lang="eng"><body><!-- Latest compiled and minified CSS -->

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<!-- Optional theme -->

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">';

  $body.="<h3>Dispute Notification(s) ".date('m/d/Y')."</h3>";//ADD mid here later

  $MID="";

  //Build class for mysql rows table and grab headers from chargebacks

  if(mysqli_num_rows($result2)) {

    $body.='<div class="table-responsive">';

  	$body.= '<table cellpadding="0" cellspacing="0" class="db-table, table, table-striped, table-condensed" style="width:100%;  border: 1px solid black;">';

		$body.='<tr style="border: 1px solid black; text-align:center;">';

    foreach ($tableHeader as $key => $value) {

      if ($value[Field] == "acquirer-reference"){

        next($tableHeader);

      }

      else{

        $body.='<td style="border: 1px solid black;">'.ucwords(str_replace('-', ' ',$value[Field])).'</td>';}

    }

		$body.='</tr>';



    foreach($notify as $value){

      $body.='<tr style="border: 1px solid black;text-align:center;">';

      //Get chargeback code for table convert

      $creditDefinition = $chargebackCodes[$value[7]][$value[9]];

      $recordType = $chargebackCodes['RecCode'][$value[11]];

      //get MID for merchant email name

      $MID=$value[1];

      //Flip me on to tell merchants-------------------------------------------------------------------------------------------------------------------

      //$to=email[$value[1]];

      echo "This is my mid for stuff: ".$MID;

      //insert period into amount for money conversion

      $partOne = substr($value[5],0, strlen($value[5])-2);

      $partTwo = substr($value[5],strlen($value[5])-2, strlen($value[5]));

      $money = $partOne.".".$partTwo;

      $money= floatval($money);

      //build table for the republic

      foreach(array_slice($value, 0,-1) as $key=>$cell) {

          if($cell == $value[9]){

            //Change definition for reason code to understandable change

    			  $body.= '<td style="border: 1px solid black;">'.$cell.'-'.$creditDefinition.'</td>';

          }elseif ($cell == $value[5]) {

            $body.= '<td style="border: 1px solid black;">'.money_format('%i',$money).'</td>';

          }elseif ($cell == $value[11]) {

            $body.= '<td style="border: 1px solid black;">'.$cell.'-'.$recordType.'</td>';

          }else{

    			$body.= '<td style="border: 1px solid black;">'.$cell.'</td>';

          }

  		}

      $body.= '</tr>';

    }

  	$body.= '</table><br />';

    $body.='</div>';

  	$body.= '<p>For further information about this Dispute Notification please go to <a href="http://dashboard.paymentportal.us">http://dashboard.paymentportal.us</a></p><br />';

  }

  $body.='</body></html>';

  echo "Grabbed new people to notify \r\n";

  if(mail ($to, $subject.$MID, $body, $headers)){

    echo ("<p>Email successfully sent!<p>");

  }

  else {

    echo ("<p> Filed delivery");

  }

  $count++;

}

mysqli_free_result($result);

mysqli_close($mariadb);

?>
