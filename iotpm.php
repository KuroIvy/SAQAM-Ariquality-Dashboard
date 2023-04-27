<?php

include_once('includes.php');

$conn = new mysqli($db["host"],$db["user"],$db["pass"],$db["name"],$db["port"]);
if ($conn->connect_error) {
  echo "Error connecting to database";
  exit();
}

$d=$_GET;

if($d["cmd"]=="add_measurement"){
  $sql ="INSERT INTO measurements (sensor_id, temperature) VALUES ( ";
  $sql.="'".$d["sensor_id"]."',"; 
  $sql.="'".$d["temperature"]."' ";
  $sql.=");";
}
else if($d["cmd"]=="add_sen55"){
  $sql ="INSERT INTO SEN55 (sensor_id, area, operator, cellid,";
  if(isset($d["latitude"])){$sql.=" latitude,";}
  if(isset($d["longitude"])){$sql.=" longitude,";}
  if(isset($d["altitude"])){$sql.=" altitude,";}
  $sql.=" temperature, humidity, pm1p0, pm2p5, pm4p0, pm10p0, voc, nox";
  $sql.=") VALUES ( ";
  $sql.="'".$d["sensor_id"]."',"; 
  $sql.="'".$d["area"]."',"; 
  $sql.="'".$d["operator"]."',"; 
  $sql.="'".$d["cellid"]."',"; 
  if(isset($d["latitude"])){$sql.="'".$d["latitude"]."', ";}
  if(isset($d["longitude"])){$sql.="'".$d["longitude"]."', ";}
  if(isset($d["altitude"])){$sql.="'".$d["altitude"]."', ";}
  $sql.="'".$d["temperature"]."', ";
  $sql.="'".$d["humidity"]."',"; 
  $sql.="'".$d["pm1p0"]."',"; 
  $sql.="'".$d["pm2p5"]."',"; 
  $sql.="'".$d["pm4p0"]."',"; 
  $sql.="'".$d["pm10p0"]."',"; 
  $sql.="'".$d["voc"]."',"; 
  $sql.="'".$d["nox"]."' "; 
  $sql.=");";
}

echo $sql;
$ret = array();
if($conn->query($sql)){
  $ret["affected_rows"]=$conn->affected_rows;
}else{
  $ret["error"]=$conn->error;  
}
//echo "close";
$conn->close();

echo json_encode($ret);
?>