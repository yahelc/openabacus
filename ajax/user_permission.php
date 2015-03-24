<?php
header("Content-type: application/json");
include "ajax.inc.php";
include MAIN_DIR . "/db.php";

if(!$user->hasPermissions("Administrator")){exit('{"Error":"You do not have permission to perform this action"}'); }


$level = intval($_POST["level"]);
$user = $_POST["user"];
if($level < 0 || $level > 4){
	exit('{"Error":"No such level"}');
}

$db[$abacus_db]->query("UPDATE user SET user_role_id=?  WHERE create_user=?", array($level, $user));
echo json_encode($db[$abacus_db]->one("SELECT user_role_id FROM user WHERE create_user=?", $user));
?>