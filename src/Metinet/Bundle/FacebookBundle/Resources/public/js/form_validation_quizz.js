$(document).ready(function() {

$("#stop").hide();
$("#reset").hide();
var startTime = 0;
var start = 0;
var end = 0;
var diff = 0;
var reset = false;
var timerID = 0;

  // Lors du chargmeent de la page, le chronomètre se lance
  Start();

  var length_quizz = $(".taille_form").data("quizz-length");
  var length_rep = $(".rep_quizz").data("rep-length");


  // On cache les forùulaires de questions/réponses
  for(i=2; i<=length_quizz; i++){
      $('#form_quizz_'+i).hide();
  }
    // On cache le formulaire pour calculer les resultats
     $('#resultat').hide();
     $('#quizz_finish').hide();

     // DIV concernant une erreur de transmission de JSON
     $('.error_JSON').hide();
     //$('.loading').hide();

     

});


// Array contenant les id des réponses choisis
var resultat = new Array();
// Array contenant les id des réponses choisis
var res = new Array();

// Nombre de formulaire au total
var taille = $(".taille_form").data("quizz-length");

/*
* Fonction permettant d'ajouter en BDD les reponses choisi par l'user
* Permet aussi de show/hide les formulaires
* Permet d'avoir une array des reponses choisies afin de pouvoir calculer le resultat
*/
function FormRep(form) {


  var url = $(form).attr("action");
  var dataURL = form.children('#reponse').val();

  var num_form=$(form).parents(".quizz").attr("id"); 

  // A chaque réponses cliqué, on ajoute dans l'array l'id de la réponse
  resultat.push(dataURL);


  // Show/Hide les formulaires
  $(form).parents(".quizz").hide()
  $(form).parents(".quizz").next().show();

    $.ajax({
        type: "POST",
        dataType: "JSON",
        url: url,
        data: "id_reponse="+dataURL,
        success: function(data){



           if(data.responseCode==200  && num_form == "form_quizz_"+taille){
                Stop();
                $('.res').val(resultat);
                //$('#resultat').submit();
                $('#quizz_finish').show();
                $('#resultat').show();   
            }

           if( data.responseCode==200 ){    
                
                $('#output').html(data.idReponse);
                $('#output').css("color","green");

            }
           else if(data.responseCode==400){//bad request
               $('.error_JSON').show();
           }
           else{
              //if we got to this point we know that the controller
              //did not return a json_encoded array. We can assume that           
              //an unexpected PHP error occured
              $('.error_JSON').show();
              alert("Pas de JSON retourné.");

              //if you want to print the error:
              $('#output').html(data);
           }
        }
    }); 

      //we dont what the browser to submit the form
      return false;
}

/*
*  Fonctions pour le chronomètre
*/

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

            $("#timer").html(hr+" hr : "+min+" min : "+sec+" sec");

          }
         
          function Start(){
               if (reset) start = new Date();
               else{
               start = new Date()-diff
                   start = new Date(start)
                }
               timerID = setInterval(chrono, 10);
          }
    
          function Stop(){
            
            clearTimeout(timerID);
          }