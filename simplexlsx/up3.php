<?php
/**
*  Dosya yuklemesi olmadan excel dosyalarını oku
**/
require_once "src/SimpleXLSX.php";

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
$dbhost = "localhost";
$dbname = "simple_xlsx";
$dbuser = "root";
$dbpass = "4200";

$dbtable = "dokum";



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

	/*
	$rowFields = array( 'student_name' => '', 'roll_no' => '', 'subject' => '' );
	$rowValues[] = array( 'student_name' => 'John Doe', 'roll_no' => 'CC0801', 'subject' => 'Computer Networks' );
	*/

	echo "<hr><pre>Code:<br>";
	// var_dump($rows);
	echo "</pre>";
	
	// tablo 
	$showTbl = '<table border="1" cellpadding="3" style="border-collapse: collapse">';
	$showTbl .= '<tr><th>'. implode('</th><th>', array_values($header_values) ) .'</th></tr>';
	
	
	$pdo = new PDO("mysql:host=$dbhost;dbname=$dbname;charset=utf8",$dbuser,$dbpass);
	$pdo->beginTransaction(); // Speed up your inserts
	$insertvalues = array();

	foreach ($rows as $d) {
		$questionmarks[] = '(' . placeholder( '?', sizeof($d)) . ')';
		$insertvalues = array_merge( $insertvalues, array_values($d) );
		
		$showTbl .= '<tr><td>'. implode('</td><td>', $d ) .'</td></tr>';
	}
	
	$showTbl .= "</table>";	
	echo $showTbl;

	$sql = "INSERT INTO ". $dbtable ." (" . implode( ',', array_values( $header_values ) ) . ") VALUES " . implode( ',', $questionmarks);
	// echo "<code> $sql </code><br>";
	
	$statement = $pdo->prepare($sql);
	try {
		$statement->execute($insertvalues);
		$rc = $statement->rowCount();
		echo ($statement) ? "success ".$rc :"error";
	} catch(PDOException $e) {
		echo $e->getMessage();
	}
	$pdo->commit();

	
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
function insert($table,$data){
	if(!empty($data) && is_array($data)){
		$columns = '';
		$values  = '';
		$i = 0;

		$columnString = implode(',', array_keys($data));
		$valueString = ":".implode(',:', array_keys($data));
		$sql = "INSERT INTO ".$table." (".$columnString.") VALUES (".$valueString.")";
		$query = $this->db->prepare($sql);
		foreach($data as $key=>$val){
			 $query->bindValue(':'.$key, $val);
		}
		$insert = $query->execute();
		return $insert?$this->db->lastInsertId():false;
	}else{
		return false;
	}
}
*/

?>
</body>
</html>
