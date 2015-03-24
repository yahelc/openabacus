<?php

consolelog("hi");

$header_set = false;
$table_header = "";
$table_body =  "";
if($_POST["format"]==="table" || $_POST["output"] === "preview"){
	$i = 0; 
	while ($row = $query_result->fetch()) {  
		$table_body.= "<tr>";
		foreach($row as $key=>$value){
			if(!$header_set){
				$table_header .= "<th>".htmlspecialchars( $key, ENT_NOQUOTES, "UTF-8")."</th>";
			}
			$table_body.=  "<td>" . htmlspecialchars($value, ENT_NOQUOTES, "UTF-8") . "</td>";
		}
		$table_body.= "</tr>";
		$header_set = true;
		if(++$i >= 5000){
			break; //limit to 5k rows of HTML
		}
	}
	
	$table_markup = "<table id='results' class='table table-bordered table-striped'><thead><tr>$table_header</tr>"  . "</thead><tbody>$table_body</tbody></table>";
}
if($_POST["format"] === "csv" || $_POST["format"] === "tsv" || $_POST["format"] === "js" ){	
	
	$file_suffix =  $_POST["format"] === "tsv" ? "txt" : $_POST["format"];
	if($_POST["format"] === "csv" || $_POST["format"] === "tsv" ){
		$delimiter = $_POST["format"] === "tsv" ? "\t" : ",";
		$csv_file_name =  $cvs_name . "-" . $query_object->file_slug . "-" .  date("Ymd-Gis")  . '.' . $file_suffix ;
		$fn = "s3://{$config["s3_bucket"]}/$cvs_name/" . $csv_file_name;
		$fp = fopen($fn, 'w');
		
		$title_set = false;
        $timer_log->start("mainfilewriting");
		while ($row = $query_result->fetch()) {
			if(!$title_set){
					fputcsv($fp, array_keys($row), $delimiter);	
					$title_set = true;
			}
	        fputcsv($fp, array_values($row), $delimiter);	
		}
        $timer_log->end("mainfilewriting");
	    
	}
	if($_POST["format"] === "js"){
		$json = array();
		$csv_file_name =  $cvs_name."-" . $query_object->file_slug . '.' . $file_suffix ;
		$fn = "s3://{$config["s3_bucket"]}/$cvs_name/" . $csv_file_name;
		$fp = fopen($fn, 'w');
		
		while ($row = $query_result->fetch()) {
			$json[] = $row; 
		}
		if(isset($_POST["callback"]) && strlen($_POST["callback"]) > 0 ){
			$json = $_POST["callback"]  . "(" . json_encode($json) . " )";
		}
		else{
			$json = json_encode($json);
		}
		fwrite($fp, $json);
	}
	
	fclose($fp);
	
	
	if(isset($_POST["zip_file"]) && $_POST["zip_file"] === "zip_file"){		
		
			$temp = sys_get_temp_dir() . "/" . $csv_file_name;
			$temp_handle = fopen($temp, "w"); 
			fwrite($temp_handle, file_get_contents($fn));
			
		
			$zip_file_name = $csv_file_name .".zip";
			$zip = new ZipArchive;
			$zip->open( sys_get_temp_dir() . "/" . $zip_file_name, ZipArchive::CREATE);
			$zip->addFile( $temp, basename($temp));
			$zip->close();
			
			//replace csv_file_name and fn with a zipped version of each
			$fn =  sys_get_temp_dir() . "/" . $zip_file_name;
			$csv_file_name = $zip_file_name;
			file_put_contents("s3://{$config["s3_bucket"]}/$cvs_name/$zip_file_name", file_get_contents($fn));
			fclose($temp_handle);
			unlink($temp);
			unlink($fn);
			$fn = "s3://{$config["s3_bucket"]}/$cvs_name/$zip_file_name";
	}
    
    
    $query_log->update(["file"=> $csv_file_name]);
    $query_log->update(["file_size"=> @filesize($fn)]);
	
	if($_POST["output"]==="ftp"){

        $ftp_data = client_ftp_credentials($cvs_name);
        
		if($ftp_data){
            
            $upload_result = upload_to_client_ftp($ftp_data, $csv_file_name, $fn);
			
			if($upload_result){
				echo $alert->success("The file has been uploaded to the FTP server.");
				$message_html = "$csv_file_name containing " .$query_object->name() . " for $client_name has been delivered to the $cvs_name FTP folder.";
				
			}
		    else{
				//FAIL
				echo $alert->error("Failed to upload. ");
				$message_html = "$csv_file_name containing " . $query_object->name() . " for $client_name has failed to upload to the FTP folder.";
                $retry_url = $config['url'] . "/retry.php?type=ftp&file_name=$csv_file_name&url=$fn&".http_build_query($_POST);
				admin_alert("FTP Upload Failure: $cvs_name", nl2br($message_html . "\n\nRun by: $user" . "\n\nRetry upload: <a href='$retry_url'>$retry_url</a>\n\n" . json_encode($_POST) ));
			}
			
		}
		else{
			echo $alert->error("FTP not configured for this client.");
		}
		
		
	}
	
	if($_POST["output"] === "sftp"){
		//get SFTP creds for client
		$sftp_credentials =  $db[$abacus_db]->one("SELECT * FROM client_sftp WHERE client=? LIMIT 1", $cvs_name);			
		
 		//fail if they don't exist.
		if( !$sftp_credentials ){
			exit("No SFTP credentials for $cvs_name found to upload the file: $fn");
		}
 		ob_flush();
		//connect
		$connection = ssh2_connect($sftp_credentials["sftp_host"], $sftp_credentials["sftp_port"]);	 	

	 	if(!is_null($sftp_credentials["public_key"]) && !is_null($sftp_credentials["private_key"])) {
		
			$public_key_path = tempnam("/tmp",$cvs_name);
			$private_key_path = tempnam("/tmp",$cvs_name);
			
			file_put_contents($public_key_path , $sftp_credentials["public_key"]);
			file_put_contents($private_key_path, $sftp_credentials["private_key"]);
	 		
			chmod($private_key_path, 6644);
			chmod($public_key_path,  6644);
			

			if (ssh2_auth_pubkey_file($connection, $sftp_credentials["sftp_user"],
			                          $public_key_path,
			                         $private_key_path, $sftp_credentials["sftp_password"])) {
			  echo "\nPublic Key Authentication Successful. \n";
			} else {
				print_r(error_get_last());
			  die('Public Key Authentication Failed');
			}
		}
		else{
			if(!is_null($sftp_credentials['sftp_user']) && !is_null($sftp_credentials['sftp_password'])) {
				//authenticate with username and password
				if (ssh2_auth_password($connection, $sftp_credentials["sftp_user"], $sftp_credentials["sftp_password"])) {
				  echo "\nUsername/Pass Authentication Successful. \n";
				} else {
					print_r(error_get_last());	
				  die('Username/Pass Authentication Failed');
				}
			}
			else{
				die('No SFTP username and/or password provided');
			}
		}
		
		$path = isset($sftp_credentials["path"]) && strlen($sftp_credentials["path"]) > 0 ? $sftp_credentials["path"] : "/./";
		
		$dstFile = $path . $csv_file_name;
 		
		$sftp = ssh2_sftp($connection);
		$sftpStream = fopen('ssh2.sftp://'.$sftp.$dstFile, 'w');
 		
		$data_to_send = file_get_contents($fn);
		try {
		if(!$sftpStream) {
		        throw new Exception("Could not open remote file: $dstFile");
		    }

		    if ($data_to_send === false) {
		        throw new Exception("Could not open local file: $fn.");
		    }
		    if (fwrite($sftpStream, $data_to_send) === false) {
		throw new Exception("Could not send data from file: $fn.");
		    }
		    else{echo "\n Successfully uploaded the file.\n";
		}
		    fclose($sftpStream);

		} catch (Exception $e) {
		    error_log('Exception: ' . $e->getMessage());
		    fclose($sftpStream);
		}
		@unlink($public_key_path);
		@unlink($private_key_path);
		
	}

	if($_POST["output"] === "cons_group_output"){ 

		$membership_resource = $_POST["output_cons_group"];

		
		$cons_group = $db[$cvs_name]->one("SELECT cons_group_id, chapter_id FROM cons_group WHERE membership_resource=?", $membership_resource);
		$cons_group_id = $cons_group["cons_group_id"];
		
		if(isset($cons_group_id)){
			// employee users can't write to any cons group that already has 1 or more members
			$count = $db[$cvs_name]->one("SELECT count(*) as count FROM $membership_resource");
			if(intval($count["count"]) > 0 && $user->is("Employee")){
				exit("For Employee access level users, we only support empty groups (so you don't mistakenly upload into a group someone is already using). Ask an Abacus admin for help.");
			}
		}
        else{
            admin_alert("Query attempted to upload to non-existant group", json_encode($_POST));
			echo $alert->error("<strong>FATAL ERROR: Attempted to upload results into a non-existant constituent group." );
            
            exit();
        }
		
        if(!isset($bsdapi)){
    		$bsdapi = new BSDAPI();
        }
        
		$bsdapi->setChapter($cons_group["chapter_id"]);
		$defer_queue = array();
		
		function doPut($method, $cons_group_id, $cons_ids){
			global $bsdapi;
			$cons_id_str = implode("\n", $cons_ids);
			//set_cons_ids_for_group will override all constituents in the group
			//add_cons_ids_to_group will simply add new ones
			$v = $bsdapi->put("cons_group", $method, $cons_id_str, "cons_group_id=$cons_group_id", true);
			return $v["body"];
		}
		
		$cons_ids = array();

		$query_result->reset();
		$i = 0;
		while ($cons_id = $query_result->fetch()) {
			$i++;
			$cons_ids[] = $cons_id["cons_id"];
		}
		$method =  $_POST["overwrite_group"] === "overwrite_group"  ? "set_cons_ids_for_group" : "add_cons_ids_to_group";
		$defer_queue[] = doPut($method, $cons_group_id, $cons_ids);
		echo $alert->success("Successfully queued an upload of $i constituents into the cons group. <span id=completedbatches>" . count($defer_queue) . "</span> task(s) remaining to be processed.");
		echo "<script>\n  poll_deferred_ids(" . json_encode($defer_queue) . ");\n</script>"; 
	
	}
	if($_POST["output"] ==="dataset_map" || $_POST["output"] === "dataset" ){
		$data = array();
		if($_POST["format"] !== "csv"){
			exit("Datasets must be in CSV format.");
		}
		
		if($_POST["output"] === "dataset"){
			$data =  array("slug"=>$_POST["dataset_slug"], "map_type"=> $_POST["map_type"]) ;
		}
		
		$bsdapi = new BSDAPI();
		
		$dataset = $bsdapi->put("cons","upload_" . $_POST["output"], file_get_contents($fn), $data, true);
		if(intval( $dataset["http_code"])<300){
			echo $alert->success("Successfully uploaded the " . $_POST["output"] ." to be processed.");
		}
		else{
			echo $alert->error("<strong>FATAL ERROR: API returned non-success code.</strong><p> Response:" . htmlspecialchars(json_encode($dataset)));
		}
	}

	
	
	if($_POST["output"] === "dropbox"){
		require MAIN_DIR . '/vendor/Dropbox/HTTP_OAuth/HTTP_OAuth-0.2.3/HTTP/OAuth/Consumer.php';
		require MAIN_DIR . '/vendor/Dropbox/autoload.php';
		$consumerKey = "vw72kbg2g7v7t1f";
		$consumerSecret = "l4og87nr4xhv7vs";
		$accessToken = '{"token":"mk5h791eyzbbg7p","token_secret":"mf2tek0ho8sbf7m"}';
		$oauth = new Dropbox_OAuth_PEAR($consumerKey, $consumerSecret);
		$oauth->setToken(json_decode($accessToken, true));
		$dropbox = new Dropbox_API($oauth);
		$file = $dropbox->putFile("$cvs_name/" . rawurlencode($csv_file_name),$fn); 
		if($file){
			echo $alert->success("$csv_file_name uploaded to the $cvs_name folder in the Tech Dropbox");
			$message_html = "$csv_file_name containing " . $query_object->name() . " has been delivered to the $cvs_name Dropbox folder.";
		}
		else{
			echo $alert->error("FAILED to upload $csv_file_name to the $cvs_name folder in the Dropbox");
			$message_html = "Failed to upload  $csv_file_name to the $cvs_name containing  " .  $query_object->name(). " to the BSD Tech Dropbox.";
		}
	}

	if($_POST["output"] === "s3"){
        if(!isset($bsdapi)){
    		$bsdapi = new BSDAPI();
        }
		
		$temp = sys_get_temp_dir() . "/" . $csv_file_name;
		$temp_handle = fopen($temp, "w");
		fwrite($temp_handle, file_get_contents($fn));
		$resp = $bsdapi->upload_file($temp, $_POST["upload_dir_id"]);
		
		fclose($temp_handle);
		unlink($temp);
		
		$response_array = json_decode($resp["body"], true);
		if($response_array["upload_file_id"]){
			$parsed_url = parse_url($response_array["url"]);
			$cdn_url = "https://s.bsd.net/$cvs_name/default" . $parsed_url["path"];
			echo $alert->success("Successfully uploaded file. <p>BSD Domain URL: <a href='". $response_array["url"] . "'>" . $response_array["url"] . "</a><p>CDN URL: <a href='". $cdn_url ."'>". $cdn_url .  "</a>" );
			$message_html = "$csv_file_name containing " . $query_object->name() . " for $client_name has been uploaded to the $cvs_name folder. <p>BSD Domain URL: <a href='". $response_array["url"] . "'>" . $response_array["url"] . "</a><p>CDN URL: <a href='". $cdn_url ."'>". $cdn_url .  "</a>";
			
			
		}else{
			echo $alert->error("FATAL ERROR: ".$resp["body"]. $tmp . json_encode($fn) . json_encode($resp));
		}
		
	}
}



if(isset($_POST["emails"]) && $_POST["emails"] !=""){	
	include "email.php";
}


if(isset($replication_monitor)){

    $messages = $replication_monitor->analyze();
    
    if($messages){
        echo $alert->warn("<strong>This query might be returning stale data due to the following replication problems</strong><li>" . implode("</li><li>", $messages) ."</ul>");
        admin_alert("Query run by $user encountered replication warning: ", implode("\n", $messages) . "\n". json_encode($_POST, JSON_PRETTY_PRINT));
        
    }
    
}



if(isset($csv_file_name)){
	echo "<div class='form-actions'><a id='csvlink' download style='display:none;' class='btn btn-large btn-success' href='file/$cvs_name/$csv_file_name'>Download File</a></div>";
	
}
echo "<div class='row'><div class='span16 table-bg'> " .  $table_markup .  "</div></div>";

	


?>
