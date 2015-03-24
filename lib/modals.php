

<div id="consgroup-modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="cg-modal-label">Create New Cons Group</h3>
  </div>
  <div class="modal-body">


	<fieldset class="control-group" id="consgroup-fields">


		<div id="extra-fields-ftp">
				<label class="control-label" for="cons_group_name"> Cons Group Name </label>
				<div class="controls">  
					<input type="text" name="cons_group_name" disabled placeholder="Group Name" value="">
				</div>
				
				<label class="control-label" for="chapter"> Chapter ID </label>
				<div class="controls">  
					<input type="text" name="chapter" disabled value="">
				</div>

		</div>

	</fieldset>



  </div>
  <div class="modal-footer">
    <button class="btn" id="cons-group-close" data-dismiss="modal" aria-hidden="true">Close</button>
    <button id="cons-group-save" class="btn btn-primary">Create Cons Group</button>
  </div>
</div>

<?php if( $user->hasPermissions("Developer") ){?>
<!-- EDIT USER-->


<div id="edituser-modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="cg-modal-label">Edit User Permissions</h3>
  </div>
  <div class="modal-body">

	<fieldset class="control-group" id="edituser-fields">

		<div id="extra-fields-edituser">
				<label class="control-label" for="cons_group_name"> User </label>
				<div class="controls">  
					<select id="user-list"><option value="--">Select user...</option>
						<?php
						$user_list = $db[$abacus_db]->query("SELECT create_user, user_role_id FROM user ORDER BY create_user ASC;");
						foreach($user_list as $i=>$users){
							echo "<option value='{$users["user_role_id"]}'>{$users["create_user"]}</option>";
						}
						?>
					</select>
				</div>
				
				<label class="control-label" for="permission-levels"> Permission Level </label>
				<div class="controls">  
					<select id="permission-levels">
						<option value="1">Employee</option>
						<option value="2">Analyst</option>
						<option value="3">Developer</option>
						<option value="4">Administrator</option>
					</select>
					<i id="ok-perm" class="icon-ok" style="margin: 0 12px; display:none;"></i>
				</div>
		</div>
	</fieldset>
<script>
$(function(){
	$("#user-list").change(function(){
		$("#permission-levels").val($(this).val());
		$("#ok-perm").hide();
	});
	$("#permission-levels").change(function(){
		var level = $(this).val();
		var user  = $("#user-list").val();
		$.post("ajax/user_permission.php", {user:$("#user-list option:checked").text(), level:level }, function(d){
			if(d.user_role_id === level){
				$("#ok-perm").show();
			}
		})
	});
	
});

</script>
  </div>
  <div class="modal-footer">
    <button class="btn" id="cons-group-close" data-dismiss="modal" aria-hidden="true">Close</button>
  </div>
</div>



<!-- END EDIT USER -- >
<?php } ?>

<!--Scheduling Modal -->
<div id="schedule-modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="schedule-h3">Schedule This Query</h3>
  </div>
  <div class="modal-body">

<ul id="schedule-meta"></ul>

<label class="control-label" for="time">Report Name </label>
<div class="controls">  
	<input type="text" name="report_name" id="reportname">
</div>

<label class="control-label" for="time">Emails </label>
<div class="controls">  
	<input type="text" name="emails2" id="emails2">
</div>


	<fieldset class="control-group" id="schedule-fields"><legend>Frequency</legend>
    

		    <div class="inputs-list controls" >
		<label class="radio"><input type="radio"  class="frequency" name="frequency" value="daily"  id="daily"></li>Daily</label>
			
		<label class="radio"><input type="radio"  class="frequency" name="frequency" value="weekly"  id="weekly"></li>Weekly</label>
		<label class="radio"><input type="radio"  class="frequency" name="frequency" value="monthly"  id="monthly"></li>Monthly</label>
		
		<input id="query_json" name="query_json" type="hidden">
		
		<label class="control-label month-label" for="emails"> Day of Month </label>
		<div class="controls">  
			<input type="number" id="month" name="month"  min="1" max="28">
		</div>
		<label class="control-label day-label" for="day"> Day of Week </label>
		<div class="controls">  
			<select id="day" name="day">
				<option value="2">Monday</option>
				<option value="3">Tuesday</option>
				<option value="4">Wednesday</option>
				<option value="5">Thursday</option>
				<option value="6">Friday</option>
				<option value="7">Saturday</option>
				<option value="1">Sunday</option>
				
			</select>
		</div>
	
		<hr/>
		<label class="control-label" for="time">Run Time (UTC)</label>
		<div class="controls">  
			<input type="time" name="time" id="time" step="120" value="12:00">
		</div>
		
			</div>

	</fieldset>

	<output></output>		


  </div>
  <div class="modal-footer">
    <button class="btn" id="schedule-close" data-dismiss="modal" aria-hidden="true">Cancel</button>
    <button id="schedule-save" class="btn btn-primary">Schedule</button>
  </div>
</div>





<!-- Log Modal -->
<div id="log-modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel">Query Log</h3>
  </div>
  <div class="modal-body">

	<table id="log-table" class="table table-bordered table-striped" style="">
		<thead></thead>
		<tbody>

		</tbody>
	</table>


  </div>
  <div class="modal-footer">
    <button class="btn" id="log-close" data-dismiss="modal" aria-hidden="true">Close</button>
  </div>
</div>


<!-- Query Editor Modal -->
<div id="query-editor-modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel">Query Editor</h3>
  </div>
  <div class="modal-body">


	<fieldset class="control-group" id="query-fields">


		<div id="extra-fields-query">
				<label class="control-label" for="query_name"> Query Name </label>
				<div class="controls">  
					<input type="text" name="query_name" id="query_name" disabled placeholder="Query Name" value="">
				</div>
				
				<label class="control-label" for="query_slug"> Slug </label>
				<div class="controls">  
					<input type="text" name="query_slug" id="query_slug" readonly disabled value="">
				</div>
				
				<label class="control-label" for="twigsql"> TwigSQL </label>
				<div class="controls">  
					<textarea type="text" name="twigsql" id="twigsql" disabled value=""></textarea>
				</div>
				
				<div class="controls">  
					<label for="min_user_role_id" class="control-label">Access Level</label>
					<select name="min_user_role_id" id="min_user_role_id">
					  <option value="1">Employee</option>
					  <option value="2">Analyst</option>
					  <option value="3">Developer</option>
					  <option value="4">Administrator</option>
					</select>
					
					<label class="checkbox" for="makeclientspecific"><input type="checkbox" name="makeclientspecific" id="makeclientspecific" value="makeclientspecific"> Make this query only appear for this client. </label>
					<label class="checkbox" for="makeprivate"><input type="checkbox" name="makeprivate" id="makeprivate" value="makeprivate"> Set this query to be private. </label>
					<script>
					$(function(){
						if(window.user_role_id && user_role_id <= 2){
							$("#makeprivate").prop("checked", true).click(function(){
								return false;
							})
						}
					})
					</script>
					<label class="control-label">Custom Fields</label>
					<select id="custom_fields_for_query" name="custom_fields_for_query[]" multiple style="width:80%;">
					
					<?php
					$custom_fields = $db[$abacus_db]->query("SELECT custom_field_id, name, slug FROM custom_field");
					foreach($custom_fields as $i=>$custom_field){
					?>
						<option value="<?=$custom_field["custom_field_id"]?>"><?=$custom_field["name"]?>  {{<?=$custom_field["slug"]?>}}</option>
						
					<?php } ?>
					</select>
					
					
					<input type="hidden" name="cvs_name" id="modal-cvs-name" disabled>
				</div>
				
				<label class="control-label" for="query-description" style="padding-top:10px;"> Description </label>
				<div class="controls">  
					<textarea type="text" name="query-description" style="width:100%" id="query-description" disabled value=""></textarea>
				</div>
				
		</div>

	</fieldset>

  </div>
  <div class="modal-footer">
    <button class="btn" id="query-close" data-dismiss="modal" aria-hidden="true">Close</button>
    <button id="query-save" class="btn btn-primary">Save changes</button>
  </div>
</div>


<!-- -->

<div id="fileupload-modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="fileupload-modal-label">Upload File to Abacus Storage</h3>
  </div>
  <div class="modal-body">

	<iframe id="upload-iframe" style="border:0; width:100%" dev-src="ajax/upload_file.php"></iframe>
  </div>
  <div class="modal-footer">
    <button class="btn" id="fileupload-modal-close" data-dismiss="modal" aria-hidden="true">Close</button>
  </div>
</div>



