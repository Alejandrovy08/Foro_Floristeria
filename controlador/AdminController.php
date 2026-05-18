<?php
require_once __DIR__ . '/../modelo/Administrador.php';

class AdminController {
    public function procesarLogin(): bool {
        //Comprueba si la sesión está activa
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        //Comprueba si el método de la petición es POST
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            return false;
        }
        
        $correo = $_POST['correo'] ?? ($_POST['email'] ?? null);
        $password = $_POST['password'] ?? ($_POST['contrasena'] ?? null);

        //Comprueba si el correo o la contraseña son nulos o vacíos
        if ($correo === null || $password === null || $correo === '' || $password === '') {
            return false;
        }

        $adminModel = new Administrador();
        $admin = $adminModel->login($correo, $password);
        //Comprueba si el administrador no existe
        if ($admin === false) {
            return false;
        }

        $_SESSION['admin_id'] = $admin['id'] ?? null;
        $_SESSION['admin_nombre'] = $admin['nombre'] ?? null;
        $_SESSION['usuario_tipo'] = 'admin';

        if ($_SESSION['admin_id'] === null || $_SESSION['admin_nombre'] === null) {
            return false;
        }

        return true;
    }
}

//Comprueba si el script se está ejecutando desde la línea de comandos o desde un navegador
if (php_sapi_name() !== 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $controller = new AdminController();
    //Comprueba si el método de la petición es POST
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $accion = $_POST['accion'] ?? '';
        //Comprueba si la acción es login_admin
        if ($accion === 'login_admin') {
            $ok = $controller->procesarLogin();
            header('Location: ' . ($ok ? '../vista/admin.php' : '../vista/login_admin.php?error=1'));
            exit;
        } elseif ($accion === 'editar_perfil_admin') {
            //Verificamos si la sesion esta activa
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }

            if (!isset($_SESSION['admin_id']) || $_SESSION['admin_id'] === '' || $_SESSION['admin_id'] === null) {
                header('Location: ../vista/login_admin.php');
                exit;
            }

            $nombre = $_POST['nombre'] ?? null;
            $correo = $_POST['correo'] ?? ($_POST['email'] ?? null);
            $telefono = $_POST['telefono'] ?? null;

            //Validamos si los datos introducidos no estan vacios
            if ($nombre === null || $correo === null || $telefono === null || $nombre === '' || $correo === '' || $telefono === '') {
                header('Location: ../vista/admin.php?tab=perfil&error=1');
                exit;
            }

            $adminModel = new Administrador();
            $adminId = (int) $_SESSION['admin_id'];
            $ok = $adminModel->actualizarDatos($adminId, $nombre, $correo, $telefono);

            //Actualizamos la sesion
            if ($ok) {
                $_SESSION['admin_nombre'] = $nombre;
            }
            //Redirigimos al usuario al panel de administracion
            header('Location: ' . ($ok ? '../vista/admin.php?tab=perfil&status=success' : '../vista/admin.php?tab=perfil&error=1'));
            exit;
            //Comprobamos que la accion es la de cambiar la contraseña
        } elseif ($accion === 'cambiar_password_admin') {
            //Verificamos si la sesion esta activa
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            //Verificamos que el usuario activo es un administrador en caso contrario lo manda al login
            if (!isset($_SESSION['admin_id']) || $_SESSION['admin_id'] === '' || $_SESSION['admin_id'] === null) {
                header('Location: ../vista/login_admin.php');
                exit;
            }
            //Recibimos la nueva contraseña y si dicha contraseña NO existe le asignamos null
            $nuevaPassword = $_POST['nuevaPassword'] ?? ($_POST['nueva_password'] ?? ($_POST['password'] ?? ($_POST['contrasena'] ?? null)));

            //Comprobamos que la contraseña introducida no esta vacia
            if ($nuevaPassword === null || $nuevaPassword === '') {
                header('Location: ../vista/admin.php?tab=perfil&error=1');
                exit;
            }
            
            $adminModel = new Administrador();
            $adminId = (int) $_SESSION['admin_id'];
            $ok = $adminModel->actualizarPassword($adminId, $nuevaPassword);
            //Redirigimos al usuario al panel con parametro de exito o de error 
            header('Location: ' . ($ok ? '../vista/admin.php?tab=perfil&status=success' : '../vista/admin.php?tab=perfil&error=1'));
            exit;
        }
    }

    header('Location: ../vista/login_admin.php');
    exit;
}

?>
