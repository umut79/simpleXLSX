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
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>...</title>
</head>
<body>
<form enctype="multipart/form-data" method="post">
<input name="file" type="file" />
<input name="sub" type="submit" value="go" />
</form>

<?php
if($xfile) {
  // echo SimpleXLSX::parse($xfile)->toHTML();
	
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
		echo "<pre style='max-height:200px; overflow:auto; border: 1px solid #666; padding:3px'>";
		print_r( $rows );
		echo "</pre>";
		
		echo "<h3>SQLizer</h3>";
		$sql = "";
		foreach($rows as $r) {
			$sql .= "insert into Table ";
			$stn = $dat = array();
			foreach($r as $rk => $rv) {
				$stn[]= "`".$rk."`";
				$dat[]= ($rv) ? "'".$rv."'" : "NULL";
			}
			$sql.= "(". implode(",", $stn) .") VALUES (". implode(",", $dat) .");<br>\n";
		}
		
		echo "<pre style='max-height:200px; overflow:auto; border: 1px solid #666; padding:3px'>";
		print_r( $sql );
		echo "</pre>";
	}
	
	/**
	// Create the SQL statement with PDO placeholders created with regex
		$sql = 'INSERT INTO ' . trim($table) . ' ('
		. implode(', ', array_keys($values)) . ') VALUES ('
		. implode(', ', preg_replace('/^([A-Za-z0-9_-]+)$/', ':${1}', array_keys($values)))
		. ')';
	**/


  echo "<hr>";
  echo "<h3>Örnek 2.1 - toHTML-2</h3>";
  if ( $xlsx = SimpleXLSX::parse($xfile) ) {
  	echo '<table border="1" cellpadding="3" style="border-collapse: collapse">';
  	foreach( $xlsx->rows() as $r ) {
  		echo '<tr><td>'.implode('</td><td>', $r ).'</td></tr>';
  	}
  	echo '</table>';
  }

}
?>

<?php

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


?>
</body>
</html>
