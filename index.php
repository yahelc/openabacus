<?php 
//error_reporting(~E_DEPRECATED);
error_reporting(0);

//DEBUG
$_SERVER["REMOTE_USER"] = "ycarmon";
//
$start_micro_time = microtime(true);
set_time_limit(30*60);
header("Content-Type: text/html; charset=utf-8");
ob_start(); 

$config = json_decode(file_get_contents("config.json"), true);

require "inc.php";
 
$doctype = false;

if(isset($_SERVER["argv"]) && !count($_POST)){
    parse_str ($_SERVER['argv'][1], $GLOBALS['_POST']);
}


include 'db.php'; 
$is_cron = false;
if(isset($_POST["cron"])){
include "lib/runner.php";
$is_cron = true;
}
if(isset($query_object)){
    $query_log = new QueryLog($cvs_name, $query_object, $job);
    $timer_log = new TimerLog($query_log->query_log_id);
}
    

    

if(!isset($_POST["ajax"])){
	include "lib/markup-generator.php";

	$custom_fields_json = array();
	$custom_fields = $db[$abacus_db]->query("SELECT query.slug as query_slug, GROUP_CONCAT(DISTINCT custom_field.slug ORDER BY custom_field_id) as field_slug FROM custom_field JOIN query__custom_field USING(custom_field_id) JOIN query USING(query_id) WHERE query__custom_field.active=1 GROUP BY 1;");
	foreach($custom_fields as $i=>$custom_field){
		$custom_fields_json[$custom_field["query_slug"]] = explode(",", $custom_field["field_slug"]); 
	}

	$custom_fields_details = array();
	$custom_fields_result = $db[$abacus_db]->query("SELECT name as label, slug as name, type, value, if(query_sql is null,0,1) as is_select FROM custom_field");

	$sftp_clients = $db[$abacus_db]->query("SELECT client FROM client_sftp;");
	$sftp_clients_array = array();
	foreach($sftp_clients as $sftp_client){
		$sftp_clients_array[] = $sftp_client["client"];
	}

	foreach($custom_fields_result as $i=>$field){
		$custom_fields_details[$field["name"]] = $field;
	}
	$doctype = true;

?>
<!DOCTYPE html>
<html><head>
<meta charset="utf-8" />
<title>OpenAbacus | Stats Export Tool</title>
<link rel="shortcut icon" href="img/favicon.ico?v=2" type="image/x-icon">
<link rel="icon" href="img/favicon.ico?v=2" type="image/x-icon">

	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/select2.css" />
	<link rel="stylesheet" href="css/abacus.css" />
	<link rel="stylesheet" href="css/codemirror.css" />
	
	<link rel="stylesheet" href="css/eclipse.css">
	
	
	<?php
	
	if(count($log_queue)){
		foreach($log_queue as $log){
			consolelog($log);
		}
	}
	
	?>
	<script>
	var user = '<?php echo $_SERVER["REMOTE_USER"]; ?>';
	
	var user_role_id = '<?php echo $user->user_role_id; ?>';
	
	var git_branch = <?php echo json_encode(trim(implode('/', array_slice(explode('/', file_get_contents('.git/HEAD')), 2)))); ?>;
	
	var commit_msg = <?php echo json_encode(shell_exec("git log -1 --pretty=format:'%h - %s (%ci)' --abbrev-commit"));?>;
	
	</script>
	<script src="js/jquery.min.js"></script>
	<script src="js/select2.min.js"></script>
	<script src="js/jquery.tablesorter.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="js/underscore-min.js"></script>
	<script src="js/abacus.js"></script>


	<script src="js/codemirror.js"></script>
	<script src="js/sql-hint.js"></script>
	
	<script src="js/sql.js"></script>

	<script src="js/modals.js"></script>
    <script src="js/log.js"></script>
	
	<script>
	abacus.custom_fields = <?php echo json_encode($custom_fields_json) ."\n"; ?>;
	abacus.custom_field_list = <?php echo json_encode($custom_fields_details) . "\n"; ?>
	abacus.sftp_clients = <?php echo json_encode($sftp_clients_array) . "\n"; ?>
	</script>
	
</head>
<body data-role="<?php echo $user->user_role_name;?>"> 
	<!-- Navbar
    ================================================== -->
    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
		<a class="brand" href=""><img src="img/abacus.gif" style="
		    height: 1em;
		    margin-right: 10px;
		    margin-top: -5px;
		">OpenAbacus</a>          <div class="nav-collapse collapse">
            <ul class="nav">
              <li class="active">
                <a href=""><i class="icon-home"></i> Home</a>
              </li>
			  <li>
				<a id="schedule-log-button" href=""><i class="icon-calendar"></i>  Scheduled</a>
			  </li>

			<?php if(!($user->is("Employee"))) {?>
              <li class="">
                <a id="add" href="#"><i class="icon-edit"></i>  Add Query</a>
              </li>
              <li class="">
                <a id="edit-query" href="#"><i class="icon-pencil"></i>  Edit Query</a>
              </li>
			<?php } ?>
			<?php
			if($user->hasPermissions("Developer")){
				?>

			  <li class="dropdown">
			    <a class="dropdown-toggle"
			       data-toggle="dropdown"
			       href="#">
			        More
			        <b class="caret"></b>
			      </a>
			    <ul class="dropdown-menu">
				                  <li> <a id="create-group" href="#"><i class="icon-user"></i> Create Cons Group</a></li>
				        <?php if($user->hasPermissions("Administrator")) {?>          <li> <a id="edit-user" href="#"><i class="icon-user"></i> Edit User Permissions</a></li><?php } ?>
									<li> <a href="#" id="upload-file-button"><i class="icon-upload"></i> Upload File </a></li>
			    </ul>
			  </li>


			<?php } ?>
			<li class="">
              <a id="query-log-button" href="#"><i class="icon-list"></i>  Query Log</a>
            </li>
            
            </ul>
          </div>
        </div>
      </div>
    </div>
<div class="row" style="padding-top: 4em;">
	
  <div class="span10 offset1">
	<form id="client_selection_form" name="client_selection" class="well form-horizontal" style="padding: 5px;" action="index.php" method="POST">
		
		<ul class="nav nav-tabs">
		  <li class="active">
		    <a id="querytab" href="#">Saved Queries</a>
		  </li>
		
		  <li class="disabled"><a id="sqltab" title="If you need Direct SQL access, please request it from the Analytics Team">Direct SQL</a></li>
		
		
		</ul>
		
		<fieldset class="control-group">
			<label class="control-label">Client</label>
			<div class="controls">
				<select id="cvs_name_select" name="cvs_name">
	  				<option></option>

	  				<?php echo generateClientSelectorMarkup($clients); ?>

				</select>
			</div>
    <p />

		<fieldset class="control-group" id="query-fieldset"><label  class="control-label">Query</label>
			<div class="input">
	

      			<div class="inputs-list controls" style="
				    width: 40%;
				    float: left;
				    margin-left: 20px;
				    position: relative;
				">
					<?php echo generateQueryRadioButtons($queries);?>

  				</div>

				<div class="hidden-phone hidden-tablet" id="explainarea" style="float: left; width: 37%; min-height: 200px; padding: 20px; margin-top: 469px; background-color: rgb(240, 240, 240);">
				</div>
			</div><!-- /clearfix -->
		</fieldset>


<?php
if($user->hasPermissions("Analyst")){
	?>
	<fieldset class="control-group direct-sql-group" >
		<label class="control-label" for='straight'>SQL:
			<small style="display: block; font-size: 9px;"><a id="loadfromfile" href="#">Load from file.</a>
				<input type="file" id="fileinput" style="display:none;"/>
			   
				</small>
			
			</label>
		<div class="controls" id="ace-div" style="position:relative;">
			<textarea name="straight" id="straight" style="width:95%; height:200px; padding-top: 5px; font-size:12px"></textarea>
			
		</div>
	</fieldset>
	<script>
	$(function(){
		window.myCodeMirror = CodeMirror.fromTextArea(document.getElementById("straight"), {
			
			mode:"text/x-mysql",
			indentWithTabs: true,
		    smartIndent: true,
		    lineNumbers: true,
		    matchBrackets : true,
		    viewportMargin: Infinity,
			lineWrapping: true
	    
		    
		});
		myCodeMirror.setOption("theme","eclipse");
	});
	
	$("#sqltab").parent().removeClass("disabled");
	$("#sqltab").attr("href","#").click(function(){
		$("#ace-div, #extra-fields-slug, .direct-sql-group").show();
		$(this).parent().addClass("active");
		$("#query-fieldset").hide().parent();
		$("#querytab").parent().removeClass("active");
		$("input[name=query]:checked").prop("checked", false);
		localStorage.setItem("navtab", this.id);
		return false;
	});
	$("#querytab").click(function(){
		$("#query-fieldset").show();
		$(this).parent().addClass("active");
		$("#ace-div, #extra-fields-slug, .direct-sql-group").hide()
		$("#sqltab").parent().removeClass("active");
		localStorage.setItem("navtab", this.id);
		return false;
	});
	</script>
	
	<div id="extra-fields-slug" class="direct-sql-group">
			<label class="control-label" for="fileslug"> File Slug</label>
			<div class="controls">  
				<input type="text" name="fileslug"  value="" placeholder=" (optional) ">
			</div>
	</div>
	
	<?php } ?>

<fieldset class="control-group" id="extra-fields">

	<div id="extra-fields-inputs">
	</div>
	
	<div id="extra-fields-emails">
			<label class="control-label" for="emails"> Email </label>
			<div class="controls">  
				<input type="email" name="emails" id="emails" multiple value="">
			</div>
	
	</div>

</fieldset>

<fieldset class="control-group"><label class="control-label">Format</label>
	<div class="controls">
		<label class='radio' for="csv"> 
			<input type="radio" name="format" id="csv" value="csv" checked /> CSV
		</label>
		<label class='radio' for="table" title="Only returns the first 10,000 rows">
			<input type="radio" name="format" id="table" value="table"  />  HTML
		</label>
		<label class="radio" for="tsv">
			<input type="radio" name="format" id="tsv" value="tsv" checked /> TSV
		</label>
		<label class="radio" for="json">
			<input type="radio" name="format" id="json" value="js" checked /> JSON
		</label>
			
</div>
</fieldset>
<fieldset class="control-group"><label class="control-label">Export to...<br><small>(Optional)</small><br><small><a href="#" id="clearoutput">(clear)</a></small></label>
	<div class="controls">
		<label class='radio' for="dropbox">
			<input type="radio" name="output"  id="dropbox" value="dropbox"  />  Dropbox
		</label>
		<label class='radio' for="ftp">
			<input type="radio" name="output"  id="ftp" value="ftp"  />  FTP
		</label>
		<label class='radio' for="sftp">
			<input type="radio" name="output"  id="sftp" value="sftp"  />  SFTP
		</label>

	<?php if( $user->hasPermissions("Analyst") ){ ?>
		<label class='radio' for="cons_group_output">
			<input type="radio" name="output"  id="cons_group_output" value="cons_group_output"  />  Cons Group
		</label>
	<?php } ?>
	
	<?php if( $user->hasPermissions("Developer")  ){ ?>
		<label class='radio' for="s3">
			<input type="radio" name="output"  id="s3" value="s3"  />  BSD File Library
		</label>
		
		<label class='radio' for="preview">
			<input type="radio" name="output"  id="preview" value="preview"  />  Preview
		</label>
	<?php } ?>
	
	<?php if( $user->hasPermissions("Administrator") ){ ?>
		
		<label class='radio' for="dataset">
			<input type="radio" name="output"  id="dataset" value="dataset"  />  Dataset 
		</label>
		
		<label class='radio' for="dataset_map">
			<input type="radio" name="output"  id="dataset_map" value="dataset_map"  />  Dataset Map
		</label>
	<?php } ?>
	

		<label class='checkbox' for="email">
			<input type="checkbox" name="email"  id="email" value="email"  />  Email
		</label>

		<label class="checkbox" for="overwrite_group" id="overwrite_group_label">
			<input type="checkbox" name="overwrite_group" id="overwrite_group" value="overwrite_group">Overwrite existing group members. 
		</label>
		<label class="checkbox" for="zip_file" id="zip_file_label">
			<input type="checkbox" name="zip_file" id="zip_file" value="zip_file">Zip outputted file. 
		</label>
		</div>

	<div class="control-group" id="callback-group" style="display:none;">
	    <label class="control-label" for="callback"> Callback </label>
	    <div class="controls">
			<input type="text" name="callback" id="callback" value="" pattern="^[a-zA-Z][a-zA-Z0-9-_\.]{1,20}$" style="width:270px;" placeholder="Function name for JSONP (optional)"> 
	    </div>
	</div>
	
	<div class="control-group" id="upload_dir_group" style="display:none;">
		<label class="control-label" for="upload_dir_id" id="upload_dir_id_label">Select Folder</label><div class="controls"><select style="width:100%" data-placeholder="Select Folder" id="upload_dir_id" name="upload_dir_id"></select></div>
		
	</div>
	<div class="control-group" style="display:none;" id="ftp_error">
		<div class="alert"><strong>No FTP configured for this account.</strong> </div>
	</div>
		<div class="control-group" id="ftp_group" style="display:none;">
		<label class="control-label" for="client_ftp_id" id="client_ftp_id_label">Select FTP</label><div class="controls"><select style="width:100%" data-placeholder="Select FTP" id="client_ftp_id" name="client_ftp_id"></select></div>
		<label class="control-label" for="callback"> Path </label>
	    <div class="controls">
			<input type="text" name="ftp_path_override" id="path" value="" style="width:270px;" placeholder=""> 
	    </div>
	    
		
	</div>
	
	
		<div class="control-group" id="dataset_group" style="display:none;">
		<label class="control-label" for="map_type" id="map_type_label">Map Type</label><div class="controls"><select style="width:100%" data-placeholder="Select Map Type" id="map_type" name="map_type"></select></div>
		<label class="control-label" for="dataset_slug">Dataset Slug </label>
	    <div class="controls">
			<input type="text" name="dataset_slug" id="dataset_slug" value="" style="width:270px;" placeholder=""> 
	    </div>
	    
		
	</div>
	

	<div class="control-group" id="consgroup-group" style="display:none;">
	</div>

<div class="form-actions">
	<input type="hidden" value='<?php echo sha1(time().rand());?>' name="uniquekey" > &nbsp;
	<input type="submit" value="Submit" id="thesubmit"  class="btn btn-large btn-primary" title="Ctrl+Enter to Submit"/>&nbsp;
	<div id="displayerror" class="alert alert-error"></div>
	<img id="spinner" src="img/ajax-loader.gif">
	<a id="kill" class="btn btn-large btn-danger">Kill</a>
	<input type="submit" value="Schedule" id="schedule-button"  class="btn"/>&nbsp;
	<input type="submit" value="Show SQL" id="showgensql" class="hide btn btn-primary">
	
	</div>
</fieldset>
	</form>
	
	
<?php

}//end second if !ajax

	?>
	
	<div id="response-section">
		<script>
	    var post = <?php echo json_encode($_POST); ?>;
		</script>
		
		<?php if($job){
			//job parser relies on this. do not change without also changing cron.php
		    echo "<script id=job type='text/json'>" . json_encode($job) . "</script>";
		}?>
		
<?php
//EVERYTHING IN THE response-section div will return in results
ob_flush();
flush();

include "lib/process.php";

if(@count($sql)>0){
	$sql_display =  str_replace(";;\n",";\n",implode(";\n", $sql) );
    $query_log->update(["query_sql"=>$sql_display]);
}

if($_POST["output"] !== "preview"){
	include 'lib/output.php';
}

$doctype = true;
if(count($_POST)){
    ?>

	<script>
	
	<?php if(isset($query_log)){
        $query_log->update(["complete"=>1]);
	?>
        
	var query_log_id = <?php echo json_encode( $query_log->query_log_id ) . ";";	}?>
    log_after_post();

	</script>
    
<?php   
}
?>
	<div id="sqlrow" class="row">
		<div class='span10' id="sql-area">
			<p />
            <?php if( strlen($sql_display) < 50000 ) {?>
			<textarea id="generated-sql" style="display:none;"><?php echo $sql_display; ?></textarea>
            <?php } else { ?>
                <a id='sql-download-link' download="" class="btn btn-success" href="">Download Generated SQL</a>
                <script>
                    $(function(){
                        $("#sql-download-link").click(function(){ //potential race condition on log.sql...
                            $(this).attr({"href": 'data:text/plain;charset=utf-8,' + encodeURIComponent(log.sql), "download":"query_log_" + query_log_id +".sql"});
                        });
                    });
                </script>
            <?php } ?>
		</div>
	</div>
	
</div>

<script>
$(function(){
	setTimeout(function(){
        if(!$("#generated-sql").length){ return; }
	    window.generatedSQL = CodeMirror.fromTextArea(document.getElementById("generated-sql"), {
		
		    mode:"text/x-mysql",
		    indentWithTabs: true,
	        smartIndent: true,
	        matchBrackets : true,
	        viewportMargin: Infinity,
		    lineWrapping: true,
		    readOnly: true
	    });
	    generatedSQL.setOption("theme","eclipse");
	}, 100);
});
</script>


<?php

if(!isset($_POST["ajax"])){

?>
</div>
</div>

<?php include "lib/modals.php"; ?>
<script>
 var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA--4']);
_gaq.push(["_setSiteSpeedSampleRate", 100]);
_gaq.push(["_setCustomVar", 1, "User",  user, 1]);
_gaq.push(['_trackPageview']);

(function() {
  var ga = document.createElement('script');
  ga.src = '//www.google-analytics.com/ga.js';
  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();

</script>


<?php
} // end third if !ajax

complete();
if(isset($query_log->query_log_id)){
    $timer_log->start("fullquery", $start_micro_time);
    $timer_log->end("fullquery");
}
?>
</body></html>