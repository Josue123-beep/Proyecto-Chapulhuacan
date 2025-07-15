<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
include_once __DIR__ . "/../Server/DataBase/Querys/DashboardQuerys.php";

class DashboardController {
    public function obtenerDatos() {
        $querys = new DashboardQuerys();

        $descargasMensuales = $querys->obtenerDescargasPorMesDesdeCargaDoc();

        $datos = [
            'porArea' => $querys->obtenerConteoPorArea(),
            'masDescargados' => $querys->obtenerMasDescargados(), 
            'totalDescargas' => $querys->obtenerTotalDescargas(),
            'descargasMensuales' => $descargasMensuales,
        ];

        // Asegura que el formato sea correcto aunque esté vacío
        if (!is_array($datos['porArea'])) $datos['porArea'] = [];
        if (!is_array($datos['masDescargados'])) $datos['masDescargados'] = [];
        if (!is_numeric($datos['totalDescargas'])) $datos['totalDescargas'] = 0;

        echo json_encode($datos);
    }
}

$controller = new DashboardController();
$controller->obtenerDatos();
?>