<?php
    session_start();
    require_once __DIR__ . '/../modelo/Publicacion.php';

    $modeloPublicacion = new Publicacion();
    $publicaciones = $modeloPublicacion->listarTodas();

    $pageTitle = 'Inicio';
    $bodyClass = 'page-home';
    require __DIR__ . '/partials/head.php';
    require __DIR__ . '/partials/header.php';
?>
<main class="site-main py-4">
    <div class="container">
        <div class="card-figma">
            <?php if (!empty($_SESSION['usuario_id']) || !empty($_SESSION['admin_id'])): ?>
                <?php
                $nombre = $_SESSION['usuario_nombre'] ?? '';
                $nombreSeguro = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
                $usuarioTipo = $_SESSION['usuario_tipo'] ?? '';
                $nombreMostrado = $nombreSeguro !== '' ? $nombreSeguro : 'usuario';
                $esAdmin = !empty($_SESSION['admin_id']) || $usuarioTipo === 'administrador';
                $enlacePerfil = $esAdmin ? 'admin.php' : 'perfil.php';
                $enlaceChat = $esAdmin ? 'admin_chats.php' : 'chat.php';
                ?>

                <h1 class="hero-welcome">
                    Bienvenido,
                    <a href="<?php echo $enlacePerfil; ?>"><?php echo $nombreMostrado; ?></a>
                </h1>


            

            <?php endif; ?>

            <section class="publicaciones-recientes">
                <h2 class="h4 mb-3">Publicaciones Recientes</h2>

                <?php if (empty($publicaciones)): ?>
                    <p class="text-muted mb-0">No hay publicaciones disponibles en este momento.</p>
                <?php else: ?>
                    <?php foreach ($publicaciones as $publicacion): ?>
                        <?php
                        $idPublicacion = (int)($publicacion['id'] ?? 0);
                        $titulo = htmlspecialchars($publicacion['titulo'] ?? 'Sin título', ENT_QUOTES, 'UTF-8');
                        $texto = nl2br(htmlspecialchars($publicacion['texto'] ?? '', ENT_QUOTES, 'UTF-8'));
                        $autor = htmlspecialchars($publicacion['nombre_autor'] ?? 'Desconocido', ENT_QUOTES, 'UTF-8');
                        $fecha = htmlspecialchars($publicacion['fecha_publicacion'] ?? ($publicacion['fecha'] ?? 'Sin fecha'), ENT_QUOTES, 'UTF-8');
                        $valoracion = htmlspecialchars((string)($publicacion['valoracion'] ?? 'Sin valoración'), ENT_QUOTES, 'UTF-8');
                        $rutaRaw = trim((string) ($publicacion['ruta_archivo'] ?? ''));
                        $urlThumb = $rutaRaw !== '' ? '../' . htmlspecialchars($rutaRaw, ENT_QUOTES, 'UTF-8') : '';
                        ?>

                        <article class="publicacion">
                            <?php if ($urlThumb !== ''): ?>
                                <div class="publicacion-thumb">
                                    <a href="detalle_publicacion.php?id=<?php echo $idPublicacion; ?>" aria-label="Ver publicación">
                                        <img src="<?php echo $urlThumb; ?>" alt="" width="120" height="80" loading="lazy">
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="publicacion-thumb publicacion-thumb--placeholder" title="Sin imagen">Sin imagen</div>
                            <?php endif; ?>
                            <div class="publicacion-body">
                                <h3 class="h5"><a href="detalle_publicacion.php?id=<?php echo $idPublicacion; ?>"><?php echo $titulo; ?></a></h3>
                                <p><?php echo $texto !== '' ? $texto : 'Sin contenido disponible.'; ?></p>
                                <p class="meta mb-0">Autor: <?php echo $autor; ?> | Fecha: <?php echo $fecha; ?> | Valoración: <?php echo $valoracion; ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </div>
    </div>
</main>
<?php
    require __DIR__ . '/partials/footer.php';
    require __DIR__ . '/partials/foot.php';
