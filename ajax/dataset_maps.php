<?php
header("Content-type: application/json");
include "ajax.inc.php";

include MAIN_DIR . "/db.php";

$dataset_maps =  $db[$cvs_name]->flat("SELECT `type` FROM personalization_dataset_map ORDER BY personalization_dataset_map_id DESC ");

echo json_encode($dataset_maps);
?>