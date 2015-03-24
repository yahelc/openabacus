<?php
include "ajax.inc.php";
header("Content-type: application/json");
$_POST["cvs_name"] = $_REQUEST["cvs_name"];
include MAIN_DIR . "/db.php";
$upload_dirs = $db[$cvs_name]->query("SELECT upload_dir_id, parent_upload_dir_id, name FROM upload_dir");

$folders = array();
foreach($upload_dirs as $upload_dir){
        $folders[$upload_dir["upload_dir_id"]] = $upload_dir;
}
function getPathForFolder($upload_dir, $text=""){
       global $folders;
        if(  intval($upload_dir["upload_dir_id"]) === 1 || !isset($upload_dir_id["parent_upload_dir_id"])){
                return  $text;
        }
        return getPathForFolder($folders[$upload_dir["parent_upload_dir_id"]],  $upload_dir["name"]."/".$text );
}
$final = array();
foreach($folders as $folder){
        $final[$folder["upload_dir_id"]] ="/". getPathForFolder($folders[$folder["upload_dir_id"]]);
}
echo json_encode($final);
?>
