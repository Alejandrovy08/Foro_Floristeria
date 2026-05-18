<?php
    /**
     * Vista para obtener los mensajes de un usuario o de un admin del chat sin tener que recargar la pagina 
    */
    session_start();
    //Cargamos los modelos
    require_once __DIR__ . '/../modelo/Mensaje.php';
    require_once __DIR__ . '/../modelo/Usuario.php';

    header('Content-Type: text/html; charset=UTF-8');

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
        http_response_code(403);
        exit;
    }

    $esAdmin = $adminIdSesion !== null;
    $esRemitenteAdmin = $esAdmin;

    //Comprobamos que el id solicitado es un numero entero
    $idSolicitado = filter_var($_GET['usuario_id'] ?? $_GET['u'] ?? 0, FILTER_VALIDATE_INT);
    $usuarioChatId = ($idSolicitado !== false && $idSolicitado > 0) ? (int) $idSolicitado : 0;

    //Comprobamos que el usuario es admin
    if ($esAdmin) {
        if ($usuarioChatId <= 0) {
            http_response_code(400);
            exit;
        }
        //Comprobamos que el usuario existe
        $usuarioModel = new Usuario();
        if ($usuarioModel->obtenerPorId($usuarioChatId) === false) {
            http_response_code(404);
            exit;
        }
    } else {
        $usuarioChatId = (int) $usuarioIdSesion;
        if ($usuarioChatId <= 0) {
            http_response_code(403);
            exit;
        }
    }

    $mensajeModel = new Mensaje();
    $mensajes = $mensajeModel->obtenerConversacion($usuarioChatId);
    //Comprobamos que no existen mensajes
    if (empty($mensajes)) {
        $vacio = $esRemitenteAdmin
            ? 'Aún no hay mensajes con este usuario.'
            : 'No hay mensajes todavía. Escribe el primero.';
        echo '<p class="vacio">' . htmlspecialchars($vacio, ENT_QUOTES, 'UTF-8') . '</p>';
        exit;
    }
    //Comprobamos que existen mensajes
    foreach ($mensajes as $msg) {
        //Comprobamos que el texto no esta vacio
        $textoMsg = nl2br(htmlspecialchars($msg['texto'] ?? '', ENT_QUOTES, 'UTF-8'));
        //Comprobamos que la fecha de envio no esta vacia
        $fechaMsg = htmlspecialchars($msg['fecha_envio'] ?? '', ENT_QUOTES, 'UTF-8');
        $por = $msg['enviado_por'] ?? 'usuario';
        //Comprombamos que el remitente es admin o usuario
        if ($esRemitenteAdmin) {
            $clase = ($por === 'admin') ? 'burbuja--yo' : 'burbuja--otro';
        } else {
            $clase = ($por === 'usuario') ? 'burbuja--yo' : 'burbuja--otro';
        }
        echo '<div class="burbuja ' . $clase . '">';
        echo $textoMsg;
        echo '<span class="hora">' . $fechaMsg . '</span>';
        echo '</div>';
    }
?>
