<?php
function registrarEnHistorial($conexion, $params) {
    $id_usuario        = $params['id_usuario'] ?? null;
    $usuario_nombre    = $params['usuario_nombre'] ?? '';
    $usuario_apellidos = $params['usuario_apellidos'] ?? '';
    $accion            = $params['accion'] ?? '';
    $area              = $params['area'] ?? '';
    $descripcion       = $params['descripcion'] ?? '';
    $archivo_id        = $params['archivo_id'] ?? null;
    $archivo_nombre    = $params['archivo_nombre'] ?? '';
    $archivo_ruta      = $params['archivo_ruta'] ?? '';

    $stmt = $conexion->prepare(
        "INSERT INTO tb_historial (
            id_usuario, usuario_nombre, usuario_apellidos, accion,
            archivo_id, archivo_nombre, archivo_ruta, area, fecha, descripcion
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)"
    );
    $stmt->bind_param(
        "isssissss",
        $id_usuario,
        $usuario_nombre,
        $usuario_apellidos,
        $accion,
        $archivo_id,
        $archivo_nombre,
        $archivo_ruta,
        $area,
        $descripcion
    );
    return $stmt->execute();
}
?>