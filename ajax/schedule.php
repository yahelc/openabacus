<?php
include "ajax.inc.php";

include MAIN_DIR . "/db.php";

header("Content-type: application/json");

if(isset($_GET["update"])){
	$db[$abacus_db]->query("UPDATE scheduled_query SET active=(active=0) WHERE scheduled_query_id=?", $_POST["scheduled_query_id"] );
}

if(isset($_GET["edit"])){
	$db[$abacus_db]->query("UPDATE scheduled_query SET post_parameters=:post_parameters WHERE scheduled_query_id=:scheduled_query_id LIMIT 1", ["scheduled_query_id"=> $_POST["scheduled_query_id"], "post_parameters" => $_POST["post_parameters"] ]);
}

if(isset($_GET["delete"])){
	$db[$abacus_db]->query("UPDATE scheduled_query SET is_deleted = 1 WHERE scheduled_query_id = ? LIMIT 1", $_POST["scheduled_query_id"]);
}

if(isset($_GET["delete"]) || isset($_GET["update"]) || isset($_GET["edit"])){
	echo json_encode($db[$abacus_db]->one("SELECT scheduled_query_id as id, active FROM scheduled_query WHERE scheduled_query_id=? AND is_deleted=0", $_POST["scheduled_query_id"])); //should return nothing after a deletion, which is good.
	exit();
}
else if(count($_POST)){
	$post_parameters = $_POST["query_json"];
	$post_var = array();
	parse_str( $_POST["query_json"], $post_var);
	$client = $post_var["cvs_name"];
	$frequency = $_POST["frequency"];
	$run_time = $_POST["time"];
	$name = $_POST["report_name"];
	$query = isset($post_var["query"]) ? $post_var["query"] : "Custom Report";
	$query_row = $db[$abacus_db]->one("SELECT query_id FROM abacus2.query WHERE slug=?", $query);
	
	$query_id = $query_row["query_id"];
	if(!isset($query_row["query_id"])){
		$query_id = null;
	}

	if(strlen($_POST["month"]) >0 ){
		$month =  $_POST["month"];
		$day = null;
		$month_to_run = intval($month) <= intval(date("j"))  ? intval(date("n")) + 1 : intval(date("n"));
		$next_run_date_string = date("Y-m-d", mktime("12","12","12", $month_to_run, intval($month), intval(date("y")) )) . " " . $_POST["time"] ;

	}
	else if(strlen($_POST["day"])>0){
		$day = $_POST["day"];
		$month = null;

		$date_map = array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
		$run_day_adjusted = intval($day) - 1;
		$next_run_date_string = date("Y-m-d", strtotime("next " . $date_map[$run_day_adjusted] ) . " noon") . " " . $_POST["time"]; 
	}
	else{ //daily
		$next_run_date_string = date("Y-m-d", strtotime("tomorrow") ) . " " . $_POST["time"];  
		$month = null;
		$day   = null;
	}


	$query_row = $db[$abacus_db]->query("INSERT INTO abacus2.scheduled_query(query_id, client, frequency, dayofmonth, dayofweek, post_parameters, create_user, active, run_time, next_run_dt, name) VALUES ?", array(array($query_id, $client, $frequency, $month, $day, $post_parameters, $user, 1, $run_time, $next_run_date_string, $name)) );

	echo json_encode(["message"=>$alert->success("Query successfully scheduled. The first query will run: " . $next_run_date_string)]);
	
}
else{
	//get list of scheduled jobs
	header("Content-Type: application/json");
	echo json_encode($db[$abacus_db]->query("SELECT scheduled_query_id as id, client, name, post_parameters, frequency, coalesce(dayofweek, dayofmonth, frequency) as 'Run Day', run_time, last_run_dt, next_run_dt, create_user, active FROM abacus2.scheduled_query WHERE is_deleted = 0 AND (create_user=? OR ? IN(SELECT create_user FROM abacus2.user WHERE user_role_id=4 AND create_user=?)) ORDER BY active DESC, id DESC ", array($user, $user, $user)));
}

?>