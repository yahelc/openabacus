<?php
$explain_log = array();

class BootstrapAlert{
	public function alert($html, $type = false){
		$type = $type ? "alert-".$type : "";
		return "<div style='display:block;' class='alert $type'>" . $html . '<a href="#" class="close" data-dismiss="alert">&times;</a></div>';
	}

	public function success($html){
		return $this->alert($html, "success");
	}
	public function info($html){
		return $this->alert($html, "info");
	}
	public function error($html){
		return $this->alert($html, "error");
	}
	public function warn($html){
		return $this->alert($html, "warn");
	}
    
	
}

$log_queue = array();

$oncomplete_queue = array();
function oncomplete($item){
	global $oncomplete_queue ;
	$oncomplete_queue[] = $item;
}
function complete(){
	global $oncomplete_queue ;
	foreach($oncomplete_queue as $item){
		$item();
	}
}

function admin_alert($subject, $body){
    global $config;
 $mail = new PHPMailer;
 $mail->isSendmail();

 $mail->From = $config['alert_email'];
 $mail->FromName = 'Abacus Alerts';
 $mail->Subject = $subject;

 $mail->addAddress($config['alert_email']);

 $mail->isHTML(true);
 $mail->msgHTML($body);
 $mail->AltBody = strip_tags($body);
 $mail->send();
 
}

function is_client_query_whitelisted(){
	global $db;
	global $abacus_db;
	if(isset($_POST["query"])){
		$query_safety_whitelist = $db[$abacus_db]->one("SELECT COUNT(*) as is_whitelisted FROM query_safety_whitelist JOIN query USING(query_id) WHERE client=? AND slug=?", array($_POST["cvs_name"], $_POST["query"]));

		return !!intval($query_safety_whitelist["is_whitelisted"]);
	}
	return false;
}

function formatBytes($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 

    // Uncomment one of the following alternatives
     $bytes /= pow(1024, $pow);
    // $bytes /= (1 << (10 * $pow)); 

    return round($bytes, $precision) . ' ' . $units[$pow]; 
} 

function consolelog($var){
	global $doctype;
	global $log_queue;
	
	if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	 //do nothing
	}
	else{
		if($doctype){// DEBUG
			echo "<script>console.log(" . json_encode($var) .")</script>";	
		}
		else{
			$log_queue[] = $var;
		}
		
	}
	
}

function startsWith($haystack, $needle)
{
    return !strncmp($haystack, $needle, strlen($needle));
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}



function analyze_query($sql){
	$rows = 1;
	global $db;
	$cvs_name = $_POST["cvs_name"];
	$fallback = array("rows"=>1, "filesort"=>false, "query"=>$sql);
	$sql =  preg_replace("/create.*select/i","SELECT", $sql);
	if(stripos($sql, "select")===false){return $fallback;}
	try{
		$explain= $db[$cvs_name]->query("Explain ".$sql);
		$filesort = false;
		for($i=0; $i<count($explain); $i++ ){
			$rows *= intval($explain[$i]["rows"]);
			if(stripos($explain[$i]["Extra"],"filesort")){
				$filesort = true;
			}
		}
		return array("rows"=>$rows, "filesort"=>$filesort, "query"=>$sql);
		
	}
	catch(Exception $e){
		return $fallback;
	}
}

function is_safe_explain($explain){
	global $explain_log;
	$explain_log[] = $explain;
	$rows = $explain["rows"];
	if($explain["filesort"] && $rows > 10000000){
		return false;
	}
	return true;
}


function upload_to_client_ftp($ftp_data, $csv_file_name, $fn){
	$ftp_function = $ftp_data["secure"] == 1 ? "ftp_ssl_connect" : "ftp_connect";
	$conn_id = $ftp_function($ftp_data["ftp_host"], $ftp_data["port"]);

	$login_result = ftp_login($conn_id, $ftp_data["ftp_user"], $ftp_data["ftp_password"]);
	ftp_pasv($conn_id, true);
	$path = isset($_POST["ftp_path_override"]) && strlen($_POST["ftp_path_override"]) ? $_POST["ftp_path_override"] : $ftp_data["path"];
	$path = $path ."/"; //add a trailing slash to the path, in case of user input error.
    $temp = sys_get_temp_dir() . "/" . $csv_file_name;
    $temp_handle = fopen($temp, "w");
    fwrite($temp_handle, file_get_contents($fn));

    $upload_result = ftp_put($conn_id, $path.$csv_file_name, $temp, FTP_BINARY);
    ftp_close($conn_id);
	unlink($temp);
    return $upload_result; 
}


function client_ftp_credentials($cvs_name){
    global $db;
    global $abacus_db; 
    
	if(isset($_REQUEST["client_ftp_id"]) && intval($_REQUEST["client_ftp_id"]) > 0 ){
		$ftp_data = $db[$abacus_db]->one("SELECT * FROM abacus2.client_ftp WHERE client_ftp_id=? AND client=? LIMIT 1", array(intval($_REQUEST["client_ftp_id"]), $cvs_name) );
	}
	else{ // grandfather in the old setup for scheduled queries. 
		$ftp_data = $db[$abacus_db]->one("SELECT * FROM abacus2.client_ftp WHERE client=? ORDER BY client_ftp_id ASC LIMIT 1", array($cvs_name));
		
	}
    return $ftp_data;
}

class TimerLog{

    private $names = [];
    protected $query_log_id;
    
    public function __construct($query_log_id){
        $this->query_log_id = $query_log_id;
    }
    
    public function start($name , $time = null){
         if(!$time){
             $time =  microtime(true);
         }
         $this->names[$name] = $time;
     }
    public function end($name){
        global $db;
        global $abacus_db;
        
        $end = microtime(true);   
        $diff = round( ( $end - $this->names[$name] )*1000) ; 
        $db[$abacus_db]->query("INSERT INTO timer_log(query_log_id, name, `time`, start_dt, end_dt) VALUES(?, ?, ?, FROM_UNIXTIME(?), FROM_UNIXTIME(?)) ", array($this->query_log_id, $name, $diff,  $this->names[$name], $end));
         
     }
 
     
}

class ReplicationMonitor{
    
    private $connection;
    private $first;
    private $latest;
    
    public function __construct($connection){
        $this->connection = $connection;
        $this->first = $this->check_slave_status();
    }
    
    public function check_slave_status(){
        $this->latest = $this->connection->one("SHOW SLAVE STATUS");
        return $this->latest;
    }
    
    public function analyze(){
    
        if(isset($this->first["Exec_Master_Log_Pos"]) && $this->first["Exec_Master_Log_Pos"] === $this->latest["Exec_Master_Log_Pos"]){
            $messages[] = "The log position hasn't moved.";
        }
        if($this->latest["Slave_IO_Running"] === "No"){
            $messages[] = "The replication IO isn't running";
        }
        if($this->latest["Slave_SQL_Running"] === "No"){
            $messages[] = "The replication SQL isn't running";
        }
        if(intval($this->latest["Seconds_Behind_Master"]) >  0){
            $messages[] = "Replication is at least {$this->latest['Seconds_Behind_Master']} seconds behind the master database";
        }
        
        return (count($messages)) ? $messages : null; 
    
        
    }
}

?>
