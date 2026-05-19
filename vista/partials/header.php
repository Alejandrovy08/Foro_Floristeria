<?php
    /*
    Plantilla para la barra de navegación Superior la cual se encarga de:
    1-Renderizado del logotipo
    2-Control de las sesiones(Usuario/Admin/No registrado)
    3-Implementacion del menu hamburguesa responsive de bootstrap
     */
    if (!empty($hideSiteChrome)) {
        return;
    }
    // Con la barra inicial / aseguramos que busque desde la raíz pública de Vercel
    $logoUrl = '/assets/img/output-onlinepngtools.png'; 
?>
<header class="site-header">
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid px-3 px-lg-4">
            <a class="navbar-brand" href="/vista/home.php">
                <img src="<?php echo htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Floristería Yerga" width="197" height="52">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navPrincipal" aria-controls="navPrincipal" aria-expanded="false" aria-label="Abrir menú">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navPrincipal">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center gap-lg-1">
                    <li class="nav-item"><a class="nav-link" href="/vista/home.php">Inicio</a></li>
                    <?php if (!empty($_SESSION['usuario_id']) || !empty($_SESSION['admin_id'])): ?>
                        <?php
                        // Aquí está la línea corregida que acepta tanto 'admin' como 'administrador'
                        $esAdminNav = !empty($_SESSION['admin_id']) || (($_SESSION['usuario_tipo'] ?? '') === 'admin') || (($_SESSION['usuario_tipo'] ?? '') === 'administrador');
                        $enlacePerfilNav = $esAdminNav ? '/vista/admin.php' : '/vista/perfil.php';
                        $enlaceChatNav = $esAdminNav ? '/vista/admin_chats.php' : '/vista/chat.php';
                        ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo $enlaceChatNav; ?>">Chat</a></li>
                        <?php if ($esAdminNav): ?>
                            <li class="nav-item"><a class="nav-link" href="/vista/admin.php">Administración</a></li>
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo $enlacePerfilNav; ?>">Perfil</a></li>
                        <li class="nav-item"><a class="nav-link" href="/controlador/LogoutController.php">Cerrar sesión</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="/vista/login.php">Iniciar sesión</a></li>
                        <li class="nav-item"><a class="nav-link" href="/vista/registro.php">Registrarse</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>