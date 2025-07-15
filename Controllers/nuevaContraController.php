<?php
header('Content-Type: application/json');
include_once __DIR__ . "/../Server/DataBase/Querys/RecuperarContraQuerys.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['icon'=>'error','text'=>'Método no permitido']);
    exit;
}

$token = $_POST['token'] ?? '';
$nueva = $_POST['nueva'] ?? '';
if (!$token || !$nueva) {
    echo json_encode(['icon'=>'error','text'=>'Datos incompletos']);
    exit;
}

$q = new RecuperarContraQuerys();
$user = $q->validarToken($token);

if (!$user) {
    echo json_encode(['icon'=>'error','text'=>'Token inválido o expirado']);
    exit;
}

// Puedes agregar aquí validación de fortaleza de contraseña si lo deseas

// Hasheamos la contraseña nueva
$hash = password_hash($nueva, PASSWORD_DEFAULT);

// Guardamos la nueva contraseña
$sql = "UPDATE tb_crear_usuario SET password = ? WHERE id = ?";
$stmt = $q->getConexion()->prepare($sql);
$stmt->bind_param("si", $hash, $user['id']);
if ($stmt->execute()) {
    $q->marcarTokenUsado($token); // Invalida el token
    echo json_encode(['icon'=>'success','text'=>'Contraseña cambiada, redirigiendo...']);
} else {
    echo json_encode(['icon'=>'error','text'=>'No se pudo cambiar la contraseña']);
}
$stmt->close();
?>
