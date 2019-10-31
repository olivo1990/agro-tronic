<?php
ini_set('memory_limit', '3024M');
set_time_limit(3600);

require("../../config/mainModel.php");

class CargarArchivo {
	
    //Espacio Para declara las funciones que retornan los datos de la BD.
	private $BD;
	
	public function __construct(){
		$this->BD = new BD();
		$this->BD->conectar();

		$this->degtorad = 0.01745329;
		$this->radtodeg = 57.29577951;

		$this->bloque = 200;
		$this->cont = 0;
		$this->hectarea = 0;
		$this->minMetros = 70;
		$this->maxMetros = 120;
		$this->arrayFechaHectarea = array();
		$this->arrayConsolidadoHectareaDias = array();
	}

	public function cargarArchivo($archivo,$nombreFinca){


		//FROM_UNIXTIME(unix_timestamp)
		$error = 0;
		$nombreColumnas = "";
		$nombreArchivo = $archivo['name'];
		$filas = 0;
		$idArchivo = 0;
		$bloqueTemp = $this->bloque;

		/*$sql = "SELECT * FROM subida_archivos WHERE nombre = '$nombreArchivo'";
		$res = $this->BD->devolver_array($sql);

		if(count($res) == 0){*/

			$this->BD->consultar("BEGIN");

			$responseCreateTabala = $this->crearTabla($archivo);

			$columna1 = $responseCreateTabala["columna1"];
			$columna2 = $responseCreateTabala["columna2"];
			$columna3 = $responseCreateTabala["columna3"];
			$columna4 = $responseCreateTabala["columna4"];
			$columna5 = $responseCreateTabala["columna5"];
			$totalFilas = $responseCreateTabala["filas"];

			//$nombrecolumnas = substr($nombrecolumnas, 0, - 1);

			$sqlExisteTabla = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'csv_fumigacion'";

			if(count($this->BD->devolver_array($sqlExisteTabla)) > 0){

				$sql = "INSERT INTO subida_archivos (nombre, fecha_subida, hora_subida, nombre_finca) VALUES ('$nombreArchivo',CURDATE(),CURTIME(), '$nombreFinca')";
				$resInsertAudi = $this->BD->consultar($sql);
				if(!$resInsertAudi){
					$error=1;
				}

				$idArchivo = $this->BD->last_insert_id();

				if (($gestor = fopen($archivo['tmp_name'], "r")) !== FALSE) {

					$sqlInsertarCsv = "INSERT INTO csv_fumigacion (fecha_unix, lat, lon, lat_gps, long_gps, id_archivo) VALUES ";
					$sqlInsertarCsvColumnas = "";

					//for ($i=0; $i < ; $i++) { 
						
						while (($datos = fgetcsv($gestor, 10000,",")) !== FALSE) {
							
							$filas++;

							if($filas > 1){

								$cadena = "";
								for ($le=0; $le < strlen($datos[$columna1]); $le++) {
									$cadena .= $datos[$columna1][$le];
									if(($le + 1) == 10){
										$cadena .= ".";
									}
								}

								/*echo $filas." - ".$bloqueTemp." - ";
								$error=1;*/

								if($filas < $bloqueTemp){

									$datos[$columna1] = $cadena;
									
									$sqlInsertarCsvColumnas .="('{$datos[$columna1]}', '{$datos[$columna2]}', '{$datos[$columna3]}', '{$datos[$columna4]}', '{$datos[$columna5]}',$idArchivo),";

								}else{
									if($filas == $bloqueTemp && $totalFilas == $bloqueTemp){
										$datos[$columna1] = $cadena;
										
										$sqlInsertarCsvColumnas .="('{$datos[$columna1]}', '{$datos[$columna2]}', '{$datos[$columna3]}', '{$datos[$columna4]}', '{$datos[$columna5]}',$idArchivo),";
									}	

									if($sqlInsertarCsvColumnas != ""){

										$sqlInsertarCsvColumnas = substr($sqlInsertarCsvColumnas, 0, - 1);
										$sqlInsertarCsv .= $sqlInsertarCsvColumnas;

										$resInsert = $this->BD->consultar($sqlInsertarCsv);
										if(!$resInsert){
											$error=1;
										}
									}

									$bloqueTemp += $this->bloque;

									if($bloqueTemp >= $totalFilas){
										$bloqueTemp = $totalFilas;
									}	
									
									//$filas--;
									$sqlInsertarCsv = "INSERT INTO csv_fumigacion (fecha_unix, lat, lon, lat_gps, long_gps, id_archivo) VALUES ";
									$sqlInsertarCsvColumnas = "";
								}
									
							}
						}

						# code...
					//}

					/*if($sqlInsertarCsvColumnas != ""){

						$sqlInsertarCsvColumnas = substr($sqlInsertarCsvColumnas, 0, - 1);
						$sqlInsertarCsv .= $sqlInsertarCsvColumnas;
						$resInsert = $this->BD->consultar($sqlInsertarCsv);
						if(!$resInsert){
							$error=1;
						}
					}*/
					
					fclose($gestor);
				}
				
			}
			


			if(!$error)
			{
				$this->BD->consultar("COMMIT");

				$this->devolverConsolidados();

				$sqlTru = "TRUNCATE consolidado_fumigacion_temp";
				$resTru = $this->BD->consultar($sqlTru);
				
				$sql = "SELECT id_archivo, COUNT(id) AS cant FROM csv_fumigacion GROUP BY id_archivo";
				$res = $this->BD->devolver_array($sql);

				for ($i=0; $i < count($res); $i++) {
					$this->cont = 0;
					$this->hectarea = 0;
					$this->reporteFumigaciones($res[$i]["id_archivo"], $res[$i]["cant"], 0);
				}

				$bloqueTemp2 = $this->bloque;

				$sqlTemp = "INSERT INTO consolidado_fumigacion_temp (fecha1, fecha2, id1, id2, mt, tiene_hectarea, nro_hectarea, cont, total_registros, id_archivo) VALUES ";
				$inserts = "";

				if(count($this->arrayFechaHectarea) > 0){
					for ($i=0; $i < count($this->arrayFechaHectarea); $i++) {

						if($i < $bloqueTemp2){
							$inserts .= "('{$this->arrayFechaHectarea[$i]["fecha"]}', '{$this->arrayFechaHectarea[$i]["fecha2"]}' , {$this->arrayFechaHectarea[$i]["id1"]}, {$this->arrayFechaHectarea[$i]["id2"]}, {$this->arrayFechaHectarea[$i]["mt"]}, {$this->arrayFechaHectarea[$i]["tieneHectarea"]}, {$this->arrayFechaHectarea[$i]["nroHectarea"]}, {$this->arrayFechaHectarea[$i]["cont"]}, {$this->arrayFechaHectarea[$i]["totalRegistros"]}, {$this->arrayFechaHectarea[$i]["idArchivo"]}),";
						}else{

							if($i == $bloqueTemp2 && count($this->arrayFechaHectarea) == $bloqueTemp2){
								$datos[$columna1] = $cadena;
								
								$sqlInsertarCsvColumnas .="('{$datos[$columna1]}', '{$datos[$columna2]}', '{$datos[$columna3]}', '{$datos[$columna4]}', '{$datos[$columna5]}',$idArchivo),";
							}

							if($inserts != ""){
								$inserts = substr($inserts, 0, - 1);
								$sqlTemp .=$inserts;
								$resInsertTemp = $this->BD->consultar($sqlTemp);
							}

							$bloqueTemp2 += $this->bloque;

							if($bloqueTemp2 >= count($this->arrayFechaHectarea)){
								$bloqueTemp2 = count($this->arrayFechaHectarea);
							}
							//$i--;
							$sqlTemp = "INSERT INTO consolidado_fumigacion_temp (fecha1, fecha2, id1, id2, mt, tiene_hectarea, nro_hectarea, cont, total_registros, id_archivo) VALUES ";
							$inserts = "";
						}
						
					}

					return $this->devolverConsolidados();

				}else{
					return array();
				}

			}
			else
			{
				$this->BD->consultar("ROLLBACK");
			}
		
		/*}else{
			return array("error"=>1, "mensaje" => "El archivo que intenta cargar ya existe!");
		}*/

	}

	public function reporteFumigaciones($idArchivo, $totalRegistros, $idFin){

		$sql = "SELECT FROM_UNIXTIME(fecha_unix) AS fecha_hora, DATE(FROM_UNIXTIME(fecha_unix)) AS fecha, lat, lon, id, id_archivo FROM csv_fumigacion WHERE id > $idFin AND lat != '' AND lon != ''";
		if($idArchivo > 0){
			$sql .=" AND id_archivo = $idArchivo";
		}
		$sql .=" ORDER BY fecha_hora LIMIT 1";
		$resPrimera = $this->BD->consultar($sql);

		if($this->BD->numreg($resPrimera) > 0){

			$lat1 = $resPrimera->fields["lat"];
			$long1 = $resPrimera->fields["lon"];
			$id1 = $resPrimera->fields["id"];
			$idArchivo = $resPrimera->fields["id_archivo"];
			$fecha = $resPrimera->fields["fecha"];

			$sql = "SELECT FROM_UNIXTIME(fecha_unix) AS fecha_hora, DATE(FROM_UNIXTIME(fecha_unix)) AS fecha, lat, lon, id, id_archivo FROM csv_fumigacion WHERE id > $id1 AND lat != '' AND lon != ''";
			if($idArchivo > 0){
				$sql .=" AND id_archivo = $idArchivo";
			}
			$sql .=" ORDER BY fecha_hora LIMIT 1";

			$resSegundo = $this->BD->consultar($sql);

			$lat2 = $resSegundo->fields["lat"];
			$long2 = $resSegundo->fields["lon"];
			$id2 = $resSegundo->fields["id"];
			$fecha2 = $resSegundo->fields["fecha"];

			$this->calcularDistanciaEntrePuntos($lat1,$long1,$lat2,$long2,$id1,$id2,$idArchivo,$totalRegistros,$fecha, $fecha2);
		}

	}

	public function calcularDistanciaEntrePuntos($lat1, $long1, $lat2, $long2, $id1, $id2, $idArchivo, $totalRegistros, $fecha, $fecha2){

		$this->cont++;
		$km = 0;

		$dlong = ($long1 - $long2);
		$dvalue = (sin($lat1 * $this->degtorad) * sin($lat2 * $this->degtorad)) + (cos($lat1 * $this->degtorad) * cos($lat2 * $this->degtorad) * cos($dlong * $this->degtorad));
		$dd = acos($dvalue) * $this->radtodeg;
		$km = ($dd * 111.302);
		$km = ($km * 100)/100;
		$mt = round($km * 1000, 2);

		//echo $mt." - ".$this->minMetros." - ".$this->maxMetros." | ";

		//echo $this->cont." - ".$totalRegistros." | ";

		$this->arrayFechaHectarea[] = array("idArchivo" => $idArchivo, "fecha" => $fecha, "fecha2" => $fecha2, "id1" => $id1, "id2" => $id2, "mt" => $mt, "tieneHectarea" => 0, "nroHectarea" => $this->hectarea, "cont" => $this->cont, "totalRegistros" => $totalRegistros);

		if($mt >= $this->minMetros && $mt <= $this->maxMetros){

			for ($i=0; $i < count($this->arrayFechaHectarea); $i++) {
				if($this->arrayFechaHectarea[$i]["id1"] == $id1){
					$this->arrayFechaHectarea[$i]["nroHectarea"] += 1;
					$this->arrayFechaHectarea[$i]["tieneHectarea"] = 1;
				}
			}

			$this->hectarea++;
			
			$this->reporteFumigaciones($idArchivo, $totalRegistros, $id2);
		}else if($this->cont <= $totalRegistros){

			$sql = "SELECT FROM_UNIXTIME(fecha_unix) AS fecha_hora, DATE(FROM_UNIXTIME(fecha_unix)) AS fecha, lat, lon, id, id_archivo FROM csv_fumigacion WHERE id > $id2 AND lat != '' AND lon != '' ";
			
			if($idArchivo > 0){
				$sql .=" AND id_archivo = $idArchivo";
			}
			$sql .=" ORDER BY fecha_hora LIMIT 1";

			$resSegundo = $this->BD->consultar($sql);

			if($this->BD->numreg($resSegundo) > 0){

				$lat2 = $resSegundo->fields["lat"];
				$long2 = $resSegundo->fields["lon"];
				$id2 = $resSegundo->fields["id"];
				$fecha2 = $resSegundo->fields["fecha"];

				$this->calcularDistanciaEntrePuntos($lat1, $long1, $lat2, $long2, $id1, $id2, $idArchivo, $totalRegistros, $fecha, $fecha2);

			}else{
				for ($i=0; $i < count($this->arrayFechaHectarea); $i++) {
					if($this->arrayFechaHectarea[$i]["id1"] == $id1){
						$this->arrayFechaHectarea[$i]["nroHectarea"] += 0.5;
						$this->arrayFechaHectarea[$i]["tieneHectarea"] = 0;
					}
				}
			}
		}/*else{
			$this->reporteFumigaciones($idArchivo, $totalRegistros, $id2);
		}*/
	}

	public function devolverConsolidados(){

		$arrayTiempo = array();

		$sql = "SELECT UPPER(nombre_finca) AS nombre_finca, fecha_subida, hora_subida, id FROM subida_archivos";
		$resFincas = $this->BD->devolver_array($sql);

		$sqlConsol = "SELECT MAX(nro_hectarea) AS cant_hectareas,id_archivo, fecha1 FROM `consolidado_fumigacion_temp` GROUP BY id_archivo, fecha1";
		$resConsolDias = $this->BD->devolver_array($sqlConsol);

		$sql = "SELECT fecha1 FROM `consolidado_fumigacion_temp` ORDER BY fecha1 ASC LIMIT 1";
		$resPrimera = $this->BD->consultar($sql);

		if($this->BD->numreg($resPrimera) > 0){
			$fecha1 = $resPrimera->fields["fecha1"];

			$sql = "SELECT fecha1 FROM `consolidado_fumigacion_temp` ORDER BY fecha1 DESC LIMIT 1";
			$resSegundo = $this->BD->consultar($sql);
			if($this->BD->numreg($resSegundo) > 0){
				$fecha2 = $resSegundo->fields["fecha1"];
			}else{
				$fecha2 = $fecha1;
			}

			$arrayTiempo = $this->diferenciaDias($fecha1, $fecha2);

		}

		return array("fincas"=>$resFincas, "consolidadosDia" => $resConsolDias, "arrayTiempo" => $arrayTiempo);
	}

	private function diferenciaDias($fecha1, $fecha2){
		$date1 = new DateTime($fecha1);
		$date2 = new DateTime($fecha2);
		$diff = $date1->diff($date2);
		$semanas = round($diff->days/7, 1);
		$meses = round($diff->days/30, 1);
		$anios = round($diff->days/365, 1);

		return array("dias" => $diff->days, "semanas" => $semanas, "meses" => $meses, "anios" => $anios);
	}
	
	private function crearTabla($archivo){
		$filas = 0;
		$nombreColumnas = "";
		$columna1 = "";
		$columna2 = "";
		$columna3 = "";
		$columna4 = "";
		$columna5 = "";

		if (($gestor = fopen($archivo['tmp_name'], "r")) !== FALSE) {

			while (($datos = fgetcsv($gestor, 10000,",")) !== FALSE) {

				$filas++;
				if($filas == 1){
					
					for ($i=0; $i < count($datos); $i++) {

						if($datos[$i] == '1-timer'){
							$columna1 = $i;
						}

						if($datos[$i] == '17-f_lat'){
							$columna2 = $i;
						}

						if($datos[$i] == '18-f_lon'){
							$columna3 = $i;
						}

						if($datos[$i] == '28-gps_lat'){
							$columna4= $i;
						}

						if($datos[$i] == '29-gps_lon'){
							$columna5 = $i;
						}
					}				

				}
			}

			fclose($gestor);
		}

		return array("columna1"=>$columna1,"columna2"=>$columna2,"columna3"=>$columna3,"columna4"=>$columna4,"columna5"=>$columna5, "filas"=>$filas);
	}
	
}// Fin clase
?>