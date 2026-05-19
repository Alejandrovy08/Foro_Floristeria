<?php
    session_start();
    
    //Cargamos los modelos de los usuarios para hacer uso de las funciones de los usuarios
    require_once __DIR__ . '/../modelo/Usuario.php';
    $usuarioModel = new Usuario();
    $usuariosRaw = $usuarioModel->listarTodos();
    $usuarios = is_array($usuariosRaw) ? $usuariosRaw : [];

    //Comprobamos si el usuario esta logeado y es administrador
    if (!isset($_SESSION['admin_id'])) {
        header('Location: login_admin.php');
        exit;
    }

    //Cargamos el modelo de las publicaciones para hacer uso de las funciones de las publicaciones
    require_once __DIR__ . '/../modelo/Publicacion.php';
    $publicacionModel = new Publicacion();
    $publicaciones = $publicacionModel->listarTodas();

    //Cargamos el modelo de los administradores para hacer uso de las funciones de los administradores
    require_once __DIR__ . '/../modelo/Administrador.php';
    $administradorModel = new Administrador();
    $datosAdminRaw = $administradorModel->obtenerPorId((int) $_SESSION['admin_id']);
    $datosAdmin = is_array($datosAdminRaw) ? $datosAdminRaw : ['nombre' => '', 'correo' => '', 'telefono' => ''];

    //Cargamos el modelo de los mensajes para hacer uso de las funciones de los mensajes
    require_once __DIR__ . '/../modelo/Mensaje.php';
    $mensajeModel = new Mensaje();
    $chatsActivosRaw = $mensajeModel->listarConversaciones();
    $chatsActivos = is_array($chatsActivosRaw) ? $chatsActivosRaw : [];

    $tabGet = $_GET['tab'] ?? 'publicaciones';
    $tabsValidas = ['publicaciones', 'usuarios', 'mensajes', 'perfil'];
    $tabActiva = in_array($tabGet, $tabsValidas, true) ? $tabGet : 'publicaciones';
    $nombreAdmin = htmlspecialchars($datosAdmin['nombre'] ?? 'Administrador', ENT_QUOTES, 'UTF-8');

    $pageTitle = 'Panel de administración';
    $bodyClass = 'page-admin';
    require __DIR__ . '/partials/head.php';
    require __DIR__ . '/partials/header.php';
?>
<main class="site-main py-4">
    <div class="container admin-container">
        <p class="admin-greeting">Buenas Administrador <?php echo $nombreAdmin; ?> ¿Qué vas a hacer hoy?</p>
        <div class="tabs" role="tablist" aria-label="Pestañas de administración">
            <button type="button" class="tab-btn" role="tab" id="tab-publicaciones" aria-controls="panel-publicaciones" aria-selected="<?php echo $tabActiva === 'publicaciones' ? 'true' : 'false'; ?>" data-tab="publicaciones">Gestionar publicaciones</button>
            <button type="button" class="tab-btn" role="tab" id="tab-usuarios" aria-controls="panel-usuarios" aria-selected="<?php echo $tabActiva === 'usuarios' ? 'true' : 'false'; ?>" data-tab="usuarios">Gestionar usuarios</button>
            <button type="button" class="tab-btn" role="tab" id="tab-mensajes" aria-controls="panel-mensajes" aria-selected="<?php echo $tabActiva === 'mensajes' ? 'true' : 'false'; ?>" data-tab="mensajes">Gestionar Mensajes</button>
            <button type="button" class="tab-btn" role="tab" id="tab-perfil" aria-controls="panel-perfil" aria-selected="<?php echo $tabActiva === 'perfil' ? 'true' : 'false'; ?>" data-tab="perfil">Editar Perfil</button>
        </div>

        <div id="panel-publicaciones" class="tab-content tab-panel" role="tabpanel" aria-labelledby="tab-publicaciones" <?php echo $tabActiva !== 'publicaciones' ? 'hidden' : ''; ?>>
            <a href="crear_publicacion.php" class="btn-crear-pub">Crear Nueva Publicación</a>

            <?php if (!empty($publicaciones)): ?>
                <div class="admin-table-wrap">
                    <table class="admin-table-cards">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Fecha de publicación</th>
                                <th>Autor</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($publicaciones as $p): ?>
                                <tr>
                                    <td data-label="ID"><?php echo htmlspecialchars($p['id']); ?></td>
                                    <td data-label="Título"><?php echo htmlspecialchars($p['titulo']); ?></td>
                                    <td data-label="Fecha"><?php echo htmlspecialchars($p['fecha_publicacion']); ?></td>
                                    <td data-label="Autor"><?php echo htmlspecialchars($p['nombre_autor']); ?></td>
                                    <td class="admin-actions-cell" data-label="Acciones">
                                        <div class="admin-actions">
                                            <a class="btn-view" href="detalle_publicacion.php?id=<?php echo (int) $p['id']; ?>">Ver publicación</a>
                                            <a class="btn-edit" href="editar_publicacion.php?id=<?php echo (int) $p['id']; ?>">Editar</a>
                                            <a class="btn-delete" href="../controlador/PublicacionController.php?accion=eliminar_publicacion&id=<?php echo (int) $p['id']; ?>" onclick="return confirm('¿Seguro?')">Eliminar</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0">Sin publicaciones</p>
            <?php endif; ?>
        </div>

        <div id="panel-usuarios" class="tab-content tab-panel" role="tabpanel" aria-labelledby="tab-usuarios" <?php echo $tabActiva !== 'usuarios' ? 'hidden' : ''; ?>>
            <?php if (!empty($usuarios)): ?>
                <div class="admin-table-wrap">
                    <table class="admin-table-cards">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $u): ?>
                                <tr>
                                    <td data-label="ID"><?php echo htmlspecialchars($u['id']); ?></td>
                                    <td data-label="Nombre"><?php echo htmlspecialchars($u['nombre']); ?></td>
                                    <td data-label="Correo"><?php echo htmlspecialchars($u['correo']); ?></td>
                                    <td class="admin-actions-cell" data-label="Acciones">
                                        <div class="admin-actions">
                                            <a class="btn-edit" href="editar_usuario.php?id=<?php echo (int) $u['id']; ?>">Editar</a>
                                            <a class="btn-delete" href="../controlador/UsuarioController.php?accion=eliminar_usuario_admin&amp;id=<?php echo (int) $u['id']; ?>"
                                               onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">Eliminar</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0">No hay usuarios registrados.</p>
            <?php endif; ?>
        </div>

        <div id="panel-mensajes" class="tab-content tab-panel" role="tabpanel" aria-labelledby="tab-mensajes" <?php echo $tabActiva !== 'mensajes' ? 'hidden' : ''; ?>>
            <h2 class="h4 mb-3">Mensajes de soporte</h2>
            <?php if (!empty($chatsActivos)): ?>
                <div class="admin-table-wrap">
                    <table class="admin-table-cards">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Último mensaje</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($chatsActivos as $chat): ?>
                                <?php
                                $uidChat = (int) ($chat['usuario_id'] ?? 0);
                                $nombreChat = htmlspecialchars($chat['nombre'] ?? 'Usuario', ENT_QUOTES, 'UTF-8');
                                $ultimoRaw = trim((string) ($chat['ultimo_mensaje'] ?? ''));
                                if (function_exists('mb_strlen') && function_exists('mb_substr')) {
                                    $ultimoTrunc = (mb_strlen($ultimoRaw) > 60)
                                        ? mb_substr($ultimoRaw, 0, 60) . '…'
                                        : $ultimoRaw;
                                } else {
                                    $ultimoTrunc = (strlen($ultimoRaw) > 60)
                                        ? substr($ultimoRaw, 0, 60) . '...'
                                        : $ultimoRaw;
                                }
                                $ultimoChat = htmlspecialchars($ultimoTrunc, ENT_QUOTES, 'UTF-8');
                                $fechaChat = htmlspecialchars($chat['fecha_ultimo'] ?? '', ENT_QUOTES, 'UTF-8');
                                ?>
                                <tr>
                                    <td data-label="Usuario"><?php echo $nombreChat; ?></td>
                                    <td class="celda-mensaje" data-label="Último mensaje" title="<?php echo htmlspecialchars($ultimoRaw, ENT_QUOTES, 'UTF-8'); ?>"><?php echo $ultimoChat !== '' ? $ultimoChat : '—'; ?></td>
                                    <td data-label="Fecha"><?php echo $fechaChat !== '' ? $fechaChat : '—'; ?></td>
                                    <td class="admin-actions-cell" data-label="Acciones">
                                        <div class="admin-actions">
                                            <a class="btn-view" href="chat.php?u=<?php echo $uidChat; ?>">Ir al Chat</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0">No hay conversaciones activas.</p>
            <?php endif; ?>
        </div>

        <div id="panel-perfil" class="tab-content tab-panel" role="tabpanel" aria-labelledby="tab-perfil" <?php echo $tabActiva !== 'perfil' ? 'hidden' : ''; ?>>
            <h2 class="h4 mb-3">Mi Perfil</h2>

            <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
                <p class="feedback ok">Los cambios se guardaron correctamente.</p>
            <?php endif; ?>

            <form class="form-figma mb-4" method="post" action="/controlador/AdminController.php">
                <input type="hidden" name="accion" value="editar_perfil_admin">
                <div class="mb-3">
                    <label class="form-label" for="admin-nombre">Nombre</label>
                    <input type="text" class="form-control" name="nombre" id="admin-nombre" value="<?php echo htmlspecialchars($datosAdmin['nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="admin-correo">Correo</label>
                    <input type="email" class="form-control" name="correo" id="admin-correo" value="<?php echo htmlspecialchars($datosAdmin['correo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="admin-telefono">Teléfono</label>
                    <input type="text" class="form-control" name="telefono" id="admin-telefono" value="<?php echo htmlspecialchars($datosAdmin['telefono'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div>
                    <button type="submit" class="btn-figma-primary">Guardar datos</button>
                </div>
            </form>

            <h3 class="h5 mb-3">Cambiar Contraseña</h3>
            <form class="form-figma" method="post" action="/controlador/AdminController.php">
                <input type="hidden" name="accion" value="cambiar_password_admin">
                <div class="mb-3">
                    <label class="form-label" for="admin-nueva-password">Nueva contraseña</label>
                    <input type="password" class="form-control" name="nuevaPassword" id="admin-nueva-password" required autocomplete="new-password">
                </div>
                <div>
                    <button type="submit" class="btn-figma-primary">Actualizar contraseña</button>
                </div>
            </form>
        </div>

        <div class="admin-footer-links">
            <a href="home.php" class="btn-figma-outline">&larr; Volver a la página de inicio</a>
            <a href="../controlador/LogoutController.php" class="btn-figma-outline">Cerrar sesión</a>
        </div>
    </div>

    <!--Script que muestra cada panel de administración al pulsar cada boton-->
    <script>
        (function () {
            var tabs = document.querySelectorAll('.tab-btn');
            var panels = {
                publicaciones: document.getElementById('panel-publicaciones'),
                usuarios: document.getElementById('panel-usuarios'),
                mensajes: document.getElementById('panel-mensajes'),
                perfil: document.getElementById('panel-perfil')
            };

            function activarTab(nombre) {
                Object.keys(panels).forEach(function (key) {
                    var show = key === nombre;
                    if (panels[key]) {
                        panels[key].hidden = !show;
                    }
                });
                tabs.forEach(function (btn) {
                    var sel = btn.getAttribute('data-tab') === nombre;
                    btn.setAttribute('aria-selected', sel ? 'true' : 'false');
                });
            }

            tabs.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    activarTab(btn.getAttribute('data-tab'));
                });
            });

            activarTab(<?php echo json_encode($tabActiva, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>);
        })();
    </script>
</main>
<?php
    require __DIR__ . '/partials/footer.php';
    require __DIR__ . '/partials/foot.php';
