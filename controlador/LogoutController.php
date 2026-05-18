<?php
    // Cierra la sesión del usuario y redirige a la home.
    session_start();

    $_SESSION = [];
    //Si se utiliza cookies, se eliminan
    if (ini_get('session.use_cookies')) {
        //Obtenemos los parametros de la cookie de la sesion
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool)$params['secure'], (bool)$params['httponly']);
    }
    //Se destruye la sesión
    session_destroy();
    //Se redirige a la página de inicio
    header('Location: vista/home.php');
    exit;
?>