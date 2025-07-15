<?php
include __DIR__ . "/../Server/DataBase/Querys/portaldescargaQuerys.php";

class PortalDescargaController {
    private $tipo;
    private $area;
    private $fecha;

    public function __construct() {
        $this->tipo = $_GET['type'] ?? '';
        $this->area = $_GET['area'] ?? '';
        $this->fecha = $_GET['date'] ?? '';
    }

    public function obtener() {
        $querys = new PortalDescargaQuerys();
        $documentos = $querys->obtenerDocumentos($this->tipo, $this->area, $this->fecha);
        echo json_encode($documentos);
    }
}

$controller = new PortalDescargaController();
$controller->obtener();
?>