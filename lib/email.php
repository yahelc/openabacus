<?php


if(strlen($table_markup)>0){
	$message_html = "<h2 style='font-size: 1.5em; margin: .75em 0;'>" . $client_name . ":  " . $query_object->name() . "</h2>";
	
	$table_css = '<style type="text/css">h2, body {font-family: Helvetica, sans-serif;} table{font-size:12px; border-collapse:collapse; padding: .5em; border: 1px solid black; width: 500px;} td{background-color:#f9f9f9; border:1px solid black; padding:3px;} th{background-color: #99CCFF; padding: 2px; min-width: 50px; border: 1px solid gray}</style>';
	
	$table_markup = str_replace(array("<table",  "<th>", "<td>"), array("\n". '<table style="font-size: 12px; border-collapse: collapse; border: 1px solid black; padding: .5em; width: 500px;" ', "\n". '<th style="min-width: 50px; background-color: #99CCFF; border: 1px solid gray; padding: 2px;" bgcolor="#99CCFF">' . "\n", "\n".'<td style="border: 1px solid black; padding: 3px; background-color: #f9f9f9;">'. "\n"), $table_markup);
	$message_html .= $table_css  . $table_markup;
}


$mime = new PHPMailer;
$mime->isSendmail();

$mime->From = $config["from_email"];
$mime->FromName = 'OpenAbacus Export';
$mime->Subject = $client_name . ' Analytics Export: ' . $query_object->name();


foreach( explode(",", $_POST["emails"]) as $i=>$recipient){
	$mime->addAddress(trim($recipient));
}

$mime->addBCC($config["bcc_reports_email"]);


if($fn && $_POST["output"]==="page"){
	$message_html = "The attached file contains a data export for " . $client_name . " containing:  " . $query_object->name();
	if(filesize($fn) > 7 * 1024 * 1024 ){
		$message_html .= "<br><br>This file will be downloadable for the next 24 hours at the following URL:<br><br>";
		$message_html .= $s3_client->getObjectUrl("{$config["s3_bucket"]}", $cvs_name . "/". $csv_file_name, "+24 hours");
	}
	else{
		$mime->addAttachment($fn);
	}
}

//Dropbox and FTP setters are in output.php
$mime->isHTML(true);
$mime->msgHTML($message_html);
$mime->AltBody = strip_tags($message_html);
$mime->addReplyTo($config["from_email"]);
 
   if($mime->send()){
		echo $alert->success("Email sent to $recipients with the attached data from ". $query_object->name());
	
	}else{
		echo $alert->error('Message could not be sent. Mailer Error: ' . $mime->ErrorInfo);
	    
	}

?>