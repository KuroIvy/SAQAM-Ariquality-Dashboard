<!DOCTYPE html>
<html>
<head>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="libs/jquery/3.6.3/jquery.min.js"></script>
</head>

<body>
<h1>List of sensors</h1>
<div>
  <div>
  <a href="/">Home</a>
  </div>
  <div id="sensors"></div>
</div>

<script>

function load_sensors(){
  $.ajax({
    url: 'https://sacaqm.web.cern.ch/dbread.php',
    type: 'get',
    data: {cmd:"get_sensors_last"},
    success: function(data) {
      pars=new URLSearchParams(window.location.search);
      console.log(pars);
      //console.log(data);  
      sensors=JSON.parse(data);
      ttt="<table>";
      ttt+="<tr>";
      ttt+="<th>Sensor ID</th>";
      ttt+="<th>Last measurement</th>";
      if(pars.has("showLocation")){
        ttt+="<th>Loc</th>";
      }
      ttt+="<th>Actions</th>";
      ttt+="</tr>";
      for(sensor of sensors){
        ttt+="<tr>";
        ttt+="<td>"+sensor["sensor_id"]+"</td>";
        ttt+="<td>"+sensor["timestamp"]+"</td>";
        if(pars.has("showLocation")){
          ttt+="<td>";
          if(sensor["latitude"].length>0 && sensor["longitude"].length>0){
            ttt+="<a target='map' href='https://www.google.com/maps/search/?api=1&query="+sensor["latitude"]+"%2C"+sensor["longitude"]+"'>map</a>";
          }
          ttt+="</td>";
        }
        ttt+="<td><a href='sensor.php?sensor_id="+sensor["sensor_id"]+"'>view</a></td>";
        ttt+="</tr>";
      }
      $("#sensors").html(ttt);
    }
  }); 
}

function formatDate(date){
  return date.getFullYear()+"-"+(date.getMonth()+1<10?"0":"")+(date.getMonth()+1)+"-"+(date.getDate()<10?"0":"")+date.getDate();
}

$(document).ready(function() {
  load_sensors();
});
</script>

</body>
</html>