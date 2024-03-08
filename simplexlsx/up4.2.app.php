<?php
ini_set('upload_max_filesize', '999M');
ini_set('memory_limit', '2G');
ini_set('max_execution_time', 600);
/**
*  Dosya yuklemesi olmadan excel dosyalarını oku
**/
require_once "src/SimpleXLSX.php";

if(isset($_FILES["file"]) && isset($_POST["db"])){
	$dbname = $_POST["db"];
}

$dbhost = "localhost";
// $dbname = "simple_xlsx";
$dbuser = "root";
$dbpass = "4200";
$dbconn = new PDO("mysql:host=$dbhost;dbname=$dbname;charset=utf8",$dbuser,$dbpass);
//$dbtable = "dokum";

if(isset($_FILES["file"])){
    if ($_FILES["file"]["error"] > 0){
        echo "File Error: " . $_FILES["file"]["error"] . "<br />";
    }else{
        //$var = file_get_contents($_FILES["file"]["tmp_name"]);
        //echo $var;  //test
        $xfile = $_FILES["file"]["tmp_name"];
		$xfileName = $_FILES["file"]["name"];
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

function trFix($tr1) {
	$tr1 = trim($tr1);
	$turkce=array("ş","Ş","ı","ü","Ü","ö","Ö","ç","Ç","ş","Ş","ı","ğ","Ğ","İ","ö","Ö","Ç","ç","ü","Ü");
	$duzgun=array("s","S","i","u","U","o","O","c","C","s","S","i","g","G","I","o","O","C","c","u","U");
	$tr1=str_replace($turkce,$duzgun,$tr1);
	$tr1 = preg_replace("@[^a-z0-9\-_şıüğçİŞĞÜÇ]+@i","_",$tr1);
	return $tr1;
}

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
$batch_size = 1000;
for( $i=0,$c=count($players); $i<$c; $i+=$batch_size ) {
    $db->beginTransaction();
    try {
        for( $k=$i; $k<$c && $k<$i+$batch_size; $k++ ) {
            $player = $players[$k];
            $sql->execute([
                ":name" => $player->name,
                ":level" => $player->level,
                ":vocation" => $player->vocation,
                ":world" => $player->world,
                ":time" => $player->time,
                ":online" => $player->online
            ]);
        }
    } catch( PDOException $e ) {
        $db->rollBack();
        // at this point you would want to implement some sort of error handling
        // or potentially re-throw the exception to be handled at a higher layer
        break;
    }
    $db->commit();
}
*/

function crtTable($pdo, $cq) {
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	if(!empty($cq)) { // create table
			try {	
				$stm = $pdo->prepare($cq);
				$stm->execute();
				return 1;
			} catch( PDOException $e ) {
				echo $e->getMessage();
			}
		}
}

function inserts($pdo, $table, $cols, $data, $crt=FALSE){
	
	if(!empty($crt)) { 
		$createTable = crtTable($pdo, $crt); 
	} // create table
	
	if((!empty($data) && is_array($data)) && (!empty($crt) && $createTable===1 )){
		$datatype = "PDO::PARAM_STR";
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$columns = '';
		$values  = '';
		$i = 0;

		$columnString = implode(', ', array_values($cols)); // col names
		$valueString = ":".implode(',:', array_values($cols)); // col names for pdo
		
		// insert 
		$sql = "INSERT INTO ".$table." (".$columnString.") VALUES (" . $valueString .")";
		$stmt = $pdo->prepare($sql);
		
		$batch_size = 1000;
		$qt=0;
		for( $i=0,$c=count($data); $i<$c; $i+=$batch_size ) {	
			$pdo->beginTransaction();
			try {
				for( $k=$i; $k<$c && $k<$i+$batch_size; $k++ ) {
					
					foreach($data[$k] as $key => &$val) {
						$keyFixed = trFix($key); // tr düzelt
						$val = trim($val);
						$stmt->bindValue(":".$keyFixed, $val, PDO::PARAM_STR);
					}
					$stmt->execute();
					$qt++;
				}
				
			} catch( PDOException $e ) {
				echo "Error on insert!!!<br>";
				echo $e->getMessage();
				$pdo->rollBack();
				break;
			}
			$pdo->commit();
		}
		echo "Inserted ". $qt ."<br>";
	} // if $data
	
} // func 

if(!empty($xfile)) {

	$msc = microtime(true);
	echo "<hr><pre>Code:<br>";
	echo "Dosya: ". $xfileName ."<br>";
	echo "Veri Tabanı: ". $dbname ."<br>";

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
    
		
		$tblName = $xlsx->sheetName(0);
		$tblName = trFix(trim($tblName));
		echo "Tablo: ". $tblName ."\n";
		// var_dump($header_values);
		
		// Create Table 
		$crt = "CREATE TABLE IF NOT EXISTS `". $tblName ."` ( ";
			foreach($header_values as $hv) {
				$col_names[] = trFix($hv);
				$colName = trFix($hv);
				$crtCols[] = "`".$colName."` TEXT NULL DEFAULT NULL ";
			}
		$crt .= implode(", \n", $crtCols);
		$crt .= ")
		COLLATE='utf8_general_ci' ENGINE=MyISAM;
		ALTER TABLE `". $tblName ."` ADD COLUMN `id` INT(8) NOT NULL PRIMARY KEY AUTO_INCREMENT FIRST;";
		// echo $crt; // create sql 
		// --------------------------------------------
		// Insert 
		$showTable = false;
		$showTbl = '<table border="1" cellpadding="3" style="border-collapse: collapse; max-width:90%">';
		$showTbl .= '<tr style="background-color: #efefef;"><th>'. implode('</th><th>', array_values($header_values) ) .'</th></tr>';
		
		echo "Okunan: " . count($rows) . " satır.";
		// insert 
		$ins = inserts($dbconn, $tblName, $col_names, $rows, $crt);
		print_r($ins);
		// print_r($col_names);
		
		foreach ($rows as $row) {
			$showTbl .= '<tr>'; 
			$showTbl .= '<td>'. implode('</td><td>', $row ) .'</td>';
			$showTbl .= '</tr>';
		}
		$showTbl .= "</table>";	
		
		
		if($showTable) {
			echo $showTbl;
		}
	}


	$msc = microtime(true)-$msc;
	echo "<hr>İşlem süresi: ". number_format($msc, 2, ",", ".") ." sn"; // in seconds
	
	echo "</pre>";

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