<?php
//phpinfo();
$mariadb = new mysqli("merchdb.c0v9kpl8n2zi.us-west-2.rds.amazonaws.com", "merch_admin", "T7ToogA#36u#UWbV", "druporta_tss_data");
if ($mariadb->connect_error) {
    echo "Failed to connect to MySQL: (" . $mariadb->connect_errno . ") " . $mariadb->connect_error;
}

mysqli_ssl_set($mariadb, NULL,"certs.pem","certs.pem",NULL,NULL);

$undesirables = array("\n", "\r","\t","\e","\f",",");

 // qryMthDep1 StatementReport6101Frontline StatementReport6101FrontlineMNA tblCPDiscountEOM tblFlexFees
$path=getcwd();

$result2 = mysqli_query($mariadb, "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'druporta_tss_data' AND TABLE_NAME = 'transactions'") or die('cannot show columns');

$headers =[];
$tableHeader = mysqli_fetch_all($result2, MYSQLI_BOTH);
foreach ($tableHeader as $item => $value) {
    $headers[] = "`".$value['0']."`";
}

$transactions = fopen('/home/wjp/Downloads/Transactions.csv', 'r');
echo $transactions."\r\n";
$counter = 0;
while (($datarow = fgetcsv($transactions, 4096, ",")) != false) {
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

  print_r($fixed);
  # cleanup mid
  $fixed[1] = "'".str_replace(" ", "", $datarow[1])."'";

  #fix date format
  $fixed[0] = strtotime($datarow[0]);
  $fixed[0] = date('Y-m-d', $fixed[0]);
  $fixed[0] = "'".$fixed[0]."'";
  #fix date format
  $fixed[10] = strtotime($datarow[10]);
  $fixed[10] = date('Y-m-d', $fixed[10]);
  $fixed[10] = "'".$fixed[10]."'";
  #fix date format
  $fixed[13] = strtotime($datarow[13]);
  $fixed[13] = date('Y-m-d', $fixed[13]);
  $fixed[13] = "'".$fixed[13]."'";

  #dollors to cents
  $dollars = str_replace('$', '', $datarow[5]);
  $fixed[5] = $dollars * 100;

  #fix time to milatary
  $fixed[11]  = date("H:i", (int)strtotime((string)$datarow[11]));
  $fixed[11] = "'".$fixed[11]."'";

  $data= implode(",",$fixed);

        $sql = 'INSERT INTO transactions('.implode(",",$headers).') VALUES ( '. $data.' )';
        print $sql;
        if ($mariadb->real_query($sql)) {
            echo "New record created successfully \r\n";
        } else {
            echo "Error: " . $sql . "<br>" . $mariadb->error;
        }
    }
    $counter++ ;
}
echo $counter." rows added to transactions table. \r\n";
fclose($transactions);

$mariadb->close();
?>
