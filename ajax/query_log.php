<?php
include "ajax.inc.php";

include MAIN_DIR . "/db.php";
header("Content-type: application/json");
if(isset($_GET["query_log_id"])){
    $query_log = $db[$abacus_db]->one("SELECT * FROM query_log WHERE create_user=? AND query_log_id=?", [$user, $_GET['query_log_id']]);
    $timer_log = $db[$abacus_db]->query("SELECT name, time, start_dt, end_dt FROM query_log JOIN timer_log USING(query_log_id) WHERE create_user=? AND query_log_id=?", [$user, $_GET['query_log_id']]);
    $result_array = ["query_log"=>$query_log, "timer_log"=>$timer_log];
}
else{
    $result_array = $db[$abacus_db]->query("SELECT * FROM query_log WHERE create_user=? AND completed!=0 ORDER BY query_log_id DESC LIMIT 100;", $user);
}
echo json_encode($result_array);
?>