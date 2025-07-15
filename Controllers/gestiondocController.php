<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../Server/DataBase/Conexion.php';
require_once __DIR__ . '/../Server/DataBase/Querys/gestiondocQuerys.php';

$conn = Conexion::obtenerConexion();
$querys = new GestionDocQuerys($conn);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $filtros = [
        'titulo'    => $_GET['titulo']    ?? '',
        'tipo'      => $_GET['tipo']      ?? '',
        'area'      => $_GET['area']      ?? '',
        'Categoria' => $_GET['Categoria'] ?? '',
        'fecha'     => $_GET['fecha']     ?? ''
    ];
    $filtros = array_filter($filtros, fn($v) => $v !== '');
    $docs = $querys->getAllDocuments($filtros);
    echo json_encode($docs, JSON_UNESCAPED_UNICODE);
    exit;
}

$input = $_POST ?: json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? null;

if ($action === 'edit') {
    $id          = $input['id'];
    $titulo      = $input['titulo'];
    $tipo        = $input['tipo'];
    $area        = $input['area'];
    $Categoria   = $input['Categoria'];
    $fecha       = $input['fecha'];
    $descripcion = $input['descripcion'];
    $subcarpeta  = $input['subcarpeta'] ?? ($_POST['subcarpeta'] ?? null);
    $subcarpetaPersonalizada = $input['subcarpetaPersonalizada'] ?? ($_POST['subcarpetaPersonalizada'] ?? null);
    $archivo     = $_FILES['archivo'] ?? null;

    $result = $querys->editDocument($id, $titulo, $tipo, $area, $Categoria, $fecha, $descripcion, $subcarpeta, $subcarpetaPersonalizada, $archivo);
    echo json_encode($result, JSON_UNESCAPED_UNICODE); exit;
}

if ($action === 'delete') {
    $id = $input['id'];
    $result = $querys->deleteDocument($id);
    echo json_encode($result, JSON_UNESCAPED_UNICODE); exit;
}

echo json_encode(['success' => false, 'message' => 'Acción no válida'], JSON_UNESCAPED_UNICODE); exit;
?>