<?php
require dirname(__FILE__) . '/../vendor/aws-autoloader.php';
use Aws\S3\S3Client;


if(!isset($abacus_db)){
    if(!function_exists("creds")){
        include dirname(__FILE__) . "/../lib/utils.php";   
    }
    if(!function_exists("db")){
        include dirname(__FILE__) . "/../db.php";   
    }
    
    $abacus_db = "abacus";
    $db[$abacus_db] = new db($config["openabacus_database"]);
}

$s3_client = S3Client::factory( $db[$abacus_db]->one("SELECT `key`, `secret` FROM auth where name='s3';") );
$s3_client->registerStreamWrapper();
