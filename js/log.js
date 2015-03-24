
function bytesToSize(bytes) {
   if(bytes == 0) return '0 Byte';
   var k = 1024;
   var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
   var i = Math.floor(Math.log(bytes) / Math.log(k));
   return (bytes / Math.pow(k, i)).toPrecision(3) + ' ' + sizes[i];
}

function log_after_post(){

	console.log("Thread ID: ", window.threadID);
	console.log("POST: ", window.post);
    
    
    if(query_log_id){
        $.getJSON("ajax/query_log.php", {query_log_id: query_log_id}, function(data){
            $("#csvlink").attr("title", data.query_log.row_count + " row(s), " + bytesToSize(data.query_log.file_size));
            
            window.log = data.query_log;
    		console.log("Query Name: " , log.query_name);
    		console.log("SQL: " , log.query_sql);
    		console.log("Row Count: " , log.row_count);
    		console.log("Query Time: " , log.query_time + " ms");
    		console.log("File Name: " , log.file);
            console.table([data.query_log]);
            console.table(data.timer_log);
            
    		_gaq.push(['_trackTiming', 'SQL', log.query_name, log.diff, log.sql]);
    		_gaq.push(["_trackEvent", "SQL", log.query_name, log.sql, log.diff, true]);
            
            
        });
    }

}