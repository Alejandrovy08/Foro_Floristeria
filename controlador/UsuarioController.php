<?php
//Carga el archivo Usuario.php
require_once __DIR__ . '/../modelo/Usuario.php';

class UsuarioController {

    
    private function obtenerUsuarioIdSesion() {
        //Comprobamos que la sesion del usuario esta activa
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        //Comprobamos que la sesión existe y no esta vacio
        $usuarioId = $_SESSION['usuario_id'] ?? null;
        if ($usuarioId === null || $usuarioId === '') {
            return false;
        }

        return (int) $usuarioId;
    }

    public function procesarLogin(): bool {
        //Comprobamos si la sesión está activa
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        //Comprobamos si el metodo de la peticion es POST
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            return false;
        }

        //Comprobamos si el correo o la contraseña están vacías
        $correo = $_POST['correo'] ?? ($_POST['email'] ?? null);
        $password = $_POST['password'] ?? ($_POST['contrasena'] ?? null);

        //Devuelve false si el correo o la contraseña están vacías
        if ($correo === null || $password === null || $correo === '' || $password === '') {
            return false;
        }

        $usuarioModel = new Usuario();//Crea una instancia del modelo Usuario
        $usuario = $usuarioModel->login($correo, $password);//Llama al metodo login del modelo Usuario

        if ($usuario === false) {//Si el usuario no existe, devuelve false
            return false;
        }

        // Guardamos datos del usuario en la sesión.
        $_SESSION['usuario_id'] = $usuario['id'] ?? null;
        $_SESSION['usuario_nombre'] = $usuario['nombre'] ?? null;
        $_SESSION['usuario_tipo'] = $usuario['tipo'] ?? null;

        // Si faltara alguno de los datos, lo tratamos como fallo.
        if ($_SESSION['usuario_id'] === null || $_SESSION['usuario_nombre'] === null) {
            return false;
        }

        return true;
    }
    
    
    public function procesarRegistro(): bool {
        //Comprobamos si el metodo de la peticion es POST
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            return false;
        }

        //Obtenemos los valores introducidos en el formulario
        $nombre = $_POST['nombre'] ?? null;
        $correo = $_POST['correo'] ?? ($_POST['email'] ?? null);
        $password = $_POST['password'] ?? ($_POST['contrasena'] ?? null);

        //Devuelve false si faltan datos o estan vacios  
        if ($nombre === null || $correo === null || $password === null || $nombre === '' || $correo === '' || $password === '') {
            return false;
        }

        $usuarioModel = new Usuario();//Crea una instancia del modelo Usuario
        return $usuarioModel->registrar($nombre, $correo, $password);//Llama al metodo registrar del modelo Usuario
    }

    public function editarPerfil(): bool {
        $usuarioId = $this->obtenerUsuarioIdSesion();
        //Comprobamos si el usuario ha iniciado sesion
        if ($usuarioId === false) {
            return false;
        }

        //Obtenemos los valores introducidos en el formulario
        $nombre = $_POST['nombre'] ?? null;
        $correo = $_POST['correo'] ?? ($_POST['email'] ?? null);

        //Devuelve false si faltan datos o están vacios
        if ($nombre === null || $correo === null || $nombre === '' || $correo === '') {
            return false;
        }

        $usuarioModel = new Usuario();
        //Si todo ha sido un exito llamamos a la funcion actualizarDatos
        $ok = $usuarioModel->actualizarDatos($usuarioId, $nombre, $correo);

        if ($ok) {
            //Actualizamos la sesion del usuario con los datos introducidos
            $_SESSION['usuario_nombre'] = $nombre;
        }

        return $ok;
    }

    //Funcion para cambiar la contraseña
    public function cambiarPassword(): bool {
        $usuarioId = $this->obtenerUsuarioIdSesion();
        if ($usuarioId === false) {
            return false;
        }

        $nuevaPassword = $_POST['nuevaPassword'] ?? ($_POST['nueva_password'] ?? ($_POST['password'] ?? null));
        //Comprobamos que la contraseña no esta vacia
        if ($nuevaPassword === null || $nuevaPassword === '') {
            return false;
        }
        
        $usuarioModel = new Usuario();
        return $usuarioModel->actualizarPassword($usuarioId, $nuevaPassword);
    }

    //Funcion para eliminar un usuario
    public function bajaUsuario(): bool {
        //Obtenemos el Id del usuario
        $usuarioId = $this->obtenerUsuarioIdSesion();
        if ($usuarioId === false) {
            return false;
        }

        $usuarioModel = new Usuario();
        //Si todo esta correcto llamamos al metodo eliminarUsuario de modelo/Usuario.php
        $ok = $usuarioModel->eliminarUsuario($usuarioId);

        //Si no esta correcto devuelve falso
        if (!$ok) {
            return false;
        }

        //Vaciamos todas las variables de la sesion
        $_SESSION = [];
        //Eliminamos las cookies de la sesión
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();

        return true;
    }

    public function editarUsuarioPorAdmin(int $usuarioIdObjetivo, string $nombre, string $correo): bool {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (!isset($_SESSION['admin_id']) || $_SESSION['admin_id'] === '' || $_SESSION['admin_id'] === null) {
            return false;
        }
        if ($usuarioIdObjetivo <= 0 || $nombre === '' || $correo === '') {
            return false;
        }
        $usuarioModel = new Usuario();
        return $usuarioModel->actualizarDatos($usuarioIdObjetivo, $nombre, $correo);
    }

    public function cambiarPasswordUsuarioPorAdmin(int $usuarioIdObjetivo, string $nuevaPassword): bool {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (!isset($_SESSION['admin_id']) || $_SESSION['admin_id'] === '' || $_SESSION['admin_id'] === null) {
            return false;
        }
        if ($usuarioIdObjetivo <= 0 || $nuevaPassword === '') {
            return false;
        }
        $usuarioModel = new Usuario();
        return $usuarioModel->actualizarPassword($usuarioIdObjetivo, $nuevaPassword);
    }
}

/* Si este controlador recibe el request directamente (por ejemplo desde el formulario),
procesamos el login y redirigimos a la vista.*/
if (php_sapi_name() !== 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $controller = new UsuarioController();
    //Comprobamos si el metodo de la peticion es POST
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $accion = $_POST['accion'] ?? '';
     
        //Mostramos un mensaje dependiendo si los datos se han actualizado correctamente y si no un mensaje de error 
        if ($accion === 'editar_perfil') {
            $ok = $controller->editarPerfil();
            header('Location: ' . ($ok ? '../vista/perfil.php?edit=ok' : '../vista/perfil.php?error=1'));
            exit;
        }

        if ($accion === 'cambiar_password') {
            $ok = $controller->cambiarPassword();
            header('Location: ' . ($ok ? '../vista/perfil.php?password=ok' : '../vista/perfil.php?error=1'));
            exit;
        }

        if ($accion === 'baja_usuario') {
            $ok = $controller->bajaUsuario();
            header('Location: ' . ($ok ? '../vista/login.php?baja=ok' : '../vista/perfil.php?error=1'));
            exit;
        }

        if ($accion === 'editar_usuario_admin') {
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            $uid = (int) ($_POST['usuario_id'] ?? 0);
            $nombre = trim((string) ($_POST['nombre'] ?? ''));
            $correo = trim((string) ($_POST['correo'] ?? ''));
            $ok = $controller->editarUsuarioPorAdmin($uid, $nombre, $correo);
            header('Location: ' . ($ok ? '../vista/editar_usuario.php?id=' . $uid . '&edit=ok' : '../vista/editar_usuario.php?id=' . $uid . '&error=1'));
            exit;
        }

        if ($accion === 'cambiar_password_usuario_admin') {
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            $uid = (int) ($_POST['usuario_id'] ?? 0);
            $nueva = $_POST['nuevaPassword'] ?? '';
            $ok = $controller->cambiarPasswordUsuarioPorAdmin($uid, (string) $nueva);
            header('Location: ' . ($ok ? '../vista/editar_usuario.php?id=' . $uid . '&password=ok' : '../vista/editar_usuario.php?id=' . $uid . '&error=1'));
            exit;
        }

        // Si llega accion=registrar procesamos el alta de usuario.
        if ($accion === 'registrar') {
            $ok = $controller->procesarRegistro();
            header('Location: ' . ($ok ? '../vista/login.php?registro=exito' : '../vista/registro.php?error=1'));
            exit;
        }

        // En caso contrario, tratamos el POST como login.
        $ok = $controller->procesarLogin();//Llama al metodo procesarLogin del controlador UsuarioController
        //Redirige al home si el usuario esta logeado y en caso contrario lo manda otra vez al login
        header('Location: ' . ($ok ? '../vista/home.php' : '../vista/login.php?error=1'));
        exit;
    }

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET' && ($_GET['accion'] ?? '') === 'eliminar_usuario_admin') {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (!isset($_SESSION['admin_id']) || $_SESSION['admin_id'] === '' || $_SESSION['admin_id'] === null) {
            header('Location: ../vista/login.php');
            exit;
        }

        $usuarioId = (int) ($_GET['id'] ?? 0);
        if ($usuarioId > 0) {
            $usuarioModel = new Usuario();
            $usuarioModel->eliminarUsuario($usuarioId);
        }

        header('Location: ../vista/admin.php?tab=usuarios');
        exit;
    }

    header('Location: ../vista/login.php');//Redirige a la vista login.php
    exit;
}

//Comprobamos que el codigo se ejecuta en un servidor web y no de una consola de comandos
if (php_sapi_name() !== 'cli') {
    $accionAdmin = $_POST['accion'] ?? ($_GET['accion'] ?? '');
    if ($accionAdmin === 'eliminar_usuario_admin') {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        //Verificamos que el usuario es un administrador en caso contrario lo redirige al login
        if (!isset($_SESSION['admin_id']) || $_SESSION['admin_id'] === '' || $_SESSION['admin_id'] === null) {
            header('Location: ../vista/login.php');
            exit;
        }
        /*
        Obtenemos el id del usuario a eliminar, lo convertimos a int y si recibimos algo extraño o vacio
        el valor asignado sera 0.
        Si el ID es mayor a 0 se puede eliminar al usuario al que le pertenece dicho ID
        */
        $usuarioId = (int) ($_POST['id'] ?? ($_GET['id'] ?? 0));
        if ($usuarioId > 0) {
            $usuarioModel = new Usuario();
            $usuarioModel->eliminarUsuario($usuarioId);
        }
        //Volvemos al panel de administracion
        header('Location: ../vista/admin.php?tab=usuarios');
        exit;
    }
}

?>