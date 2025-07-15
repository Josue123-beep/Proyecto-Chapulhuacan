<?php
include __DIR__ . "/../Server/DataBase/Querys/crearusuarioQuerys.php";

class CrearUsuarioController {
    private ?string $nombre;
    private ?string $apellidos;
    private ?string $email;
    private ?string $password;
    private ?string $rol;

    public function __construct() {
        $this->nombre = $_POST['nombre'] ?? null;
        $this->apellidos = $_POST['apellidos'] ?? null;
        $this->email = $_POST['email'] ?? null;
        $this->password = $_POST['password'] ?? null;
        $this->rol = $_POST['rol'] ?? null;
    }

    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['icon' => 'error', 'text' => 'Método no permitido']);
            return;
        }

        $usuarioQuery = new CrearUsuarioQuerys(); 

        // Validar campos vacíos
        if (!$this->nombre || !$this->apellidos || !$this->email || !$this->password || !$this->rol) {
            echo json_encode(['icon' => 'warning', 'text' => 'Todos los campos son obligatorios']);
            return;
        }

        // Validación si ya existe el correo
        if ($usuarioQuery->verificarEmailExistente($this->email)) {
            echo json_encode(['icon' => 'error', 'text' => 'Este correo ya está registrado']);
            return;
        }

        // Crear usuario
        $creado = $usuarioQuery->crearUsuario($this->nombre, $this->apellidos, $this->email, $this->password, $this->rol);
        if ($creado) {
            echo json_encode(['icon' => 'success', 'text' => 'Usuario registrado correctamente', 'response' => 1]);
        } else {
            echo json_encode(['icon' => 'error', 'text' => 'Error al registrar el usuario']);
        }
    }
}

// Ejecutar controlador
$controller = new CrearUsuarioController();
$controller->crear();
?>
