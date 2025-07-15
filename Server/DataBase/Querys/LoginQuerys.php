<?php
include_once __DIR__ . "/../Conexion.php";

class LoginQuerys {
    private $conexion;

    public function __construct() {
        $this->conexion = Conexion::obtenerConexion();
    }

    public function LoginQuery($user, $password) {
        try {
            $query = "SELECT * FROM tb_crear_usuario WHERE email = ?";
            $statement = $this->conexion->prepare($query);
            if ($statement === false) {
                throw new Exception("Error en la preparación de la consulta: " . $this->conexion->error);
            }
            $statement->bind_param("s", $user);
            $statement->execute();
            $result = $statement->get_result();
            $userdata = $result->fetch_object();
            $statement->close();

            if ($userdata) {
                $storedPassword = $userdata->contraseña; 
                if ($password === $storedPassword) {
                    return $userdata;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            return false; 
        }
    }

    public function __destruct() {
        $this->conexion->close();
    }
}
?>