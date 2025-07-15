<?php
header('Content-Type: application/json');
include_once __DIR__ . "/../Server/DataBase/Querys/RecuperarContraQuerys.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['icon'=>'error','text'=>'Método no permitido']);
    exit;
}

$email = $_POST['email'] ?? '';
if (!$email) {
    echo json_encode(['icon'=>'error','text'=>'Correo requerido']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['icon'=>'error','text'=>'Correo no válido']);
    exit;
}

$q = new RecuperarContraQuerys();
$user = $q->buscarPorEmail($email);

if (!$user) {
    echo json_encode(['icon'=>'error','text'=>'Correo no encontrado']);
    exit;
}

// Generamos token y expiración
$token      = bin2hex(random_bytes(32));
$expiracion = date('Y-m-d H:i:s', strtotime('+5 minutes'));

// Guardamos en la misma tabla
if (!$q->guardarTokenRecuperacion($user['id'], $token, $expiracion)) {
    echo json_encode(['icon'=>'error','text'=>'No se pudo guardar el token']);
    exit;
}

// Enlace que apunte a tu formulario de nueva contraseña
$link = "http://localhost/Modulo%201/Recuperar%20contraseña/nueva_Contra.html   ?token=$token";

$asunto   = "Recuperación de contraseña";
$mensaje  = "Haz clic aquí para restablecer tu contraseña:\n$link\n(Expira en 5 minutos)";

// Configura PHPMailer
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com'; // Cambia por tu SMTP
    $mail->SMTPAuth   = true;
    $mail->Username   = 'TUCORREO@gmail.com'; // Cambia por tu correo
    $mail->Password   = 'TUPASSWORD';         // Cambia por tu contraseña o app password
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;
    
    $mail->setFrom('no-reply@tusitio.com', 'Chapulhuacan');
    $mail->addAddress($email);

    $mail->Subject = $asunto;
    $mail->Body    = $mensaje;

    $mail->send();
    echo json_encode(['icon'=>'success','text'=>'Revisa tu correo para el enlace.']);
} catch (Exception $e) {
    echo json_encode(['icon'=>'error','text'=>'No se pudo enviar el correo: ' . $mail->ErrorInfo]);
}
?>
