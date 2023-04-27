<!DOCTYPE html>
<html>
<head>
</head>
<body>
<div>
    <h1>The CERN Gateway to SACAQM</h1>
    <h2>The South African Consortium of Air Quality Monitoring</h2>
    <h2>Links</h2>
    <ul>
      <li><a href="dashboard.php">Dashboard</a></li>      
      <li><a href="sensor.php">Sensor data</a></li>      
      <li><a href="sensors.php">List of sensors</a></li>
      <li><a href="dbread.php?cmd=get_sensors">List of sensors (raw data)</a></li>
      <li><a href="dbread.php?cmd=get_sen55&from=<?=date('Y-m-d');?>">Measurements from today (raw data)</a></li>
      <li><a href="dbread.php?cmd=get_sen55&from=<?=date('Y-m-d',strtotime('-1 days'));?>">Measurements from yesterday (raw data)</a></li>     
      <li><a href="https://www.sacaqm.org/">SACAQM</a></li>
    </ul>

<h2>Data format</h2>
Results are returned in JSON format as a list of measurements.
Each measurement is a key-value pair. 
<ul>
  <li>id: database entry index</li>
  <li>sensor_id: sensor unique identifier</li>
  <li>timestamp: European Central Time</li>
  <li>temperature: degrees celsius</li>
  <li>humidity: relative in per cent</li>
  <li>pm1p0: parts per million</li>
  <li>pm2p5: parts per million</li>
  <li>pm4p0: parts per million</li>
  <li>pm10p0: parts per million</li>
  <li>voc: index</li>
  <li>nox: index</li>
  <li>latitude: degrees</li>
  <li>longitude: degrees</li>
  <li>altitude: meters</li>
  <li>area: Mobile carrier area</li>
  <li>operator: Mobile carrier operator code</li>
  <li>cellid: Mobile carrier tower</li>
</ul>
    
An example of a measurement is the following.
<pre>
  [
  {"id":"724",
  "sensor_id":"351358811387312",
  "timestamp":"2023-03-02 07:57:01",
  "temperature":"13.130",
  "humidity":"33.69",
  "pm1p0":"32.05",
  "pm2p5":"37.00",
  "pm4p0":"39.04",
  "pm10p0":"40.05",
  "voc":"19.00",
  "nox":"1.00",
  "latitude":"46.233139",
  "longitude":"6.052717",
  "altitude":"492.600006",
  "area":"0457",
  "operator":"22801",
  "cellid":"0110FE03"
  },...
  ]
</pre>
</div>
</body>
</html>