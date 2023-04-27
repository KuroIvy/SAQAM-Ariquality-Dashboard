<!DOCTYPE html>
<html>
<head>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
<script src="histograms.js"></script>
</head>

<body>
  
    <canvas id="histogram"></canvas>
  
<script>
 
function get_data(){
  $.ajax({
    url: 'https://sacaqm.web.cern.ch/dbread.php',
    type: 'get',
    data: {
      cmd:"get_sen55", 
      from:"2023-03-03" 
    },
    success: function(data) {
      //console.log(data);  
      points=JSON.parse(data);
      pm1p0 = new H1(50,0,30);
      pm2p5 = new H1(50,0,30);
      pm4p0 = new H1(50,0,30);
      pm10p0 = new H1(50,0,30);
      for (p of points){
        pm1p0.fill(p["pm1p0"]);
        pm2p5.fill(p["pm2p5"]);
        pm4p0.fill(p["pm4p0"]);
        pm10p0.fill(p["pm10p0"]);
      }
      console.log(pm1p0.data);
      console.log(pm2p5.data);
      console.log(pm4p0.data);
      console.log(pm10p0.data);
      new Chart($("#histogram"), {
          type: 'bar',
          data: {
            labels: pm1p0.bins,
            datasets: [
              {label: 'pm1.0', data: pm1p0.data},
              {label: 'pm2.0', data: pm2p5.data},
              {label: 'pm4.5', data: pm4p0.data},
              {label: 'pm10.0', data: pm10p0.data},
            ]
          },
          options: { scales: { 
            x: { title: { text: 'PPM' , display: true}},
            y: { title: { text: 'Frequency', display: true }}
          }}
        });
     }
  }); 
}
 
$( document ).ready(function() {
  get_data();  
});
</script>

</body>

</html>