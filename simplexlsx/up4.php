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

function placeholder( $text, $count = 0, $separator = ',' ) {
	$result = array();

	if ($count > 0) {
		for ($x = 0; $x < $count; $x++) {
			$result[] = $text;
		}
	}
	return implode( $separator, $result );
}

function insert($db, $table, $data){
	if(!empty($data) && is_array($data)){
		$columns = '';
		$values  = '';
		$i = 0;

		$columnString = implode(',', array_keys($data));
		$valueString = ":".implode(',:', array_keys($data));
		$sql = "INSERT INTO ".$table." (".$columnString.") VALUES (".$valueString.")";
		$bug = $sql;
		$query = $db->prepare($sql);
		foreach($data as $key=>$val){
			//			
			$val2date = find_date($val);
			$val = ($val2date) ? $val2date['y']."-".$val2date['m']."-".$val2date['d'] : $val;
			
			if (is_int($val)) {
				$datatype = PDO::PARAM_INT;
			} elseif (is_bool($val)) {
				$datatype = PDO::PARAM_BOOL;
			} elseif (is_null($val)) {
				$datatype = PDO::PARAM_NULL;
			} elseif ($val instanceof DateTime) {
				$val = $val->format('Y-m-d H:i:s');
				$datatype = PDO::PARAM_STR;
			} else {
				$datatype = PDO::PARAM_STR;
			}
			// --
			 $query->bindValue(':'.$key, $val, $datatype);
			 $bug .= 'db->bindValue(:'.$key.', '.$val.', $datatype)';
		}
		$insert = $query->execute();
		return $insert?$db->lastInsertId():false;
	}else{
		return false;
	}
}


// insert v.3 ????????????????????
function insert3($pdo, $table, $cols, $data) {
	$pdo->beginTransaction(); // Speed up your inserts	
		$columns = '';
		$values  = '';
		$i = 0;

		$columnString = implode(',', array_keys($data));
		$valueString = ":".implode(',:', array_keys($data));
		$sql = "INSERT INTO ".$table." (".$columnString.") VALUES (".$valueString.")";
		$bug = $sql;
		$query = $pdo->prepare($sql);
		foreach($data as $key=>$val){
			//			
			$val2date = find_date($val);
			$val = ($val2date) ? $val2date['y']."-".$val2date['m']."-".$val2date['d'] : $val;			
			if (is_int($val)) {
				$datatype = PDO::PARAM_INT;
			} elseif (is_bool($val)) {
				$datatype = PDO::PARAM_BOOL;
			} elseif (is_null($val)) {
				$datatype = PDO::PARAM_NULL;
			} elseif ($val instanceof DateTime) {
				$val = $val->format('Y-m-d H:i:s');
				$datatype = PDO::PARAM_STR;
			} else {
				$datatype = PDO::PARAM_STR;
			}
			// --
			$query->bindValue(':'.$key, $val, $datatype);
			$bug .= 'db->bindValue(:'.$key.', '.$val.', $datatype)';
		}
		
		try {
			$insert = $query->execute();
			// $src = $insert?$query->lastInsertId():false;
			// $src = $insert->rowCount(); //
			return ($insert) ? true : false;
		} catch(PDOException $e) {
			return $e->getMessage();
		}
		$pdo->commit();
}




function insert2($pdo, $table, $cols, $data) {
	$pdo->beginTransaction(); // Speed up your inserts
	$insertvalues = array();
	
	foreach ($data as $d) {
		$questionmarks[] = '(' . placeholder( '?', sizeof($d)) . ')';
		$insertvalues = array_merge( $insertvalues, array_values($d) );
	}
	$sql = "INSERT INTO ". $table ." (" . implode( ',', array_values( $cols ) ) . ") VALUES " . implode( ',', $questionmarks);
	// echo "<code> $sql </code><br>";
	$statement = $pdo->prepare($sql);
	try {
		$statement->execute($insertvalues);
		$src = $statement->rowCount(); //
		return ($statement) ? $src : false;
	} catch(PDOException $e) {
		return $e->getMessage();
	}
	$pdo->commit();
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

/*

CREATE TABLE `dokum` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`dok_turu` VARCHAR(20) NULL DEFAULT NULL COLLATE 'utf8_turkish_ci',
	`dok_krm_tur_kodu` VARCHAR(10) NULL DEFAULT NULL COLLATE 'utf8_turkish_ci',
	`dok_alan_id` SMALLINT(6) NULL DEFAULT NULL,
	`dok_sinif_kodu` VARCHAR(10) NULL DEFAULT NULL COLLATE 'utf8_turkish_ci',
	`dok_adi` VARCHAR(250) NOT NULL DEFAULT '\'İsimsiz Belge\'' COLLATE 'utf8_turkish_ci',
	`dok_dosya` VARCHAR(500) NOT NULL COLLATE 'utf8_turkish_ci',
	`dok_img` VARCHAR(300) NULL DEFAULT '\'images/icon/dok-default.png\'' COLLATE 'utf8_turkish_ci',
	`dok_kodu` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_turkish_ci',
	`dok_sayi` VARCHAR(20) NULL DEFAULT NULL COLLATE 'utf8_turkish_ci',
	`dok_tarih` DATE NULL DEFAULT NULL,
	`dok_onaysayi` VARCHAR(30) NULL DEFAULT NULL COLLATE 'utf8_turkish_ci',
	`dok_onaytarih` DATE NULL DEFAULT NULL,
	`dok_alan` VARCHAR(200) NULL DEFAULT NULL COLLATE 'utf8_turkish_ci',
	`yolx` VARCHAR(500) NULL DEFAULT NULL COLLATE 'utf8_turkish_ci',
	`indirme` MEDIUMINT(9) NULL DEFAULT NULL,
	`durum` VARCHAR(1) NOT NULL DEFAULT 'y' COLLATE 'utf8_turkish_ci',
	`kayit` DATETIME NULL DEFAULT current_timestamp(),
	`guncelleme` DATETIME NULL DEFAULT NULL,
	`notlar` TEXT NULL DEFAULT NULL COLLATE 'utf8_turkish_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `dok_alan_id` (`dok_alan_id`) USING BTREE,
	INDEX `dok_krm_tur_kodu` (`dok_krm_tur_kodu`) USING BTREE,
	INDEX `dok_sinif_kodu` (`dok_sinif_kodu`) USING BTREE,
	INDEX `dok_turu` (`dok_turu`) USING BTREE
)
COLLATE='utf8_turkish_ci'
ENGINE=InnoDB
;

*/
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
		// var_dump($rows);
		echo "</pre>";
		
		// tablo 
		$showTbl = '<table border="1" cellpadding="3" style="border-collapse: collapse;">';
		$showTbl .= '<tr style="background-color: #efefef;"><th>#</th><th>'. implode('</th><th>', array_values($header_values) ) .'</th></tr>';
		$rowc = 0;
		// insert 
		$dbi = insert2($dbconn, $dbtable, $header_values, $rows);
		
		foreach ($rows as $row) {
			// $ins = insert($dbconn, $dbtable, $row);
			// $ins = insert3($dbconn, $dbtable, $header_values, $row);
			$rid = $ins ? $ins : "x";
			$rowc = $ins ? $rowc+1 : $rowc;
			$showTbl .= '<tr><td>'. $rid .'</td><td>'. implode('</td><td>', $row ) .'</td></tr>';
		}
		$showTbl .= "</table><br>Okunan: " . $rowc . " satır.";	
		
		
		if($dbi) {
			echo $showTbl;
			echo "Yazılan: ". $dbi ." satır.";
		}
	}


	$msc = microtime(true)-$msc;
	echo "Sorgu süresi: ". $msc . " s / "; // in seconds
	echo "Sorgu süresi: ". ($msc * 1000) . " ms"; // in millseconds

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

<?php
/*

*/

?>
</body>
</html>
