<?php
require_once __DIR__ . '/../Conexion.php';

class HistAuditQuerys
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = Conexion::obtenerConexion();
    }

    // Insertar acción en el historial
    public function registrarAccion($data)
    {
        require_once __DIR__ . '/registro_historial.php';
        return registrarEnHistorial($this->conexion, $data);
    }

    // Obtener historial, con filtro opcional por área y fecha
    public function obtenerHistorial($area = '', $fecha = '')
    {
        $sql = "SELECT * FROM tb_historial WHERE 1=1";
        $params = [];
        $types = '';
        if ($area) {
            $sql .= " AND area = ?";
            $params[] = $area;
            $types .= "s";
        }
        if ($fecha) {
            $sql .= " AND DATE(fecha) = ?";
            $params[] = $fecha;
            $types .= "s";
        }
        $sql .= " ORDER BY fecha DESC";
        $stmt = $this->conexion->prepare($sql);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function __destruct()
    {
        if ($this->conexion) {
            $this->conexion->close();
        }
    }
}
?>