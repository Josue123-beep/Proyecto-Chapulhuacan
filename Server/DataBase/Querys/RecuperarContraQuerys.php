<?php
include_once __DIR__ . "/../Conexion.php";

class RecuperarContraQuerys {
    private $conexion;

    public function __construct() {
        $this->conexion = Conexion::obtenerConexion();
    }

    // Permite acceder a la conexión desde fuera de la clase
    public function getConexion() {
        return $this->conexion;
    }

    //  Busca al usuario por email
    public function buscarPorEmail(string $email): ?array {
        $sql = "SELECT id FROM tb_crear_usuario WHERE email = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $res ?: null;
    }

    // Guarda token y expiración en la misma fila del usuario
    public function guardarTokenRecuperacion(int $usuario_id, string $token, string $expiracion): bool {
        $sql = "UPDATE tb_crear_usuario 
                  SET token_recuperacion = ?, tiempo_token = ? 
                WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("ssi", $token, $expiracion, $usuario_id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    // Valida que el token exista y no haya expirado
    public function validarToken(string $token): ?array {
        $sql = "SELECT id 
                  FROM tb_crear_usuario 
                 WHERE token_recuperacion = ? 
                   AND tiempo_token > NOW()
                 LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $res ?: null;
    }

    // Consume el token (se borra para que no vuelva a usarse)
    public function marcarTokenUsado(string $token): bool {
        $sql = "UPDATE tb_crear_usuario 
                   SET token_recuperacion = NULL, tiempo_token = NULL 
                 WHERE token_recuperacion = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("s", $token);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
?>
