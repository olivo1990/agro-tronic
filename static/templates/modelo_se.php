<?php
require("../../config/mainModel.php");
class [[modulo]] extends mainModel {
/*
  METODOS DE LA BD.
  $this->BD->consultar($query); // Ejecuta la consulta y devuelve string.!
  $this->BD->devolver_array($query); // Ejecuta la consulta y devuelve array asociativo.!
  $this->BD->consultar("BEGIN"); // Antes de transacciones.!
  $this->BD->consultar("COMMIT"); // Commit para guardar datos.!
  $this->BD->consultar("ROLLBACK"); // Devolver datos si hay error.!
  $this->BD->numreg($query); // Devuelve el numero de registros de la consulta.!
*/
	public function __construct()
    {
      $BD=new BD();
      $this->BD = $BD;
      $this->BD->conectar();
    }
    public function __destruct()
    {
      $this->BD->desconectar();
    }
   //Espacio Para declara las funciones que retornan los datos de la DB.
   
  
}// Fin clase
?>