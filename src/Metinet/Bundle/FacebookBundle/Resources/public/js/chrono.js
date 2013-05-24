$(document).ready(function(){

$("#stop").hide();
var startTime = 0;
var start = 0;
var end = 0;
var diff = 0;
var reset = false;
var timerID = 0;



Start();


$("#stop").click(function(e){
  e.preventDefault();
  Stop();
  return false;
});
          

$("#reset").click(function(e){
  e.preventDefault();
  Reset();
  return false;
});
            

          function chrono(){
             end = new Date();
             diff = end - start;
             diff = new Date(diff);
             var sec = diff.getSeconds();
             var min = diff.getMinutes();
             var hr = diff.getHours()-1;

             if (sec < 10){
                sec = "0" + sec;
             }
             if (min < 10){
                min = "0" + min;
             }
             if (hr < 10){
                hr = "0" + hr;
             }

            $("#timer").html(hr+" : "+min+" : "+sec);

          }
         
          function Start(){
             $(".tog").toggle();
               if (reset) start = new Date();
               else{
               start = new Date()-diff
                   start = new Date(start)
                }
               timerID = setInterval(chrono, 10);
          }

          function Reset(){
            $("#timer").html("00 : 00 : 00 ");
            start = new Date();
            reset = true;
          }
    
          function Stop(){
            $(".tog").toggle();
            clearTimeout(timerID);
          }
});
