$(document).ready(function() {

  var url=document.URL;
  var a = url.split('admin');



if(url.search('admin') != -1){
      $('#navbar_notadmin').hide();
        $('#navbar_admin').show();
  } else{
    $('#navbar_notadmin').show();
        $('#navbar_admin').hide();
  }

});

 