<?php
include_once __DIR__ . "/../Conexion.php";

class PortalDescargaQuerys {
    private $conexion;

    public function __construct() {
        $this->conexion = Conexion::obtenerConexion();
    }

    public function obtenerDocumentos($tipo = '', $area = '', $fecha = '') {
        $query = "SELECT id, titulo AS title, tipo AS type, area, fecha AS date, archivo_ruta AS file FROM tb_carga_doc WHERE 1";        $parametros = [];
        $tipos = "";

        if (!empty($tipo)) {
            $query .= " AND tipo = ?";
            $parametros[] = $tipo;
            $tipos .= "s";
        }

        if (!empty($area)) {
            $query .= " AND area = ?";
            $parametros[] = $area;
            $tipos .= "s";
        }

        if (!empty($fecha)) {
            $query .= " AND fecha = ?";
            $parametros[] = $fecha;
            $tipos .= "s";
        }

        $stmt = $this->conexion->prepare($query);

        if (!empty($parametros)) {
            $stmt->bind_param($tipos, ...$parametros);
        }

        $stmt->execute();
        $resultado = $stmt->get_result();
        $documentos = [];

        while ($fila = $resultado->fetch_assoc()) {
            $documentos[] = $fila;
        }

        $stmt->close();
        return $documentos;
    }

    public function __destruct() {
        $this->conexion->close();
    }
}
?>          