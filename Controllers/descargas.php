<?php
include_once(__DIR__ . '/../Server/DataBase/Conexion.php');

if (!isset($_GET['id'])) {
    die("ID de documento no especificado.");
}

$id = intval($_GET['id']);
$conexion = Conexion::obtenerConexion();

// 1. Buscar el archivo a descargar
$stmt = $conexion->prepare("SELECT archivo_ruta, titulo FROM tb_carga_doc WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->bind_result($archivo, $titulo);

if ($stmt->fetch()) {
    $stmt->close();

    // 2. Sumar 1 al total de descargas del documento
    $conexion->query("UPDATE tb_carga_doc SET descargas = descargas + 1 WHERE id = $id");

    // 3. Registrar la descarga en el historial
    $conexion->query("INSERT INTO tb_descargas (id_documento, fecha_descarga) VALUES ($id, NOW())");

    // 4. Descargar el archivo al usuario
    $rutaArchivo = __DIR__ . '/../uploads/' . $archivo;
    if (file_exists($rutaArchivo)) {
        // Detectar el tipo MIME adecuado
        $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
        $mime_types = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
        $mime = $mime_types[$extension] ?? 'application/octet-stream';

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . basename($archivo) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($rutaArchivo));
        readfile($rutaArchivo);
        exit;
    } else {
        echo "Archivo no encontrado.";
    }
} else {
    echo "Documento no encontrado.";
}
?>