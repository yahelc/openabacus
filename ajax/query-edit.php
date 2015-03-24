<?php
include "ajax.inc.php";

include MAIN_DIR . "/db.php";
header("Content-type: application/json");
$query_slug = $_REQUEST["slug"];

if(count($_GET)){
	$result_array = $db[$abacus_db]->one("SELECT query.*, GROUP_CONCAT(custom_field_id) as custom_fields FROM abacus2.query LEFT JOIN query__custom_field qcf ON qcf.query_id=query.query_id AND qcf.active=1 WHERE slug=? GROUP BY query_id;", $query_slug);
	echo json_encode($result_array);
	
}
else if(count($_POST)){
	//UPDATE OR CREATE
	$name = $_POST["query_name"]; 
	$slug = $_POST["query_slug"]; 
	$sql  = $_POST["twigsql"]; 
	$global  = isset($_POST["makeclientspecific"]) && $_POST["makeclientspecific"] === "makeclientspecific" ? 0 : 1;
	$public  = isset($_POST["makeprivate"]) && $_POST["makeprivate"] === "makeprivate" ? 0 : 1;
	$client  =  $_POST["cvs_name"];
	$min_user_role_id = intval($_POST["min_user_role_id"]) > 0 ?  intval($_POST["min_user_role_id"]) : 1 ;
	$description =  $_POST["query-description"];
	
	// enforce permissioning
	function reject_post($txt){
		echo json_encode($txt);
		exit();
	}
	$query = $db[$abacus_db]->one("SELECT query_id, min_user_role_id, public, create_user FROM abacus2.query WHERE slug=?", $slug);
	
	if(isset($id["query_id"])){ // this is an update
		
		if(!$user->hasPermissions("Developer")){
		  if($user != $id["create_user"]){
			//cant edit other ppls queries
			reject_post(array("error"=>"You do not have permission to edit other user's saved queries."));
		  }
		  elseif($id["public"] === 0){
			//cant edit public queries
			reject_post(array("error"=>"You do not have permission to edit public queries."));
		}
		}
	}
	else{ //this is a create
		if($public === 1 && !$user->hasPermissions("Developer") ){
			//cant creat
			reject_post(array("error"=>"You do not have permission to create public queries."));
		}
	}

	// reject non-private queries from analysts and employees
	
	$res = $db[$abacus_db]->query("INSERT INTO abacus2.query(slug, name, query_sql, global, public, create_user, min_user_role_id, description) VALUES(:slug, :name, :sql, :global, :public, :user, :min_user_role_id, :description) ON DUPLICATE KEY UPDATE query_sql = VALUES(query_sql), name = VALUES(name), global = VALUES(global), public = VALUES(public), min_user_role_id = VALUES(min_user_role_id), description = VALUES(description)", array("slug"=>$slug, "name"=>$name, "sql"=>$sql, "global"=>$global, "public"=>$public, "user"=>$user, "min_user_role_id"=>$min_user_role_id, "description"=>$description));
	
	$id = $db[$abacus_db]->one("SELECT query_id FROM abacus2.query WHERE slug=?", array($slug));
	$query_id = $id["query_id"];
	if(!$global && count($id)){
			$db[$abacus_db]->query("INSERT IGNORE INTO abacus2.client_query_map(query_id, client) VALUES(?, ?)", array($query_id, $client));
	}
	else if($global === 1){ // if global, make sure we don't have any orphan client/query mappings
		$db[$abacus_db]->query("DELETE FROM abacus2.client_query_map WHERE query_id=?", array($query_id));
	}
	$custom_fields = $_POST["custom_fields_for_query"];
	
	
	if(count($custom_fields)){
		$custom_field_ids = array();
		foreach($custom_fields as $custom_field){
			$custom_field_ids[] = $custom_field;
			$db[$abacus_db]->query("INSERT IGNORE INTO abacus2.query__custom_field(query_id, custom_field_id, active) VALUES(?, ?, 1);", array($query_id, $custom_field)); 
		}
		$db[$abacus_db]->query("UPDATE abacus2.query__custom_field SET active=0 WHERE query_id=? AND custom_field_id NOT IN ?", array($query_id, $custom_field_ids));
		$db[$abacus_db]->query("UPDATE abacus2.query__custom_field SET active=1 WHERE query_id=? AND custom_field_id IN ?", array($query_id, $custom_field_ids) );
	}
	else{ // if an update and there are no custom fields, that means we need to deactive all existing custom fields (because they've all been removed)
		$db[$abacus_db]->query("UPDATE abacus2.query__custom_field SET active=0 WHERE query_id=?", $query_id);
	}
	
	
	echo json_encode($res);
}

?>