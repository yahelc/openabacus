<?php
header("Content-type: application/json");
include "ajax.inc.php";

include MAIN_DIR . "/db.php";
if($is_framework === 1){
	include  MAIN_DIR . "/bsdapi.php";
}

$bsdapi = new BSDAPI();

echo json_encode($bsdapi->post("cons_group", "add_constituent_groups","<api><cons_group><name>". $_POST["cons_group_name"]  ."</name></cons_group></api>"));


?>