jQuery.crearVentana = function( titulo,info ){
 
	$("<div>", {
		class: 'overlay',
		id: 'overlay'
	}).appendTo('body').fadeIn("fast");
	
	if(titulo!=""){
			
		$("<div>", {
			class: 'ventana',
			id: 'ventana'
		}).append( 
			
			$('<div>', {
				class: 'barra-titulos',
				text: titulo 
			}).append(
				$('<span>', {
					class: 'cerrar-ventana',
					id: 'cerrar-ventana'
				}).click(function(){
					$("#overlay").remove();
					$("#ventana").remove();
					$("body").css("overflow","auto");
					
				})
				
			 ),
			
			$('<div>', {
				class: 'contenido' 
			}).append(info)
	
		).appendTo('body').fadeIn("fast");
		
		$('#ventana').draggable({
			handle:'.barra-titulos'
		});
	
   }else{
	   $("<div>", {
			class: 'ventana',
			id: 'ventana'
		}).append(
			$('<span>', {
				class: 'cerrar-ventana',
				id: 'cerrar-ventana'
			}).click(function(){
				$("#overlay").remove();
				$("#ventana").remove();
			}),
			
			$('<div>', {
				class: 'contenido' 
			}).append(info)
			 
		).appendTo('body').fadeIn("fast");
		
		$('#ventana').draggable();
   }
   
   if(window.innerHeight<750){
		$("#ventana").css("position","absolute");
		$("html, body").animate({ scrollTop: 0 }, 200);
	}else{
		$("body").css("overflow","hidden");
		
	}   
   //$("html, body").animate({ scrollTop: 0 }, 200);
   
   //$('html, body').animate({ scrollTop: $('#ventana') }, 600);
   
}

function onDrag(e){
	var d = e.data;
	if (d.left < 0){d.left = 0}
	if (d.top < 0){d.top = 0}
	if (d.left + $(d.target).outerWidth() > $(d.parent).width()){
		d.left = $(d.parent).width() - $(d.target).outerWidth();
	}
	if (d.top + $(d.target).outerHeight() > $(d.parent).height()){
		d.top = $(d.parent).height() - $(d.target).outerHeight();
	}
}

function cerrar_ventana() {
	
	$("#overlay").remove();
	$("#ventana").remove();
	$("body").css("overflow","auto");
	
}

$(document).keyup(function(event){
	if(event.which==27)
	{
		cerrar_ventana();
	}
		

});

$( document ).ready(function() {
	$('body').on('click', '#cl_bt', function (){
		cerrar_ventana();
	});		
});
