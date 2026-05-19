<?php
    session_start();

    if (empty($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit;
    }

    require_once __DIR__ . '/../modelo/Usuario.php';

    $usuarioModel = new Usuario();
    $usuarioId = (int) $_SESSION['usuario_id'];
    $usuario = $usuarioModel->obtenerPorId($usuarioId);

    if ($usuario === false) {
        header('Location: home.php?error=1');
        exit;
    }

    $nombre = $usuario['nombre'] ?? '';
    $correo = $usuario['correo'] ?? '';

    $editOk = (($_GET['edit'] ?? '') === 'ok');
    $error = (($_GET['error'] ?? '') === '1');

    $pageTitle = 'Mi perfil';
    $bodyClass = 'page-perfil';
    require __DIR__ . '/partials/head.php';
    require __DIR__ . '/partials/header.php';
?>
<main class="site-main py-4">
    <div class="container">
        <h1 class="page-title text-center">Mi perfil</h1>

        <?php if ($editOk): ?>
            <div class="feedback ok">Datos actualizados correctamente.</div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="feedback error">Ha ocurrido un error. Inténtalo de nuevo.</div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
                <div class="perfil-avatar-wrap">
                    <img src="../assets/img/avatar-default.svg" alt="Avatar de usuario" width="180" height="180">
                </div>

                <div class="perfil-panel mb-4">
                    <h2 class="h4 mb-3">Datos personales</h2>
                    <form class="form-figma" method="post" action="/controlador/"UsuarioController.php">
                        <input type="hidden" name="accion" value="editar_perfil">

                        <div class="mb-3">
                            <label class="form-label" for="nombre">Nombre</label>
                            <input
                                type="text"
                                class="form-control"
                                id="nombre"
                                name="nombre"
                                value="<?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?>"
                                required
                            >
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="correo">Correo</label>
                            <input
                                type="email"
                                class="form-control"
                                id="correo"
                                name="correo"
                                value="<?php echo htmlspecialchars($correo, ENT_QUOTES, 'UTF-8'); ?>"
                                required
                            >
                        </div>

                        <button class="btn-figma-primary" type="submit">Guardar cambios</button>
                    </form>
                </div>

                <div class="perfil-panel mb-4">
                    <h2 class="h4 mb-3">Cambiar contraseña</h2>
                    <form class="form-figma" method="post" action="/controlador/"UsuarioController.php">
                        <input type="hidden" name="accion" value="cambiar_password">

                        <div class="mb-4">
                            <label class="form-label" for="nuevaPassword">Nueva contraseña</label>
                            <input type="password" class="form-control" id="nuevaPassword" name="nuevaPassword" required>
                        </div>

                        <button class="btn-figma-outline" type="submit">Actualizar contraseña</button>
                    </form>
                </div>

                <div class="perfil-panel">
                    <h2 class="h4 mb-3">Zona de baja</h2>
                    <form method="post" action="/controlador/"UsuarioController.php">
                        <input type="hidden" name="accion" value="baja_usuario">
                        <button class="btn-figma-danger" type="submit">Eliminar mi cuenta</button>
                    </form>
                </div>

                <p class="text-center mt-4">
                    <a href="home.php" class="btn-figma-outline">&larr; Volver al inicio</a>
                </p>
            </div>
        </div>
    </div>
</main>
<?php
    require __DIR__ . '/partials/footer.php';
    require __DIR__ . '/partials/foot.php';
