<?php
    session_start();
    require_once __DIR__ . '/../modelo/Publicacion.php';

    if (!isset($_SESSION['admin_id'])) {
        header('Location: login_admin.php');
        exit;
    }

    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if ($id <= 0) {
        header('Location: admin.php?error=1');
        exit;
    }

    $publicacionModel = new Publicacion();
    $publicacion = $publicacionModel->obtenerPorId($id);

    if (!$publicacion) {
        header('Location: admin.php?error=1');
        exit;
    }

    $imagenes = $publicacionModel->obtenerImagenes($id);

    $pageTitle = 'Editar Publicación';
    $bodyClass = 'page-form-admin';
    require __DIR__ . '/partials/head.php';
    require __DIR__ . '/partials/header.php';
?>
<main class="site-main py-4">
    <div class="container">
        <h1 class="page-title">Editar Publicación</h1>

        <?php if (($_GET['error'] ?? '') === '1'): ?>
            <p class="feedback error">Ha ocurrido un error al actualizar la publicación o con la imagen. Inténtalo de nuevo.</p>
        <?php endif; ?>

        <?php if (($_GET['guardado'] ?? '') === '1'): ?>
            <p class="feedback ok">Cambios guardados correctamente.</p>
        <?php endif; ?>

        <h2 class="form-section-title">Imágenes actuales</h2>
        <div class="lista-imagenes mb-4">
            <?php if (empty($imagenes)): ?>
                <p class="text-muted">Esta publicación no tiene imágenes.</p>
            <?php else: ?>
                <div class="img-upload-grid">
                    <?php foreach ($imagenes as $img): ?>
                        <?php
                        $imgId = (int) ($img['id'] ?? 0);
                        $rutaRaw = trim((string) ($img['ruta_archivo'] ?? ''));
                        $srcThumb = $rutaRaw !== '' ? '../' . htmlspecialchars($rutaRaw, ENT_QUOTES, 'UTF-8') : '';
                        ?>
                        <div class="fila-imagen img-slot" style="margin-right: 15px; margin-bottom: 15px; height: auto; min-height: 175px; display: inline-flex; flex-direction: column; align-items: center; justify-content: flex-start;">
                        <?php if ($srcThumb !== ''): ?>
                            <div style="width: 131px; height: 131px; overflow: hidden; border-radius: 10px; border: 2px dashed #6be4ff; background: #e8f7fc;">
                                <img src="<?php echo $srcThumb; ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        <?php endif; ?>
                            <form method="post" action="/controlador/PublicacionController.php" class="mt-2">
                                <input type="hidden" name="accion" value="eliminar_imagen">
                                <input type="hidden" name="imagen_id" value="<?php echo $imgId; ?>">
                                <input type="hidden" name="publicacion_id" value="<?php echo (int) $id; ?>">
                                <button type="submit" class="btn-figma-danger btn-sm" onclick="return confirm('¿Eliminar esta imagen?');">Eliminar</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <h2 class="form-section-title">Datos y nueva imagen</h2>
        <div class="card-figma">
            <form class="form-figma" action="/controlador/PublicacionController.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="editar_publicacion">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars((string) $publicacion['id'], ENT_QUOTES, 'UTF-8'); ?>">

                <div class="mb-3">
                    <label class="form-label" for="titulo">Título</label>
                    <input
                        type="text"
                        class="form-control"
                        id="titulo"
                        name="titulo"
                        value="<?php echo htmlspecialchars($publicacion['titulo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label" for="texto">Texto</label>
                    <textarea class="form-control" id="texto" name="texto" required><?php echo htmlspecialchars($publicacion['texto'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="imagen">Añadir imagen (JPG o PNG, opcional)</label>
                    <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*">
                </div>

                <button type="submit" class="btn-figma-primary">Guardar cambios</button>
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
