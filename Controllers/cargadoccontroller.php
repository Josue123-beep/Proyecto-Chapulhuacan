<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Si es GET, respondemos con las carpetas disponibles en uploads/
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    $baseDir = realpath(__DIR__ . '/../uploads');
    $carpetas = [];
    if ($baseDir && is_dir($baseDir)) {
        $archivos = scandir($baseDir);
        foreach ($archivos as $archivo) {
            if ($archivo === '.' || $archivo === '..') continue;
            $ruta = $baseDir . '/' . $archivo;
            if (is_dir($ruta)) {
                $carpetas[] = $archivo;
            }
        }
    }
    echo json_encode($carpetas);
    exit;
}

require_once __DIR__ . '/../Server/DataBase/Querys/cargadocQuerys.php';

class CargaDocController {
    private ?string $titulo;
    private ?string $tipo;
    private ?string $area;
    private ?string $fecha;
    private ?string $descripcion;
    private array $archivos;
    private ?string $subcarpeta;
    private ?string $subcarpetaPersonalizada;
    private ?string $categoria;

    public function __construct() {
        $this->titulo = $_POST['titulo'] ?? null;
        $this->tipo = $_POST['tipo'] ?? null;
        $this->area = $_POST['area'] ?? null;
        $this->fecha = $_POST['fecha'] ?? null;
        $this->descripcion = $_POST['descripcion'] ?? null;
        $this->archivos = $_FILES['archivos'] ?? [];
        $this->subcarpeta = $_POST['subcarpeta'] ?? null;
        $this->subcarpetaPersonalizada = $_POST['subcarpetaPersonalizada'] ?? null;
        $this->categoria = $_POST['categoria'] ?? null;
    }

    public function cargar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['icon' => 'error', 'text' => 'Método no permitido']);
            return;
        }

        $docQuery = new CargaDocQuerys();

        if (
            !$this->titulo || !$this->tipo || !$this->area ||
            !$this->fecha || !$this->descripcion || empty($this->archivos['name'][0]) ||
            !$this->subcarpeta || !$this->categoria
        ) {
            echo json_encode(['icon' => 'warning', 'text' => 'Todos los campos y al menos un archivo son obligatorios']);
            return;
        }

        // Determinar subcarpeta final
        $subcarpetaFinal = '';
        if ($this->subcarpeta === 'Otra') {
            $subcarpetaFinal = trim($this->subcarpetaPersonalizada);
            if (!$subcarpetaFinal) {
                echo json_encode(['icon' => 'warning', 'text' => 'Debes indicar el nombre de la subcarpeta personalizada.']);
                return;
            }
        } else {
            $subcarpetaFinal = $this->subcarpeta;
        }
        $subcarpetaFinal = preg_replace('/[^A-Za-z0-9 _-]/', '', $subcarpetaFinal);

        $permitidos = ['pdf', 'docx', 'doc', 'xlsx', 'xls', 'jpg', 'jpeg', 'png'];
        foreach ($this->archivos['name'] as $i => $nombre) {
            $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
            if (!in_array($ext, $permitidos)) {
                echo json_encode(['icon' => 'error', 'text' => "El archivo \"$nombre\" tiene un formato no permitido"]);
                return;
            }
        }

        $subidos = 0;
        $directorioUploads = __DIR__ . "/../uploads";
        $directorioDestino = $directorioUploads . '/' . $subcarpetaFinal;
        if (!is_dir($directorioDestino)) {
            if (!mkdir($directorioDestino, 0777, true)) {
                echo json_encode(['icon' => 'error', 'text' => 'No se pudo crear la subcarpeta de destino.']);
                return;
            }
        }

        foreach ($this->archivos['name'] as $i => $nombreOriginal) {
            $tmp = $this->archivos['tmp_name'][$i];
            $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
            $nombreBase = pathinfo($nombreOriginal, PATHINFO_FILENAME);

            // Renombrar si existe archivo con el mismo nombre
            $nombreFinal = $nombreOriginal;
            $rutaDestino = $directorioDestino . "/" . $nombreFinal;
            $contador = 1;
            while (file_exists($rutaDestino)) {
                $nombreFinal = $nombreBase . "($contador)." . $ext;
                $rutaDestino = $directorioDestino . "/" . $nombreFinal;
                $contador++;
            }

            if (!is_uploaded_file($tmp)) {
                echo json_encode(['icon' => 'error', 'text' => "El archivo temporal $tmp no existe"]);
                return;
            }

            if (!move_uploaded_file($tmp, $rutaDestino)) {
                echo json_encode(['icon' => 'error', 'text' => "No se pudo mover el archivo: $nombreOriginal a $rutaDestino"]);
                return;
            }

            // Guardar la ruta relativa: subcarpeta/nombreFinal
            $archivoRutaRelativa = $subcarpetaFinal . "/" . $nombreFinal;

            $guardado = $docQuery->guardarDocumento(
                $this->titulo,
                $this->tipo,
                $this->area,
                $this->fecha,
                $this->descripcion,
                $nombreFinal,   // El nombre final que puede tener (1), (2), etc
                $archivoRutaRelativa,
                $this->categoria
            );
            if (!$guardado) {
                echo json_encode(['icon' => 'error', 'text' => 'Error al guardar en la BD']);
                return;
            }
            $subidos++;
        }

        if ($subidos > 0) {
            echo json_encode(['icon' => 'success', 'text' => 'Documentos cargados correctamente', 'response' => 1]);
        } else {
            echo json_encode(['icon' => 'error', 'text' => 'Error al cargar los documentos']);
        }
    }
}

// Solo procesa POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new CargaDocController();
    $controller->cargar();
}
?>