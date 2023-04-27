<?php

include_once('includes.php');

$conn = new mysqli($db["host"],$db["user"],$db["pass"],$db["name"],$db["port"]);
if ($conn->connect_error) {
  echo "Error connecting to database";
  exit();
}

$d=$_GET;

if($d["cmd"]=="get_sen55"){
  $sql="SELECT * FROM SEN55";
  $cond=array();
  if(isset($d["from"]))     {$cond[]=" timestamp > '".$d["from"]."' ";}
  if(isset($d["to"]))       {$cond[]=" timestamp < '".$d["to"]."' ";}
  if(isset($d["sensor_id"])){$cond[]=" sensor_id = '".$d["sensor_id"]."' ";}
  if(isset($d["weekdays"])) {$cond[]=" WEEKDAY(timestamp)<5 ";}
  if(isset($d["weekend"]))  {$cond[]=" WEEKDAY(timestamp)>4 ";}
  
  if(count($cond)>0){
    $sql.=" WHERE ";
    $sql.=implode(" AND ",$cond);
  }
  $sql.=";";
}
else if($d["cmd"]=="get_sensors"){
  $sql="SELECT DISTINCT sensor_id FROM SEN55;";
}
else if($d["cmd"]=="get_sensors_last"){
  //$sql="SELECT sensor_id, max(timestamp) AS timestamp FROM SEN55 GROUP BY sensor_id ORDER BY timestamp DESC;";
  $sql ="SELECT sensor_id, timestamp, longitude, latitude, altitude, pm1p0,pm2p5,pm4p0,pm10p0,temperature,humidity,vox,nox FROM SEN55 "; 
  $sql.="INNER JOIN ( SELECT sensor_id as sid, MAX(timestamp) AS mts FROM SEN55 GROUP BY sensor_id ) AS tt ON tt.mts = SEN55.timestamp AND tt.sid=SEN55.sensor_id ";
  $sql.="ORDER BY timestamp DESC;";
}


if(@$_GET["debug"]=="true"){echo $sql;}
$result=$conn->query($sql);
$ret = array();
while($row = $result->fetch_assoc()) {
  //$ret[] = $row;
  $row2=array();
  foreach($row as $k=>$v){
    //$row2[$k]=htmlentities($v,ENT_COMPAT,'ISO-8859-1', true);
    $row2[$k]=mb_convert_encoding($v,"UTF-8","ISO-8859-1");
    //$row2[$k]=utf8_encode($v);
    //$row2[$k]=$v;
  }
  $ret[]=$row2;
}
//echo "close";
$conn->close();

echo json_encode($ret);
?>