<?php
    require_once __DIR__ . '/../modelo/Comentario.php';
    //Comprobamos que la sesion esta activa
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    //Comprobamos si hay alguien logeado y en caso contrario lo mandamos al menu de login
    if (empty($_SESSION['usuario_id']) && empty($_SESSION['admin_id'])) {
        header('Location: ../vista/login.php');
        exit;
    }

    $publicacionId = (int)($_POST['publicacion_id'] ?? 0);
    $texto = trim((string)($_POST['texto'] ?? ''));

    $usuarioId = null;
    $adminId = null;

    $tipoUsuario = (string)($_SESSION['usuario_tipo'] ?? '');
    $esAdminPorTipo = ($tipoUsuario === 'administrador' || $tipoUsuario === 'admin');

    //Comprobamos que en la sesion activa esta iniciada por un administrador
    if (isset($_SESSION['admin_id']) && $_SESSION['admin_id'] !== null && $_SESSION['admin_id'] !== '') {
        $usuarioId = null;
        //Guardamos en una variable el ID del admin si este es un numero entero y si es lo contrario como false
        $tmpAdmin = filter_var($_SESSION['admin_id'], FILTER_VALIDATE_INT);
        //Aseguramos que la variable adminId adquiere dos posibles valores: un numero entero o null
        $adminId = ($tmpAdmin !== false && $tmpAdmin > 0) ? $tmpAdmin : null;
    //Comprobamos que en la sesion activa esta iniciada por un usuario
    } elseif (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] !== null && $_SESSION['usuario_id'] !== '') {
        $adminId = null;
        //Guardamos en una variable el ID del usuario si este es un numero entero
        $tmpUsuario = filter_var($_SESSION['usuario_id'], FILTER_VALIDATE_INT);
        //Aseguramos que la variable usuarioId adquiere dos posibles valores: un numero entero o null
        $usuarioId = ($tmpUsuario !== false && $tmpUsuario > 0) ? $tmpUsuario : null;
    }

    //Comprobamos que el ID de la publicacion es distinto a 0 y que tenga texto
    if ($publicacionId > 0 && $texto !== '') {
        $comentarioModel = new Comentario();
        $comentarioModel->crear($texto, $usuarioId, $publicacionId, $adminId);
    }

    header('Location: ../vista/detalle_publicacion.php?id=' . $publicacionId);
    exit;
?>
