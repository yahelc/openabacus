<?php
if(!class_exists("db")){
    require_once dirname(__FILE__) . "/db.class.php";
}
require_once dirname(__FILE__) . "/lib/utils.php";
require_once dirname(__FILE__) . "/lib/models.php";
require_once dirname(__FILE__) . "/lib/log.php";
$credentials = null;
$cvs_name = FALSE;
$alert = new BootstrapAlert();


if( !$_SERVER["REMOTE_USER"]  && count($_SERVER["argv"])>0){ //if remote user isn't set, and there are cli arguments, this is a cron user
	 $_SERVER["REMOTE_USER"]  = "cron"; 
	
}
$user = $_SERVER["REMOTE_USER"] ;



//if there are cli args, convert them into post vars. This is for cron. 
if(isset($_SERVER["argv"]) && count($_SERVER["argv"])>0 && !count($_POST)){
    parse_str ($_SERVER['argv'][1], $GLOBALS['_POST']);

}
//if a legacy script isn't setting format, assume table.
if(count($_POST)>0 && !isset($_POST["format"]) ){
	$_POST["format"] =  "table";
}
//if a legacy script isn't setting putout, assume page.
if(count($_POST)>0 && !isset($_POST["output"]) ){
	$_POST["output"] =  "page";
}
$abacus_db = "abacus";
$db[$abacus_db] = new db($config["openabacus_database"]);
$user = new User($_SERVER["REMOTE_USER"]);


$and_restrict = "";


    $clients = [];
	
	$additional_dbs = $db[$abacus_db]->query("SELECT client_slug as cvs_name, client_name, host, 0 as is_framework FROM database_credentials WHERE is_visible=1");
	foreach($additional_dbs as $i=>$additional_db){
		$clients[] = $additional_db;
	}

//if this is a submission. 
if (isset($_POST['cvs_name']) && $_POST['cvs_name'] && ($_POST['cvs_name'] != '')) {
    $cvs_name = $_POST['cvs_name'];

	$query_object = new Query($_POST);

	$credentials = $db[$abacus_db]->one("SELECT host as db_host, 3306 as db_port, user as db_user, password as db_pass, database_name as db_name, is_framework FROM database_credentials WHERE client_slug=?", $cvs_name);
	$is_framework = intval($credentials["is_framework"]);
	
    // Connect to client DB
	$db[$cvs_name] = new db($credentials);

	try{
		 $db[$cvs_name]->query("SELECT 1");
	}
	catch(Exception $e){
		exit("DB is unavailable");
	}
    
    if($is_framework === 1){
        $replication_monitor = new ReplicationMonitor($db[$cvs_name]);
    }
}


foreach($clients as $client) {
    if ($client['cvs_name'] == $cvs_name) {
        $client_name = $client['client_name'];
    }
}
?>
