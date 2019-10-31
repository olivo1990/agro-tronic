var datosCSV = []; 
var dtable;
$(document).ready(function(){

  devolverConsolidados();

  $( "#archivo" ).change(function() {

    subirArchivo();

  });
	
});

function devolverConsolidados () {
  $.post("modulos/subida_csv/controlador.php",
    {
      accion: "devolverConsolidados"
    },
    function (data, textStatus) {
      data = JSON.parse(data);

      if(data.fincas.length == 0 || data.consolidadosDia.length == 0 || data.arrayTiempo.length == 0){
        return;
      }

      armarConsolidadoDia(data);

    }
  );

}

function subirArchivo() {

  if($("#finca").val() == ""){
    alert("El nombre de la finca es necesario");
    $("#archivo").val("");
    return;
  }

  var data = new FormData();
	
  if($('#archivo').val() == ""){
    alert("No hay archivo cargado. Por favor, selecciona un archivo para cargarlo.");
    return;
  }
    
  var ext = $('#archivo').val().split('.').pop().toLowerCase();
  if($.inArray(ext.toLowerCase(), ['csv']) == -1) {
    alert("Extensión de archivo no valida. El archivo a importar debe ser de extensión 'csv'");
    return;
  }

  $("#textoEspera1").show();
  $("#textoEspera2").hide();
  $("#textoEspera3").hide();
  $("#textoEspera4").hide();

  $(".load").fadeIn(800);
  $("#textoEspera1").delay(7000).fadeOut(1000);
  $("#textoEspera2").delay(8000).fadeIn(1000);
  $("#textoEspera2").delay(4000).fadeOut(1000);
  $("#textoEspera3").delay(14000).fadeIn(1000);
  $("#textoEspera3").delay(3000).fadeOut(1000);
  $("#textoEspera4").delay(19000).fadeIn(1000);
  
  var FilesUp = jQuery('#archivo')[0].files;
  data.append('archivo',FilesUp[0]);
  data.append("nombreFinca",$("#finca").val()); 
  data.append('accion',"cargarArchivo");
  $.ajax({
      type: "POST", 
      data:data,
      url: "modulos/subida_csv/controlador.php",
      cache: false,
      contentType: false,
      processData: false, 
      success: function( data )  
      {

        datos = JSON.parse(data); 
        
        armarConsolidadoDia(datos);
        $("#archivo").val("");
      
      }
  });	
	
}

function armarConsolidadoDia(datos){

  let fincas = datos["fincas"];
  let consolidadosDia = datos["consolidadosDia"];
  let tiempos = datos["arrayTiempo"];
  let html = "";
  let totalhectaresas = 0;

  /*html +='<div class="row">';
  html +='<div class="col-md-6 col-md-offset-3 text-right">';
  html +='<buttom id="exportar" class="btn btn-outline-primary" style="color: #007bff;border-color: #007bff;">PDF <i class="glyphicon glyphicon-export"></i></buttom>';
  html +='</div>';
  html +='</div>';*/

  $.each(fincas,function(index, finca) {

    html +='<div class="row">';
    html +='<div class="col-md-6 col-md-offset-3">';
    html +='<div class="panel panel-primary">';
    html +='<div class="panel-heading text-center">';
    html +='<h3 class="panel-title">'+finca.nombre_finca+'</h3>';
    html +='</div>';

    $.each(consolidadosDia,function(index2, dia) {

      if(dia.id_archivo == finca.id){

        html +='<div class="panel-body">';
        html +='<div>';
        html +='<b>Fecha fumigación:</b> '+dia.fecha1;
        html +='</div>';
        html +='<div>';
        html +='<b>Cantidad hectáreas:</b> '+addCommas(dia.cant_hectareas);
        html +='</div>';
        html +='</div>';

        totalhectaresas += parseFloat(dia.cant_hectareas);
      }
    
    });

    html +='<div class="panel-footer text-left" style="background-color: #ffffff!important;">';
    html +='<b>Fecha y hora subida archivo:</b> ' +finca.fecha_subida+" "+finca.hora_subida;
    html +='</div>';

    html +='</div>';
    html +='</div>';
    html +='</div>';
  });

  html +='<div class="row">';
  html +='<div class="col-md-6 col-md-offset-3">';
  html +='<div class="panel panel-primary">';
  html +='<div class="panel-heading text-center" style="background-color: #f8f9fa!important;color:#333">';
  html +='<h3 class="panel-title">Total</h3>';
  html +='</div>';

  html +='<div class="panel-body">';
  html +='<div>';
  html +='<b>Total hectáreas:</b> '+addCommas(totalhectaresas);
  html +='</div>';
  html +='<div>';
  html +='<b>Semanas de fumigación:</b> '+addCommas(tiempos.semanas);
  html +='</div>';
  html +='<div>';
  html +='<b>Meses de fumigación:</b> '+addCommas(tiempos.meses);
  html +='</div>';
  html +='<div>';
  html +='<b>Años de fumigación:</b> '+addCommas(tiempos.anios);
  html +='</div>';
  html +='</div>';

  html +='</div>';
  html +='</div>';
  html +='</div>';

  $("#consolidados").html(html);
  $(".load").fadeOut("1000");
  $("#finca").val("");
  visualizarGraficas(datos);

}

function visualizarGraficas(datos){
  google.charts.load('current', {'packages':['bar']});
  google.charts.setOnLoadCallback(drawChart);

  function drawChart() {

    let fincas = datos["fincas"];
    let consolidadosDia = datos["consolidadosDia"];
    let tiempos = datos["arrayTiempo"];
    let fincasCantidades = [];

    fincasCantidades.push(['Elementos', 'Hectareas', { role: 'style' }]);

    $.each(fincas,function(index, finca) {
      $.each(consolidadosDia,function(index2, dia) {
        if(dia.id_archivo == finca.id){
          fincasCantidades.push([finca.nombre_finca, dia.cant_hectareas, '#b87333']);
        }
      });
    });

    console.log(fincasCantidades);

    var data = google.visualization.arrayToDataTable(fincasCantidades);

    var options = {
      chart: {
        title: 'AgroTronic',
        subtitle: '',
      }
    };

    var chart_div = document.getElementById('columnchart_material');
    var chart = new google.charts.Bar(chart_div);

    chart.draw(data, google.charts.Bar.convertOptions(options));
  }

  $("#grafica").fadeIn(1000);
}

function addCommas(nStr)
{
  nStr += '';
  x = nStr.split('.');
  x1 = x[0];
  x2 = x.length > 1 ? '.' + x[1] : '';
  var rgx = /(\d+)(\d{3})/;
  while (rgx.test(x1)) {
    x1 = x1.replace(rgx, '$1' + ',' + '$2');
  }
  return x1 + x2;
}