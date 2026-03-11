<?php
include 'config.php';
$water=$_GET['water']??0;
$battery=$_GET['battery']??100;
$signal=$_GET['signal']??'GOOD';
$status=($water<20)?'LEAK':'SAFE';

$conn->query("UPDATE tank_status SET
water_level='$water',
battery='$battery',
signal='$signal',
status='$status',
last_updated=NOW()
WHERE id=1");

$conn->query("INSERT INTO leak_logs(status,source) VALUES('$status','ESP32')");

// Placeholder for alerts (email/SMS)
if($water<20){
  $msg="Leak Alert! Water level $water%";
  $conn->query("INSERT INTO alerts(type,message) VALUES('LEAK','$msg')");
}

echo "OK";
?>