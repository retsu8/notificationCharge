<?php
//phpinfo();
$mariadb = new mysqli("merchdb.c0v9kpl8n2zi.us-west-2.rds.amazonaws.com", "merch_admin", "T7ToogA#36u#UWbV", "statementData");
if ($mariadb->connect_error) {
    echo "Failed to connect to MySQL: (" . $mariadb->connect_errno . ") " . $mariadb->connect_error;
}

mysqli_ssl_set($mariadb, NULL,"certs.pem","certs.pem",NULL,NULL);

$undesirables = array("\n", "\r","\t","\e","\f",",");

 // qryMthDep1 StatementReport6101Frontline StatementReport6101FrontlineMNA tblCPDiscountEOM tblFlexFees
$path=getcwd();

$date = new DateTime();
$date->modify("last day of previous month");
echo $date->format("Y-m-d")."/n";

function _combine_array(&$row, $key, $header) {
  $row = array_combine($header, $row);
}
function compareheaders($maria, $csv){
  $prophead = [];
  array_push($prophead, "Date");
  foreach($csv as $row){
    switch($row){
      case "strMID":
        array_push($prophead, "MID");
        break;
      case "Name":
        array_push($prophead, "Name");
        break;
      case "Add":
        array_push($prophead, "Address");
        break;
      case "City":
        array_push($prophead, "City");
        break;
      case "State":
        array_push($prophead, "State");
        break;
      case "Zip":
        array_push($prophead, "Zip");
        break;
      case "strDDA1":
        array_push($prophead, "DDA1");
        break;
      case "strTR1":
        array_push($prophead, "TR1");
        break;
      case "TotalSalesCt":
        array_push($prophead, "TotalSalesCt");
        break;
      case "TotalRefundCt":
        array_push($prophead, "TotalRefundCt");
        break;
      case "TotalDebitCt":
        array_push($prophead, "TotalDebitCt");
        break;
      case "TotalCount":
        array_push($prophead, "TotalCount");
        break;
      case "TotalSalesVol":
        array_push($prophead, "TotalSalesVol");
        break;
      case "TotalRefundVol":
        array_push($prophead, "TotalRefundVol");
        break;
      case "DebitNetTotAmt":
        array_push($prophead, "DebitNetTotAmt");
        break;
      case "ReservePercent":
        array_push($prophead, "ReservePercent");
        break;
      case "HoldResBal":
        array_push($prophead, "HoldResBal");
        break;
      case "dblMonthlyFee":
        array_push($prophead, "dblMonthlyFee");
        break;
      case "dblDailyDiscTotal":
        array_push($prophead, "dblDailyDiscTotal");
        break;
      case "TotalChrgsForMth":
        array_push($prophead, "TotalChrgsForMth");
        break;
      case "Message":
        array_push($prophead, "Message");
        break;
      case "SortCode":
        array_push($prophead, "SortCode");
        break;
      case "UnsettSaleCt":
        array_push($prophead, "UnsettSaleCt");
        break;
      case "UnsettSaleAmt":
        array_push($prophead, "UnsettSaleAmt");
        break;
      case "UnsettRefCt":
        array_push($prophead, "UnsettRefCt");
        break;
      case "UnsettRefAmt":
        array_push($prophead, "UnsettRefAmt");
        break;
      case "Name2":
        array_push($prophead, "Name2");
        break;
      case "Add2":
        array_push($prophead, "Address2");
        break;
      case "City2":
        array_push($prophead, "City2");
        break;
      case "State2":
        array_push($prophead, "State2");
        break;
      case "Zip2":
        array_push($prophead, "Zip2");
        break;
      case "ERDesc":
        array_push($prophead, "ERDescription");
        break;
      case "EffRate":
        array_push($prophead, "EffRate");
        break;
    }
  }
  return $prophead;
}

$monthlyDescriptors =  file($path.'/statements/MonthDescriptors.csv');

$bigArray=[];
$bigArray = array_map('str_getcsv', $monthlyDescriptors);
$header = array_shift($bigArray);

$counter = 0;
foreach($bigArray as $datarow) {
  //fill empty rows with empty something

  foreach ($datarow as $item => $value) {
      $datarow[$item] = str_replace($undesirables, "", $datarow[$item]);
      if (empty($value) or $datarow[$item] == '') {
          $datarow[$item] = '""';
      }
      else{
        $datarow[$item] = "'".$datarow[$item]."'";
      }
  }

  $data= implode(",",$datarow);

        $sql = 'INSERT ignore INTO MonthDescriptors(MonId, MonName, YTDName, QtrName, ActRptYTD, StatementName) VALUES ( '. $data.' )';
        if ($mariadb->real_query($sql)) {

        } else {
            echo "Error: " . $sql . "<br>" . $mariadb->error;
        }
    $counter++ ;
}
echo $counter." rows added to MonthDescriptors table. \r\n";

$qryMthDep =  file($path.'/statements/qryMthDep.csv');

$bigArray = [];
$bigArray = array_map('str_getcsv', $qryMthDep);
$header = array_shift($bigArray);

$counter = 0;
foreach($bigArray as $datarow){
  //fill empty rows with empty something
  foreach ($datarow as $item => $value) {
      $fixed[$item] = str_replace($undesirables, "", $datarow[$item]);
      if (empty($value) or $datarow[$item] == '') {
          $fixed[$item] = '""';
      }
      else{
        $fixed[$item] = "'".$datarow[$item]."'";
      }
  }

  $fixed[0] = "'".str_replace(" ", "", $datarow[0])."'";

  $fixed[5] = strtotime($datarow[5]);
  $fixed[5] = date('Y-m-d', $fixed[5]);

  $fixed[5] = "'".$fixed[5]."'";

  $data= implode(",",$fixed);

        $sql = 'INSERT ignore INTO qryMthDep(MID, SumOfItem, Batch, Adjmnt, Dep, FirstOfBatchDate) VALUES ( '. $data.' )';
        if ($mariadb->real_query($sql)) {

        } else {
            echo "Error: " . $sql . "<br>" . $mariadb->error;
        }
    $counter++ ;
}
echo $counter." rows added to qryMthDep table. \r\n";

$qryMthDep1 =  file($path.'/statements/qryMthDep1.csv');

$bigArray = [];
$bigArray = array_map('str_getcsv', $qryMthDep1);
$header = array_shift($bigArray);

$counter = 0;
foreach($bigArray as $datarow) {
  //fill empty rows with empty something
  $fixed=[];

  foreach ($datarow as $item => $value) {
      $fixed[$item] = str_replace($undesirables, "", $datarow[$item]);
      if (empty($value) or $datarow[$item] == '') {
          $fixed[$item] = '""';
      }
      else{
        $fixed[$item] = "'".$datarow[$item]."'";
      }
  }

  $fixed[0] = "'".str_replace(" ", "", $datarow[0])."'";

  $fixed[5] = strtotime($datarow[5]);
  $fixed[5] = date('Y-m-d', $fixed[5]);

  $fixed[5] = "'".$fixed[5]."'";

  $data= implode(",",$fixed);

        $sql = 'INSERT ignore INTO qryMthDep(MID, SumOfItem, Batch, Adjmnt, Dep, FirstOfBatchDate) VALUES ( '. $data.' )';
        if ($mariadb->real_query($sql)) {

        } else {
            echo "Error: " . $sql . "<br>" . $mariadb->error;
        }
    $counter++ ;
}
echo $counter." rows added to qryMthDep1 table. \r\n";

$result2 = mysqli_query($mariadb, "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'statementData' AND TABLE_NAME = 'StatementReport6101Frontline'") or die('cannot show columns');

$fencer =[];
$tableHeader = mysqli_fetch_all($result2, MYSQLI_BOTH);
foreach ($tableHeader as $item => $value) {
    $fencer[] = "`".$value['0']."`";
}
//print_r($array);

$StatementReport6101Frontline =  file($path.'/statements/StatementReport6101Frontline.csv');

$bigArray = array_map('str_getcsv', $StatementReport6101Frontline);
$header = array_shift($bigArray);

$counter = 0;
$headers = compareheaders($fencer, $header);

//print_r($headers);

foreach($bigArray as $datarow) {
  //fill empty rows with empty something
  $fixed=[];

  foreach ($datarow as $item => $value) {
      $fixed[$item] = str_replace($undesirables, "", $datarow[$item]);
      if (empty($value) or $datarow[$item] == '') {
          $fixed[$item] = '""';
      }
      else{
        $fixed[$item] = "'".$datarow[$item]."'";
      }
  }

  $fixed[0] = "'".str_replace(" ", "", $datarow[0])."'";

  $data= implode(",",$fixed);
  //print_r($datarow);

  $sql = 'INSERT ignore INTO StatementReport6101Frontline('.implode(",", $headers).') VALUES ( "'.($date->format("Y-m-d")).'",'. $data.' )';
  if ($mariadb->real_query($sql)) {

  } else {
      echo "Error: " . $sql . "<br>" . $mariadb->error;
  }
  $counter++ ;
}
echo $counter." rows added to StatementReport6101Frontline table. \r\n";

$result2 = mysqli_query($mariadb, "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'statementData' AND TABLE_NAME = 'StatementReport6101FrontlineMNA'") or die('cannot show columns');
$tableHeader = mysqli_fetch_all($result2, MYSQLI_BOTH);

$fencer =[];
foreach ($tableHeader as $item => $value) {
    $fencer[] = "`".$value['0']."`";
}
$StatementReport6101FrontlineMNA =  file($path.'/statements/StatementReport6101FrontlineMNA.csv');

$bigArray = [];
$bigArray = array_map('str_getcsv', $StatementReport6101FrontlineMNA);
$header = array_shift($bigArray);

$counter = 0;
$headers = compareheaders($fencer, $header);
foreach ($bigArray as $datarow) {

  //fill empty rows with empty something
  $fixed=[];
  foreach ($datarow as $item => $value) {
      $fixed[$item] = str_replace($undesirables, "", $datarow[$item]);
      if (empty($value) or $datarow[$item] == '') {
          $fixed[$item] = '""';
      }
      else{
        $fixed[$item] = "'".$datarow[$item]."'";
      }
  }

  $fixed[0] = "'".str_replace(" ", "", $datarow[0])."'";

  $data= implode(",",$fixed);



        $sql = 'INSERT ignore INTO StatementReport6101FrontlineMNA('.implode(",", $headers).') VALUES ( f"'.($date->format("Y-m-d")).'",'.$data.' )';
        if ($mariadb->real_query($sql)) {

        } else {
            echo "Error: " . $sql . "<br>" . $mariadb->error;
        }
    $counter++ ;
}
echo $counter." rows added to StatementReport6101FrontlineMNA table. \r\n";


$result2 = mysqli_query($mariadb, "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'statementData' AND TABLE_NAME = 'tblCPDiscountEOM'") or die('cannot show columns');
$tableHeader = mysqli_fetch_all($result2, MYSQLI_BOTH);
$headers =[];
foreach ($tableHeader as $item => $value) {
    $headers[] = "`".$value['0']."`";
}

$tblCPDiscountEOM =  file($path.'/statements/tblCPDiscountEOM.csv');

$bigArray = [];
$bigArray = array_map('str_getcsv', $tblCPDiscountEOM);
array_shift($bigArray);

$counter = 0;

foreach ($bigArray as $datarow) {
  $fixed = [];
  //fill empty rows with empty something

  foreach ($datarow as $item => $value) {
      $fixed[$item] = str_replace($undesirables, "", $datarow[$item]);
      if (empty($value) or $datarow[$item] == '') {
          $fixed[$item] = '""';
      }
      else{
        $fixed[$item] = "'".$datarow[$item]."'";
      }
  }

  $fixed[0] = "'".str_replace(" ", "", $datarow[0])."'";

  $data= implode(",",$fixed);

  //print $data."\n";

        $sql = 'INSERT ignore INTO tblCPDiscountEOM('.implode(",", $headers).') VALUES ( "'.($date->format("Y-m-d")).'",'. $data.' )';
        if ($mariadb->real_query($sql)) {

        } else {
            echo "Error: " . $sql . "<br>" . $mariadb->error;
        }
    $counter++;
}
echo $counter." rows added to tblCPDiscountEOM table. \r\n";

$result2 = mysqli_query($mariadb, "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'statementData' AND TABLE_NAME = 'tblFlexFees'") or die('cannot show columns');
$tableHeader = mysqli_fetch_all($result2, MYSQLI_BOTH);
$headers =[];
foreach ($tableHeader as $item => $value) {
    $headers[] = "`".$value['0']."`";
}

$tblFlexFees =  file($path.'/statements/tblFlexFees.csv');

$bigArray = [];
$bigArray = array_map('str_getcsv', $tblFlexFees);
array_shift($bigArray);

$counter = 0;
foreach($bigArray as $datarow) {
  $fixed = [];

  //fill empty rows with empty something

  foreach ($datarow as $item => $value) {
      $fixed[$item] = str_replace($undesirables, "", $datarow[$item]);
      if (empty($value) or $datarow[$item] == '') {
          $fixed[$item] = '""';
      }
      else{
        $fixed[$item] = "'".$datarow[$item]."'";
      }
  }

  $fixed[0] = "'".str_replace(" ", "", $datarow[0])."'";

  $data= implode(",",$fixed);
  //print $data."\n";

        $sql = 'INSERT ignore INTO tblFlexFees('.implode(",", $headers).') VALUES ( "'.($date->format("Y-m-d")).'",'. $data.' )';
        print $sql;
        if ($mariadb->real_query($sql)) {

        } else {
            echo "Error: " . $sql . "<br>" . $mariadb->error;
        }
    $counter++ ;
}
echo $counter." rows added to tblFlexFees table. \r\n";

$mariadb->close();
?>
