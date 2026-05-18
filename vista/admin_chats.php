<?php

    session_start();

    if (!isset($_SESSION['admin_id'])) {
        header('Location: login_admin.php');
        exit;
    }

    require_once __DIR__ . '/../modelo/Mensaje.php';
    $mensajeModel = new Mensaje();
    $chatsActivosRaw = $mensajeModel->listarConversaciones();
    $chatsActivos = is_array($chatsActivosRaw) ? $chatsActivosRaw : [];

    $pageTitle = 'Chat de atención al cliente';
    $bodyClass = 'page-admin-chats';
    require __DIR__ . '/partials/head.php';
    require __DIR__ . '/partials/header.php';
    ?>
    <main class="site-main py-4">
        <div class="container">
            <h1 class="page-title text-center mb-4">Chat de atención al cliente</h1>

            <?php if (($_GET['error'] ?? '') === '1'): ?>
                <p class="feedback aviso">No se pudo completar la acción. Inténtalo de nuevo.</p>
            <?php endif; ?>

            <?php if (!empty($chatsActivos)): ?>
                <div class="chat-list-panel">
                    <ul class="chat-list list-unstyled mb-0">
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
                            <li class="chat-list-item">
                                <img class="chat-list-avatar" src="../assets/img/avatar-default.svg" alt="" width="82" height="83">
                                <div class="chat-list-body">
                                    <h2 class="chat-list-name"><?php echo $nombreChat; ?></h2>
                                    <p class="chat-list-preview" title="<?php echo htmlspecialchars($ultimoRaw, ENT_QUOTES, 'UTF-8'); ?>"><?php echo $ultimoChat !== '' ? $ultimoChat : 'Sin mensajes'; ?></p>
                                    <?php if ($fechaChat !== ''): ?>
                                        <p class="chat-list-meta mb-0"><?php echo $fechaChat; ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="chat-list-action">
                                    <a href="chat.php?u=<?php echo $uidChat; ?>">Ir al Chat</a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php else: ?>
                <p class="text-center text-muted">No hay conversaciones activas.</p>
            <?php endif; ?>

            <p class="mt-4 text-center">
                <a href="admin.php?tab=mensajes" class="btn-figma-outline me-2">Panel de administración</a>
                <a href="home.php" class="btn-figma-primary">Inicio</a>
            </p>
        </div>
    </main>
<?php
require __DIR__ . '/partials/footer.php';
require __DIR__ . '/partials/foot.php';
