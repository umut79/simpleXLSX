<?php
// çalışmıyor
$filename='database_backup_'.date('G_a_m_d_y').'.sql';


//$result=exec('mysqldump simple_xlsx --password=4200 --user=root --single-transaction >/backups/'.$filename,$output);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$database = 'simple_xlsx';
$user = 'root';
$pass = '4200';
$host = 'localhost';
$dir = dirname(__FILE__) . '/'. $filename;

echo "<h3>Backing up database to `<code>{$dir}</code>`</h3>";

exec("mysqldump --user={$user} --password={$pass} --host={$host} {$database} --result-file={$dir} 2>&1", $output);

var_dump($output);

if(empty($output)){
/* no output is good */
} else {
/* we have something to log the output here*/
}

?>