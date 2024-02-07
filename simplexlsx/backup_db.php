<?php

$filename='database_backup_'.date('G_a_m_d_y').'.sql';

$result=exec('mysqldump simple_xlsx --password=4200 --user=root --single-transaction >/backups/'.$filename,$output);

if(empty($output)){
/* no output is good */
} else {
/* we have something to log the output here*/
}

?>