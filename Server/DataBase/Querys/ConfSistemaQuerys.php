    <?php
include_once __DIR__ . "/../Conexion.php";

class ConfSistemaQuerys {
    private $conexion;

    public function __construct() {
        $this->conexion = Conexion::obtenerConexion();
    }

    // Obtiene la configuración actual
    public function obtenerConfiguracion() {
        $query = "SELECT * FROM configuracion_sistema LIMIT 1";
        $result = $this->conexion->query($query);
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }

    // Guarda o actualiza la configuración
    public function guardarConfiguracion($data) {
        // Aquí suponemos que solo hay un registro de configuración
        $existing = $this->obtenerConfiguracion();

        if ($existing) {
            $stmt = $this->conexion->prepare(
                "UPDATE configuracion_sistema SET 
                    username = ?, 
                    role = ?, 
                    doc_type = ?, 
                    active_area = ?, 
                    allowed_formats = ?, 
                    max_file_size = ?, 
                    custom_text = ?, 
                    logo = ?
                 WHERE id = ?"
            );
            $stmt->bind_param(
                "sssssisii",
                $data['username'],
                $data['role'],
                $data['doc_type'],
                $data['active_area'],
                $data['allowed_formats'],
                $data['max_file_size'],
                $data['custom_text'],
                $data['logo'],
                $existing['id']
            );
            $stmt->execute();
            $stmt->close();
            return true;
        } else {
            $stmt = $this->conexion->prepare(
                "INSERT INTO configuracion_sistema
                 (username, role, doc_type, active_area, allowed_formats, max_file_size, custom_text, logo)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "sssssis",
                $data['username'],
                $data['role'],
                $data['doc_type'],
                $data['active_area'],
                $data['allowed_formats'],
                $data['max_file_size'],
                $data['custom_text'],
                $data['logo']
            );
            $stmt->execute();
            $stmt->close();
            return true;
        }
    }

    public function __destruct() {
        $this->conexion->close();
    }
}
?>
