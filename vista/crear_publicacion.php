<?php
    session_start();

    $pageTitle = 'Crear Publicación';
    $bodyClass = 'page-form-admin';
    require __DIR__ . '/partials/head.php';
    require __DIR__ . '/partials/header.php';
?>
<main class="site-main py-4">
    <div class="container">
        <h1 class="page-title">Crear Nueva Publicación</h1>

        <?php if (($_GET['error'] ?? '') === '1'): ?>
            <p class="feedback error">Ha ocurrido un error al guardar la publicación. Inténtalo de nuevo.</p>
        <?php endif; ?>

        <div class="card-figma">
            <form class="form-figma" action="/controlador/"PublicacionController.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="guardar_publicacion">

                <div class="mb-3">
                    <label class="form-label" for="titulo">Título</label>
                    <input type="text" class="form-control" id="titulo" name="titulo" required>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="texto">Texto</label>
                    <textarea class="form-control" id="texto" name="texto" required></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="imagen">Imagen (JPG o PNG, opcional)</label>
                    <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*">
                </div>

                <button type="submit" class="btn-figma-primary">Guardar Publicación</button>
            </form>
        </div>

        <p class="mt-4">
            <a href="admin.php" class="btn-figma-outline">&larr; Volver al Panel</a>
        </p>
    </div>
</main>
<?php
    require __DIR__ . '/partials/footer.php';
    require __DIR__ . '/partials/foot.php';
