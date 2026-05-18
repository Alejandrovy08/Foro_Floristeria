<?php
    session_start();
    if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT) || (int)$_GET['id'] <= 0) {
        header('Location: home.php');
        exit;
    }

    $publicacionId = (int)$_GET['id'];

    require_once __DIR__ . '/../modelo/Publicacion.php';
    require_once __DIR__ . '/../modelo/Comentario.php';
    $modeloPublicacion = new Publicacion();
    $modeloComentario = new Comentario();
    $publicacion = $modeloPublicacion->obtenerPorId($publicacionId);
    if (!$publicacion) {
        header('Location: home.php');
        exit;
    }
    
    //Obtenemos todas las imagenes de la publicacion
    $imagenes = $modeloPublicacion->obtenerImagenes($publicacionId);
    
    $comentarios = $modeloComentario->obtenerPorPublicacion($publicacionId);
    $usuarioLogeado = !empty($_SESSION['usuario_id']) || !empty($_SESSION['admin_id']);
    $titulo = htmlspecialchars($publicacion['titulo'] ?? 'Sin título', ENT_QUOTES, 'UTF-8');
    $texto = nl2br(htmlspecialchars($publicacion['texto'] ?? '', ENT_QUOTES, 'UTF-8'));
    $fecha = htmlspecialchars($publicacion['fecha_publicacion'] ?? ($publicacion['fecha'] ?? 'Sin fecha'), ENT_QUOTES, 'UTF-8');
    $autor = htmlspecialchars(
        $publicacion['nombre_autor'] ?? ('Autor #' . ($publicacion['autor_id'] ?? 'desconocido')),
        ENT_QUOTES,
        'UTF-8'
    );

    $pageTitle = $titulo;
    $bodyClass = 'page-detalle';
    require __DIR__ . '/partials/head.php';
    require __DIR__ . '/partials/header.php';
?>
<main class="site-main py-4">
    <div class="container">
    <article class="publicacion-detalle card-figma mb-4">
            <h1 class="h2 mb-4"><?php echo $titulo; ?></h1>
            
            <div class="row g-4 align-items-start">
                
                <?php if (!empty($imagenes)): ?>
                    <div class="col-12 col-md-5 col-lg-4">
                        <div style="max-width: 100%; width: 100%; display: flex; justify-content: flex-start; mb-3;">
                            <?php if (count($imagenes) === 1): ?>
                                <?php $srcImg = '../' . htmlspecialchars(trim($imagenes[0]['ruta_archivo']), ENT_QUOTES, 'UTF-8'); ?>
                                <div style="width: 100%; height: 350px; overflow: hidden; border-radius: 15px; border: 3px solid #6be4ff; box-shadow: 0 4px 12px rgba(26,26,26,0.08); background: #f7f9fa;">
                                    <img src="<?php echo $srcImg; ?>" alt="" style="width: 100%; height: 100%; object-fit: contain;">
                                </div>
                            <?php else: ?>
                                <div id="carruselPublicacion" class="carousel slide" data-bs-ride="carousel" style="width: 100%; border-radius: 15px; border: 3px solid #6be4ff; overflow: hidden; box-shadow: 0 4px 12px rgba(26,26,26,0.08);">
                                    <div class="carousel-indicators">
                                        <?php foreach ($imagenes as $index => $img): ?>
                                            <button type="button" data-bs-target="#carruselPublicacion" data-bs-slide-to="<?php echo $index; ?>" class="<?php echo $index === 0 ? 'active' : ''; ?>" aria-current="<?php echo $index === 0 ? 'true' : ''; ?>" aria-label="Slide <?php echo $index + 1; ?>"></button>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="carousel-inner" style="height: 350px; background: #f7f9fa;">
                                        <?php foreach ($imagenes as $index => $img): ?>
                                            <?php $srcImg = '../' . htmlspecialchars(trim($img['ruta_archivo']), ENT_QUOTES, 'UTF-8'); ?>
                                            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>" style="height: 100%;">
                                                <img src="<?php echo $srcImg; ?>" class="d-block w-100" alt="" style="width: 100%; height: 100%; object-fit: contain;">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#carruselPublicacion" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true" style="filter: drop-shadow(0px 2px 4px rgba(0,0,0,0.5));"></span>
                                        <span class="visually-hidden">Anterior</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#carruselPublicacion" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true" style="filter: drop-shadow(0px 2px 4px rgba(0,0,0,0.5));"></span>
                                        <span class="visually-hidden">Siguiente</span>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="meta mt-3 pt-2" style="border-top: 1px solid rgba(107, 228, 255, 0.25) !important;">
                            <p class="mb-0" style="color: #666; font-size: 0.9rem; line-height: 1.4;">
                                <strong>Autor:</strong> <?php echo $autor; ?><br>
                                <strong>Fecha:</strong> <?php echo $fecha; ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="col-12 <?php echo !empty($imagenes) ? 'col-md-7 col-lg-8' : ''; ?>">
                    <div class="cuerpo-texto">
                        <p style="font-size: 1.1rem; line-height: 1.6; color: #1a1a1a; margin-bottom: 0; text-align: justify;">
                            <?php echo $texto !== '' ? $texto : 'Sin contenido disponible.'; ?>
                        </p>
                    </div>
                </div>

            </div> </article>

        <section class="nuevo-comentario card-figma mb-4">
            <h2 class="h4 mb-3">Deja tu comentario</h2>
            <?php if ($usuarioLogeado): ?>
                <form class="form-figma" action="../controlador/ComentarioController.php" method="POST">
                    <input type="hidden" name="publicacion_id" value="<?php echo $publicacionId; ?>">
                    <div class="mb-3">
                        <label class="form-label" for="texto-comentario">Comentario</label>
                        <textarea class="form-control" id="texto-comentario" name="texto" rows="4" placeholder="Escribe tu comentario..." required></textarea>
                    </div>
                    <button type="submit" class="btn-figma-primary">Publicar comentario</button>
                </form>
            <?php else: ?>
                <p class="mb-0">Debes iniciar sesión para comentar. <a href="login.php">Iniciar sesión</a></p>
            <?php endif; ?>
        </section>

        <section class="comentarios">
            <h2 class="h4 mb-3">Comentarios</h2>

            <?php if (empty($comentarios)): ?>
                <p class="text-muted">Aún no hay comentarios.</p>
            <?php else: ?>
                <?php foreach ($comentarios as $comentario): ?>
                    <?php
                    $nombreUsuario = htmlspecialchars($comentario['nombre_usuario'] ?? 'Usuario desconocido', ENT_QUOTES, 'UTF-8');
                    $textoComentario = nl2br(htmlspecialchars($comentario['texto'] ?? '', ENT_QUOTES, 'UTF-8'));
                    $fechaComentario = htmlspecialchars($comentario['fecha_publicacion'] ?? ($comentario['fecha'] ?? 'Sin fecha'), ENT_QUOTES, 'UTF-8');
                    ?>
                    <article class="comentario">
                        <p class="mb-1"><strong><?php echo $nombreUsuario; ?></strong></p>
                        <p class="mb-1"><?php echo $textoComentario !== '' ? $textoComentario : 'Comentario sin contenido.'; ?></p>
                        <p class="meta mb-0">Fecha: <?php echo $fechaComentario; ?></p>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <p class="mt-4">
            <a href="home.php" class="btn-figma-outline">&larr; Volver al Inicio</a>
        </p>
    </div>
</main>
<?php
    require __DIR__ . '/partials/footer.php';
    require __DIR__ . '/partials/foot.php';
?>