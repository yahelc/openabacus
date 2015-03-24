<?php

class QueryLog{
    private $abacus_db_connection;
    public $query_log_id;
    
	public function __construct($cvs_name, $query_object, $job){
		global $db;
		global $abacus_db;
        $this->abacus_db_connection = $db[$abacus_db];        
        
        $log_query_name = $query_object->isSavedQuery() ? $query_object->slug : "Direct";
        $scheduled_query_id = (isset($job)) ? $job["scheduled_query_id"] : null;
    	$post_parameters = (isset($job)) ? $job["post_parameters"] : http_build_query($_POST);
        
        
		$log_sql = "INSERT INTO abacus2.query_log (query_name, client, post_parameters, create_user, scheduled_query_id) VALUES(:query_name, :client, :post_parameters, :create_user, :scheduled_query_id)";
    	try {
        		
		    $db[$abacus_db]->query($log_sql, array("query_name"=>$log_query_name, "client"=> $cvs_name, "post_parameters"=> $post_parameters, "create_user"=> $_SERVER["REMOTE_USER"],  "scheduled_query_id"=> $scheduled_query_id));
	        $this->query_log_id = $db[$abacus_db]->insert_id;    
        
        }
        catch (Exception $e) {
       	   consolelog($e->getMessage());
       	}
        
	}
    public function update($data){
       
       $fields = [];
        foreach($data as $col=>$val){
            $fields[] = "$col=:$col";
        }           
        $sql = "UPDATE query_log SET " .  implode(",", $fields)  .  " WHERE query_log_id={$this->query_log_id} LIMIT 1";
        $this->abacus_db_connection->query($sql, $data);
    }
}

?>
