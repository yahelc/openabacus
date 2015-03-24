<?php
header("Content-type: application/json");
include "ajax.inc.php";

include MAIN_DIR . "/db.php";

	$custom_field_query =  $db[$abacus_db]->one("select slug, query_sql FROM custom_field WHERE slug=? LIMIT 1;", $_REQUEST["slug"]);
	echo json_encode($db[$cvs_name]->query($custom_field_query["query_sql"]));
?>
