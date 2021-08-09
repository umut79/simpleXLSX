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


</body>
</html>
