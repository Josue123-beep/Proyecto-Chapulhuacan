<?php
class Conexion {
    private static $conexion = null;
    public static function obtenerConexion() {
        $host = "localhost"; 
        $user = 'root';
        $pass = '';
        $db = 'chapulhuacan';
        $conexion = new mysqli($host, $user, $pass, $db);
        if ($conexion->connect_error) {
            die("Error de conexión a la base de datos: " . $conexion->connect_error);
        }
        $conexion->set_charset("utf8");
        return $conexion;
    }
}
?>
