<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Si se busca la raíz limpia o index.php, directos al home
if ($uri === '/' || $uri === '/index.php') {
    require __DIR__ . '/../vista/home.php';
    exit;
}

// Si la URL se ha vuelto loca acumulando carpetas, la limpiamos quedándonos con el archivo real
if (preg_match('/\/([^\/]+\.php)$/', $uri, $matches)) {
    $archivo_final = $matches[1];
    
    // Si es un controlador, lo buscamos en su sitio
    if (strpos($uri, 'controlador/') !== false) {
        $controlador_path = __DIR__ . '/../controlador/' . $archivo_final;
        if (file_exists($controlador_path)) {
            require $controlador_path;
            exit;
        }
    }
    
    // Si no, lo buscamos en la carpeta vista
    $vista_path = __DIR__ . '/../vista/' . $archivo_final;
    if (file_exists($vista_path)) {
        require $vista_path;
        exit;
    }
}

// Intento estándar por si la ruta viene limpia
$file_path = __DIR__ . '/..' . $uri;
if (file_exists($file_path) && !is_dir($file_path)) {
    require $file_path;
    exit;
}

// Si todo lo demás falla, al home por seguridad
require __DIR__ . '/../vista/home.php';