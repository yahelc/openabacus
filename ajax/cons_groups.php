<?php
header("Content-type: application/json");
include "ajax.inc.php";

include MAIN_DIR . "/db.php";

$cons_groups =  $db[$cvs_name]->query("SELECT cons_group_id, name, membership_resource FROM cons_group ORDER BY cons_group_id DESC");

echo json_encode($cons_groups);
?>