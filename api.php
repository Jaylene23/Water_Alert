<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header("Content-Type: application/json");
include 'config.php';
if(!isLoggedIn()) { echo json_encode(['success'=>false]); exit; }

$response=['success'=>false];
$action=$_POST['action']??'';

if($action=='get_status'){
  $response=['success'=>true,'data'=>getTankStatus($conn)];
}

elseif($action=='simulate_leak'){
  $status=$_POST['status']??'SAFE';
  $level=($status=='LEAK')?10:75;
  $conn->query("INSERT INTO leak_logs(status,source) VALUES('$status','WEB_SIMULATION')");
  $conn->query("UPDATE tank_status SET water_level='$level',status='$status',last_updated=NOW() WHERE id=1");
  $response=['success'=>true];
}

elseif($action=='toggle_valve'){
  $current=$_POST['current']??'OPEN';
  $new=($current=='OPEN')?'CLOSED':'OPEN';
  $conn->query("UPDATE tank_status SET valve_state='$new' WHERE id=1");
  $response=['success'=>true,'new'=>$new];
}

elseif($action=='get_logs'){
  $res=$conn->query("SELECT * FROM leak_logs ORDER BY created_at DESC LIMIT 50");
  $logs=[];
  while($row=$res->fetch_assoc()) $logs[]=$row;
  $response=['success'=>true,'logs'=>$logs];
}

elseif($action=='get_alerts'){
  $res=$conn->query("SELECT * FROM alerts ORDER BY sent_at DESC LIMIT 20");
  $alerts=[];
  while($row=$res->fetch_assoc()) $alerts[]=$row;
  $response=['success'=>true,'alerts'=>$alerts];
}

echo json_encode($response);
?>