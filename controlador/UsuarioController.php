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

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->login($correo, $password);

        //Devuelve false si el usuario no existe
        if ($usuario === false) {
            return false;
        }

        //Comprobamos si el usuario es administrador
        if (isset($usuario['tipo']) && $usuario['tipo'] === 'administrador') {
            $_SESSION['admin_id'] = $usuario['id'] ?? null;
            $_SESSION['admin_nombre'] = $usuario['nombre'] ?? null;
            $_SESSION['usuario_tipo'] = 'admin';

            if ($_SESSION['admin_id'] === null || $_SESSION['admin_nombre'] === null) {
                return false;
            }

            return true;
        }

        //Asignamos los datos del usuario a la sesión si no es administrador
        $_SESSION['usuario_id'] = $usuario['id'] ?? null;
        $_SESSION['usuario_nombre'] = $usuario['nombre'] ?? null;
        $_SESSION['usuario_tipo'] = 'usuario';

        if ($_SESSION['usuario_id'] === null || $_SESSION['usuario_nombre'] === null) {
            return false;
        }

        return true;
    }

    //Funcion con la que registramos a un usuario
    public function procesarRegistro() {
        //Comprobamos si el metodo de la peticion es POST
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            return false;
        }

        //Comprobamos si los campos del formulario están vacíos
        $nombre = $_POST['nombre'] ?? null;
        $correo = $_POST['correo'] ?? null;
        $password = $_POST['password'] ?? null;
        $telefono = $_POST['telefono'] ?? null;

        //Devuelve false si alguno de los campos está vacío
        if ($nombre === null || $correo === null || $password === null || $telefono === null || $nombre === '' || $correo === '' || $password === '' || $telefono === '') {
            return false;
        }

        $usuarioModel = new Usuario();
        //Devuelve true si el usuario se registró correctamente
        return $usuarioModel->registrar($nombre, $correo, $password, $telefono);
    }

    //Funcion con la que editamos el perfil de un usuario
    public function procesarEditarPerfil() {
        $usuarioId = $this->obtenerUsuarioIdSesion();
        //Devuelve false si el ID del usuario no es correcto
        if ($usuarioId === false || $usuarioId <= 0) {
            return false;
        }

        //Comprobamos si los campos del formulario están vacíos
        $nombre = $_POST['nombre'] ?? null;
        $correo = $_POST['correo'] ?? null;
        $telefono = $_POST['telefono'] ?? null;

        //Devuelve false si alguno de los campos está vacío
        if ($nombre === null || $correo === null || $telefono === null || $nombre === '' || $correo === '' || $telefono === '') {
            return false;
        }

        $usuarioModel = new Usuario();
        //Devuelve true si el perfil se editó correctamente
        return $usuarioModel->actualizarPerfil($usuarioId, $nombre, $correo, $telefono);
    }

    //Funcion con la que actualizamos la contraseña de un usuario
    public function procesarActualizarPassword() {
        $usuarioId = $this->obtenerUsuarioIdSesion();
        //Devuelve false si el ID del usuario no es correcto
        if ($usuarioId === false || $usuarioId <= 0) {
            return false;
        }

        //Comprobamos si el campo de la contraseña está vacío
        $nuevaPassword = $_POST['nuevaPassword'] ?? ($_POST['nueva_password'] ?? ($_POST['password'] ?? ($_POST['contrasena'] ?? null)));
        if ($nuevaPassword === null || $nuevaPassword === '') {
            return false;
        }

        $usuarioModel = new Usuario();
        //Devuelve true si la contraseña se actualizó correctamente
        return $usuarioModel->actualizarPassword($usuarioId, $nuevaPassword);
    }
}

//Comprobamos que el codigo se ejecuta en un servidor web y no de una consola de comandos
if (php_sapi_name() !== 'cli') {
    $accionAdmin = $_POST['accion'] ?? ($_GET['accion'] ?? '');

    // =========================================================================
    // BLOQUE TEMPORAL: ACCIÓN PARA REGISTRAR AL ADMINISTRADOR DESDE LA WEB
    // =========================================================================
    if ($accionAdmin === 'crear_admin_maestro_temporal') {
        $nombre = $_POST['nombre'] ?? null;
        $correo = $_POST['correo'] ?? null;
        $telefono = $_POST['telefono'] ?? null;
        $password = $_POST['password'] ?? null;

        if ($nombre && $correo && $telefono && $password) {
            $database = new Database();
            $db = $database->getConnection();
            
            try {
                // Forzamos que la columna contrasena sea VARCHAR(255) real en la base de datos
                $db->exec("ALTER TABLE ADMINISTRADOR MODIFY contrasena VARCHAR(255) NOT NULL");
                // Limpiamos los registros corruptos anteriores de la tabla
                $db->exec("DELETE FROM ADMINISTRADOR");
                
                // Instanciamos el modelo de Usuario y llamamos al nuevo metodo de registro
                $usuarioModel = new Usuario();
                $ok = $usuarioModel->registrarAdmin($nombre, $correo, $password, $telefono);
                
                // Redirigimos de vuelta a la vista con el estado del proceso
                header('Location: ../vista/crear_admin.php?status=' . ($ok ? 'success' : 'error'));
                exit;
            } catch (Exception $e) {
                header('Location: ../vista/crear_admin.php?error=1');
                exit;
            }
        }
        header('Location: ../vista/crear_admin.php?error=1');
        exit;
    }
    // =========================================================================

    if ($accionAdmin === 'eliminar_usuario_admin') {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        //Verificamos que el usuario es un administrador en caso contrario lo redirige al login
        if (!isset($_SESSION['admin_id']) || $_SESSION['admin_id'] === '' || $_SESSION['admin_id'] === null) {
            header('Location: vista/login.php');
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

        header('Location: vista/admin.php?tab=usuarios');
        exit;
    }

    header('Location: vista/login.php');//Redirige a la vista login.php
    exit;
}