<?PHP
// db setting
if(stristr($_SERVER['SERVER_NAME'], "local")) {
    // Localhost ayarları
  error_reporting(E_ALL & ~E_NOTICE);
	@define("DBhost", "localhost");
	@define("DBuser", "root");
	@define("DBpass", "4200");
	@define("DBname", "a0okulu");
    //define("dev", 1);
} else {
  error_reporting(0);
	@define("DBhost", "localhost");
	@define("DBuser", "fg163_u411z1Z4wa");
	@define("DBpass", "4NKv9Q9x");
	@define("DBname", "fg163u411z1z419u5");
}
// xlsx genenrator
require_once __DIR__."/src/SimpleXLSXGen.php";

function getSql($sql) {
	try {
			$db  = new PDO('mysql:host='.DBhost.';dbname='.DBname.';charset=utf8', DBuser, DBpass);
			$db->exec("SET NAMES utf8");
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$stm = $db->prepare($sql);
			$stm->execute();
			$rc = $stm->rowCount();

			if(!$rc || $rc<=0) {
				$ret['data'] = false;
				$ret['err'] = false;
				$ret['msg'] = "Dönen değer yok!";
			} elseif ($rc == 1) {
				$ret['data'] = $stm->fetch(PDO::FETCH_ASSOC);
				$ret['err'] = false;
				$ret['msg'] = "Sorgu başarılı. ". $rc ." kayıt bulundu.";
			} else {
				$ret['data'] = $stm->fetchAll(PDO::FETCH_ASSOC);
				$ret['err'] = false;
				$ret['msg'] = "Sorgu başarılı. ". $rc. " kayıt bulundu.";
			}
	} catch (PDOException $e) {
		$ret['err'] = 1;
		$ret['msg'] = "Hata: " . $e->getMessage();
	}
	return $ret;
}

?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>XLSXgen</title>
</head>
<body>
<form enctype="multipart/form-data" method="post">
	<input name="q" type="text" value="select * from kurum" />
	<input name="s" type="submit" value="Git" />
</form>

<?php

if($_POST['q'] ) {
	$getData = getSql($_POST['q']);
	if($getData['err']) {
		echo $getData['msg'];
	} else {
		$xlsData = $getData['data'];

		$dh[] = array_keys($xlsData[0]); // headers
		foreach ($xlsData as $key => $value) {
			$dt[] = $value; // rows
		}
		
		$xData = array_merge($dh, $dt); // header + rows
		$xName = date("Y_m_d_his")."_".rand(0,9999).".xlsx";
		$xlsx = new SimpleXLSXGen();
		$xlsx->addSheet( $xData, 'Kurumlar' );
		$xlsx->downloadAs($xName);
		// SimpleXLSXGen::fromArray($xlsData)->downloadAs($xName);
	}
}

?>

</body>
</html>
