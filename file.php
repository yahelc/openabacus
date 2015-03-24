<?php
require dirname(__FILE__) . "/lib/s3.php";

$file = $_GET["file"];
$client = $_GET["client"];
$data = file_get_contents('s3://'. $config["s3_bucket"] . '/' .$client .  "/". $file);
header('Content-Disposition: attachment; filename='.$file);

echo $data;
