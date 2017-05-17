<?php

$masterID = '0B7PSHsdd0u-CcThjazNVMnZ5Wms';
$folderID = '';

print_r($file);
//Build upload function for pdf
function upload($content, $name, $location)
{
    $fileMetadata = new Google_Service_Drive_DriveFile(array(
    'name' => $name,
    'mimeType' => 'application/pdf'));
    $content = file_get_contents($location);
    $upload = $driveService->files->create($fileMetadata, array(
    'data' => $content,
    'mimeType' => 'application/pdf',
    'uploadType' => 'multipart',
    'fields' => 'id'));
    printf("File ID: %s\n", $upload->id);
    return $upload->id;
}
//Create folders
function createFolder($name, $location)
{
    $fileMetadata = new Google_Service_Drive_DriveFile(array(
      'name' => $name,
      "parents" => array($location),
      'mimeType' => 'application/vnd.google-apps.folder'));
    $file = $driveService->files->create($fileMetadata, array(
      'fields' => 'id'));
    print_r($file);
      echo "I made it";
      print_r(error_get_last());
    printf("Folder ID: %s\n", $file->id);
    return $upload->id;
}

//Connect to database for cahrgebacks
$mariadb = new mysqli("merchdb.c0v9kpl8n2zi.us-west-2.rds.amazonaws.com", "merch_admin", "T7ToogA#36u#UWbV", "druporta_tss_data");
$chargDatabase = new mysqli("merchdb.c0v9kpl8n2zi.us-west-2.rds.amazonaws.com", "merch_admin", "T7ToogA#36u#UWbV", "chargebackNotifications");

if ($mariadb->connect_error) {
    echo "Failed to connect to MySQL: (" . $mariadb->connect_errno . ") " . $mariadb->connect_error;
    mysqli_close($mariadb);
}
if ($chargDatabase->connect_error) {
    echo "Failed to connect to MySQL: (" . $chargDatabase->connect_errno . ") " . $chargDatabase->connect_error;
    mysqli_close($chargDatabase);
}
mysqli_ssl_set($mariadb, null, "certs.pem", "certs.pem", null, null);
mysqli_ssl_set($chargDatabase, null, "certs.pem", "certs.pem", null, null);

//setup variables for messaging
$to = "wjp@frontlineprocessing.com";
$subject = "Dispute Notification(s) ".date('m/d/Y')." - MID:";
$body = "";
$headers = "From: donotreply@frontlineprocessing.com\r\n";
$headers .= "BCC: chargebacks@frontlineprocessing.com\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8";
$headers .= 'X-Mailer: PHP/' .phpversion();
$invalid_characters = array("$",",", "%", "#", "<", ">", "|");

$builder = array("Processing Date", "MID","Merchant-Name","Tran-type","tran-identifier","amount","case-number","card-type","dbcr-indicator","reason-code","reason-desc","record-type","auth-code","card-number","reference-number","bin-ica","transaction-date","acquirer-reference");

$result=mysqli_query($mariadb, 'select * from druporta_tss_data.notifications where `notified` != 0');
mysqli_query($mariadb, "update druporta_tss_data.notifications set notified=1 where notified = 0");
$notification = mysqli_fetch_all($result, MYSQLI_BOTH);
$result2 = mysqli_query($mariadb, 'SHOW COLUMNS FROM chargebacks') or die('cannot show columns from '.$table);
$tableHeader = mysqli_fetch_all($result2, MYSQLI_BOTH);
$mrchprofile = mysqli_fetch_all(mysqli_query($mariadb, "select * from druporta_tss_data.mrchprofile"), MYSQLI_BOTH);
$count = 0;
mysqli_free_result($result);
mysqli_free_result($result2);

//build table variables for getting information
$table = "notifications";

//chargeback codes for all magor credit companies and there explanation
$refcodes = 'select * from druporta_tss_data.chargeback_reference_codes';
$chargebackCodes = mysqli_fetch_all(mysqli_query($mariadb, $refcodes), MYSQLI_BOTH);

function rubybuild($merchant, $reason, $notify)
{
    print "Made it";
    $merchant['street'] = str_replace($invalid_characters, " ", $merchant['street']);
    $buildRuby ='"'.trim($reason[7]).','.$reason[5].','.$merchant['merchant-name'].','.$merchant['street'].','.$merchant['city'].','.$merchant['state'].','.$merchant['zip'].','.$reason[5].','.$reason[5].','.$reason[11].','.$chargebackCodes[$reason[7]][$reason[9]].','.''.','.$reason[16].','.$reason[13].','.$reason[5].','.$reason[17].','.''.','.$reason[14].','.$reason[11].'"';

    print_r($buildRuby);
    $time = DateTime::createFromFormat('Y-j-m', (string)$reason[16]);
    //echo $time -> format('Y-m-d');
    shell_exec('ruby pdfAddition.rb '.$notify[MID].' '.$buildRuby);
    shell_exec('drive push -no-prompt -destination chargebackPDF chargebackPDF/'.$notify[MID].'/'.$year.'/'.$month.'/card'.$reason[13].'reference'.$reason[17].'.pdf');
    $year = $time-> format('Y');
    $month = $time-> format('F');
    chdir('chargebackPDF');
    //print 'chargebackPDF/'.$notify[MID].'/'.$year.'/'.$month.'/card'.$reason[13].'reference'.$reason[17].'.pdf\n';
    $url = shell_exec('drive url chargebackPDF/'.$notify[MID].'/'.$year.'/'.$month.'/card'.$reason[13].'reference'.$reason[17].'.pdf');
    shell_exec('drive share -with-link chargebackPDF/'.$notify[MID].'/'.$year.'/'.$month.'/card'.$reason[13].'reference'.$reason[17].'.pdf');
    chdir('../');
    $url = explode(" ", $url);
    //$updateQueary = 'update notifications set url="'.trim($url[1]).'", notified="1" where MID like "'.trim($notify[MID]).'" and block like  "%'.$reason[13]."%".$reason[17].'%"';
    $updateQueary = 'select * from notifications where MID like "'.trim($notify[MID]).'" and block like  "%'.$reason[13]."%".$reason[17].'%"';
    if (mysqli_query($mariadb, $updateQueary)=== true) {
        echo "notification set and url created";
    } else {
        //echo $updateQueary;
      echo "Failed to update";
    }
    //print($url[1]);
    return $url[1];
}

//call to creat the current mid folder and year
function createMID($mid)
{
    $folderID = createFolder($mid, $masterID);
    $createFolder = 'INSERT into `midFolderID`(`mid`,`folderID`) VALUES('.$notify['MID'].','.$folderID.') ON DUPLICATE KEY UPDATE';
    mysqli_query($chargDatabase, $createFolder);
    return $folderID;
}
//call to create the year
function createYear($id, $year, $month)
{
    $yearID = createFolder($year, $id);
    $monthID = createFolder($month, $yearID);
    $createFolder = 'INSERT into `'.$mid.'-folderID`(`YEAR`,`'.date('M').'`) VALUES('.$yearID.','.$monthID.') ON DUPLICATE KEY UPDATE';
    mysqli_query($chargDatabase, $createFolder);
    return $monthID;
}
//build an array to hold all notifications for multiple notifications per merchant, so they get a table instead of a ton of emails
$simpleArray=array();
$email= array();
$i=0;

chdir('pdfParser');
//echo "My current dir is".getcwd();
foreach ($notification as $notify) {
    //print_r($notify);
    $merchant ="";
    $chargebacks = 'select * from druporta_tss_data.chargebacks where ID='.$notify['chargebackID'];
    $dispute = mysqli_fetch_all(mysqli_query($mariadb, $chargebacks), MYSQLI_BOTH);
    $reason= $dispute[0];
    //print_r($reason);
    //Get the merchant to cantact
    foreach ($mrchprofile as $mrch) {
        if (array_search($notify['MID'], $mrch)) {
            $merchant = $mrch;
            break;
        }
    }
    if (in_array($notify['MID'], $simpleArray)) {
        print "Made it to mid";
        print_r($simpleArray);
        if (count($reason) > 1) {
            $url = rubybuild($merchant, $reason, $notify);
            array_push($reason, $url);
          //print_r($reason);
        }
        array_push($simpleArray[$notify['MID']][$i], $reason);
        $i++;
    } else {
        //print "Made it to email";
        $email[$notify['email']] = $notify['MID'];
        $simpleArray[$notify['MID']] = $notify['MID'];
        $crTbMID = 'CREATE TABLE `'.$notify['MID'].'-folderID`(`date` DATE NOT NULL,`YEAR` VARCHAR(50), `JAN` VARCHAR(50), `FEB` VARCHAR(50),`MAR` VARCHAR(50),`APR` VARCHAR(50),`MAY` VARCHAR(50),`JUN` VARCHAR(50),`JUL` VARCHAR(50),`AUG` VARCHAR(50),`SEP` VARCHAR(50),`OCT` VARCHAR(50),`NOV` VARCHAR(50),`DEC` VARCHAR(50), UNIQUE KEY(year))';
        if (mysqli_query($chargDatabase, $crTbMID)) {
            print "New table crteated";
        } else {
            $findMID = 'Select * from chargebackNotifications.midFolderID where mid = '.$notify['MID'];
            $midFound = mysqli_fetch_all(mysqli_query($chargDatabase, $findMID), MYSQLI_BOTH);
            if (empty($midFound)) {
                $folderID = createFolder($notify['MID'], $masterID);
            } else {
                $findMonthID = 'Select * from `'.$notify['MID'].'-folderID` where YEAR(`date`) like YEAR("'.date("Y-01-01").'")';
                $success = mysqli_fetch_all(mysqli_query($chargDatabase, $findMonthID), MYSQLI_ASSOC);
                print_r($success);
                if (empty($success)) {
                    $monthID = createYear($folderID, date("Y"), date("M"));
                }
            }
            print("This is the success ".$success);
        }
        $crtFileID = 'CREATE TABLE `'.$notify['MID'].'-fileID(`Date` DATE NOT NULL, `name` VARCHAR(50), ID INT(11), fileID VARCHAR(50), MID VARCHAR(50))';
        if (mysqli_query($chargDatabase, $crtFileID)) {
            $folderID = createFolder($notify['MID']);
            $monthID = createYear($folderID, date("Y"), date("M"));
        }
        //print "\nThis is the url for new email\n".$url;
        if (count($reason) > 1) {
            print_r($reason);
            $url = rubybuild($merchant, $reason, $notify);
            array_push($reason, $url);
          //print_r($reason);
        }
        $simpleArray[$notify['MID']][$i] = $reason;
        $i++;
    }
}$i=0;

//print_r($email);

//print_r($simpleArray);



//build message for mail and send it

echo "Going to add things to mail.";

foreach ($simpleArray as $notify) {
    //print_r($notify);
  //setup interactive bootstrap email enviorment for email compatability across browsers

  //---NOTICE:DO NOT ADD JAVASCRIPT TO THIS IT WILL BE MARKED UNDER SPAM AND KILLED

  $body='<!DOCTYPE html><meta name="viewport" content="width=device-width, initial-scale=1"><html lang="eng"><body><!-- Latest compiled and minified CSS -->

  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

  <!-- Optional theme -->

  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">';

    $body.="<h3>Dispute Notification(s) ".date('m/d/Y')."</h3>";//ADD mid here later

  $MID="";

  //Build class for mysql rows table and grab headers from chargebacks
    $body.='<div class="table-responsive">';

    $body.= '<table cellpadding="0" cellspacing="0" class="db-table, table, table-striped, table-condensed" style="width:100%;  border: 1px solid black;">';

    $body.='<tr style="border: 1px solid black; text-align:center;">';

    foreach ($tableHeader as $key => $value) {
        //print_r($value);
        if ($value['Field'] == "acquirer-reference") {
            next($tableHeader);
        } else {
            $body.='<td style="border: 1px solid black;">'.ucwords(str_replace('-', ' ', $value['Field'])).'</td>';
        }
    }
    $body.='</tr>';

    foreach ($notify as $value) {
        //print_r($value);
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
        $partOne = substr($value[5], 0, strlen($value[5])-2);
        $partTwo = substr($value[5], strlen($value[5])-2, strlen($value[5]));
        $money = $partOne.".".$partTwo;
        $money= floatval($money);
          //build table for the republic
        foreach (array_slice($value, 0, -1) as $key=>$cell) {
            if ($cell == $value[9]) {
                //Change definition for reason code to understandable change
                $body.= '<td style="border: 1px solid black;">'.trim($cell).'-'.trim($creditDefinition).'</td>';
            } elseif ($cell == $value[5]) {
                $body.= '<td style="border: 1px solid black;">'.money_format('%i', $money).'</td>';
            } elseif ($cell == $value[11]) {
                $body.= '<td style="border: 1px solid black;">'.trim($cell).'-'.trim($recordType).'</td>';
            } elseif (empty($cell)) {
                $body.= '<td style="border: 1px solid black;"></td>';
            } else {
                $body.= '<td style="border: 1px solid black;">'.trim($cell).'</td>';
            }
        }

        $body.= '</tr>';
    }

    $body.= '</table><br />';

    $body.='</div>';

    $body.= '<p>For further information about this Dispute Notification please go to <a href="http://dashboard.paymentportal.us">http://dashboard.paymentportal.us</a></p><br />';

    mysqli_free_result($result2);

    $body.='<p>Disclaimer: You are receiving this notice as a value-added service provided by Frontline Processing. You should response to every chargeback and retrieval advice you receive by U.S. Mail, whether or not a chargeback appears on this report. Additionally, advice letters on this value-added service may be slightly different from the ones received via U.S. Mail, which may include additional documents from the card issuer. </p></body></html>';

    echo "Grabbed new people to notify \r\n";

    if (mail($to, $subject.$MID, $body, $headers)) {
        echo("<p>Email successfully sent!<p>");
    } else {
        echo("<p> Filed delivery");
    }

    $count++;
}


print_r(error_get_last());
mysqli_free_result($result);

mysqli_close($mariadb);
