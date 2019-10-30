<?php
require 'config.php';
require 'mainTemplate.php';
//ini_set('display_errors', false);

register_shutdown_function(function(){
	$error = error_get_last();
		if(null !== $error) {
		// $error ( [type] , [message] , [file] , [line] )
			echo "<div style='font-size:11px'><b>Mensaje: </b>".$error['message']."<br>";
			echo "<b>Archivo: </b>".$error['file']."<br>";
			echo "<b>Linea: </b>".$error['line']."<br></div>";
		}
	});
class mainController
{

	function __construct(){
	}

    function Crear_Archivos_Modulo($modulo,$datos_ejemplo,$descripcion)
	{
			$rutaTemplate = "../../static/templates/";
			//Creacion de la carpeta
			$Template = new Template();
			$carpetaModulo="../".$modulo;

			if(!file_exists($carpetaModulo))
			{
				$Template->makeDir($carpetaModulo);
				if($datos_ejemplo == "N")
				{
					//Creacion de la vista.!
					$vista = $Template->getFile($rutaTemplate."vista_se.html");
					$vista = str_replace("[[modulo]]",strtolower($modulo), $vista);
					$vista = str_replace("[[descripcion_modulo]]",$descripcion, $vista);
					$archivoVista = $carpetaModulo."/vista.html";
					$Template->putFile($archivoVista,$vista);

					//Creacion del Controlador.!
					$controlador = $Template->getFile($rutaTemplate."controlador_se.php");
					$controlador = str_replace("[[modulo]]",strtolower($modulo), $controlador);
					$archivoControlador = $carpetaModulo."/controlador.php";
					$Template->putFile($archivoControlador,$controlador);

					//Creacion del Modelo.!
					$modelo = $Template->getFile($rutaTemplate."modelo_se.php");
					$modelo = str_replace("[[modulo]]",strtolower($modulo), $modelo);
					$archivoModelo = $carpetaModulo."/modelo.php";
					$Template->putFile($archivoModelo,$modelo);

					//Creacion del Js!
					$JS = $Template->getFile($rutaTemplate."controlador_js_se.js");
					$JS = str_replace("[[modulo]]",strtolower($modulo), $JS);
					$archivoJS= $carpetaModulo."/controlador.js";
					$Template->putFile($archivoJS,$JS);

				}
				
				return "S";
			}
			else
			{
				return "R";
			}


	}


}
?>