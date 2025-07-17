<?php
include_once __DIR__ . "/../Conexion.php";

class HistAuditQuerys {
    private $conexion;

    public function __construct() {
        $this->conexion = Conexion::obtenerConexion();
    }

    public function obtenerRegistros($area = null, $fecha = null) {
        $query = "SELECT 
                    usuario_nombre,
                    usuario_apellidos,
                    accion,
                    archivo_nombre AS archivo,
                    area,
                    fecha,
                    archivo_ruta
                  FROM tb_historial
                  WHERE 1=1";
        $params = [];
        $types = "";

        if ($area) {
            $query .= " AND area = ?";
            $params[] = $area;
            $types .= "s";
        }
        if ($fecha) {
            $query .= " AND DATE(fecha) = ?";
            $params[] = $fecha;
            $types .= "s";
        }
        $query .= " ORDER BY fecha DESC";

        $stmt = $this->conexion->prepare($query);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $registros = [];
        while ($row = $result->fetch_assoc()) {
            $registros[] = $row;
        }
        $stmt->close();
        return $registros;
    }

    public function __destruct() {
        $this->conexion->close();
    }
}
?>