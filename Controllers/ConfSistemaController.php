<?php
include __DIR__ . "/../Server/DataBase/Querys/ConfSistemaQuerys.php";

class ConfSistemaController {
    private ConfSistemaQuerys $querys;

    public function __construct() {
        $this->querys = new ConfSistemaQuerys();
    }
    
    // Maneja petición GET para obtener configuración
    public function obtenerConfiguracion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        $config = $this->querys->obtenerConfiguracion();
        echo json_encode(['configuracion' => $config]);
    }

    // Maneja petición POST para guardar configuración
    public function guardarConfiguracion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        // Leer JSON enviado por fetch
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos inválidos']);
            return;
        }

        // Para simplificar, se guarda nombre del archivo logo como texto
        $data = [
            'username' => $input['username'] ?? '',
            'role' => $input['role'] ?? '',
            'doc_type' => $input['docType'] ?? '',
            'active_area' => $input['areaActiva'] ?? '',
            'allowed_formats' => intval($input['formatosPermitidos'] ?? 0),
            'max_file_size' => intval($input['maxFileSize'] ?? 0),
            'custom_text' => $input['customText'] ?? '',
            'logo' => $input['logo'] ?? ''
        ];

        $success = $this->querys->guardarConfiguracion($data);

        if ($success) {
            echo json_encode(['mensaje' => 'Configuración guardada correctamente']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al guardar configuración']);
        }
    }
}

$controller = new ConfSistemaController();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller->obtenerConfiguracion();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->guardarConfiguracion();
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
?>
