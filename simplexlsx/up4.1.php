<?php
/**
*  Dosya yuklemesi olmadan excel dosyalarını oku
**/
require_once "src/SimpleXLSX.php";

$dbhost = "localhost";
$dbname = "simple_xlsx";
$dbuser = "root";
$dbpass = "4200";
$dbconn = new PDO("mysql:host=$dbhost;dbname=$dbname;charset=utf8",$dbuser,$dbpass);
$dbtable = "dokum";

if(isset($_FILES["file"])){
    if ($_FILES["file"]["error"] > 0){
        echo "Error: " . $_FILES["file"]["error"] . "<br />";
    }else{
        //$var = file_get_contents($_FILES["file"]["tmp_name"]);
        //echo $var;  //test
        $xfile = $_FILES["file"]["tmp_name"];
    }
}

function find_date( $string ) {
	$day = ""; $month = ""; $year = "";
	// \d{4}[\.\-\/]+\d{1,2}[\.\-\/]+\d{1,2} ==> yyyy-mm-dd
	// Match dates: 01/01/2012 or 30-12-11 or 1 2 1985
	preg_match( '/([0-9]?[0-9])[\.\-\/ ]+([0-1]?[0-9])[\.\-\/ ]+([0-9]{2,4})/', $string, $matches );
	if ( $matches ) {
		if ( $matches[1] ) $day = $matches[1];
		if ( $matches[2] ) $month = $matches[2];
		if ( $matches[3] ) $year = $matches[3];
	}
	// Day leading 0
	if ( 1 == strlen( $day ) )
	$day = '0' . $day;
	// Month leading 0
	if ( 1 == strlen( $month ) )
	$month = '0' . $month;
	// Check year:
	if(strlen($year) < 4) $year = false;

	$date = array(
		'y'  => $year,
		'm' => $month,
		'd'   => $day
	);
  
	if ( empty( $year ) && empty( $month ) && empty( $day ) )
		return false;
	else
		return $date;
}

function placeholder( $text, $count = 0, $separator = ',' ) {
	$result = array();
	if ($count > 0) {
		for ($x = 0; $x < $count; $x++) {
			$result[] = $text;
		}
	}
	return implode( $separator, $result );
}

/*
$prep = array();
foreach($insData as $k => $v ) {
    $prep[':'.$k] = $v;
}
$sth = $db->prepare("INSERT INTO table ( " . implode(', ',array_keys($insData)) . ") VALUES (" . implode(', ',array_keys($prep)) . ")");
$res = $sth->execute($prep);
*/

function escape_mysql_identifier($field){
    return "`".str_replace("`", "``", $field)."`";
}

function pdo_insert($pdo, $table, $data) {
	$keys = array_keys($data);
    $keys = array_map('escape_mysql_identifier', $keys);
    $fields = implode(",", $keys);
    $table = escape_mysql_identifier($table);
    $placeholders = str_repeat('?,', count($keys) - 1) . '?';
    $sql = "INSERT INTO ". $table ." (". $fields .") VALUES (". $placeholders .")";
    $pdo->prepare($sql)->execute(array_values($data));
	echo $sql; // $pdo->rowCount();
}


/**
* 
* @param $db
* @param $table
* @param $rows
* @param $data
* 
* @return
*/

function inserts($pdo, $table, $rows, $data, $crt=FALSE){
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	/*
	if(!empty($crt)) {
	$pdo->beginTransaction();
	$stmt_1 = $pdo->prepare($crt);
	$stmt_1->execute();
	$stmt_1->commit();
	}*/
	
	
	if(!empty($data) && is_array($data)){
		$columns = '';
		$values  = '';
		$i = 0;

		$columnString = implode(', ', array_values($rows));
		$valueString = ":".implode(',:', array_values($rows));
		
		$pdo->beginTransaction();
		if(!empty($crt)) {
			$stmt = $pdo->prepare($crt);
			$stmt->execute();
			//$stmt->commit();
		}
		$sql = "INSERT INTO ".$table." (".$columnString.") VALUES (" . $valueString .")";
		$bug = $sql;
		$stmt = $pdo->prepare($sql);
		
		// echo $bug;
		// exec
			  $rc=0;
			  foreach($data as $d){
				  
					foreach($d as $key => &$val) {
						if(empty($val) || $val=="") {
							$val = "";
							$datatype = "PDO::PARAM_NULL";
						} else {
							$datatype = "PDO::PARAM_STR";
							if (is_int($val)) {
								$datatype = "PDO::PARAM_INT";
								$val = $val;
							} elseif (empty($val)) {
								$val = "";
								$datatype = "PDO::PARAM_NULL";
							} elseif ($val instanceof DateTime) {
								$val = $val->format('Y-m-d H:i:s');
								$datatype = "PDO::PARAM_STR";
							} else {
								$datatype = "PDO::PARAM_STR";
							}
						}
						
						echo $key." = ".$val." -> datatype: ".$datatype."<br>";
						// --
						//$stmt->bindValue(":".$key, $val, $datatype);
						$stmt->bindValue(":".$key, $val, PDO::PARAM_STR);
						$rc++;
					}
				}
		try {
			$stmt->execute();
			//$stmt->commit();
			$ic = $stmt->rowCount();
			echo $rc ." -- ". $ic;
			return $rc;
			// return $bug;
		} catch(PDOException $e) {
			//$stmt->rollback();
			throw $e;
			return $e->getMessage() . "<hr>## Q: ". $bug;
		}
		$pdo->commit();
	} else {
		return false;
	}
}


// insert v.3 ????????????????????
function insert3($pdo, $table, $cols, $data) {
	// print_r($data);
	$pdo->beginTransaction(); // Speed up your inserts	
		
		foreach($data as $d){
			$columns = '';
			$values  = '';
			$i = 0;
			
			$columnString = implode(', ', array_keys($d));
			$valueString = ":".implode(', :', array_keys($d));
			
			$sql = "INSERT INTO ".$table." (".$columnString.") VALUES (".$valueString.")";
				$query = $pdo->prepare($sql);
				echo $sql."<br>";
			
		}
			foreach($d as $key=>$val) {
				
				echo ":".$key."->". $val ." / ";
				
				if (is_int($val)) {
					$datatype = PDO::PARAM_INT;			
				} elseif (is_null($val)) {
					$val = "NULL";
					$datatype = PDO::PARAM_NULL;
				} elseif (empty($val)) {
					$val = "NULL";
					$datatype = PDO::PARAM_NULL;
				} elseif ($val instanceof DateTime) {
					$val = $val->format('Y-m-d H:i:s');
					$datatype = PDO::PARAM_STR;
				} else {
					$val = filter_var($val, FILTER_SANITIZE_STRING);
					$datatype = PDO::PARAM_STR;
				}
				// --
				$query->bindValue(":".$key, $val, $datatype);
				echo '<b> query->bindValue(:'.$key.', '.$val.', '.$datatype.') </b><br>';
			}
	
		
		try {
			$query->execute();
			// $src = $insert?$query->lastInsertId():false;
			// $src = $insert->rowCount(); //
			return ($query) ? true : false;
		} catch(PDOException $e) {
			return $e->getMessage();
		}
		
		
	$pdo->commit();
}




function insert2_0($pdo, $table, $cols, $data) {
	$pdo->beginTransaction(); // Speed up your inserts
	$insertvalues = array();
	$questionmarks[] = '(' . placeholder( '?', sizeof($cols)) . ')';
	foreach ($data as $d) {
		
		$insertvalues = array_merge( $insertvalues, array_values($d) );
	}
	$sql = "INSERT INTO ". $table ." (" . implode( ',', array_values( $cols ) ) . ") VALUES " . implode( ',', $questionmarks);
  ## $sql = "INSERT INTO ". $table ." (" . implode( ',', array_values( $cols ) ) . ") VALUES " . array_values( $data ) .";";
	echo "<pre>";
   echo $sql;
	 var_dump($insertvalues);
  echo "</pre>";
	$statement = $pdo->prepare($sql);
	try {
		$statement->execute($data);
		$src = $statement->rowCount(); //
		return ($statement) ? $src : false;
	} catch(PDOException $e) {
		return $e->getMessage();
	}
	$pdo->commit();
}

function insert2($pdo, $table, $cols, $data) {
	$pdo->beginTransaction(); // Speed up your inserts
  /**
  $data = [
    'name' => $name,
    'surname' => $surname,
    'sex' => $sex,
    ];
  $sql = "INSERT INTO users (name, surname, sex) VALUES (:name, :surname, :sex)";
  $stmt= $pdo->prepare($sql);
  $stmt->execute($data); 
  */

  $ph = ":".implode(',:', array_values($cols));
	$sql = "INSERT INTO ". $table ." (" . implode( ',', array_values( $cols ) ) . ") VALUES " . "(" . $ph .")";

	echo "<pre>";
   echo $sql;
  echo "</pre>";
	$statement = $pdo->prepare($sql);
	try {
		$statement->execute($data);
		$src = $statement->rowCount(); //
		return ($statement) ? $src : false;
	} catch(PDOException $e) {
		return $e->getMessage();
	}
	$pdo->commit();
}




?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>xlsx2sql insert v.1</title>
</head>
<body>
<form enctype="multipart/form-data" method="post">
<input name="file" type="file" />
<input name="sub" type="submit" value="Yükle" />
</form>

<?php

if($xfile) {

	$msc = microtime(true);

	if ( $xlsx = SimpleXLSX::parse($xfile)) {
		// Produce array keys from the array values of 1st array element
		$header_values = $rows = [];
		foreach ( $xlsx->rows() as $k => $r ) {
			if ( $k === 0 ) {
				$header_values = $r;
				continue;
			}
			$rows[] = array_combine( $header_values, $r );
		}
    
		echo "<hr><pre>Code:<br>";
		$sName = $dbtable = $xlsx->sheetName(0);
		echo $sName ."\n";
		var_dump($header_values);
		
		// Create Table 
		$crt = "CREATE TABLE IF NOT EXISTS `". $sName ."` ( ";
			foreach($header_values as $hv) {
			$crtRow[] = "`".$hv."` TEXT NULL DEFAULT NULL ";
			}
		$crt .= implode(", \n", $crtRow);
		$crt .= ")
		COLLATE='utf8_general_ci'
		ENGINE=MyISAM;
		ALTER TABLE `". $sName ."`
		ADD COLUMN `id` INT(8) NOT NULL AUTO_INCREMENT,
		ADD PRIMARY KEY (`id`);";
		
		echo $crt; // create
		
		// --------------------------------------------
		// Insert 
		
		echo "<hr>";
		// var_dump($rows);
		/*
		foreach($rows as $rk=>$rv) {
			
		}
		*/
		echo "</pre>";
		
		// tablo 
		$showTbl = '<table border="1" cellpadding="3" style="border-collapse: collapse;">';
		$showTbl .= '<tr style="background-color: #efefef;"><th>#</th><th>'. implode('</th><th>', array_values($header_values) ) .'</th></tr>';
		$rowc = 0;
		// insert 
		# $dbi = insert2($dbconn, $dbtable, $header_values, $rows);
		 $ins = inserts($dbconn, $dbtable, $header_values, $rows, $crt);
		// $ins = pdo_insert($dbconn, $dbtable, $rows);
		print_r($ins);
		
		$rowsCount = count($rows); // satir say
		
		foreach ($rows as $row) {
			//++ $ins = insert($dbconn, $dbtable, $row);
			//--$ins = insert3($dbconn, $dbtable, $header_values, $row);
			#$rid = $ins ? $ins : "x";
			#$rowc = $ins ? $rowc+1 : $rowc;
			//$rowc++;
			$showTbl .= '<tr><td>'. $rowc .' / Q: '. $ins .'</td><td>'. implode('</td><td>', $row ) .'</td></tr>';
		}
		$showTbl .= "</table><br>Okunan: " . $rowsCount . " satır.";	
		
		
		if($rowc) {
			echo $showTbl;
			echo "Yazılan: ". $rowc ." satır.";
		}
	}


	$msc = microtime(true)-$msc;
	echo "<hr>Sorgu süresi: ". $msc . " s / ". ($msc * 1000) . " ms"; // in millseconds

}



// HTML TABLE GOSTER ------------------------------------------------------------------
/* ************************************************************************************
echo "<hr>";
echo "<h3>xlsx 2 toHTML v.2</h3>";
if ( $xlsx = SimpleXLSX::parse($xfile) ) {
	echo '<table border="1" cellpadding="3" style="border-collapse: collapse">';
	foreach( $xlsx->rows() as $r ) {
		echo '<tr><td>'. implode('</td><td>', $r ) .'</td></tr>';
	}
	echo '</table>';
}
************************************************************************************** */


?>
</body>
</html>
