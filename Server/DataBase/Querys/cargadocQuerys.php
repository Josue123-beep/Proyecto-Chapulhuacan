<?php
class CargaDocQuerys {
    private $conn;

    public function __construct() {
        require_once __DIR__ . '/../Conexion.php';
        $this->conn = Conexion::obtenerConexion();
    }

    public function guardarDocumento($titulo, $tipo, $area, $fecha, $descripcion, $archivo_nombre, $archivo_ruta, $categoria) {
        $stmt = $this->conn->prepare("INSERT INTO tb_carga_doc (titulo, tipo, area, fecha, descripcion, archivo_nombre, archivo_ruta, categoria) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) return false;
        $stmt->bind_param("ssssssss", $titulo, $tipo, $area, $fecha, $descripcion, $archivo_nombre, $archivo_ruta, $categoria);
        return $stmt->execute();
    }
}
?>