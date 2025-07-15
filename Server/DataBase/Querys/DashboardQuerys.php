<?php
include_once __DIR__ .  "/../Conexion.php";

class DashboardQuerys {
    private $conexion;

    public function __construct() {
        $this->conexion = Conexion::obtenerConexion();
    }

    public function obtenerConteoPorArea() {
        $query = "SELECT area, COUNT(*) AS total FROM tb_carga_doc GROUP BY area";
        $result = $this->conexion->query($query);
        $datos = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $datos[] = $row;
            }
        }
        return $datos;
    }   

    public function obtenerMasDescargados($limite = 25) {
        $query = "SELECT id, titulo, descargas FROM tb_carga_doc ORDER BY descargas DESC, id DESC LIMIT ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("i", $limite);
        $stmt->execute();
        $result = $stmt->get_result();
        $datos = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $datos[] = $row;
            }
        }
        $stmt->close();
        return $datos;
    }

    public function obtenerTotalDescargas() {
        $query = "SELECT SUM(descargas) as total FROM tb_carga_doc";
        $result = $this->conexion->query($query);
        $row = $result ? $result->fetch_assoc() : null;
        return isset($row["total"]) && $row["total"] !== null ? (int)$row["total"] : 0;
    }

    public function obtenerDescargasPorMesDesdeCargaDoc() {
        $query = "SELECT SUM(descargas) AS total FROM tb_carga_doc";
        $result = $this->conexion->query($query);
        $row = $result ? $result->fetch_assoc() : null;
        $datos = array_fill(1, 12, 0);
        // Pon todo el total en el mes actual (no es lo ideal, pero es lo que puedes hacer con la estructura actual)
        $mesActual = date('n'); // 1-12
        $datos[$mesActual] = isset($row["total"]) ? (int)$row["total"] : 0;
        return $datos;
    }

    public function __destruct() {
        $this->conexion->close();
    }
}
?>