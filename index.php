<?php
include_once("config/config.php");
include_once("config/mainTemplate.php");

if(isset($_GET['mod'])){
	if($_GET['mod']){
		$programa = $_GET['mod'];
		#include(MODULE_PATH."$programa/modelo.php");//clase controladora de aplicaciones

		$controlador=ROOT_PATH."/modulos/".$_GET['mod']."/controlador.php";

		#$modelo=ROOT_PATH."/modulos/$menu/".$_GET['modulo']."/modelo.php";
			$vista = ROOT_PATH."/modulos/".$_GET['mod']."/vista.html";
			if(file_exists($vista)){
			#include(MODULE_PATH."$programa/controlador.php");//clase controladora de aplicaciones
		}
		###---VISTA
		$template=new Template();
		$template->modulo=$_GET['mod'];
		$template->cargarTemplate();
	}

}



?>