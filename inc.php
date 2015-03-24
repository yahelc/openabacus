<?php
    
ini_set("include_path", dirname(__FILE__) .'/vendor/Dropbox/HTTP_OAuth/HTTP_OAuth-0.2.3' . PATH_SEPARATOR . dirname(__FILE__) .'/vendor/Dropbox/HTTP_OAuth/HTTP_Request2-2.1.1' . PATH_SEPARATOR . dirname(__FILE__) .'/vendor/Dropbox/HTTP_OAuth/Net_URL2-2.0.0' . PATH_SEPARATOR . dirname(__FILE__) .'/vendor/Mail' . PATH_SEPARATOR . ini_get("include_path"));

require 'PHPMailerAutoload.php';
require("lib/s3.php");
if(isset($_POST["cvs_name"])){
    define("FILE_DIR", 's3://' . $config["s3_bucket"].'/' . $_POST["cvs_name"] . "/");
}
define("MAIN_DIR", dirname(__FILE__) );


    
?>