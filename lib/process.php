<?php
if ($cvs_name &&( isset($_POST["query"]) ||  isset($_POST["straight"]))) {
	
	include MAIN_DIR . '/vendor/SqlFormatter/SqlFormatter.php';
	include "queries.php";

	/* This may be going away, to be replaced by the in-lined one.*/
	for($i = 0; $i<count($sql); $i++){
		$explain = analyze_query($sql[$i]);
		$rows = $explain["rows"];
		if(!is_safe_explain($explain)){
			
			if(is_client_query_whitelisted()){
				$body = "This query would have been deemed unsafe and rejected, but it was whitelisted for this client/query combo.\n\n It scanned $rows rows and required a filesort.  Here it is: ". $explain["query"] . "\n\nHere's the explain log:\n\n" . json_encode($explain_log) . "\n\nHere's the POST fields:\n\n " . json_encode($_POST); 
				admin_alert("Query Safety Bypassed for ". $_POST["cvs_name"], nl2br($body));
			}
			else{
				exit( "<div id='response-section'><div class='alert alert-danger'>FATAL ERROR: The query has been deemed unsafe, scanning $rows rows and requiring a filesort . Here it is:". $explain["query"] . "\n<br>Here's the explain log:" . json_encode($explain_log) . "</div></div>");
				
			}
		}	
		

	}
	
	consolelog($explain_log); 
	ob_flush();
	flush();

	if($_POST["output"] !== "preview"){
		
		$start_time = microtime(true);
        
        if(isset($replication_monitor)){
            $replication_monitor->check_slave_status();
        }
        
		$query_result = $db[$cvs_name]->query($sql, null, true, true);

		$diff =round(( microtime(true) - $start_time)*1000); 
	
		$row_count = $db[$cvs_name]->rows;
        $query_log->update(["row_count"=>$row_count, "query_time"=>$diff]);
        
	}
	else{
		$query_result = $explain_log;
	}

}
?>