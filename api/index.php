<?php
// Enrutador puente para Vercel Serverless
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Si se busca la raíz, cargamos el home directamente
if ($uri === '/' || $uri === '/index.php') {
    require __DIR__ . '/../vista/home.php';
    exit;
}

// Reescribimos la ruta para buscar el archivo real en tus carpetas locales
$file_path = __DIR__ . '/..' . $uri;

if (file_exists($file_path) && !is_dir($file_path)) {
    // Si es un script PHP válido, lo ejecutamos
    if (pathinfo($file_path, PATHINFO_EXTENSION) === 'php') {
        require $file_path;
        exit;
    }
}

// Si la ruta no existe, mandamos al home por seguridad
require __DIR__ . '/../vista/home.php';