<!DOCTYPE html>
<html>
<head>
<script type="text/javascript" src="https://root.cern/js/latest/scripts/JSRoot.core.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
</head>

<!--https://root-forum.cern.ch/t/stand-alone-t-multi-graph-example-of-jsroot/20394/6-->
<body>
  
  <div id="drawing" style="height:200px;width:200px;"></div>
  
<script>


function get_data(){
  $.ajax({
    url: 'https://sacaqm.web.cern.ch/dbread.php',
    type: 'get',
    data: {
      cmd:"get_sen55",
      from:"2023-03-02"
    },
    success: function(data) {

       console.log(data);
     }
  }); 
}
 
$( document ).ready(function() {
  get_data();  
});
</script>

</body>
   <script type='module'>

      //import { createHistogram, redraw } from '../modules/main.mjs';

 

      let cnt = 0;

      function updateGUI() {

         let histo = createHistogram("TH2I", 20, 20);

         for (let iy = 0; iy < 20; iy++)

            for (let ix = 0; ix < 20; ix++) {

               let bin = histo.getBin(ix+1, iy+1), val = 0;

               switch (cnt % 4) {

                  case 1: val = ix + 19 - iy; break;

                  case 2: val = 38 - ix - iy; break;

                  case 3: val = 19 - ix + iy; break;

                  default: val = ix + iy; break;

               }

               histo.setBinContent(bin, val);

            }

 

         histo.fName = "generated";

         histo.fTitle = "Drawing " + cnt++;

         redraw('object_draw', histo, "colz");

      }

      updateGUI();

      setInterval(updateGUI, 3000);

   </script>
</html>