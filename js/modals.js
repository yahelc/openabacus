jQuery(function(){
	$(".frequency").change(function(){
		var weekly = this.checked && this.value === "weekly";
		var monthly = this.checked && this.value === "monthly";
		$("#day"  ).prop("disabled", !weekly).parent().add(".day-label").toggle(weekly);
		$("#month").prop("disabled", !monthly).parent().add(".month-label").toggle(monthly);
		
	}).change();
	$("#day").select2();
	
	$("#emails").change(function(){
	  $("#emails2").val(this.value);
	});
	$("#emails2").change(function(){
	  $("#emails").val(this.value);
      $("#query_json").val($("#client_selection_form").serialize());

	});
	$("#edit-user").click(function(){
		$("#edituser-modal").modal();
	});
	
	$("#create-group").click(function(){
		$("#consgroup-modal").modal().find("input").removeAttr("disabled");
	});
	
	$("#cons-group-save").click(function(){
		$.post("ajax/createConsGroup.php", $("#consgroup-modal input").serialize() + "&cvs_name=" + abacus.client(), function(d){
			console.log(d);
			
			if(d.http_code < 400){
				setTimeout(function(){
					$("#cons-group-close").click();
				},1000);
			}
			else{
				alert("Error :(. Probably a dupe name or an invalid character.");
			}
		});
		
		return false;
	});
	
	$("#upload-file-button").click(function(){
		$("#fileupload-modal").modal();
		$("#upload-client", $("#upload-iframe").contents()).val(abacus.client());
	});
	
	
	$("#schedule-button").click(function(){
		window.myCodeMirror && window.myCodeMirror.save();
		if (validates()) {
			$("#schedule-modal").modal();
			$("#email").prop("checked",true).change(); //ensure that email field is shown, and that it's not disabled. 
			$("#query_json").val($("#client_selection_form").serialize());
			if($("#page:checked").length && !$("input[name=emails]").val()){
				$("#schedule-modal output").html('<div class="alert"> <button type="button" class="close" data-dismiss="alert">&times;</button> <strong>Warning!</strong> You haven\'t configured any email recipients for this scheduled query, so no one will receive the report</div>');
			}
			$("#schedule-meta").html(function(){
				var html="";
				html+= "<li><b>Client:</b> " + abacus.client() + "</li>";
				html+= "<li><b>Query:</b> "  + abacus.query()  + "</li>";
				html+= "<li><b>Format:</b> " + $("input[name=format]:checked").parent().text() + "</li>";
				html+= "<li><b>Output:</b> " + $("input[name=output]:checked").parent().text() + "</li>";
				if($("input[name=emails]").val()){
					html+= "<li>Recipients:</b> " + $("input[name=emails]").val() + "</li>";
				}
				return html;
			});
		}
		return false;
	});
	
	
	$("#schedule-save").click(function(){
		$.post("ajax/schedule.php", $("#schedule-modal *").serialize(), function(html){
			$("#schedule-modal output").html(html.message);
			setTimeout(function(){
				$("#schedule-close").click();
			},5000);
		});
		
	return false;	
	});
	
	
	
	$("#query-log-button").click(function(){
		$.get("ajax/query_log.php", function(log){
            $('#myModalLabel').text('Query Log'); // ensure that label switches correctly
			window.querylog = log;
			html="";
			for(var i=0; i<log.length; i++){
				var query=log[i];
				var sql = "<a href='#' class=showsql>Show SQL</a><span class='hidden-sql' style='display:none;'>"+ _.escape(query.query_sql) + "</span>";
				var fields = [query.client, query.create_dt, query.query_name, query.query_time/1000, sql];
				var actions = (query.file)  ? "<a href='file/" + query.client +"/" + query.file + "'>Download</a>  " :"";
				fields.push(actions);
				html+= "<tr><td>" + fields.join("</td><td>") + "</td></tr>";
			};
			try{
				$("#log-table tbody").html(html);
 
			}catch(eee){}
			$('#log-modal').modal();
			$('#log-table thead').html("<tr> <th> Client </th> <th> Date </th> <th> Query </th><th> Run Time </th> <th> SQL </th> <th> Actions </th> </tr>");
			$('#log-table').tablesorter();

		});
	});
	$(".showsql").live("click", function(){
		$(this).siblings().show();
		$(this).hide();

		return false;
	});
	
	 var active_tmpl =_.template('<span class="label label-<%=label%>"><%=text%></span> 	<a href="#" class="schedule-change btn btn-mini" data-scheduled_query_id=<%=id%>><i class="icon-<%=icon%>"></i> </a>	 <a href="#" class="schedule-remove btn btn-mini" data-scheduled_query_id=<%=id%>><i class="icon-remove"></i>  <a href="#" class="schedule-edit btn btn-mini" data-scheduled_query_id=<%=id%>><i class="icon-edit"></i> </a>' );
	 var active_data = [{label:"important",icon:"play",text:"Paused"}, {label:"success",icon:"pause",text:"Active"}];
	
	$("#schedule-log-button").click(function(){
	
		$.getJSON("ajax/schedule.php", function(log){
			html="";
			window.scheduled_queries =  $.extend(true,{},log); //clone without reference 
			$("#log-modal").addClass("schedule-list");
            $('#myModalLabel').text('Scheduled Queries'); // override label
			for(var i=0; i<log.length; i++){
				if(!i){
					$("#log-table thead").html(function(){
						return  "<tr><th>" + Object.keys(log[i]).join("</th><th>") + "</th></tr>";
					});
				}
				var query=log[i];
				html += "<tr>";
				for(var head in query){ 
					if(head=="post_parameters"){
						query[head]="<span class='More'>" + deparam(query[head]).query + "</span><span style='display:none;'>" + query[head] + "</span>";
					}
					if(head==="active"){
						var tmpl = _.extend({id:query.id}, active_data[+query[head]]);
						query[head] = active_tmpl(tmpl);
					}
					if(head==="Run Day" && query.frequency==="weekly"){
						query[head] = $("#day option[value=" + (+query[head]) + "]").text()+"s";
					}
					html +="<td>" + query[head] + "</td>";
				}
				html+= "</tr>";
			}
			try{
				$("#log-table tbody").html(html);
				$('#log-table').tablesorter();
			}catch(eee){}
			$('#log-modal').modal();

		});
		return false;	
	});
	
	$(".schedule-change").live("click", function(){
		$(this).siblings().add(this).fadeOut("slow");
		var td = $(this).closest("td");
		$.post("ajax/schedule.php?update=true", $(this).data(), function(d){
	  		var tmpl = _.extend({id:d.id}, active_data[+d.active]);
	  		td.html(active_tmpl(tmpl)).fadeIn("slow");
		});
	});
	$(".schedule-edit").live("click", function(){
		var scheduled_query = _.where(scheduled_queries, {"id": ""+$(this).data("scheduled_query_id") }).pop();
		if(scheduled_query){
			var post_object = deparam(scheduled_query.post_parameters);
			var emails  = decodeURIComponent(post_object.emails);
			var emails_prompt = prompt("Edit Recipients for this report:", emails);
			if(emails_prompt != null){ //as long as they dont hit cancel
				post_object.emails = emails_prompt.replace(/ /g,"");
				$.post("ajax/schedule.php?edit=true", {"scheduled_query_id":$(this).data("scheduled_query_id"), "post_parameters": $.param(post_object)}, function(d){
					window.location.reload()
				});
			}

		}
	})
	$(".schedule-remove").live("click", function(){
		if(confirm("Are you sure you want to delete this scheduled report? This cannot be undone.")){
			$(this).siblings().add(this).fadeOut("slow");
			var td = $(this).closest("td");
			$.post("ajax/schedule.php?delete=true", $(this).data(), function(d){
		  	//nothing really to do here...
		       td.closest("tr").fadeOut();
			});
			
			
		}
		
	});
	
	
	
	$("#query-save").click(function(e){
		e.preventDefault();
		$.post("ajax/query-edit.php", $("#query-editor-modal *").serialize(), function(){
			$("#query-close").click();
			setTimeout(function(){location=location;}, 1000);
		});
	});
	$("#query-close").click(function(){
		$("#query-fields input, #query-fields textarea").val("");	
		$("#query-editor-modal *").not(this).not("option").prop("disabled", true);
		
	});

	$("#add").click(function(e){
		e.preventDefault();
		$("#query-editor-modal *").not("option").prop("disabled", false).val("");
		$("#custom_fields_for_query").val("").select2();
		$('#query-editor-modal').modal();
		$("#query_slug").removeData("edit");
		$("#makeclientspecific").attr("disabled", !abacus.client()).parent().toggleClass("disabled-input", !abacus.client()); //disable setting client specific if no client currently set
		$("#makeprivate,#makeclientspecific").val(function(){return this.id; });
		$("#modal-cvs-name").attr("disabled", !abacus.client()).val(abacus.client());
		
	});

	$("#edit-query").click(function(){
		var query = $("input[name='query']:checked:visible").val();
		if(!query){ alert("Please select a query to edit. "); return;};
		if(user_role_id <= 2 && ( !$("input[name='query']:checked").siblings("i").hasClass("icon-lock") && $("input[name='query']:checked").data("createuser") !== user )  ){
			alert("You do not have permission to edit non-private queries that you didn't create."); return;
		}
		$.get("ajax/query-edit.php", {slug:query}, function(data){
			$("#add").click();
			
			$("#query_name").val(data.name);
			$("#query_slug").val(data.slug);
			$("#makeclientspecific,#makeprivate").attr("checked", false); // clear if they are already checked
			if(data.global === "0"){
				$("#makeclientspecific").attr("checked", true);
			}
			if(data["public"] === "0"){ //public is a reserved word :(
				$("#makeprivate").attr("checked", true);
			}
			$("#makeprivate,#makeclientspecific").val(function(){return this.id; });
			$("#min_user_role_id option[value='" + data.min_user_role_id +"']").prop("selected", true);
			$("#twigsql").val(data.query_sql);
			$("#query-description").val(data.description);
			$("#query_slug").data("edit", true);
			if(data.custom_fields){
				$("#custom_fields_for_query").val(data.custom_fields.split(",")).select2();
				
			}
			
		});
	});
	
	$("#query_name").keyup(function(){
		if($("#query_slug").data("edit")){ return; }

		$("#query_slug").val($(this).val().replace(/ /gi,"_").replace(/\W/gi,"").toLowerCase());
		if($("input.query[value='" + $("#query_slug").val() + "']").length){
			$("#query_slug").val(function(i,v){return v+"_1";});
		}
	});
		
	
});
