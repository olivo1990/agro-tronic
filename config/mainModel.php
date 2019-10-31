<?php
ini_set('memory_limit', '3024M');
set_time_limit(900);

include 'adodb/adodb.inc.php';

class BD {
  var $servidor;
  var $usuario;
  var $password;
  var $dtbs;
  var $conexion;
  var $respuesta;
  var $filas;
  var $db;
  var $res;

  ////*PRU4 CONSTRUCTOR DE LA CLASE BASE DE DATOS QUE INICIALIZA LAS VARIABLES DE LA CLASE*/
  FUNCTION BD () {
    $bd = "fumigaciones";
    $this->servidor = "127.0.0.1:3306";
    $this->usuario = "root";
    $this->password = "sasa";
    $this->dtbs = $bd;

    /*$bd = "vjr4j5r60d50rngk";
    $this->servidor = "enqhzd10cxh7hv2e.cbetxkdyhwsb.us-east-1.rds.amazonaws.com:3306";
    $this->usuario = "ac0olzl2o5bap52q";
    $this->password = "yh8fxuvtmaacn42k";
    $this->dtbs = $bd;*/
  }

  /*FUNCION QUE CUENTA EL NUMERO DE REGISTROS DE UNA CONSULTA DADA*/
  FUNCTION numreg ($consulta) {
    RETURN $consulta->RecordCount();
  }

  function rows_affect () {
    $rows = $this->db->Affected_Rows();
    return $rows;
  }

  /*FUNCION CONECTAR, QUE REALIZA LA CONEXION A LA BASE DE DATOS Y LA SELECION DE LA MISMA */
  FUNCTION conectar () {
    $this->db = NewADOConnection("mysqli");
    $this->conexion = $this->db->Connect($this->servidor, $this->usuario, $this->password, $this->dtbs) or die("Unable to connect!");

    $this->setCharset();

    RETURN $this->conexion;
  }

  /*FUNCION CONSULTAR QUE PERMITE REALIZAR LAS CONSULTAS A LA BASE DE DATOS Y RETORNA LA RESPUESTA*/
  FUNCTION consultar ($sql) {
    $this->res = $this->db->Execute($sql);
    if (!$this->res) {
      echo $sql;
    }

    RETURN $this->res;
  }

  public function last_insert_id () {
    return $this->db->Insert_ID();
  }

  public function setCharset ($parameter = "utf8") {
    return $this->db->setCharset($parameter);
  }

  function devolver_array ($sql) {
    $this->db->SetFetchMode(ADODB_FETCH_ASSOC);
    $this->res = $this->db->GetAll($sql);
    if (!$this->res) {
      if (strlen($this->msn_error()) > 0) {
      }
    }

    RETURN $this->res;
  }

  function msn_error () {
    RETURN $this->db->ErrorMsg();
  }

  /*FUNCION QUE DEVUELVE EL ID DE LA SECUENCIA*/
  FUNCTION id ($sec) {
    RETURN $this->db->GenID($sec);
  }
  
  /*FUNCION DESCONECTAR QUE CIERRA LA CONEXION CON LA BASE DE DATOS*/
  FUNCTION desconectar () {
    $this->db->Close();
  }

  FUNCTION proof ($consulta) {
    RETURN $consulta->GetAssoc();
  }

  FUNCTION numupdate () {
    RETURN $this->db->Affected_Rows();
  }
}

?>