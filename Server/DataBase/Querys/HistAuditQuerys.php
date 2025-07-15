<?php
include_once __DIR__ . "/../Conexion.php";

class HistAuditQuerys {
    private $conexion;

    public function __construct() {
        $this->conexion = Conexion::obtenerConexion();
    }

    public function obtenerRegistros($area = null, $fecha = null) {
        $query = "SELECT usuario, accion, archivo, area, fecha, hora FROM historial_auditoria WHERE 1=1";
        $params = [];
        $types = "";

        if ($area) {
            $query .= " AND area = ?";
            $params[] = $area;
            $types .= "s";
        }

        if ($fecha) {
            $query .= " AND fecha = ?";
            $params[] = $fecha;
            $types .= "s";
        }

        $query .= " ORDER BY fecha DESC, hora DESC";

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
