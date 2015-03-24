<?php 

function get_string_between($string, $start, $end){
    $string = " ".$string;
    $ini = strpos($string,$start);
    if ($ini == 0) return "";
    $ini += strlen($start);
    $len = strpos($string,$end,$ini) - $ini;
    return substr($string,$ini,$len);
}

function senderror($subject, $str){
 global $job;
 global $config;
 $to = $config['alert_email'];
 mail($to, $subject . " in OpenAbacus Scheduled Query for " . $job["client"], $str);
}

$cron_output = stream_get_contents(STDIN); 




include "db.php"; 

if(strlen($cron_output) === 0 || !$cron_output) { exit(); }


$log = json_decode(get_string_between($cron_output, "var log = ",";\n"), true);
$job = json_decode(get_string_between($cron_output, "<script id=job type='text/json'>","</script>"), true);


if(stripos($cron_output, "Fatal error:") !== false ){
  senderror("Fatal Error", $cron_output);
}
else if(stripos($cron_output, "Warning:") !== false ){
  senderror("Warning", $cron_output);
}

$db[$abacus_db]->query("INSERT INTO cron_log (body, create_dt, client, scheduled_query_id) VALUES (:cron_output, NOW(), :client, :scheduled_query_id)", array("cron_output"=>$cron_output, "client"=>$job["client"], "scheduled_query_id"=>$job["scheduled_query_id"]));

echo $cron_output;



?>
