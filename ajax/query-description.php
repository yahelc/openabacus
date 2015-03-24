<?php
include "ajax.inc.php";

include MAIN_DIR . "/db.php";
header("Content-type: application/json");
$result_array = $db[$abacus_db]->one("SELECT name, description FROM abacus2.query WHERE slug=?",  $_GET["slug"]);
echo json_encode($result_array);
?>