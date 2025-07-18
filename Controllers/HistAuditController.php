<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
require_once __DIR__ . '/../Server/DataBase/Querys/HistAuditQuerys.php';

class HistAuditController
{
    public function obtenerHistorial($area = '', $fecha = '')
    {
        $querys = new HistAuditQuerys();
        $registros = $querys->obtenerHistorial($area, $fecha);

        if ($registros === false) {
            echo json_encode(['registros' => [], 'error' => 'Error de base de datos']);
            return;
        }

        echo json_encode(['registros' => $registros]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $area = $_POST['area'] ?? '';
    $fecha = $_POST['fecha'] ?? '';
    $controller = new HistAuditController();
    $controller->obtenerHistorial($area, $fecha);
} else {
    echo json_encode(['registros' => [], 'error' => 'Método no permitido']);
}
?>