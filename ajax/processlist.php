<?php 
include "ajax.inc.php";

header("Content-type: application/json");
include MAIN_DIR . "/db.php";
$list = array();
$query_result = $db[$cvs_name]->query("SHOW FULL PROCESSLIST; /*{showprocesslistpoll}*/");

foreach($query_result as $index => $row) {
		$list[] = $row;
}
echo json_encode($list);
?>