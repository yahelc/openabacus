<?php
$query = "select * FROM scheduled_query WHERE next_run_dt<now() AND active=1 AND is_deleted=0";
if(isset($_POST["hourlycron"])){
	$query .= " AND frequency ='hourly' ";
}
else{
	$query .= " AND frequency != 'hourly' "; 
}
$job = $db[$abacus_db]->one($query . " ORDER BY RAND() LIMIT 1");

if(!$job){
	exit();  
}

parse_str($job["post_parameters"], $GLOBALS['_POST']);
$_POST["ajax"] = "true";
$cvs_name = $cvs_name ? $cvs_name : $_POST['cvs_name'];
include MAIN_DIR . '/db.php'; 
	

$frequency = $job["frequency"];
		
	switch ($frequency) {
	    case "weekly":
	        $interval = "INTERVAL 7 DAY";
	        break;
	    case "monthly":
	         $interval = "INTERVAL 1 MONTH";
	        break;
	    case "daily":
	         $interval = "INTERVAL 1 DAY";
	        break;
	    case "hourly":
	         $interval = "INTERVAL 1 HOUR";
	        break;
		default:
			$interval = "INTERVAL 1 DAY";
	}

		
$db[$abacus_db]->query("UPDATE scheduled_query SET last_run_dt=next_run_dt WHERE scheduled_query_id=?", $job["scheduled_query_id"]);		

$db[$abacus_db]->query("UPDATE scheduled_query SET next_run_dt=DATE_ADD(next_run_dt, $interval) WHERE scheduled_query_id=?", $job["scheduled_query_id"] );		

$date_break_loop = true;
while($date_break_loop){
	$date_status =  $db[$abacus_db]->query("SELECT next_run_dt, last_run_dt, now() as now FROM scheduled_query WHERE now() > next_run_dt AND scheduled_query_id=?", $job["scheduled_query_id"] );
	if(count($date_status)){
		$db[$abacus_db]->query("UPDATE scheduled_query SET last_run_dt=next_run_dt WHERE scheduled_query_id=?", $job["scheduled_query_id"]);		
		$db[$abacus_db]->query("UPDATE scheduled_query SET next_run_dt=DATE_ADD(next_run_dt, $interval) WHERE scheduled_query_id=?", $job["scheduled_query_id"]);		
		
	}
	else{
		$date_break_loop = false;
	}
}

?>