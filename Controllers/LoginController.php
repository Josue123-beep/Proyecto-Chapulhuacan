<?php
header('Content-Type: application/json');
include __DIR__ . "/../Server/DataBase/Querys/LoginQuerys.php";
session_start();

class LoginController {
    private $email;
    private $password;

    public function __construct() {
        $this->email = strtolower(trim($_POST['email'] ?? ''));
        $this->password = trim($_POST['password'] ?? '');   
        error_log("Email: " . $this->email . " Password: " . $this->password);
    }

    public function login(){
        $usuariosQuerys = new LoginQuerys();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("POST recibido: email={$this->email}, password={$this->password}");
            $usuariosdatos = $usuariosQuerys->LoginQuery($this->email, $this->password);
            if ($usuariosdatos) {
                error_log("Login exitoso para usuario: {$this->email}");
                $_SESSION['id'] = $usuariosdatos->id; 
                echo json_encode(array('icon' => 'success', 'text' => 'Bienvenido', 'response'=> 2));
            } else {
                error_log("Login fallido para usuario: {$this->email}");
                echo json_encode(array('icon' => 'error', 'text' => 'Usuario y/o contraseña incorrectos'));
            }
        } else {
            error_log("Método no permitido");
            echo json_encode(array('icon' => 'error', 'text' => 'Método no permitido'));
        }
    }
}

$LoginController = new LoginController();
$LoginController->login();
?>