<?php
include __DIR__ . "/../Server/DataBase/Querys/HistAuditQuerys.php";

class HistAuditController {
    private ?string $area;
    private ?string $fecha;

    public function __construct() {
        $this->area = $_POST['area'] ?? null;
        $this->fecha = $_POST['fecha'] ?? null;
    }

    public function obtenerHistorial() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        $querys = new HistAuditQuerys();
        $registros = $querys->obtenerRegistros($this->area, $this->fecha);
        echo json_encode(['registros' => $registros]);
    }
}

$controller = new HistAuditController();
$controller->obtenerHistorial();
?>