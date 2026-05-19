<?php
    session_start();

    if (!isset($_SESSION['admin_id'])) {
        header('Location: login_admin.php');
        exit;
    }

    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if ($id <= 0) {
        header('Location: admin.php?tab=usuarios&error=1');
        exit;
    }

    require_once __DIR__ . '/../modelo/Usuario.php';
    $usuarioModel = new Usuario();
    $usuario = $usuarioModel->obtenerPorId($id);

    if ($usuario === false) {
        header('Location: admin.php?tab=usuarios&error=1');
        exit;
    }

    $nombre = $usuario['nombre'] ?? '';
    $correo = $usuario['correo'] ?? '';
    $editOk = (($_GET['edit'] ?? '') === 'ok');
    $passwordOk = (($_GET['password'] ?? '') === 'ok');
    $error = (($_GET['error'] ?? '') === '1');

    $pageTitle = 'Editar usuario';
    $bodyClass = 'page-form-admin';
    require __DIR__ . '/partials/head.php';
    require __DIR__ . '/partials/header.php';
?>
<main class="site-main py-4">
    <div class="container">
        <h1 class="page-title">Editar usuario</h1>

        <?php if ($editOk): ?>
            <p class="feedback ok">Datos actualizados correctamente.</p>
        <?php endif; ?>

        <?php if ($passwordOk): ?>
            <p class="feedback ok">Contraseña actualizada correctamente.</p>
        <?php endif; ?>

        <?php if ($error): ?>
            <p class="feedback error">Ha ocurrido un error. Inténtalo de nuevo.</p>
        <?php endif; ?>

        <div class="card-figma mb-4">
            <form class="form-figma" method="post" action="/controlador/"UsuarioController.php">
                <input type="hidden" name="accion" value="editar_usuario_admin">
                <input type="hidden" name="usuario_id" value="<?php echo (int) $id; ?>">
                <div class="mb-3">
                    <label class="form-label" for="nombre">Nombre</label>
                    <input type="text" class="form-control" name="nombre" id="nombre" value="<?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="mb-4">
                    <label class="form-label" for="correo">Correo</label>
                    <input type="email" class="form-control" name="correo" id="correo" value="<?php echo htmlspecialchars($correo, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <button type="submit" class="btn-figma-primary">Guardar datos</button>
            </form>
        </div>

        <div class="card-figma">
            <h2 class="h5 mb-3">Cambiar contraseña</h2>
            <form class="form-figma" method="post" action="/controlador/"UsuarioController.php">
                <input type="hidden" name="accion" value="cambiar_password_usuario_admin">
                <input type="hidden" name="usuario_id" value="<?php echo (int) $id; ?>">
                <div class="mb-4">
                    <label class="form-label" for="nuevaPassword">Nueva contraseña</label>
                    <input type="password" class="form-control" name="nuevaPassword" id="nuevaPassword" required autocomplete="new-password">
                </div>
                <button type="submit" class="btn-figma-primary">Actualizar contraseña</button>
            </form>
        </div>

        <p class="mt-4">
            <a href="admin.php?tab=usuarios" class="btn-figma-outline">&larr; Volver al panel</a>
        </p>
    </div>
</main>
<?php
    require __DIR__ . '/partials/footer.php';
    require __DIR__ . '/partials/foot.php';
