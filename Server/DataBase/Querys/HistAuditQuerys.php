<?php
require_once __DIR__ . '/../Conexion.php';

class HistAuditQuerys
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = Conexion::obtenerConexion();
    }

    public function obtenerHistorial($area = '', $fecha = '')
    {
        $filtros = [];
        $params = [];
        $tipos = "";

        if (!empty($area)) {
            $filtros[] = "area = ?";
            $params[] = $area;
            $tipos .= "s";
        }
        if (!empty($fecha)) {
            $filtros[] = "DATE(fecha) = ?";
            $params[] = $fecha;
            $tipos .= "s";
        }

        $sql = "SELECT * FROM tb_historial";
        if (!empty($filtros)) {
            $sql .= " WHERE " . implode(" AND ", $filtros);
        }
        $sql .= " ORDER BY fecha DESC";

        $stmt = $this->conexion->prepare($sql);
        if (!$stmt) return false;

        if (!empty($params)) {
            $stmt->bind_param($tipos, ...$params);
        }

        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }

        $result = $stmt->get_result();
        if (!$result) {
            $stmt->close();
            return false;
        }

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
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