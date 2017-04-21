<?php
//phpinfo();
$mariadb = new mysqli("merchdb.c0v9kpl8n2zi.us-west-2.rds.amazonaws.com", "merch_admin", "T7ToogA#36u#UWbV", "druporta_tss_data");
if ($mariadb->connect_error) {
    echo "Failed to connect to MySQL: (" . $mariadb->connect_errno . ") " . $mariadb->connect_error;
}

mysqli_ssl_set($mariadb, NULL,"certs.pem","certs.pem",NULL,NULL);

$undesirables = array("\n", "\r","\t","\e","\f");

$auth_csv = fopen('/home/druportal/trisource/FrtLnAuth.txt', 'r');
echo $auth_csv."\r\n";
$counter = 0;
while (($datarow = fgets($auth_csv, 4096)) != false) {
    if ($counter <> 0) {
      $fixUpload =  explode(",", $datarow);

  //fill empty rows with empty something

  foreach ($fixUpload as $item => $value) {
      $fixUpload[$item] = str_replace($undesirables, "", $fixUpload[$item]);
      if (empty($value) or $fixUpload[$item] == '') {
          $fixUpload[$item] = '""';
      }
  }
  $datarow = implode(",",$fixUpload);

        $sql = 'INSERT INTO authorizations VALUES ( '. $datarow.' )';
        if ($mariadb->real_query($sql) === true) {
            echo "New record created successfully \r\n";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    $counter++ ;
}
echo $counter." rows added to authorizations table. \r\n";
fclose($auth_csv);

//
//  Now lets process the Transaction file
//

$counter = 0;
$tran_csv = fopen('/home/druportal/trisource/FrtLnTran.txt', 'r');
echo $tran_csv."\r\n";
while (($datarow = fgets($tran_csv, 4096)) != false) {
    if ($counter <> 0) {
      $fixUpload =  explode(",", $datarow);

  //fill empty rows with empty something

  foreach ($fixUpload as $item => $value) {
      $fixUpload[$item] = str_replace($undesirables, "", $fixUpload[$item]);
      if (empty($value) or $fixUpload[$item] == '') {
          $fixUpload[$item] = '""';
      }
  }
  $datarow = implode(",",$fixUpload);

        $sql = 'INSERT INTO transactions VALUES ( '. $datarow.' )';
        if ($mariadb->query($sql) === true) {
            //echo "New record created successfully \r\n";
        } else {
            //echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    $counter++ ;
}

echo $counter." rows added to transactions table.\r\n";

fclose($tran_csv);

//

//  Now lets process the ACHS file

//

$counter = 0;

$achs_csv = fopen('/home/druportal/trisource/FrtLnACH.txt', 'r');

echo $achs_csv."\r\n";

while (($datarow = fgets($achs_csv, 4096)) != false) {
    if ($counter <> 0) {
      $fixUpload =  explode(",", $datarow);

  //fill empty rows with empty something

  foreach ($fixUpload as $item => $value) {
      print_r($fixUpload);
      if (empty($value) or $fixUpload[$item] == '') {
          $fixUpload[$item] = '""';
      }
  }
  $datarow = implode(",",$fixUpload);

        $sql = 'INSERT INTO achs VALUES ( '. $datarow.' )';

        if ($mariadb->query($sql) === true) {

            //echo "New record created successfully \r\n";
        } else {

            //echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    $counter++ ;
}

echo $counter." rows added to achs table.\r\n";

fclose($achs_csv);

//

//  Now lets process the MrchProfile file

//

$counter = 0;

$mrchprofile_csv = fopen('/home/druportal/trisource/FrtLnMrchProfile.txt', 'r');

while (($datarow = fgets($mrchprofile_csv, 4096)) != false) {
    if ($counter <> 0) {
        $fixUpload =  explode(",", $datarow);

    //fill empty rows with empty something

    foreach ($fixUpload as $item => $value) {
        $fixUpload[$item] = str_replace($undesirables, "", $fixUpload[$item]);
        if (empty($value) or $fixUpload[$item] == '') {
            $fixUpload[$item] = '""';
        }
    }
    $datarow = implode(",",$fixUpload);

        $sql = 'INSERT INTO mrchprofile VALUES ( '. $datarow.' )';

        if ($mariadb->query($sql) === true) {

            //echo "New record created successfully \r\n";
        } else {

            //echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }

    $counter++ ;
}

echo $counter." rows added to mrchprofile table.\r\n";

fclose($mrchprofile_csv);


//

// Processing Charge Backs

//

$counter = 0;

$chargeback_csv = fopen('/home/druportal/trisource/FrtLnCBs.txt', 'r');

//Fix up headers to add column to value
$result2 = mysqli_query($mariadb, 'SHOW COLUMNS FROM chargebacks') or die('cannot show columns');
$tableHeader = mysqli_fetch_all($result2, MYSQLI_BOTH);
foreach ($tableHeader as $item => $value) {
    $headers[] = "`".$value['0']."`";
}

$headers = array_slice($headers, 0, -1);

while (($datarow = fgets($chargeback_csv, 4096)) != false) {
    if ($counter <> 0) {
        $fixUpload =  explode(",", $datarow);

    //fill empty rows with empty something

    foreach ($fixUpload as $item => $value) {
        $fixUpload[$item] = str_replace($undesirables, "", $fixUpload[$item]);
        if (empty($value) or $fixUpload[$item] == '') {
            $fixUpload[$item] = '""';
        }
    }
    $datarow = implode(",",$fixUpload);


        $sql = 'INSERT INTO chargebacks('.implode(",", $headers).') VALUES ( '. $datarow.' )';

        if ($mariadb->query($sql) === true) {
            echo "New record created successfully \r\n";
        } else {
            echo "Error: " . $sql . "<br>";
            print_r($mysqli->error_list);
        }
    }

    $counter++ ;
}

echo $counter." rows added to chargebacks table.\r\n";

fclose($chargeback_csv);
