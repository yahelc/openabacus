<?php
class BSDAPI { 
	public $id_to_field_map = array();
	protected $signup_form_fields;
	protected $chapter; 
	
	protected $domain; 
	
	protected $api_secret;
	
	protected $app_id = '$internal';
	
	public function __construct(){
		global $db;
		global $cvs_name;
		$api_secret = $db[$cvs_name]->one('select secret from api_user where `id`="$internal";');
		$this->api_secret= $api_secret["secret"];
		
		$this->chapter = isset($_POST["chapter"]) ? $_POST["chapter"] : 1;
		$this->setChapter($this->chapter);
	}
	
	public function setChapter($chapter){
		$this->chapter = $chapter;
		global $db;
		global $cvs_name;
		$api_domain = $db[$cvs_name]->one('SELECT collection_name FROM chapter_collection WHERE chapter_id=?' , $this->chapter);
		if( isset($api_domain["collection_name"]) ){
			$this->domain = "https://{$api_domain["collection_name"]}-{$cvs_name}.bsd.net";
		}
		else{
			$this->domain = "https://$cvs_name.bsd.net";
		}
		
	}
	protected function _curl($url, $post = false, $put = false, $headers = []) {
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
	    if ($post || $put) {
	        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	        curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);
	        if(!$put){ 
				curl_setopt($ch, CURLOPT_POST, true);
				@curl_setopt($ch, CURLOPT_MUTE, 1);
			}       
			else{
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			}	
 			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	    }
	 	
	    $response     = array(); //will hold the http_code response and body of the response
	    $tempResponse = curl_exec($ch); //request the URL
	    if ($tempResponse) {
	        $response['body'] = $tempResponse;
	    } else {
	        $response['body'] = false;
	    }
	    $requestInfo           = curl_getinfo($ch); //information about response codes, time, content type, etc...
	    $response['http_code'] = $requestInfo['http_code'];
	
	/*	var_dump(curl_getinfo($ch,CURLINFO_HEADER_OUT));
		var_dump(curl_getinfo($ch, CURLINFO_HTTP_CODE));
		echo curl_error($ch);
	*/
	    curl_close($ch);
	    return $response;
	}
    
	public function make_api_call($module, $call, $post = false, $get = false, $put = false, $headers = ["Content-Type: text/xml"]) {
	    $ts = time();
		$slug = "/page/api/" . $module;
		if($call){
			$slug .= "/" . $call;
		} 
	    $querystring = "api_ver=2&api_id=" . $this->app_id . "&api_ts=$ts";
	    if ($get) {
	        if (is_array($get)) {
	            $querystring .=  "&" . http_build_query($get);
	        } else {
	            $querystring .= "&$get";
	        }
	    }
	    $signing_array  = array(
	        $this->app_id,
	        $ts,
	        $slug,
	        $querystring
	    );
	    $signing_string = implode("\n", $signing_array);
	    $api_mac        = hash_hmac("sha1", $signing_string, $this->api_secret);
	    $api_url        = $this->domain . $slug . "?$querystring&api_mac=$api_mac"; 
	    return $this->_curl($api_url, $post, $put, $headers);
	}
	public function post($module, $call, $post, $get = false) {
	    return $this->make_api_call($module, $call, $post, $get);
	}
	public function get($module, $call,  $get) {
	    return $this->make_api_call($module, $call, false, $get);
	}
	public function put($module, $call, $post, $get, $put){
	    return $this->make_api_call($module, $call, $post, $get, true);
	
	}
	public function xml_to_array($xml_string){
		return json_decode(json_encode(simplexml_load_string($xml_string)), true);
	}
    public function upload_file($file, $upload_dir_id){
        return $this->make_api_call("upload", "create_file", ["file"=>"@$file"],  ["upload_dir_id"=> $upload_dir_id], false, [ ]);
    }
    
 }


?>