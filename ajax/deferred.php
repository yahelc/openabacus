<?php
header("Content-type: application/json");
include "ajax.inc.php";
include MAIN_DIR . "/db.php";
include MAIN_DIR . "/bsdapi.php";
$bsdapi = new BSDAPI();
$deferred_results = $bsdapi->get("get_deferred_results", null, array("deferred_id"=> $_POST["deferred_id"]));
echo json_encode($deferred_results);
?>