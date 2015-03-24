<?php

require(MAIN_DIR .'/vendor/Twig/lib/Twig/Autoloader.php');
Twig_Autoloader::register();

$loader = new Twig_Loader_String();
$twig = new Twig_Environment($loader, array(
    'autoescape' => false
));

date_default_timezone_set($config["time_zone"]);
$tz = $config["time_zone"];



$load_random = time()."_". rand(1,100);

function generateTemporaryTableName($string){
	global $load_random;
	return $string . "_" . $load_random;
}

function generateTableName($string){
	global $load_random;
	return "test." .$string . "_" . $load_random;
}
$twig->addFunction(new Twig_SimpleFunction('tmp', 'generateTableName'));
$twig->addFunction(new Twig_SimpleFunction('tbl', 'generateTableName'));


function generateTZstring($date, $time_zone, $offset){
	global $tz;
	$timestamp = date("Y-m-d H:i:s", strtotime($date) + $offset);
	if (isset($time_zone) && $tz !== $time_zone) {
		// " CONVERT_TZ('2014-01-01 00:00:00', 'US/Pacific', 'UTC') "
		return "CONVERT_TZ('$timestamp', '$time_zone', '$tz')";
	} else {
		// don't bother writing an unnecessary conversion
		return "'$timestamp' ";
	}
}

/* DEPRECATED FUNCTION. Remove once no longer in use in query.query_sql*/
function generateDateAppend($field_name){
	$sql = "";
	if(isset($_POST["start_dt"]) && $_POST["start_dt"]!= ""){
		$sql .= "AND $field_name >= " . generateTZstring($_POST["start_dt"], $_POST["timezone"], 0);		 
	}
	if(isset($_POST["end_dt"]) &&  $_POST["end_dt"]!= ""){
		$sql .= " AND $field_name <= " . generateTZstring($_POST["end_dt"], $_POST["timezone"] ,24*3600-1);
	}
	return $sql;
}
$twig->addFunction(new Twig_SimpleFunction('create_dt', 'generateDateAppend'));


function dateRangeSQL($field_name){
	$sql = array();
	if(isset($_POST["start_dt"]) && $_POST["start_dt"]!= ""){		 
		$sql[] = " $field_name >= " .generateTZstring($_POST["start_dt"], $_POST["timezone"], 0) . " ";
	}
	if(isset($_POST["end_dt"]) &&  $_POST["end_dt"]!= ""){
		$sql[] = " $field_name <= " . generateTZstring($_POST["end_dt"], $_POST["timezone"], 24*3600-1) . " ";
	}
	return (count($sql) === 0) ? null : implode(" AND ", $sql);
}
$twig->addFunction(new Twig_SimpleFunction('date_range', 'dateRangeSQL'));


function valueRangeSQL($field_name){
	$sql = array();
	if(isset($_POST["min_value"]) && $_POST["min_value"] !== ""){
		$sql[] = "$field_name >= {$_POST['min_value']}";
	}
	if(isset($_POST["max_value"]) && $_POST["max_value"] !== ""){
		$sql[] = "$field_name <= {$_POST['max_value']}";
	}
	return (count($sql) === 0) ? null : implode(" AND ", $sql);
}
$twig->addFunction(new Twig_SimpleFunction('value_range', 'valueRangeSQL'));


function eval_sql($sql){
	global $db;
	global $cvs_name;
	return $db[$cvs_name]->query($sql);
}
$twig->addFunction(new Twig_SimpleFunction('eval_sql', 'eval_sql'));


function load_csv($table, $url){
	$temp = sys_get_temp_dir() . "/abacus-tmp-" . basename($url);
	$temp_handle = fopen($temp, "w");

	
	fwrite($temp_handle, str_replace("\r\n", "\n", file_get_contents($url)));
	fclose($temp_handle);
	oncomplete(function() use ($temp){ // this has to happen later or else the file will be deleted before it can be used.
		unlink($temp);
	});
	return "LOAD DATA LOCAL INFILE '$temp' INTO TABLE $table FIELDS TERMINATED BY ','  OPTIONALLY ENCLOSED BY '\"'  LINES TERMINATED BY '" . '\n' . "' IGNORE 1 LINES;";
}
$twig->addFunction( new Twig_SimpleFunction('load_csv', 'load_csv'));

	



function twig_mysql_escape($variable){
	global $db;
	global $cvs_name;
	return $db[$cvs_name]->param($variable);		
}
$twig->addFunction(new Twig_SimpleFunction("sql", "twig_mysql_escape"));
$twig->addFilter(new Twig_SimpleFilter('sql', 'twig_mysql_escape'));
	

function get_all_groups(){
	global $db;
	global $cvs_name;
	$groups = array();
	$groups_db = $db[$cvs_name]->query("SELECT name, membership_resource FROM cons_group");
	global $name_resource;
	$name_resource = $groups_db;
	foreach($groups_db as $row){
		$groups[] = $row["membership_resource"];
	}
	return $groups;
}
$twig->addFunction(new Twig_SimpleFunction("get_all_groups", "get_all_groups"));
	
	  
	function use_fn($service){
        //@TODO: Re-implement using database_credentials table
	}
	$twig->addFunction(new Twig_SimpleFunction("use", "use_fn"));

function pluck($array, $key){ 
	return array_map(function($array) use($key) {
		return $array[$key];
	}, $array);
}
$twig->addFunction(new Twig_SimpleFunction("pluck", "pluck"));
$twig->addFilter(new Twig_SimpleFilter('pluck', 'pluck'));
	
	
$twig->addFunction(new Twig_SimpleFunction("unserialize", "unserialize"));
$twig->addFilter(new Twig_SimpleFilter('unserialize', 'unserialize'));


function json_decode_array($str){
    return json_decode($str, true);
}
$twig->addFunction(new Twig_SimpleFunction('json_decode', 'json_decode_array'));
$twig->addFilter(new Twig_SimpleFilter('json_decode', 'json_decode_array'));
	
	
function killall(){
	global $db;
	global $cvs_name;
	$ids = array();
	$show_sql = "SHOW FULL PROCESSLIST /*{showprocesslistpoll}*/";
	$process_list = $db[$cvs_name]->query($show_sql);
	foreach($process_list as $process){
		if($process["Info"] && !preg_match("/SHOW FULL PROCESSLIST/i", $process["Info"]) && stripos($process["Info"], "on Abacus {{") ){
				preg_match("/By\: (.*) on OpenAbacus/", $process["Info"], $matches);
			$running_user = trim($matches[1]);
			if($running_user === $_SERVER["REMOTE_USER"]){
				$ids[] = "KILL " .$process["Id"] .";";
			}
		}
	}
	$ids[] = $show_sql;
	return implode("\n", $ids);
}
$twig->addFunction(new Twig_SimpleFunction("killall", "killall"));
	

function generateSQLFromTwig($sql, $twig, $template){
	$template = $twig->loadTemplate( $sql);
	return array_filter(SqlFormatter::splitQuery(trim( $template->render($_POST) )));
}

?>