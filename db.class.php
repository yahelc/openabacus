<?php

class db {

	public $connection;
	public $rows;
	public $thread_id; 
	public $host;
    public $insert_id;

   public function __construct($server, $username = false, $password = false, $database = false) {
		if(is_array($server)){ 
			$username = $server["db_user"];
			$password = $server["db_pass"];
			$database = $server["db_name"];
			$server   = $server["db_host"];
		}
		try {
			$this->connect(
				$server,
				$username,
				$password,
				$database);
                
		} catch (Exception $e) {
		   // throw $e;
		}
	}

	public function __destruct() {
		$this->disconnect();
	}

	public function connect($server, $username, $password, $database) {
		$this->connection = mysql_connect($server, $username, $password, true);	
			
		mysql_set_charset("UTF8", $this->connection);
		
		if ($this->connection === false) {
			throw new Exception(mysql_error());
		}

		$select_db = mysql_select_db($database, $this->connection);

		if ($select_db === false) {
			throw new Exception(mysql_error());
			$this->disconnect();
		}
		$this->host = $server;
	}

	public function disconnect() {
		if ($this->connection) {
			mysql_close($this->connection);
			$this->connection = false;
		}
	}
	public function one($sql,  $data = false, $buffer = false){
		$result = $this->query($sql , $data, $buffer);
		return count($result) ? $result[0] : $result;
	}

	public function flat($sql,  $data = false, $buffer = false){
		$result =  $this->query($sql, $data, $buffer);
		$list_arr = array();
		for($i=0; $i<count($result); $i++){
			foreach($result[$i] as $key=>$value){
				$list_arr[] = $value;
			}
		}
		return $list_arr;
	}
	
	private function apply($sql, $data){
		if(!is_array($data)){
			$sql = preg_replace("/\?/", $this->param($data), $sql, 1);
		}
		else{
			foreach($data as $key=>$field){
				if(is_string($key)){
					$sql = str_replace(":". $key, $this->param($field), $sql);
				}
				else{
					$sql = preg_replace("/\?/", $this->param($field), $sql, 1);
				}
			}
		}
		return $sql;
	}

	public function query($sql, $data = false, $buffer = false, $safe_mode = false ) {
		if(isset($data) && $data !== false ){
			$sql = $this->apply($sql, $data);
		}
		if (!$this->connection) {
			throw new Exception('Not connected to server');
		}
		if(is_array($sql)){
			foreach($sql as $query){
				$last_result = $this->query($query, $data, $buffer, $safe_mode);
			}
			return $last_result;
		}
		if($safe_mode && !preg_match("/^explain/i", $sql) ){
			$this->isSafe($sql);
		}

		$unique_key = isset($_POST["uniquekey"]) ? $_POST["uniquekey"] : md5(json_encode($_POST));
		$resource = mysql_query($sql . "/* By: " . $_SERVER["REMOTE_USER"] . " on OpenAbacus {{". $unique_key ."}}. This can be killed if its been running for more than 90 minutes. Please let ". $_SERVER["REMOTE_USER"]  . " know if you've killed it. */", $this->connection);


		if ($resource === false && !preg_match("/^explain/i", $sql)) {
			consolelog($sql);
			echo '<div class="alert alert-danger bs-alert-old-docs">';
			echo '<strong>Attempted query:</strong> ' . $sql;
			echo '<br><strong>Error: </strong>' . mysql_error();
			echo '<pre>';
			throw new Exception(mysql_error()."</div>");
		}

		$this->rows = @mysql_num_rows($resource);
		$this->thread_id = @mysql_thread_id($this->connection);
        $this->insert_id = @mysql_insert_id($this->connection);
		$result = array();
		if ($resource !== true && $buffer === false) {
			while ($row = @mysql_fetch_assoc($resource)) {
				$result[] = $row;
				$row = null;
			} 
			@mysql_free_result($resource);
		}
		elseif($buffer === true){
			$saved = new DBResource($resource);
			return $saved;
		}
		

		

		return $result;
	}

	public function escape($text) { //eventually, stop using this and use sql_param( ) instead
		return mysql_real_escape_string($text, $this->connection);
	}
	
	public function isSafe($sql){

		global $explain_log;
		
		$explain = analyze_query($sql);
		$rows = $explain["rows"];
		
		if(! is_safe_explain($explain) ){
			if(is_client_query_whitelisted()){
				$body = "This query would have been deemed unsafe and rejected, but it was whitelisted for this client/query combo.\n\n It scanned $rows rows and required a filesort.  Here it is: ". $explain["query"] . "\n\nHere's the explain log:\n\n" . json_encode($explain_log) . "\n\nHere's the POST fields:\n\n " . json_encode($_POST); 
				admin_alert("Query Safety Bypassed for ". $_POST["cvs_name"], nl2br($body));
			}
			else{
				$body = "This query would have been rejected by the new per-query safety checker but it's not yet enabled.\n\n It scanned $rows rows and required a filesort.  Here it is: ". $explain["query"] . "\n\nHere's the explain log:\n\n" . json_encode($explain_log) . "\n\nHere's the POST fields:\n\n " . json_encode($_POST); 
				admin_alert("New Query Safety Auto Bypassed (for now) for ". $_POST["cvs_name"], nl2br($body));
			}
			
		}
		
		
		return true; // assume it's safe if we haven't exited at this point.
	}
	public function param($variable){
		if(is_numeric($variable)){
			return $variable;
		}
		else if(is_array($variable)){
			$in_holding_array = array();
			foreach($variable as $item){
				$in_holding_array[] = $this->param($item);
			}
			return "(" . implode(",", $in_holding_array) . ")";
		}
		else if($variable === false || $variable === null ){
			return "NULL";
		}
		return "'" . mysql_real_escape_string($variable, $this->connection) . "'";
		
	}
	

}
class DBResource { 
 public $resource;


public function __construct($resource){
	$this->resource = $resource;
}

public function fetch(){
	return @mysql_fetch_assoc($this->resource); 
}

public function reset(){
	return @mysql_data_seek($this->resource, 0);
}
	
}
