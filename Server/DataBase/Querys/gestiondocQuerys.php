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

            // 1. Determinar subcarpeta final
            $subcarpetaFinal = ($subcarpeta === 'Otra')
                ? trim($subcarpetaPersonalizada)
                : trim($subcarpeta);
            $subcarpetaFinal = preg_replace('/[^A-Za-z0-9 _-]/', '', $subcarpetaFinal);

            // 2. Obtener info actual del documento
            $sqlSel = "SELECT archivo_nombre, archivo_ruta FROM tb_carga_doc WHERE id = ?";
            $stmtSel = $this->conn->prepare($sqlSel);
            if (!$stmtSel) throw new Exception($this->conn->error);
            $stmtSel->bind_param("i", $id);
            $stmtSel->execute();
            $stmtSel->bind_result($old_nombre, $old_ruta_rel);
            $stmtSel->fetch();
            $stmtSel->close();

            $old_ruta_abs = $old_ruta_rel ? ($uploadDir . $old_ruta_rel) : null;
            $archivo_nombre = $old_nombre;
            $archivo_ruta = $old_ruta_rel;

            // 3. Si hay archivo nuevo, subirlo y eliminar el anterior
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
                    // Elimina el archivo anterior si existe
                    if ($old_ruta_abs && file_exists($old_ruta_abs)) {
                        unlink($old_ruta_abs);
                    }
                    $archivo_nombre = $nombreFinal;
                    $archivo_ruta = $subcarpetaFinal . "/" . $nombreFinal;
                } else {
                    return ['success' => false, 'message' => 'Error al guardar el archivo nuevo'];
                }
            } else if ($old_ruta_rel) {
                // 4. Si NO hay archivo nuevo, pero se cambió de carpeta, mueve el archivo
                $old_carpeta = dirname($old_ruta_rel);
                if ($old_carpeta !== $subcarpetaFinal) {
                    $nombreArchivo = basename($old_ruta_rel);
                    $nuevaRutaAbs = $uploadDir . $subcarpetaFinal . '/' . $nombreArchivo;
                    $nuevaRutaRel = $subcarpetaFinal . '/' . $nombreArchivo;
                    // Crea la carpeta si no existe
                    if (!is_dir($uploadDir . $subcarpetaFinal)) {
                        mkdir($uploadDir . $subcarpetaFinal, 0777, true);
                    }
                    // Mueve el archivo solo si existe el anterior y no es el mismo destino
                    if (file_exists($old_ruta_abs) && $old_ruta_abs !== $nuevaRutaAbs) {
                        if (rename($old_ruta_abs, $nuevaRutaAbs)) {
                            $archivo_ruta = $nuevaRutaRel;
                        } else {
                            return ['success' => false, 'message' => 'No se pudo mover el archivo a la nueva carpeta'];
                        }
                    }
                }
            }

            // 5. Actualizar la BD (si hay archivo, guardar nombre y ruta. Si no, solo los otros campos)
            if ($archivo_ruta) {
                $sql = "UPDATE tb_carga_doc SET titulo=?, tipo=?, area=?, Categoria=?, fecha=?, descripcion=?, archivo_nombre=?, archivo_ruta=? WHERE id=?";
                $stmt = $this->conn->prepare($sql);
                if (!$stmt) throw new Exception($this->conn->error);
                $stmt->bind_param("ssssssssi", $titulo, $tipo, $area, $Categoria, $fecha, $descripcion, $archivo_nombre, $archivo_ruta, $id);
            } else {
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