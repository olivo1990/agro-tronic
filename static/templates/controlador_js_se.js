$(document).ready(function() {
	
	//Funcion del boton Agregar..!
	$("#btn_crear").click(function() {
		$('.modal').modal('show'); // Abre el modal donde se crea el formulario.
	})

	//Funcion para cuando se haga el submit del form.
	$("#frm_ejemplo").submit(function(event) {
	 	event.preventDefault();
	 	BootstrapDialog.confirm("¿Esta seguro de enviar el formulario?", function(result){
		   if(result) {
			   CRUD_ejemplo();
			  }
		});
    });
});

function CRUD_ejemplo()
{
	Notificacion("Crud ejemplo","success"); // success, info, warning, error
	$('.modal').modal('hide');
}