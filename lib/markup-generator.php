<?php

function generateClientSelectorMarkup($clients){
	$client_selector = "";
	global $cvs_name;
	global $client_name;
	
	  foreach($clients as $client) {
	      $client_selector .= '<option value="'.$client['cvs_name'].'"';
	      if ($client['cvs_name'] == $cvs_name) {
	        $client_name = $client['client_name'];
	        $client_selector .= ' selected';
	      }
		  if(intval($client["is_framework"]) === 0){
			$client_selector .= " data-external='true' ";
		}
	      $client_selector .=  ' data-name="'. $client['client_name'] . '">'.$client['cvs_name']." (".$client['client_name'].")</option>\n";
	  }
	return $client_selector;
}

$queries = $db[$abacus_db]->query("SELECT  slug, name, client, public, create_user FROM abacus2.query  LEFT JOIN abacus2.client_query_map USING(query_id) WHERE (public=1 OR create_user=?) AND  min_user_role_id <=(SELECT user_role_id FROM abacus2.user WHERE create_user=?) ORDER BY global DESC, order_index DESC, name ASC", array($user, $user ));	

function generateQueryRadioButtons($queries){
	$radioButtons = "";
	for($i=0; $i<count($queries); $i++){
		$query = $queries[$i];
		$name = $query["name"];
		$slug = $query["slug"];
		$client = strlen($query["client"]) > 0 ? $query["client"] ." client": "";
		
		$clientplus_icon = strlen($query["client"]) > 0 ? '<i class="icon-plus-sign"></i>' :"";
		
		$lock_icon = intval($query["public"]) === 1 ? "" : '<i class="icon-lock"></i> ';
		$client_styles =  strlen($query["client"]) > 0  ? ' style="display:none !important;" ': "";
    	$radioButtons .= "<label $client_styles class='radio $client query'><input type='radio' class='query $client' name='query' value='$slug'  id='$slug' data-createuser='{$query["create_user"]}'>$lock_icon $clientplus_icon $name</label>\n";
	}
	return $radioButtons;
}
?>
