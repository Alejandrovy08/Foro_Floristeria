<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri === '/' || $uri === '/index.php') {
    require __DIR__ . '/../vista/home.php';
    exit;
}

// 1. Intentar buscar el archivo en la ruta tal cual (por si incluye /vista/)
$file_path = __DIR__ . '/..' . $uri;
if (file_exists($file_path) && !is_dir($file_path)) {
    if (pathinfo($file_path, PATHINFO_EXTENSION) === 'php') {
        require $file_path;
        exit;
    }
}

// 2. Si no se encuentra, intentar buscarlo asumiendo que omitieron la palabra "vista"
$vista_path = __DIR__ . '/../vista' . $uri;
if (file_exists($vista_path) && !is_dir($vista_path)) {
    if (pathinfo($vista_path, PATHINFO_EXTENSION) === 'php') {
        require $vista_path;
        exit;
    }
}

require __DIR__ . '/../vista/home.php';