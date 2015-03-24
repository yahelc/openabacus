<?php
include "ajax.inc.php";

include MAIN_DIR . "/db.php";
header("Content-type: application/json");

$ftp_settings = $db[$abacus_db]->query("SELECT client_ftp_id, ftp_host, ftp_user, path FROM abacus2.client_ftp WHERE client=? ORDER BY client_ftp_id DESC", array($cvs_name));

echo json_encode($ftp_settings);

?>