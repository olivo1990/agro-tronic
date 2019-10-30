<?php
/// comentario
session_start();
require_once 'jso.php';
ob_start();
class Template {
  public $modulo;
  public $template = "template.html";
  public $VistaModulo = 'vista.html';
  private $arrayXajaxDefault = array("xajax_cargando()");
  private $arrayHead = array();
  private $arrayBody = array();
  private $arrayFooter = array();
  private $objxAjax;

  public function __construct() {
  }
  public function AgregarCSS($file) {
    $templateCSS = "<link rel='stylesheet' type='text/css' href='$file'/>";
    $this->AgregarHead($templateCSS);
  }

  public function create_hash($input) {
    $hash = hash('sha384', $input, true);
    $hash_base64 = base64_encode($hash);
    return "sha384-$hash_base64";
  }

  private function create_integrity($file, $var) {

    $nom_js = "";
    $nom_file = parse_url($file, PHP_URL_PATH);

    if (strpos($nom_file, '/') !== FALSE) {
      $carpeta = $nom_file;
      $carpeta = dirname($carpeta);

      $carpeta = explode('/', $carpeta);
      $carpeta = array_pop($carpeta);

      $nom_js = explode('/', $nom_file);
      $nom_js = array_pop($nom_js);
    }

    if ($this->modulo == "admin_promociones_noticias" && $nom_js == "controlador.js") {

      $archivojs = $nom_js;

      $ruta_ab = dirname(__FILE__);
      $ruta_ab = dirname($ruta_ab);
      $ruta_ab = $ruta_ab . "/modulos/" . $this->modulo . "/" . $archivojs;

      $read_js = "";

      $archivo = fopen($ruta_ab, 'r');

      while ($linea = fgets($archivo)) {
        $read_js .= $linea;
      }

      fclose($archivo);

      return "integrity='" . $this->create_hash($read_js) . "'";
    }
  }

  public function AgregarJS($file) {
    $var = date("YmdHis");

    $integri = $this->create_integrity($file, $var);

    $templateCSS = "<script type='text/javascript' src='$file?$var' " . $integri . "></script>";
    $this->AgregarFooter($templateCSS);
  }

  public function AgregarJShead($file) {
    $var = date("YmdHis");

    $integri = $this->create_integrity($file, $var);

    $templateCSS = "<script type='text/javascript' src='$file?$var' " . $integri . "></script>";
    $this->AgregarHead($templateCSS);
  }

  private function AgregarHead($html) {
    $this->arrayHead[] = $html;
  }
  private function AgregarFooter($html) {
    $this->arrayFooter[] = $html;
  }

  public function getFile($file) {
    $link = @fopen($file, 'r');
    if ($link) {
      $size = filesize($file);
      if ($size == 0) {
        $size = 1;
      }

      $data = fread($link, $size);
      fclose($link);
    }
    return $data;
  }
  public function putFile($file, $data, $method = 'a+') {
    $link = @fopen($file, $method);
    if ($link) {
      $data = fputs($link, $data);
      fclose($link);
    }
    @chmod($file, 0777);
  }
  public function makeDir($dir) {
    if (!file_exists($dir)) {
      if (@mkdir($dir) !== false) {
      } else {
        die("Problema con permisos en las  carpetas, verifique");
      }
      @chmod($dir, 0777);
    }
  }

  public function cargarTemplate() {
    $dataVista = "";
    #trae template
    $fileTemplate = TEMPLATE_PATH . $this->template;
    $dataTemplate = $this->getFile($fileTemplate);

    #trae vista del modulo
    $fileVista = MODULE_PATH . $this->modulo . "/" . $this->VistaModulo;
    if (file_exists($fileVista)) {
      $dataVista = $this->getFile($fileVista);
    } else {
      $fileVista = "404.html";
      if (file_exists($fileVista)) {
        $this->modulo = 'Error';
        $dataVista = $this->getFile($fileVista);
      }
    }

    if ($dataTemplate == '') {
      $dataTemplate = $dataVista;
    }

    $ControllerJS = MODULE_PATH . $this->modulo . "/controlador.js";
    $this->AgregarJS(STATIC_PATH . "js/application.js");
    if (file_exists($ControllerJS)) {

      $this->AgregarJS($ControllerJS);
    }
    $titulo = strtoupper($this->modulo);
    $dataTemplate = str_replace("</titulo>", $titulo . "</titulo>", $dataTemplate);
    $dataTemplate = str_replace("</modulo>", $dataVista . "</modulo>", $dataTemplate);
    $dataTemplate = str_replace("</head>", implode("\n", $this->arrayHead) . "</head>", $dataTemplate);
    $dataTemplate = str_replace("</body>", implode("\n", $this->arrayFooter) . "</body>", $dataTemplate);


      //Obtenemos el UserAgent
      $useragent = $_SERVER['HTTP_USER_AGENT'];
      //Creamos una variable para detectar los móviles
      $ismobile = preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|zh-cn|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4));

      if ($ismobile) {

        $dataTemplate = str_replace("</nousuario>", "Ferney" . " " . "Lopez Murillo" . "</nousuario>", $dataTemplate);

      }

    echo $dataTemplate;
  }
}
?>