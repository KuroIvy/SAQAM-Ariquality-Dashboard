<!DOCTYPE html>
<html>
<head>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="libs/jquery/3.6.3/jquery.min.js"></script>
<script src="histograms.js"></script>
<link href="https://api.mapbox.com/mapbox-gl-js/v2.14.0/mapbox-gl.css" rel="stylesheet">
<script src="https://api.mapbox.com/mapbox-gl-js/v2.14.0/mapbox-gl.js"></script>
<style>
body { margin: 0; padding: 0; }
#map { position: relative; width: 600px; height: 400px;}
.marker {
            width: 50px;
            height: 50px;
            color: "#FFFFFF";
            cursor: pointer;
        }
</style>
</head>

<body>
<h1>Dashboard</h1>
<div>
  <div>
  <a href="/">Home</a>
  </div>
  <div>
  Time period 
  <input type="radio" name="db_from" value="db_from_all" id="db_from_all" checked><label for="db_from_all">All</label>
  <input type="radio" name="db_from" value="db_from_month" id="db_from_month"><label for="db_from_month">Last month</label>
  <input type="radio" name="db_from" value="db_from_week" id="db_from_week"><label for="db_from_week">Last week</label>
  </div>
  <div>
  Days of the week
  <input type="radio" name="db_days" value="db_days_all" id="db_days_all" checked><label for="db_days_all">All</label>
  <input type="radio" name="db_days" value="db_days_wd" id="db_days_wd"><label for="db_days_wd">Monday to Friday</label>
  <input type="radio" name="db_days" value="db_days_we" id="db_days_we"><label for="db_days_we">Saturday and Sunday</label>
  </div>
  <div>
  Sensors
  <select name="db_sensors" id="db_sensors"></select>
  </div>
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
  <canvas id="ppm"></canvas>
  <canvas id="ppm_daily"></canvas>
  <canvas id="temperature"></canvas>
  <canvas id="humidity"></canvas>
</div>

<script>
var Sen_ID =[];
var Locations =[];
var Latest_Measurements =[];
//load_sensors();
function load_sensors(){
  $.ajax({
    url: 'https://sacaqm.web.cern.ch/dbread.php',
    type: 'get',
    data: {cmd:"get_sensors_last"},
    success: function(data) {
      //console.log(data);  
      sensors=JSON.parse(data);
      opt="";
      for(sensor of sensors){
        opt+="<option value='"+sensor["sensor_id"]+"'>"+sensor["sensor_id"]+"</option>\n"; 
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

$("#db_submit").click(function() {
  var request={cmd:"get_sen55"};
  time=$('input[name=db_from]:checked').val();
  if (time.includes("month")){
    var date = new Date();
    date.setDate(date.getDate()-30);
    request["from"]=date.getFullYear()+"-"+(date.getMonth()+1<10?"0":"")+(date.getMonth()+1)+"-"+(date.getDate()<10?"0":"")+date.getDate();
  }else if (time.includes("week")){
    var date = new Date();
    date.setDate(date.getDate()-7);
    request["from"]=date.getFullYear()+"-"+(date.getMonth()+1<10?"0":"")+(date.getMonth()+1)+"-"+(date.getDate()<10?"0":"")+date.getDate();
  }
  days=$('input[name=db_days]:checked').val();
  if (days.includes("wd")){
    request["weekdays"]="true"
  }else if (days.includes("we")){
    request["weekend"]="true"
  }
  for(i=0;i<$('#db_sensors').find(":selected").length;i++){
    request["sensor_id"]=$('#db_sensors').find(":selected")[i].value;
  }
  
  get_data(request);
  return false;
});

var c_ppm=null;
var c_ppm_daily=null;
var c_temperature=null;
var c_humidity=null;

function get_data(request){
  $.ajax({
    url: 'https://sacaqm.web.cern.ch/dbread.php',
    type: 'get',
    data: request,
    success: function(data) {
      //console.log(data);  
      points=JSON.parse(data);
      pm1p0 = new H1(55,0,55);
      pm2p5 = new H1(55,0,55);
      pm4p0 = new H1(55,0,55);
      pm10p0 = new H1(55,0,55);
      temp = new H1(80,-10,30);
      humidity = new H1(80,0,80);
      ppm1p0 = new Profile(48,0,24);
      ppm2p5 = new Profile(48,0,24);
      ppm4p0 = new Profile(48,0,24);
      ppm10p0 = new Profile(48,0,24);
      
      for (p of points){ 
        pm1p0.fill(p["pm1p0"]);
        pm2p5.fill(p["pm2p5"]);
        pm4p0.fill(p["pm4p0"]);
        pm10p0.fill(p["pm10p0"]);
        temp.fill(p["temperature"]);
        humidity.fill(p["humidity"]);
        //compute the hour the measurement was taken as a decimal
        hour=parseFloat(p["timestamp"].split(" ")[1].split(":")[0]);
        hour+=parseFloat(p["timestamp"].split(" ")[1].split(":")[1]/60.);
        hour+=parseFloat(p["timestamp"].split(" ")[1].split(":")[2]/3600.);
        //console.log("time: "+p["timestamp"].split(" ")[1]+" => value:"+hour);
        ppm1p0.fill(hour,p["pm1p0"]);
        ppm2p5.fill(hour,p["pm2p5"]);
        ppm4p0.fill(hour,p["pm4p0"]);
        ppm10p0.fill(hour,p["pm10p0"]);
      }

      //console.log("ppm1p0: bins="+ppm1p0.bins.length+", values="+ppm1p0.data);
      
      if (c_ppm) {c_ppm.clear();c_ppm.destroy();c_ppm=null;}
      c_ppm = new Chart($("#ppm"), {
          type: 'bar',
          data: {
            labels: pm1p0.bins,
            datasets: [
              {label: 'pm1.0', data: pm1p0.data},
              {label: 'pm2.5', data: pm2p5.data},
              {label: 'pm4.0', data: pm4p0.data},
              {label: 'pm10.0', data: pm10p0.data},
            ]
          },
          options: { scales: { 
            x: { title: { text: 'PM Concentration (μg/m³)' , display: true}},
            y: { title: { text: 'Frequency', display: true }}
          }}
      });

      if (c_ppm_daily) {c_ppm_daily.clear();c_ppm_daily.destroy();c_ppm_daily=null;}
      c_ppm_daily = new Chart($("#ppm_daily"), {
          type: 'bar',
          data: {
            labels: [
              "0:00","0:30","1:00","1:30","2:00","2:30","3:00","3:30",
              "4:00","4:30","5:00","5:30","6:00","6:30","7:00","7:30",
              "8:00","8:30","9:00","9:30","10:00","10:30","11:00","11:30",
              "12:00","12:30","13:00","13:30","14:00","14:30","15:00","15:30",
              "16:00","16:30","17:00","17:30","18:00","18:30","19:00","19:30",
              "20:00","20:30","21:00","21:30","22:00","22:30","23:00","23:30",
            ],
            datasets: [
              {label: 'pm1.0', data: ppm1p0.data},
              {label: 'pm2.5', data: ppm2p5.data},
              {label: 'pm4.0', data: ppm4p0.data},
              {label: 'pm10.0', data: ppm10p0.data},
            ]
          },
          options: { 
            scales: 
            { x: { title: { text: 'Hour in the day' , display: true}},
              y: { title: { text: 'Mean value (μg/m³)', display: true }}},
            animation: false
          }
      });

      if (c_temperature) {c_temperature.clear();c_temperature.destroy();c_temperature=null;}
      c_temperature = new Chart($("#temperature"), {
          type: 'bar',
          data: {
            labels: temp.bins,
            datasets: [
              {label: 'temperature', data: temp.data},
            ]
          },
          options: { 
            scales: 
            { x: { title: { text: 'Degrees Celsius' , display: true}},
              y: { title: { text: 'Frequency', display: true }}}, 
            animation: false
          }
      });

      if (c_humidity) {c_humidity.clear();c_humidity.destroy();c_humidity=null;}
      c_humidity = new Chart($("#humidity"), {
          type: 'bar',
          data: {
            labels: humidity.bins,
            datasets: [
              {label: 'humidity', data: humidity.data},
            ]
          },
          options: { 
            scales: 
            { x: { title: { text: 'Relative humidity %' , display: true}},
              y: { title: { text: 'Frequency', display: true }}},
            animation: false
        }
      });

    }
  });
} 
 
$(document).ready(function() {
  load_sensors();
  get_data({cmd:"get_sen55", from:"2023-03-01"});  
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
              request["from"]=date.getFullYear()+"-"+(date.getMonth()+1<10?"0":"")+(date.getMonth()+1)+"-"+(date.getDate()<10?"0":"")+date.getDate();
            }else if (time.includes("week")){
              var date = new Date();
              date.setDate(date.getDate()-7);
              request["from"]=date.getFullYear()+"-"+(date.getMonth()+1<10?"0":"")+(date.getMonth()+1)+"-"+(date.getDate()<10?"0":"")+date.getDate();
            }
            days=$('input[name=db_days]:checked').val();
            if (days.includes("wd")){
              request["weekdays"]="true"
            }else if (days.includes("we")){
              request["weekend"]="true"
            }
            for(i=0;i<$('#db_sensors').find(":selected").length;i++){
              request["sensor_id"]=marker.name;
            }
            
            get_data(request);
            return false;
        });
        });
        
  });
}
</script>

</body>
</html>