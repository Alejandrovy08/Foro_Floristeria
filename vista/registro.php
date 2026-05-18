<?php
$mensaje = '';

if (($_GET['error'] ?? '') === '1') {
    $mensaje = 'No se pudo completar el registro. Verifica los datos o usa otro correo.';
}

$pageTitle = 'Registro';
$bodyClass = 'page-auth';
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/header.php';
?>
<main class="site-main py-4">
    <div class="auth-wrap">
        <div class="auth-card">
            <h1 class="auth-title text-center">Crear cuenta</h1>

            <?php if ($mensaje !== ''): ?>
                <p class="feedback error"><?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>

            <form class="form-figma" method="post" action="../controlador/UsuarioController.php">
                <input type="hidden" name="accion" value="registrar">

                <div class="mb-3">
                    <label class="form-label" for="nombre">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="correo">Correo</label>
                    <input type="email" class="form-control" id="correo" name="correo" required>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="password">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <button type="submit" class="btn-figma-primary w-100">Registrarse</button>
            </form>

            <p class="text-center mt-4 mb-0"><a href="login.php">Ya tengo cuenta</a></p>
        </div>
    </div>
</main>
<?php
require __DIR__ . '/partials/footer.php';
require __DIR__ . '/partials/foot.php';
