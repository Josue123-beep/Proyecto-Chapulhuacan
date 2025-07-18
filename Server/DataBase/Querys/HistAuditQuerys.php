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

    // Buscar un archivo por nombre en uploads y subcarpetas
    public static function buscarArchivoEnUploads($nombreArchivo, $carpeta = null)
    {
        if ($carpeta === null) {
            $carpeta = __DIR__ . '/../../../uploads';
        }
        $nombreArchivo = strtolower($nombreArchivo);
        $directory = new RecursiveDirectoryIterator($carpeta);
        $iterator = new RecursiveIteratorIterator($directory);

        foreach ($iterator as $file) {
            if ($file->isFile() && strtolower($file->getFilename()) === $nombreArchivo) {
                // Construye la ruta web relativa para el navegador
                $rutaAbsoluta = $file->getPathname();
                $rutaRelativa = str_replace(DIRECTORY_SEPARATOR, '/', $rutaAbsoluta);
                // Encuentra la parte desde /uploads/... en adelante
                $pos = strpos($rutaRelativa, '/uploads/');
                if ($pos !== false) {
                    return '/CHAPULHUACAN' . substr($rutaRelativa, $pos);
                }
            }
        }
        return false; // No encontrado
    }

    public function __destruct()
    {
        if ($this->conexion) {
            $this->conexion->close();
        }
    }
}
?>