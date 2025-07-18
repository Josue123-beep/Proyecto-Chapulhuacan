<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

header("Content-Type: application/json");
require_once __DIR__ . '/../Server/DataBase/Querys/HistAuditQuerys.php';

class HistAuditController
{
    // Registrar acción en historial
    public function registrarAccion()
    {
        // Toma los datos del usuario desde la sesión
        $data = [
            'id_usuario'        => $_SESSION['id_usuario'] ?? null,
            'usuario_nombre'    => $_SESSION['usuario_nombre'] ?? '',
            'usuario_apellidos' => $_SESSION['usuario_apellidos'] ?? '',
            'accion'            => $_POST['accion'] ?? '',
            'archivo_id'        => $_POST['archivo_id'] ?? null,
            'archivo_nombre'    => $_POST['archivo_nombre'] ?? '',
            'archivo_ruta'      => $_POST['archivo_ruta'] ?? '',
            'area'              => $_SESSION['area'] ?? '',
            'descripcion'       => $_POST['descripcion'] ?? ''
        ];
        $querys = new HistAuditQuerys();
        $ok = $querys->registrarAccion($data);
        echo json_encode(['success' => $ok]);
    }

    // Obtener historial
    public function obtenerHistorial()
    {
        $area  = $_POST['area']  ?? '';
        $fecha = $_POST['fecha'] ?? '';
        $querys = new HistAuditQuerys();
        $registros = $querys->obtenerHistorial($area, $fecha);
        echo json_encode(['registros' => $registros]);
    }

    // NUEVO: Buscar la ruta de un archivo por nombre
    public function buscarRutaArchivo()
    {
        $nombreArchivo = $_POST['archivo_nombre'] ?? '';
        if (!$nombreArchivo) {
            echo json_encode(['error' => 'Nombre de archivo requerido']);
            return;
        }
        $ruta = HistAuditQuerys::buscarArchivoEnUploads($nombreArchivo);
        if ($ruta) {
            echo json_encode(['ruta' => $ruta]);
        } else {
            echo json_encode(['error' => 'Archivo no encontrado']);
        }
    }
}

// Ruteo simple
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['buscar_ruta']) && $_POST['buscar_ruta'] == '1') {
        // Busca la ruta del archivo por nombre
        $controller = new HistAuditController();
        $controller->buscarRutaArchivo();
    } else if (isset($_POST['accion']) && !empty($_POST['accion']) && $_POST['accion'] !== 'Mostrar') {
        // Registrar acción
        $controller = new HistAuditController();
        $controller->registrarAccion();
    } else {
        // Mostrar historial
        $controller = new HistAuditController();
        $controller->obtenerHistorial();
    }
} else {
    echo json_encode(['error' => 'Método no permitido']);
}
?>