<?php
	
	if( $query_object->isSavedQuery() ){
		
		if( intval($query_object->global) === 0 ){ // make sure its running on the right client
			$client_query_map = $db[$abacus_db]->one("SELECT count(*) as count FROM client_query_map WHERE client=? AND query_id=?", array($cvs_name, $query_object->query_id));
			
			if( intval($client_query_map["count"]) === 0){
				exit("'" . $query_object->name ."' is not a query that has permission to run for $cvs_name");
			}
		}
		if( intval( $query_object->min_user_role_id ) > $user->user_role_id ){
			exit("This user does not have permission to run this query");
		}
	}

	if(  (is_array($_POST["cons_group"]) && count($_POST["cons_group"])> 0) || (is_string($_POST["cons_group"]) && strlen($_POST["cons_group"]) > 0)){

		$cg = is_array($_POST["cons_group"]) ? $_POST["cons_group"] : array($_POST["cons_group"]);
		$name_resource = $db[$cvs_name]->query("SELECT name, membership_resource FROM cons_group WHERE membership_resource IN ?", array($cg));
	}
	
	include "twig.php";
	$sql = generateSQLFromTwig($query_object->sql(), $twig, $template);

	consolelog($sql);
?>
