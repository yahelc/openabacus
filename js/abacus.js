var abacus = {
    client: function() {
        return $("#cvs_name_select").val();
    },
    query: function() {
        return $(".query:checked").parent().text() || "Custom Query";
    },
    format: function() {
        return $("input[name=format]:checked").val()
    },
    output: function() {
        return $("input[name=output]:checked").val()
    },
    client_name: function() {
        return $("#cvs_name_select option:checked").data("name");
    }
};
var deparam = function(param) {
    var map = {};
    ("?" + param).replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m, k, v) {
        map[k] = v;
    });
    return map;
};

if (!("localStorage" in window)) {
    window.localStorage = {
        getItem: $.noop,
        setItem: $.noop
    }
}

var polls = 0,
    interval = 1000,
    post = {};

function processlistPoll() {
    return $.post("ajax/processlist.php", {
        cvs_name: abacus.client()
    }, function(data) {
        for (var i = 0; i < data.length; i++) {
            if (data[i].Info && !data[i].Info.match(/^(EXPLAIN|KILL) /i) && data[i].Info.indexOf("{showprocesslistpoll}") === -1 && data[i].Info.indexOf($("input[name=uniquekey]").val())) {
                window.threadID = data[i].Id;
                $("#kill").fadeIn().prop("disabled", false);
				window.polls = 0; 
                return;
            }
        }
        if (++polls < 10) {
            setTimeout(processlistPoll, interval);
            interval = interval * 2;
        }

    });
}

function validates() {
    var fields = [];
    if (!abacus.client()) {
        fields.push("Client Name");
    }
    if (!$("input[name=query]:checked:visible, #straight").val()) {
        fields.push("Query");
    }
    if (fields.length) {
        $("#displayerror").html('<strong>Warning:</strong> Required field' + (fields.length > 1 ? "s" : "") + ' not set: ' + fields.join(", ") + '.</div>').css("display", "inline");
        return false;
    }

    return true;
};

function poll_deferred_ids(ids){
	window.deferred_ids = ids;
	for(var i=0; i<ids.length;i++){
		$.post("ajax/deferred.php", {deferred_id: ids[i], cvs_name: abacus.client()}, function(data){
		//	{"body":false,"http_code":204}
		if(data.http_code === 204 || data.http_code === 410){
			deferred_ids.splice(deferred_ids.indexOf(ids[i]), 1); //remove from deferred array
			$("#completedbatches").text(""+deferred_ids.length);
		}
		if(deferred_ids.length){
			setTimeout(function(){
				poll_deferred_ids(deferred_ids);
			}, 1000);
		}
		});
	}
}

jQuery(function($) {

	$("#kill").click(function() {
        $(".query:radio:checked").prop("checked", false);
        if (window.latestAjax) {
            latestAjax.abort();
            latestAjax = null;
        }
        $("input[value='table']").prop("checked", true);
		$("#client_selection_form input:disabled:visible").attr("disabled", false)
		var sql = "Kill " + (+window.threadID) + ";\nSHOW PROCESSLIST;"
        $("#straight").val(sql);
		myCodeMirror.getDoc().setValue(sql);
	    myCodeMirror.save();
		$("#client_selection_form").submit();
        $("#straight").val("");
		myCodeMirror.getDoc().setValue("");
	    myCodeMirror.save();
		$(this).fadeOut();
    });

    $(document).keydown(function(e) {
        if (e.keyCode === 13 && window.commandHeld) {
            $("form").submit();
        }
        window.commandHeld = e.metaKey || e.ctrlKey;
    });

    $(".alert").alert();

    $(".navbar-inner .nav a").mousedown(function() {
        $(".navbar-inner .nav li").removeClass("active");
        $(this).closest("li").addClass("active");
    });
	$("#clearoutput").click(function(){
		$("input[name=output]").prop("checked", false).filter(":checked").change();
		$('#consgroup-group, #upload_dir_group, #overwrite_group_label, #ftp_group').hide(); //hide any group selectors that have shown
		localStorage.removeItem("output");
		return false;
	})
    $('.modal').on('hidden', function() {
        $(".navbar-inner .nav li").removeClass("active").first().addClass("active");
    });

    $(window).on("resize", function() {
        $(".CodeMirror").width($("#client_selection_form").width() - 200)
    });

    $("form").not("#upload-file-form").submit(function(e) {
        window.myCodeMirror && window.myCodeMirror.save();
		window.polls = 0;
		
        if (!validates()) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        $(".alert").css("display", "none");
        var inputs = $("input:not(:disabled)");
        var serial = $("form").serialize() + "&ajax=true";
        inputs.prop("disabled", true);

        $("#spinner").show();
        $('#results').html("").hide();
        $("#csvlink").attr("href", "").hide();


        window.latestAjax = $.post('index.php', serial, function(d) {
            polls = 10; // end the processlist polling
            $("#kill").hide();

            var output = $("input[name=format]:checked").val();
            $("#response-section").replaceWith(d);

            $("#sqlrow").show();
            inputs.prop("disabled", false);
            $("#spinner").hide();

            if (output === "table") {
                $('#results, .table-bg').show();
                var query_label = abacus.query();
                var client_label = abacus.client();
                $("#results").before("<h2>" + client_label + ": " + query_label + "</h2>");
                if (!$("#results tbody tr").length) {
                    $("#results tbody").html("<tr><td>No results returned</td></tr>");
                }

            }
            if (output === "csv" || output === "tsv" || output === "js") {
                $("#csvlink").show();
            }

        });
        processlistPoll();

        return false;
    });

    $("#sqlrow").hide();

    if (location.pathname.match(/^\/staging/)) {
        $("body").append('<div class="alert alert-danger" style="position: fixed;top: 0;right: 0;z-index:1000000"><b><h4 id="git-branch">STAGING: ' + git_branch + '</h4></b></div>');
        $(".staging").show();
		$("#git-branch").attr("title", commit_msg);
    }



    $("input[name=output]").bind("change foo", function() {
        var display_cg_label = this.value == "cons_group_output" ? "show" : "hide";
        $("#overwrite_group_label")[display_cg_label]();
		$("#consgroup-group").html("");
		
        if (this.value === "cons_group_output" && abacus.client()) {
            abacus.custom_field_functions.populateOutputConsGroup(abacus.client());
        }
		if(this.value === "s3"){
			$("#upload_dir_id,#upload_dir_id_label,#upload_dir_group").show();
			
			$.get("ajax/upload_dir.php", {cvs_name : abacus.client()}, function(data){
				var html = "";
				for(var upload_dir_id in data){
					html+= "<option value=" + upload_dir_id + ">" + data[upload_dir_id]+ "</option>";
				}
				$("#upload_dir_id").html(html).select2();
			})
		}
		else{
			$("#upload_dir_id").html("").add("#upload_dir_id_label,#upload_dir_group").hide();
		}
		
		if(this.value === "dataset" && this.checked){
			$("#dataset_group").show();
			$.post("ajax/dataset_maps.php",  {cvs_name : abacus.client()}, function(data){
				var html = "";
				for(var i = 0; i< data.length; i++){
					var dataset_map = data[i];
					html+= "<option value='" + dataset_map + "'>" + dataset_map +  "</option>";
				}
				if(data.length){
					$("#map_type").html(html).select2();
				}
			});
		}
		else{
			$("#dataset_group").hide();
			
			$("#map_type").html("");
			$("#dataset_slug").val("");
		}
		
		if(this.value === "ftp" && this.checked){
			$("#ftp_group").show();

			$.post("ajax/ftp.php", {cvs_name : abacus.client()}, function(data){
				var html = "";
		 		for(var i = 0; i< data.length; i++){
					var ftp = data[i];
					var selected = i ? "" : " selected ";
					html+= "<option data-path='" + ftp.path +"' value=" + ftp.client_ftp_id + " " + selected + ">" + ftp.ftp_user + "@"+ ftp.ftp_host +  "</option>";
				}
				if(html){
					$("#client_ftp_id").html(html).select2().bind("change foo", function(){
						$("#path").val($("#client_ftp_id option:selected").data("path"));
					}).trigger("foo");
					$("#ftp_error").hide();
				}
				else{
					$("#ftp_error").show();
					$("#ftp_group").hide();
					
				}
			});
			
		}
		else{
			$("#client_ftp_id").html("");
			$("#path").val("");
			$("#ftp_group").hide();
			$("#ftp_error").hide();
		}

    });

	

    //ensure that we don't secretly send emails to the wrong people. 
    $("#email").change(function() {
        $("#emails").prop("disabled", !this.checked);
    });

    $("#cvs_name_select").val(localStorage.getItem("cvs_name")).select2();

    $("#cvs_name_select").change(function() {
        $(".query:checked").trigger("foo"); // this will ensure that the custom fields refresh for the new client
        $(".client").hide();
        var client = abacus.client();
		$(".query").hide();
		$(".query:not('.client')").show();

        if (client) {
            $("." + client).show().css("display", "block");
        }

		$("#sftp").parent("label").toggle($.inArray(abacus.client(), abacus.sftp_clients) >= 0 );
		$("#ftp").trigger("foo");
		
		$("input[name=output]:checked").trigger("foo");        
    });
    $("#cvs_name_select").change();

    if ($("body").data("role") === "Employee") {
        $("#showgensql").show();
        $("#sql-area").hide();
        $("#showgensql").click(function() {
            $("#showgensql").hide();
            $("#sql-area").show();
            $("html, body").animate({
                scrollTop: $('#sqlrow').offset().top
            }, 500);
            return false;
        });
    }


    $("#email").bind("change foo", function() {
        if (this.checked) {
            $("#extra-fields-emails").show();
        } else {
            $("#extra-fields-emails").hide().val("");

        }
    }).trigger("foo");



    $("input.query:radio").bind("change foo", function() {
        var val = $(this).val();
		
		$.get("ajax/query-description.php",{slug:val} ,function(data){
			if(data.description){
				$("#explainarea").css("margin-top", Math.min($(".query:visible:last").offset().top - 2*$("#explainarea").height(), Math.max($(window).scrollTop()-$("#explainarea").height()/2,0) ));
				$("#explainarea").html("<strong>" + data.name + "</strong><p>" + data.description).fadeIn();
			}
			else{
				$("#explainarea").hide();
			}
		});
        var html = "";

        var field_name_to_obj_map = abacus.custom_field_list;


        if (abacus.custom_fields[val]) {

            var map = $.map(abacus.custom_fields[val], function(val) {
                return field_name_to_obj_map[val];
            });


            for (var i = 0; i < map.length; i++) {
                var type = map[i].type || "text";
                var value = map[i].value || "";
				if(map[i].is_select === "1" && abacus.client()){
					(function(custom_field){
						
							$.post("ajax/custom_field_data.php", {cvs_name: abacus.client(), slug: custom_field.name } , function(data){
								if(!data){return;}
								
								var multiple = !!custom_field.name.match(/\[\]$/);
								var label = custom_field.label;
					            var name = custom_field.name;
								var bare_name = custom_field.name.replace("[]","");
					            var multi_bool = (multiple ? "multiple" : "");
					            var html = '<label class="control-label">' + label + '</label><div class="controls"><select style="width:100%" data-placeholder="' + label + '" ' + multi_bool + ' id="' + bare_name + '_select" name="' + name + '">';
					            for (var i = 0; i < data.length; i++) {
					                var item = data[i];
					                html += "<option value='" + _.escape(item.value) + "'>" + _.escape(item.text) + "</option>";
					            }
					            html += "</select></div>";
					            $("#extra-fields-inputs").show().append(html);
					            $("#" + bare_name + '_select').select2();

						});
						
						
					}(map[i]));
				}
 				else if(type === "checkbox"){
				 	html += '<label class="control-label">Extra</label><div class="controls"><label class="checkbox"><input type="checkbox" name="' + map[i].name + '" value="' + map[i].value  + '"> ' + map[i].label +'</label></div>';
				}
				else {
                    var step_append = (type === "number") ? " step='any' " : ""
                    html += "<label class='control-label' for='" + map[i].name + "'> " + map[i].label + " </label><div class='controls'>  <input type='" + type + "'" + step_append + " name='" + map[i].name + "' value='" + value + "'  id='" + map[i].name.replace(/\[|\]/g, "") + "'/></div>";
                }
            }
            $("#extra-fields-inputs").show().html(html);
            $("#start_dt, #end_dt").attr("placeholder", "yyy-mm-dd");
            if ($("#end_dt,#start_dt").length) {
                addTimeZoneSelector();
            }
        } else {
            //cleanup
            $("#extra-fields-inputs").hide().html("");
        }
        var cvs_name = abacus.client();

    });
    $("input.query:radio:checked").trigger("foo");

    $("table").first().tablesorter().css({
        'margin-top': "3em"
    });
    $("table").wrap("<div class='tablewrap'/>");



   function populateOutputConsGroup(client) {

        $.post("ajax/cons_groups.php", {
            cvs_name: client
        }, function(d) {
            var html = '<label class="control-label">Cons Group</label><div class="controls"><select style="width:100%" data-placeholder="Select cons groups" id="output_cons_group" name="output_cons_group">';
            for (var i = 0; i < d.length; i++) {
                var group = d[i];
                html += "<option value='" + group.membership_resource + "'>" + group.name + "</option>";
            }
            html += "</select></div>";
            $("#consgroup-group").show().append(html);
            $("#output_cons_group").select2();

        });
    }

 
    function addTimeZoneSelector() {
        var html = '<label class="control-label">Time Zone</label><div class="controls"><select id="timezone" name="timezone">	<option value="UTC" selected>UTC  (default)</option> <option value="Europe/London">Europe/London</option>	<option value="US/Eastern">US/Eastern</option>	<option value="US/Central">US/Central</option>	<option value="US/Mountain">US/Mountain</option>	<option value="US/Pacific">US/Pacific</option></select></div>';
        $("#end_dt,#start_dt").last().parent("div").after(html);
        $("#timezone").select2();

    }
    window.abacus.custom_field_functions = {
        addTimeZoneSelector: addTimeZoneSelector,
		populateOutputConsGroup : populateOutputConsGroup
    }

    $("input[name=format], input[name=cvs_name], #cvs_name_select").change(function() {
        localStorage.setItem(this.name, this.value);
    });
    for (key in localStorage) {
        if (localStorage.hasOwnProperty(key)) {
            var node = $("input[name='" + key + "']");
            var stored = localStorage.getItem(key);
            if (node[0] && node[0].type === "radio") {
                var tocheck = node.filter("[value=" + stored + "]");
                if (tocheck[0]) {
                    tocheck[0].checked = true;
                }
                tocheck.change();
            } else if (key === "navtab") {
                $("#" + stored).click();

            }
        }
    }

    $("#custom_fields_for_query").select2();

    $("input[name=format]").bind("change foo", function() {
        // certain fields should only display when a file export fmt is selected
        var display_file_export_options = abacus.format() === "table" ? "hide" : "show";
        $("#zip_file_label, #extra-fields-slug")[display_file_export_options]();
        // callback should only display for JSON output
	    if ($("#json")[0].checked) {
            $("#callback-group").show();
        } else {
            $("#callback-group").hide().val("");
        }
		//disable and uncheck output options of output is html. 
		$("input[name=output]").prop("disabled", abacus.format() === "table").prop("checked", abacus.format() !== "table");
		$("#clearoutput").click();
    }).trigger("foo");

$("#loadfromfile").click(function(){
	$("#fileinput").click();
});


$("#fileinput").change(function(evt){
  //Retrieve the first (and only!) File from the FileList object
  var f = evt.target.files[0]; 

  if (f) {
    var r = new FileReader();
    r.onload = function(e) { 
        var contents = e.target.result;
      myCodeMirror.getDoc().setValue(contents);
    }
    r.readAsText(f);

  } 
});







});
