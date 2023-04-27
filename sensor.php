<!DOCTYPE html>
<html>
<head>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="libs/jquery/3.6.3/jquery.min.js"></script>
<script src="histograms.js"></script>
<script src="https://polyfill.io/v3/polyfill.min.js?features=es6"></script>
<script src="https://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-MML-AM_CHTML"></script>
<link href="https://api.mapbox.com/mapbox-gl-js/v2.14.0/mapbox-gl.css" rel="stylesheet">
<script src="https://api.mapbox.com/mapbox-gl-js/v2.14.0/mapbox-gl.js"></script>
<style>
body { margin: 0; padding: 0; }
#map { position: relative; width: 600px; height: 400px;}
</style>
</head>

<body>
<h1>Sensor</h1>
<div>
  <div>
  <a href="/">Home</a>
  </div>
  <div>
  Time period
  <input type="radio" name="db_from" value="db_from_all" id="db_from_all" checked><label for="db_from_all">All</label>
  <input type="radio" name="db_from" value="db_from_month" id="db_from_month"><label for="db_from_month">Last month</label>
  <input type="radio" name="db_from" value="db_from_week" id="db_from_week"><label for="db_from_week">Last week</label>
  <input type="radio" name="db_from" value="db_from_day" id="db_from_day"><label for="db_from_day">Last day</label>
  <!--<input type="radio" name="db_from" value="db_from_hours" id="db_from_hours" checked><label for="db_from_hours">Last few hours</label>-->
  </div>
  <div>
  Sensors
  <select name="db_sensors" id="db_sensors"></select>
  </div>
  <!-- <div>
  Frequency
  <select name="freq_menu" id="freq_menu">
    <option value="All">All</option>
    <option value="Hourly">Hourly Avg</option>
    <option value="Daily">Daily Avg</option>
  </select>
  </div> -->
  <button id="db_submit">Submit</button>
</div>
<style>
.mapboxgl-popup {
max-width: 400px;
font: 12px/20px 'Helvetica Neue', Arial, Helvetica, sans-serif;
}
</style>
<div id="map"></div>
<div>
  <canvas id="sensor"></canvas>
</div>

<script>
var Sen_ID =[];
var Locations =[];
var Latest_Measurements =[];

function load_sensors(){
  let urlParams = new URLSearchParams(window.location.search);
  let sensor_id = (urlParams.has("sensor_id")?urlParams.get("sensor_id"):"");
  $.ajax({
    url: 'https://sacaqm.web.cern.ch/dbread.php',
    type: 'get',
    data: {cmd:"get_sensors_last"},
    success: function(data) {
      //console.log(data);
      sensors=JSON.parse(data);
      opt="";
      for(sensor of sensors){
        checked=(sensor_id==sensor["sensor_id"]?" selected ":"");
        opt+="<option "+checked+"value='"+sensor["sensor_id"]+"'>"+sensor["sensor_id"]+"</option>\n";
        Sen_ID.push(sensor["sensor_id"]);
        if(sensor["latitude"].length>0 && sensor["longitude"].length>0)
        {
          Locations.push([parseFloat(sensor["longitude"]),parseFloat(sensor["latitude"])]);
        }
        else {Locations.push([0.0,0.0]);}
        Latest_Measurements.push('PM1.0: '+sensor["pm1p0"]+' PM2.5: '+sensor["pm2p5"]+' PM4.0: '+sensor["pm4p0"]
        +' PM10.0: '+sensor["pm10p0"]+' Temp: '+sensor["temperature"]+' Humidity: '+sensor["humidity"]);
      }
      load_map();
      $("#db_sensors").html(opt);
    }
  });
}

function createMarkers(names, descriptions, locations) {
  // Create an empty array to hold the markers
  var data = [];

  // Iterate over each location and create a new marker object
  for (var i = 0; i < locations.length; i++) {
    var marker = {
      name: names[i],
      description: descriptions[i],
      location: locations[i]
    };
    data.push(marker);
  }

  return data;
}

function formatDate(date){
  return date.getFullYear()+"-"+(date.getMonth()+1<10?"0":"")+(date.getMonth()+1)+"-"+(date.getDate()<10?"0":"")+date.getDate();
}

$("#db_submit").click(request_data);


function request_data() {
  var request={cmd:"get_sen55"};
  time=$('input[name=db_from]:checked').val();
  if (time.includes("month")){
    var date = new Date();
    date.setDate(date.getDate()-30);
    request["from"]=formatDate(date);
  }else if (time.includes("week")){
    var date = new Date();
    date.setDate(date.getDate()-7);
    request["from"]=formatDate(date);
  }else if (time.includes("day")){
    var date = new Date();
    date.setDate(date.getDate()-1);
    request["from"]=formatDate(date);
  }
  /*else if (time.includes("hours")){
    var date = new Date();
    date.setDate(date.getDate()-0.5);
    request["from"]=formatDate(date);
    request["from"]+=" "+date.getHours()+":"+(date.getMinutes()<10?"0":"")+date.getMinutes()+":"+(date.getSeconds()<10?"0":"")+date.getSeconds();
  }*/
  for(i=0;i<$('#db_sensors').find(":selected").length;i++){
    request["sensor_id"]=$('#db_sensors').find(":selected")[i].value;
  }

  get_data(request);
  return false;
}

var c_sensor=null;

function get_data(request){
  $.ajax({
    url: 'https://sacaqm.web.cern.ch/dbread.php',
    type: 'get',
    data: request,
    success: function(data) {
      //console.log(data);
      points=JSON.parse(data);
      dates = []
      pm1p0 = []
      pm2p5 = []
      pm4p0 = []
      pm10p0 = []
      humidity = []
      temperature = []
      for (p of points){
        dates.push(p["timestamp"]);
        date=new Date();
        date.setFullYear(parseInt(p["timestamp"].split(" ")[0].split("-")[0]));
        date.setMonth(parseInt(p["timestamp"].split(" ")[0].split("-")[1])-1);
        date.setDate(parseInt(p["timestamp"].split(" ")[0].split("-")[2]));
        date.setHours(parseInt(p["timestamp"].split(" ")[1].split(":")[0]));
        date.setMinutes(parseInt(p["timestamp"].split(" ")[1].split(":")[1]));
        date.setSeconds(parseInt(p["timestamp"].split(" ")[1].split(":")[2]));
        pm1p0.push({x:date.getTime(),y:parseFloat(p["pm1p0"])});
        pm2p5.push({x:date.getTime(),y:parseFloat(p["pm2p5"])});
        pm4p0.push({x:date.getTime(),y:parseFloat(p["pm4p0"])});
        pm10p0.push({x:date.getTime(),y:parseFloat(p["pm10p0"])});
        humidity.push({x:date.getTime(),y:parseFloat(p["humidity"])});
        temperature.push({x:date.getTime(),y:parseFloat(p["temperature"])});
      }

      if (c_sensor) {c_sensor.clear();c_sensor.destroy();c_sensor=null;}
      c_sensor = new Chart($("#sensor"), {
          type: 'line',
          data: {
            labels: dates,
            datasets: [
              {label: 'pm1.0', data: pm1p0, showLine: false},
              {label: 'pm2.5', data: pm2p5, showLine: false},
              {label: 'pm4.0', data: pm4p0, showLine: false},
              {label: 'pm10.0', data: pm10p0, showLine: false},
              {label: 'humidity', data: humidity, showLine: false, yAxisID: 'yh',},
              {label: 'temperature', data: temperature, showLine: false, yAxisID: 'yt'},
            ]
          },
          options: {
            scales: {
              x: { title: { text: 'Time' , display: true }}, //type: 'time', time: {unit: 'millisecond'}}
              y: { title: { text: 'μg/m³ ', display: true }},
              yt: { title: { text: 'C', display: true }, position:'right', grid: { drawOnChartArea: false}},
              yh: { title: { text: '%', display: true }, position:'right', grid: { drawOnChartArea: false}},
            },
            animation: false
          }
      });


    }
  });
}

$(document).ready(function() {
  load_sensors();
  setTimeout(request_data, 200);
});
function load_map(){
  //Setting up the map
   //insert Mapbox API token 
   mapboxgl.accessToken = 'YOUR_TOKEN_GOES_HERE';
  const map = new mapboxgl.Map({
  container: 'map', // container ID
  // Choose from Mapbox's core styles, or make your own style with Mapbox Studio
  style: 'mapbox://styles/mapbox/streets-v12', // style URL
  //made CERN the centre for now
  center: [6.072080, 46.231709], // starting position [lng, lat]
  zoom: 9 // starting zoom
  });
  const centrePoint = new mapboxgl.Marker({
    color: "#eb4034"})
    .setLngLat([6.072080, 46.231709])
    .setPopup(new mapboxgl.Popup({ offset: 25 }) // add popups
    .setHTML('<h3> CERN Campus </h3><p>Center of the map</p>'))
    .addTo(map);
  map.on('load', () => {
    var data = createMarkers(Sen_ID, Latest_Measurements, Locations);
 
    data.forEach(function(marker) {
        // var el = document.createElement('div');
        // el.className = 'marker';
        var markerObj = new mapboxgl.Marker()
            .setLngLat(marker.location)
            .setPopup(new mapboxgl.Popup({ offset: 25 }) // add popups
            .setHTML('<h3>' + marker.name + '</h3><p>' + marker.description + '</p>'))
            .addTo(map);

          markerObj.getElement().addEventListener('click', function() {
            // Your code to run when the marker is clicked
            var request={cmd:"get_sen55"};
            time=$('input[name=db_from]:checked').val();
            if (time.includes("month")){
              var date = new Date();
              date.setDate(date.getDate()-30);
              request["from"]=formatDate(date);
            }else if (time.includes("week")){
              var date = new Date();
              date.setDate(date.getDate()-7);
              request["from"]=formatDate(date);
            }else if (time.includes("day")){
              var date = new Date();
              date.setDate(date.getDate()-1);
              request["from"]=formatDate(date);
            }
            /*else if (time.includes("hours")){
              var date = new Date();
              date.setDate(date.getDate()-0.5);
              request["from"]=formatDate(date);
              request["from"]+=" "+date.getHours()+":"+(date.getMinutes()<10?"0":"")+date.getMinutes()+":"+(date.getSeconds()<10?"0":"")+date.getSeconds();
            }*/
            request["sensor_id"]=marker.name;
            

            get_data(request);
            return false;
        });
        });
        
  });
}
</script>

</body>
</html>