<?php
require "inc.php";
include "db.php";

if($_GET["type"] === "ftp"){
    
    $ftp_data = client_ftp_credentials($_GET["cvs_name"]);
    
	if($ftp_data){
        $csv_file_name =  $_GET["file_name"];
        $cvs_name = $_GET["cvs_name"];
        $upload_result = upload_to_client_ftp($ftp_data, $csv_file_name, $_GET["url"]);
		
		if($upload_result){
			$message_html = "$csv_file_name  has been delivered to the $cvs_name FTP folder.";
			echo $alert->success("The file has been uploaded to the FTP server. $message_html");
            admin_alert("FTP Upload successfully retried: $cvs_name", "$csv_file_name was successfully retried by $user and has been uploaded to the FTP.");
			
		}
	    else{
			//FAIL
			$message_html = "$csv_file_name for $cvs_name has failed to upload to the FTP server.";
			echo $alert->error("Failed to upload. $message_html");
            
		}
		
	}
	else{
		echo $alert->error("FTP not configured for this client.");
	}
}
