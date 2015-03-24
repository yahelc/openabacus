<?php
header("Content-type: application/json");
include "ajax.inc.php";

include MAIN_DIR . "/db.php";
echo json_encode($clients);?>
