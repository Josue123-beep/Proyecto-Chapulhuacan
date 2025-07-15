<?php
include_once __DIR__ . "/../Conexion.php";

class CrearUsuarioQuerys {
    private $conexion;

    public function __construct() {
        $this->conexion = Conexion::obtenerConexion();
    }

    public function crearUsuario($nombre, $apellidos, $email, $password, $rol) {
        try {
            $passwordHash = $password;
            $query = "INSERT INTO tb_crear_usuario (nombre, apellidos, email, contraseña, rol) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->conexion->prepare($query);
            $stmt->bind_param("sssss", $nombre, $apellidos, $email, $passwordHash, $rol);
            $stmt->execute();
            $stmt->close();
            return true;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function verificarEmailExistente($email) {
        // Cambia el nombre de la tabla
        $query = "SELECT email FROM tb_crear_usuario WHERE email = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $existe = $resultado->num_rows > 0;
        $stmt->close();
        return $existe;
    }

    public function __destruct() {
        $this->conexion->close();
    }
}
?>
