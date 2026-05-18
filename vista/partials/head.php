<?php
    /*
    Plantilla para inicializar de forma segura las sesiones de PHP,configurar el idioma,juego de caracteres y la responsividad,
    conexion con el framework Bootstrap y enlazado de las tipografias de Google fonts y con el scss
    */
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $pageTitle = $pageTitle ?? 'Floristería Yerga';
    $bodyClass = $bodyClass ?? '';
    $hideSiteChrome = !empty($hideSiteChrome);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/scss/main.css">
</head>
<body class="<?php echo htmlspecialchars(trim($bodyClass), ENT_QUOTES, 'UTF-8'); ?>">
