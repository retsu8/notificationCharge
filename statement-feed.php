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
echo $date->format("Y-m-d");

$monthlyDescriptors = fopen($path.'/statements/MonthDescriptors.csv', 'r');
echo $monthlyDescriptors."\r\n";
$counter = 0;
while (($datarow = fgetcsv($monthlyDescriptors, 4096, ",")) != false) {
    if ($counter <> 0) {

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
            echo "New record created successfully \r\n";
        } else {
            echo "Error: " . $sql . "<br>" . $mariadb->error;
        }
    }
    $counter++ ;
}
echo $counter." rows added to MonthDescriptors table. \r\n";
fclose($monthlyDescriptors);

$qryMthDep = fopen($path.'/statements/qryMthDep.csv', 'r');
echo $qryMthDep."\r\n";
$counter = 0;
while (($datarow = fgetcsv($qryMthDep, 4096, ",")) != false) {
    if ($counter <> 0) {

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
            echo "New record created successfully \r\n";
        } else {
            echo "Error: " . $sql . "<br>" . $mariadb->error;
        }
    }
    $counter++ ;
}
echo $counter." rows added to qryMthDep table. \r\n";
fclose($qryMthDep);

$qryMthDep1 = fopen($path.'/statements/qryMthDep1.csv', 'r');
echo $qryMthDep1."\r\n";
$counter = 0;
while (($datarow = fgetcsv($qryMthDep1, 4096, ",")) != false) {
    if ($counter <> 0) {

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
            echo "New record created successfully \r\n";
        } else {
            echo "Error: " . $sql . "<br>" . $mariadb->error;
        }
    }
    $counter++ ;
}
echo $counter." rows added to qryMthDep1 table. \r\n";
fclose($qryMthDep1);

$result2 = mysqli_query($mariadb, "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'statementData' AND TABLE_NAME = 'StatementReport6101Frontline'") or die('cannot show columns');

$headers =[];
$tableHeader = mysqli_fetch_all($result2, MYSQLI_BOTH);
foreach ($tableHeader as $item => $value) {
    $headers[] = "`".$value['0']."`";
}

$StatementReport6101Frontline = fopen($path.'/statements/StatementReport6101Frontline.csv', 'r');
echo $StatementReport6101Frontline."\r\n";
$counter = 0;
while (($datarow = fgetcsv($StatementReport6101Frontline, 4096, ",")) != false) {
    if ($counter <> 0) {

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



        $sql = 'INSERT ignore INTO StatementReport6101Frontline('.implode(",", $headers).') VALUES ( '.($date->format("Y-m-d")).','. $data.' )';
        if ($mariadb->real_query($sql)) {
            echo "New record created successfully \r\n";
        } else {
            echo "Error: " . $sql . "<br>" . $mariadb->error;
        }
    }
    $counter++ ;
}
echo $counter." rows added to StatementReport6101Frontline table. \r\n";
fclose($StatementReport6101Frontline);

$result2 = mysqli_query($mariadb, "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'statementData' AND TABLE_NAME = 'StatementReport6101FrontlineMNA'") or die('cannot show columns');
$tableHeader = mysqli_fetch_all($result2, MYSQLI_BOTH);
$headers =[];
foreach ($tableHeader as $item => $value) {
    $headers[] = "`".$value['0']."`";
}

$StatementReport6101FrontlineMNA = fopen($path.'/statements/StatementReport6101FrontlineMNA.csv', 'r');
echo $StatementReport6101FrontlineMNA."\r\n";
$counter = 0;
while (($datarow = fgetcsv($StatementReport6101FrontlineMNA, 4096, ",")) != false) {
    if ($counter <> 0) {

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



        $sql = 'INSERT ignore INTO StatementReport6101FrontlineMNA('.implode(",", $headers).') VALUES ( '.($date->format("Y-m-d")).",".$data.' )';
        if ($mariadb->real_query($sql)) {
            echo "New record created successfully \r\n";
        } else {
            echo "Error: " . $sql . "<br>" . $mariadb->error;
        }
    }
    $counter++ ;
}
echo $counter." rows added to StatementReport6101FrontlineMNA table. \r\n";
fclose($StatementReport6101FrontlineMNA);

$result2 = mysqli_query($mariadb, "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'statementData' AND TABLE_NAME = 'tblCPDiscountEOM'") or die('cannot show columns');
$tableHeader = mysqli_fetch_all($result2, MYSQLI_BOTH);
$headers =[];
foreach ($tableHeader as $item => $value) {
    $headers[] = "`".$value['0']."`";
}

print_r($headers);

$tblCPDiscountEOM = fopen($path.'/statements/tblCPDiscountEOM.csv', 'r');
echo $tblCPDiscountEOM."\r\n";
$counter = 0;
$fixed = [];
while (($datarow = fgetcsv($tblCPDiscountEOM, 4096, ",")) != false) {
    if ($counter <> 0) {

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

        $sql = 'INSERT ignore INTO tblCPDiscountEOM('.implode(",", $headers).') VALUES ( '.($date->format("Y-m-d")).",". $data.' )';
        if ($mariadb->real_query($sql)) {
            echo "New record created successfully \r\n";
        } else {
            echo "Error: " . $sql . "<br>" . $mariadb->error;
        }
    }
    $counter++ ;
}
echo $counter." rows added to tblCPDiscountEOM table. \r\n";
fclose($tblCPDiscountEOM);

$result2 = mysqli_query($mariadb, "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'statementData' AND TABLE_NAME = 'tblFlexFees'") or die('cannot show columns');
$tableHeader = mysqli_fetch_all($result2, MYSQLI_BOTH);
$headers =[];
foreach ($tableHeader as $item => $value) {
    $headers[] = "`".$value['0']."`";
}

print_r($headers);

$tblFlexFees = fopen($path.'/statements/tblFlexFees.csv', 'r');
echo $tblFlexFees."\r\n";
$counter = 0;
$fixed = [];
while (($datarow = fgetcsv($tblFlexFees, 4096, ",")) != false) {
    if ($counter <> 0) {

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

        $sql = 'INSERT ignore INTO tblFlexFees('.implode(",", $headers).') VALUES ( '.($date->format("Y-m-d")).",". $data.' )';
        if ($mariadb->real_query($sql)) {
            echo "New record created successfully \r\n";
        } else {
            echo "Error: " . $sql . "<br>" . $mariadb->error;
        }
    }
    $counter++ ;
}
echo $counter." rows added to tblFlexFees table. \r\n";
fclose($tblFlexFees);

$mariadb->close();
?>
