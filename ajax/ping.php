<?php
header("Content-type: application/json");
include "ajax.inc.php";
$_POST["cvs_name"] = "yahel";
include MAIN_DIR . "/db.php";

$ping = array("ec2_client_db"=>false, "rds_abacus_db"=>false);
$ec2_db =  $db[$cvs_name]->one('select app_config_id FROM app_config LIMIT 1;');
if(intval($ec2_db["app_config_id"]) > 0){
	$ping["ec2_client_db"] = true;
}

$abacus_db_ping = $db[$abacus_db]->one("SELECT query_id FROM query LIMIT 1;");
if(intval($abacus_db_ping["query_id"]) > 0){
	$ping["rds_abacus_db"] = true;
}

echo json_encode($ping);
?>