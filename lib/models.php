<?php

class Query{
	protected $db;
	private $post;
	protected $data;
	protected $file_name;
	
	public function __construct($post){
		global $db;
		global $abacus_db;
		$this->db = $db[$abacus_db];
		$this->post = $post;
		$this->data = $this->db->one("SELECT * FROM query where slug=? LIMIT 1", array($post["query"]));
		$this->generateFileSlug();
	}
	
	public function __get($field){
		return $this->data[$field];
	}
	
	public function isDirectSQL(){
		return !!count($this->data);
	}
	public function isSavedQuery(){
		return !!count($this->data);
	}
	public function sql(){
		return $this->isSavedQuery() ? $this->query_sql : $this->post["straight"];
	}
	public function name(){
		return $this->isSavedQuery() ? $this->name : "Custom Report";
	}
	
	private function generateFileSlug(){
		
		if($this->isSavedQuery() && !$this->post["fileslug"]){
			$this->file_slug = $this->slug;
		}
		else{
			$this->file_slug =  strlen($this->post["fileslug"]) ? $this->post["fileslug"] : md5($this->post["straight"]);
		}
	}
}


class User{
	public $user;
	private $db;
	private $roles = array("Employee", "Analyst", "Developer", "Administrator");
	private $role_ids   = array("Employee"=>1, "Analyst"=>2, "Developer"=>3, "Administrator"=>4);
	public $user_role_id;
	public $user_type_id;
	public $user_role_name;
	
	public function __construct($user, $create = true){
		global $db;
		global $abacus_db;
		$this->user = $user;
		$this->db = $db[$abacus_db];
		$user_role = $this->getUserRow();
		if(!$user_role && $create){
			$this->db->query("INSERT IGNORE INTO user(create_user, user_role_id) VALUES (?, 1)", array($user));
			$user_role = $this->getUserRow();
		}
		$this->user_role_id = intval($user_role["user_role_id"]);
		$this->user_type_id = intval($user_role["user_type_id"]);
		$this->user_role_name = intval($user_role["user_role_name"]);
		
	}
	private function getUserRow(){
        
		$user_row =  $this->db->one("SELECT user_id, user_role_id, create_user, user_role_name, user_type_id FROM `user` INNER JOIN user_role using(user_role_id) WHERE create_user=? LIMIT 1", array($this->user));
		return $user_row;
	}
	
	public function hasPermissions($role_name){ //calling pattern: hasPermissions("Analyst")
		return $this->role_ids[$role_name] <= $this->user_role_id;
	}
	
	public function is($role_name){
		return $this->role_ids[$role_name] === $this->user_role_id;
	}
	public function __toString(){
		return $this->user;
	}
}

?>