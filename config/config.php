<?php
//definicion de rutas y constantes
define('ROOT_PATH',	".");
define('MODULE_PATH',	ROOT_PATH.'/modulos/');
define('STATIC_PATH',	ROOT_PATH.'/static/');
define('TEMPLATE_PATH',	ROOT_PATH.'/template/');
define('CONF_PATH',  ROOT_PATH.'/config.php');
define("CONFIG",ROOT_PATH."/config/");
define ('BASE_URL_PATH', 'http://'.dirname($_SERVER['HTTP_HOST'].''.$_SERVER['SCRIPT_NAME']).'/');
define( 'DB_HOST',         '');
define( 'DB_DATABASE',     '');
define( 'DB_USER',         '');
define( 'DB_PASSWORD',     '');
//require CONF_PATH;
?>