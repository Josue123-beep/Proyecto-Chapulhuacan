<?php
class GestionDocQuerys {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllDocuments($filters = []) {
        try {
            $sql = "SELECT id, titulo, tipo, area, Categoria, fecha, descripcion, archivo_nombre, archivo_ruta FROM tb_carga_doc WHERE 1=1";
            $params = [];
            $types = "";

            if (!empty($filters['titulo'])) {
                $sql .= " AND titulo LIKE ?";
                $params[] = "%" . $filters['titulo'] . "%";
                $types .= "s";
            }
            if (!empty($filters['tipo'])) {
                $sql .= " AND tipo = ?";
                $params[] = $filters['tipo'];
                $types .= "s";
            }
            if (!empty($filters['area'])) {
                $sql .= " AND area = ?";
                $params[] = $filters['area'];
                $types .= "s";
            }
            if (!empty($filters['Categoria'])) {
                $sql .= " AND Categoria = ?";
                $params[] = $filters['Categoria'];
                $types .= "s";
            }
            if (!empty($filters['fecha'])) {
                $sql .= " AND DATE(fecha) = ?";
                $params[] = $filters['fecha'];
                $types .= "s";
            }

            $sql .= " ORDER BY fecha DESC";

            $stmt = $this->conn->prepare($sql);
            if (!$stmt) throw new Exception("Error preparing statement: " . $this->conn->error);

            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $docs = [];
            while ($row = $result->fetch_assoc()) {
                $docs[] = $row;
            }
            $stmt->close();
            return $docs;
        } catch (Exception $e) {
            error_log("Error en query: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        }
    }

    public function editDocument($id, $titulo, $tipo, $area, $Categoria, $fecha, $descripcion, $subcarpeta, $subcarpetaPersonalizada, $archivo = null) {
        try {
            $uploadDir = __DIR__ . '/../../../uploads/';
            $archivo_nombre = null;
            $archivo_ruta = null;

            // 1. Determinar subcarpeta final para guardar el archivo
            $subcarpetaFinal = '';
            if ($subcarpeta === 'Otra') {
                $subcarpetaFinal = trim($subcarpetaPersonalizada);
                if (!$subcarpetaFinal) {
                    return ['success' => false, 'message' => 'Debes indicar el nombre de la subcarpeta personalizada.'];
                }
            } else {
                $subcarpetaFinal = $subcarpeta;
            }
            $subcarpetaFinal = preg_replace('/[^A-Za-z0-9 _-]/', '', $subcarpetaFinal);

            // 2. Si se sube un archivo nuevo, lo movemos a la subcarpeta seleccionada
            if ($archivo && isset($archivo['tmp_name']) && $archivo['tmp_name']) {
                $ruta_final = $uploadDir . $subcarpetaFinal . '/';
                if (!is_dir($ruta_final)) {
                    mkdir($ruta_final, 0777, true);
                }
                $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
                $nombreBase = pathinfo($archivo['name'], PATHINFO_FILENAME);
                $nombreFinal = $archivo['name'];
                $rutaDestino = $ruta_final . $nombreFinal;
                $contador = 1;
                while (file_exists($rutaDestino)) {
                    $nombreFinal = $nombreBase . "($contador)." . $ext;
                    $rutaDestino = $ruta_final . $nombreFinal;
                    $contador++;
                }

                if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                    $archivo_nombre = $nombreFinal;
                    // Guardar la ruta relativa a "uploads/"
                    $archivo_ruta = $subcarpetaFinal . "/" . $nombreFinal;
                    $sql = "UPDATE tb_carga_doc SET titulo=?, tipo=?, area=?, Categoria=?, fecha=?, descripcion=?, archivo_nombre=?, archivo_ruta=? WHERE id=?";
                    $stmt = $this->conn->prepare($sql);
                    if (!$stmt) throw new Exception($this->conn->error);
                    $stmt->bind_param("ssssssssi", $titulo, $tipo, $area, $Categoria, $fecha, $descripcion, $archivo_nombre, $archivo_ruta, $id);
                } else {
                    return ['success' => false, 'message' => 'Error al guardar archivo'];
                }
            } else {
                // Si no se sube archivo, solo actualizar los demás datos
                $sql = "UPDATE tb_carga_doc SET titulo=?, tipo=?, area=?, Categoria=?, fecha=?, descripcion=? WHERE id=?";
                $stmt = $this->conn->prepare($sql);
                if (!$stmt) throw new Exception($this->conn->error);
                $stmt->bind_param("ssssssi", $titulo, $tipo, $area, $Categoria, $fecha, $descripcion, $id);
            }

            $stmt->execute();
            $success = $stmt->affected_rows > 0;
            $stmt->close();
            return ['success'=>$success];
        } catch (Exception $e) {
            return ['success'=>false, 'message'=>$e->getMessage()];
        }
    }

    public function deleteDocument($id) {
        try {
            $sql = "DELETE FROM tb_carga_doc WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) throw new Exception($this->conn->error);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $success = $stmt->affected_rows > 0;
            $stmt->close();
            return ['success'=>$success];
        } catch (Exception $e) {
            return ['success'=>false, 'message'=>$e->getMessage()];
        }
    }
}
?>