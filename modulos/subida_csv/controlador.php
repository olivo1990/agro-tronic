<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include_once("../../config/mainController.php"); // Incluye el Controlador Principal
include_once("../../modulos/subida_csv/modelo.php");	// Incluye el Modelo.
$controller = new mainController; // Instancia a la clase MainController
$modelo = new CargarArchivo(); // Instancia a la clase del modelo
try // Try, manejo de Errores
{
	$metodo = $_SERVER['REQUEST_METHOD'];
	$tipo_res = "";
	$response = null; 
	// Se manejaran dos tipos JSON y HTML
	// Dependiendo del método de la petición ejecutaremos la acción correspondiente.
	// Por ahora solo POST, todas las llamadas se haran por POST
    $variables = $_POST;
	$accion = $variables['accion'];
	// Dependiendo de la accion se ejecutaran las tareas y se definira el tipo de respuesta.
	switch($accion) {

		case 'devolverConsolidados':
				$tipo_res = 'JSON'; 

				$response = $modelo->devolverConsolidados();
		break;
		
		case 'cargarArchivo':
				$archivo = $_FILES["archivo"];
				$nombreFinca = $variables['nombreFinca'];
				$tipo_res = 'JSON'; 

				$response = $modelo->cargarArchivo($archivo,$nombreFinca );
		break;
		
	}

	// Respuestas del Controlador
	if($tipo_res == "JSON")
	{
	  echo json_encode($response,true); // $response será un array con los datos de nuestra respuesta.
	}
	elseif ($tipo_res == "HTML") {
	  echo $response; // $response será un html con el string de nuestra respuesta.
	}
} // Fin Try
catch (Exception $e) {}
