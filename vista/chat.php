<?php
    session_start();
    //Cargamos los modelos
    require_once __DIR__ . '/../modelo/Mensaje.php';
    require_once __DIR__ . '/../modelo/Usuario.php';

    $usuarioIdSesion = null;
    $adminIdSesion = null;

    //Comprobamos que la sesion del usuario esta activa
    if (!empty($_SESSION['usuario_id'])) {
        $tmp = filter_var($_SESSION['usuario_id'], FILTER_VALIDATE_INT);
        $usuarioIdSesion = ($tmp !== false && $tmp > 0) ? $tmp : null;
    }
    //Comprobamos que la sesion del admin esta activa
    if (!empty($_SESSION['admin_id'])) {
        $tmp = filter_var($_SESSION['admin_id'], FILTER_VALIDATE_INT);
        $adminIdSesion = ($tmp !== false && $tmp > 0) ? $tmp : null;
    }
    //Comprobamos que la sesion del usuario o del admin esta activa
    if ($usuarioIdSesion === null && $adminIdSesion === null) {
        header('Location: login.php');
        exit;
    }

    $esAdmin = $adminIdSesion !== null;
    $esUsuarioRegistrado = !$esAdmin && $usuarioIdSesion !== null;
    $mensajeModel = new Mensaje();
    $error = (($_GET['error'] ?? '') === '1');

    $usuarioChatId = 0;
    $mensajes = [];
    $nombreInterlocutor = 'Soporte';
    $esRemitenteAdmin = false;

    //Comprobamos que el usuario esta registrado
    if ($esUsuarioRegistrado) {
        if (isset($_GET['u'])) {
            header('Location: chat.php');
            exit;
        }

        $usuarioChatId = $usuarioIdSesion;
        $mensajes = $mensajeModel->obtenerConversacion($usuarioChatId);
        //Comprobamos que no existen mensajes
        if (!empty($mensajes)) {
            $nombreInterlocutor = htmlspecialchars(
                $mensajes[0]['nombre_admin'] ?? 'Administrador',
                ENT_QUOTES,
                'UTF-8'
            );
        }

    } elseif ($esAdmin) {
        if (!isset($_GET['u']) || $_GET['u'] === '') {
            header('Location: admin_chats.php');
            exit;
        }

        $idDesdeGet = filter_var($_GET['u'], FILTER_VALIDATE_INT);
        $usuarioChatId = ($idDesdeGet !== false && $idDesdeGet > 0) ? (int) $idDesdeGet : 0;

        //Comprobamos que el id del usuario es un numero entero
        if ($usuarioChatId <= 0) {
            header('Location: admin_chats.php');
            exit;
        }

        $usuarioModel = new Usuario();
        $datosUsuario = $usuarioModel->obtenerPorId($usuarioChatId);
        //Comprobamos que los datos del usuario no estan vacios
        if ($datosUsuario === false) {
            header('Location: admin_chats.php');
            exit;
        }

        //Comprobamos que el remitente es admin
        $esRemitenteAdmin = true;
        //Comprobamos que el nombre del interlocutor no esta vacio
        $nombreInterlocutor = htmlspecialchars($datosUsuario['nombre'] ?? 'Usuario', ENT_QUOTES, 'UTF-8');
        $mensajes = $mensajeModel->obtenerConversacion($usuarioChatId);
    }

    $pageTitle = 'Chat de soporte';
    $bodyClass = 'page-chat';
    $hideSiteChrome = true;
    require __DIR__ . '/partials/head.php';
?>
<div class="chat-wrap" style="display: flex; flex-direction: column; height: 100vh; width: 100vw; max-width: 100%; margin: 0; background-color: #ffffff;">
    <header class="chat-header" style="flex-shrink: 0;">
        <?php if ($esRemitenteAdmin): ?>
            <a href="admin_chats.php" aria-label="Volver a la lista de chats">&larr;</a>
            <h1><?php echo $nombreInterlocutor; ?></h1>
        <?php else: ?>
            <h1>Chat con <?php echo $nombreInterlocutor; ?></h1>
        <?php endif; ?>
        <a class="nav-home" href="home.php">Inicio</a>
    </header>

    <?php if ($error): ?>
        <p class="aviso" style="flex-shrink: 0;">No se pudo enviar el mensaje. Comprueba el texto e inténtalo de nuevo.</p>
    <?php endif; ?>

    <div class="mensajes" id="mensajes" style="flex-grow: 1; overflow-y: auto; background-color: #f8fafc; padding: 1.5rem;">
        <div id="contenedor-mensajes" style="display: flex; flex-direction: column; gap: 1rem; width: 100%; max-width: 1200px; margin: 0 auto;">
        <?php if (empty($mensajes)): ?>
            <p class="vacio" style="text-align: center; color: #666; margin-top: 2rem;"><?php echo $esRemitenteAdmin ? 'Aún no hay mensajes con este usuario.' : 'No hay mensajes todavía. Escribe el primero.'; ?></p>
        <?php else: ?>
            <?php foreach ($mensajes as $msg): ?>
                <?php
                $textoMsg = nl2br(htmlspecialchars($msg['texto'] ?? '', ENT_QUOTES, 'UTF-8'));
                $fechaMsg = htmlspecialchars($msg['fecha_envio'] ?? '', ENT_QUOTES, 'UTF-8');
                $por = $msg['enviado_por'] ?? 'usuario';
                
                if ($esRemitenteAdmin) {
                    $esYo = ($por === 'admin');
                } else {
                    $esYo = ($por === 'usuario');
                }

                $alineacionWrapper = $esYo ? 'justify-content: flex-end;' : 'justify-content: flex-start;';
                $colorBurbuja = $esYo ? 'background-color: #a8ebd8; color: #1a1a1a; border-radius: 15px 15px 0px 15px;' : 'background-color: #e2e8f0; color: #1a1a1a; border-radius: 15px 15px 15px 0px;';
                ?>
                <div class="d-flex w-100" style="display: flex; <?php echo $alineacionWrapper; ?>">
                    <div class="burbuja" style="<?php echo $colorBurbuja; ?> padding: 0.85rem 1.2rem; max-width: 65%; box-shadow: 0 2px 5px rgba(0,0,0,0.04); font-size: 1rem; line-height: 1.5; display: flex; flex-direction: column; gap: 0.35rem;">
                        <span style="word-break: break-word;"><?php echo $textoMsg; ?></span>
                        <span class="hora" style="display: block; font-size: 0.75rem; color: #666; text-align: right; margin-top: 0.15rem;"><?php echo $fechaMsg; ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        </div>
    </div>

    <form class="form-chat" method="post" action="/controlador/"ChatController.php" style="flex-shrink: 0; background-color: #ffffff; border-top: 1px solid rgba(0,0,0,0.08); padding: 1rem; display: flex; justify-content: center; align-items: center; width: 100%;">
        <div style="display: flex; gap: 0.75rem; width: 100%; max-width: 1200px; margin: 0 auto; align-items: center;">
            <?php if ($esRemitenteAdmin && $usuarioChatId > 0): ?>
                <input type="hidden" name="usuario_id" value="<?php echo (int) $usuarioChatId; ?>">
            <?php endif; ?>
            <input type="text" name="texto" placeholder="Escribe un mensaje..." required autocomplete="off" maxlength="2000" style="flex-grow: 1; border-radius: 25px; border: 1px solid rgba(107, 228, 255, 0.6); padding: 0.65rem 1.25rem; font-size: 1rem; outline: none; background-color: #ffffff;">
            <button type="submit" style="border-radius: 25px; padding: 0.65rem 1.5rem; border: none; background-color: #20c997; color: #ffffff; font-weight: 600; cursor: pointer; white-space: nowrap;">Enviar</button>
        </div>
    </form>
</div>
<script>
    (function () {
        var contenedorScroll = document.getElementById('mensajes');
        var contenedorMensajes = document.getElementById('contenedor-mensajes');
        var usuarioChatId = <?php echo (int) $usuarioChatId; ?>;

        function scrollAbajo(forzar) {
            if (!contenedorScroll) return;
            if (forzar) {
                contenedorScroll.scrollTop = contenedorScroll.scrollHeight;
                return;
            }
            var umbral = 80;
            var cercaDelFinal = contenedorScroll.scrollHeight - contenedorScroll.scrollTop - contenedorScroll.clientHeight <= umbral;
            if (cercaDelFinal) {
                contenedorScroll.scrollTop = contenedorScroll.scrollHeight;
            }
        }

        function actualizarMensajes() {
            if (!contenedorMensajes || usuarioChatId <= 0) return;

            var url = 'obtener_mensajes.php?usuario_id=' + encodeURIComponent(usuarioChatId);
            fetch(url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (res) {
                    if (!res.ok) throw new Error('respuesta');
                    return res.text();
                })
                .then(function (html) {
                    if (html === contenedorMensajes.innerHTML) return;
                    var estabaAbajo = contenedorScroll.scrollHeight - contenedorScroll.scrollTop - contenedorScroll.clientHeight <= 80;
                    contenedorMensajes.innerHTML = html;
                    if (estabaAbajo) {
                        scrollAbajo(true);
                    }
                })
                .catch(function () { /* ignorar errores de red puntuales */ });
        }

        scrollAbajo(true);
        actualizarMensajes();
        setInterval(actualizarMensajes, 3000);
    })();
</script>
<?php require __DIR__ . '/partials/foot.php'; ?>