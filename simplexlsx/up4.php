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
			 $query->bindValue(':'.$key, $val);
			 $bug .= 'db->bindValue(:'.$key.', '.$val.')';
		}
		$insert = $query->execute();
		return $insert?$db->lastInsertId():false;
	}else{
		return false;
	}
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
	foreach ($rows as $row) {
		$ins = insert($dbconn, $dbtable, $row);
		$rid = $ins ? $ins : "x";
		$rowc = $ins ? $rowc+1 : $rowc;
		$showTbl .= '<tr><td>'. $rid .'</td><td>'. implode('</td><td>', $row ) .'</td></tr>';
	}
	
	$showTbl .= "</table><br>" . $rowc . " satır eklendi";	
	
	echo $showTbl;
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

}
?>

<?php
/*

*/

?>
</body>
</html>
