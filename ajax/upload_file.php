<?php
require '../lib/s3.php';
include "ajax.inc.php";
$file = $_FILES["file"]["name"];
if(isset($_POST["cvs_name"]) && isset($file)){
	$base = basename($_FILES["file"]["name"]);
	$s3_url = "s3://{$config["s3_bucket"]}/" .  $_POST["cvs_name"] ."/". $base;
	file_put_contents($s3_url, file_get_contents($_FILES["file"]["tmp_name"]));
	unset($_FILES["file"]["tmp_name"]);
	
	?>
	
	<div class="alert alert-success"> <strong>OpenAbacus URL:</strong>: <?=$s3_url?>
	
	<p>
	<strong>Direct URL: </strong> <?=$config['url']?>/file/<?=$_POST["cvs_name"]?>/<?=basename($s3_url)?>
		
		
	</div>
	<?php
}


?>

<link rel="stylesheet" href="../css/bootstrap.min.css">
<body style="background:white;">

<form id="upload-file-form" action="upload_file.php" method="post" enctype="multipart/form-data">
    Select file to upload:<p>
    <input type="file" name="file" id="filetoupload">
	<input type="hidden" name="cvs_name" id="upload-client" /><p>
    <input type="submit" value="Upload File" name="Upload" class="btn btn-primary">
</form>
</body>