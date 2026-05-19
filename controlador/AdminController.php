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

    //Funcion con la que editamos el perfil de un administrador
    public function procesarEditarPerfil() {
        //Verificamos si la sesion esta activa
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        //Verificamos que el usuario activo es un administrador en caso contrario lo manda al login
        if (!isset($_SESSION['admin_id']) || $_SESSION['admin_id'] === '' || $_SESSION['admin_id'] === null) {
            header('Location: /vista/login_admin.php');
            exit;
        }

        //Comprobamos que los campos requeridos no estan vacios
        $nombre = $_POST['nombre'] ?? null;
        $correo = $_POST['correo'] ?? null;
        $telefono = $_POST['telefono'] ?? null;

        if ($nombre === null || $correo === null || $telefono === null || $nombre === '' || $correo === '' || $telefono === '') {
            header('Location: /vista/admin.php?tab=perfil&error=1');
            exit;
        }

        $adminModel = new Administrador();
        $adminId = (int) $_SESSION['admin_id'];
        $ok = $adminModel->actualizarPerfil($adminId, $nombre, $correo, $telefono);
        //Redirigimos al usuario al panel con parametro de exito o de error 
        header('Location: ' . ($ok ? '/vista/admin.php?tab=perfil&status=success' : '/vista/admin.php?tab=perfil&error=1'));
        exit;
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
            // CORRECCIÓN: Forzamos la redirección absoluta a la raíz de la vista publica
            header('Location: ' . ($ok ? '/vista/admin.php' : '/vista/login_admin.php?error=1'));
            exit;
            
        } elseif ($accion === 'crear_admin_maestro_temporal') {
            $nombre = $_POST['nombre'] ?? null;
            $correo = $_POST['correo'] ?? null;
            $telefono = $_POST['telefono'] ?? null;
            $password = $_POST['password'] ?? null;

            if ($nombre && $correo && $telefono && $password) {
                $database = new Database();
                $db = $database->getConnection();
                
                try {
                    $db->exec("ALTER TABLE ADMINISTRADOR MODIFY contrasena VARCHAR(255) NOT NULL");
                    $db->exec("DELETE FROM ADMINISTRADOR");
                    
                    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                    
                    $stmt = $db->prepare("INSERT INTO ADMINISTRADOR (nombre, correo, contrasena, telefono) VALUES (?, ?, ?, ?)");
                    $ok = $stmt->execute([$nombre, $correo, $passwordHash, $telefono]);
                    
                    header('Location: /vista/crear_admin.php?status=' . ($ok ? 'success' : 'error'));
                    exit;
                } catch (Exception $e) {
                    header('Location: /vista/crear_admin.php?error=1');
                    exit;
                }
            }
            header('Location: /vista/crear_admin.php?error=1');
            exit;

        } elseif ($accion === 'editar_perfil_admin') {
            $controller->procesarEditarPerfil();
            exit;
            
        } elseif ($accion === 'cambiar_password_admin') {
            //Verificamos si la sesion esta activa
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            //Verificamos que el usuario activo es un administrador en caso contrario lo manda al login
            if (!isset($_SESSION['admin_id']) || $_SESSION['admin_id'] === '' || $_SESSION['admin_id'] === null) {
                header('Location: /vista/login_admin.php');
                exit;
            }
            
            $nuevaPassword = $_POST['nuevaPassword'] ?? ($_POST['nueva_password'] ?? ($_POST['password'] ?? ($_POST['contrasena'] ?? null)));

            if ($nuevaPassword === null || $nuevaPassword === '') {
                header('Location: /vista/admin.php?tab=perfil&error=1');
                exit;
            }
            
            $adminModel = new Administrador();
            $adminId = (int) $_SESSION['admin_id'];
            $ok = $adminModel->actualizarPassword($adminId, $nuevaPassword);
            header('Location: ' . ($ok ? '/vista/admin.php?tab=perfil&status=success' : '/vista/admin.php?tab=perfil&error=1'));
            exit;
        }
    }

    header('Location: /vista/login_admin.php');
    exit;
}