<?php
    require_once __DIR__ . '/../modelo/Mensaje.php';
    //Comprobamos que la sesion esta activa
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $usuarioIdSesion = null;
    $adminIdSesion = null;

    //Comprobamos que la sesion del usuario esta activa
    if (!empty($_SESSION['usuario_id'])) {
        $tmp = filter_var($_SESSION['usuario_id'], FILTER_VALIDATE_INT);
        $usuarioIdSesion = ($tmp !== false && $tmp > 0) ? $tmp : null;
    }
    //Comprobamos que la sesion del admin esta activa
    if (!empty($_SESSION['admin_id'])) {
        $tmp = filter_var($_SESSION['admin_id'], FILTER_VALIDATE_INT);
        $adminIdSesion = ($tmp !== false && $tmp > 0) ? $tmp : null;
    }   
    //Comprobamos que la sesion del usuario o del admin esta activa
    if ($usuarioIdSesion === null && $adminIdSesion === null) {
        header('Location: vista/login.php');
        exit;
    }   
    //Comprobamos que el metodo de la peticion es POST
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        if ($adminIdSesion !== null) {
            header('Location: vista/admin_chats.php');
        } else {
            header('Location: vista/chat.php');
        }
        exit;
    }

    //Comprobamos que el texto no esta vacio
    $texto = trim((string) ($_POST['texto'] ?? ''));
    //Comprobamos que el usuario destino es un numero entero
    $usuarioDestino = isset($_POST['usuario_id']) ? (int) $_POST['usuario_id'] : 0;

    $mensajeModel = new Mensaje();

    //Comprobamos que el admin esta logueado
    if ($adminIdSesion !== null) {
        //Comprobamos que el usuario destino es un numero entero
        if ($usuarioDestino <= 0) {
            header('Location: vista/admin_chats.php?error=1');
            exit;
        }
        //Comprobamos que el texto no esta vacio
        if ($texto === '') {
            header('Location: vista/chat.php?u=' . $usuarioDestino . '&error=1');
            exit;
        }

        require_once __DIR__ . '/../modelo/Usuario.php';
        $usuarioModel = new Usuario();
        //Comprobamos que el usuario destino existe
        if ($usuarioModel->obtenerPorId($usuarioDestino) === false) {
            header('Location: vista/admin_chats.php?error=1');
            exit;
        }
        //Comprobamos que el mensaje se ha enviado correctamente
        $ok = $mensajeModel->enviar($usuarioDestino, $adminIdSesion, $texto, 'admin');
        if ($ok) {
            header('Location: vista/chat.php?u=' . $usuarioDestino);
        } else {
            header('Location: vista/chat.php?u=' . $usuarioDestino . '&error=1');
        }
        exit;
    }

    //Comprobamos que el usuario esta logueado
    if ($usuarioIdSesion !== null && $adminIdSesion === null) {
        if ($texto === '') {
            header('Location: vista/chat.php?error=1');
            exit;
        }

        //Comprobamos que el admin asignando es un numero entero
        $adminAsignado = $mensajeModel->obtenerAdminIdParaUsuario($usuarioIdSesion);
        if ($adminAsignado <= 0) {
            $adminAsignado = $mensajeModel->obtenerPrimerAdministradorId();
        }
        if ($adminAsignado <= 0) {
            header('Location: vista/chat.php?error=1');
            exit;
        }

        //Comprobamos que el mensaje se ha enviado correctamente
        $ok = $mensajeModel->enviar($usuarioIdSesion, $adminAsignado, $texto, 'usuario');
        header('Location: vista/chat.php' . ($ok ? '' : '?error=1'));
        exit;
    }

    header('Location: vista/chat.php');
exit;
?>