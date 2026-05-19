<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Creador de Administrador</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { margin-top: 0; color: #333; text-align: center; }
        .group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #666; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #22c55e; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; font-weight: bold; }
        button:hover { background: #16a34a; }
        .msg { padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; font-weight: bold; }
        .success { background: #d1fae5; color: #065f46; }
        .error { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>

<div class="card">
    <h2>Registro Administrador Real</h2>
    
    <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
        <div class="msg success">¡Administrador creado con éxito! Ya puedes iniciar sesión en tu login habitual.</div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="msg error">Error al registrar. Revisa la conexión.</div>
    <?php endif; ?>

    <form action="../controlador/UsuarioController.php" method="POST">
        <input type="hidden" name="accion" value="crear_admin_maestro_temporal">
        
        <div class="group">
            <label>Nombre del Administrador:</label>
            <input type="text" name="nombre" value="Alejandro Administrador" required>
        </div>
        <div class="group">
            <label>Correo Electrónico:</label>
            <input type="email" name="correo" value="admin@yerga.com" required>
        </div>
        <div class="group">
            <label>Teléfono:</label>
            <input type="text" name="telefono" value="600123456" required>
        </div>
        <div class="group">
            <label>Contraseña para acceder:</label>
            <input type="password" name="password" placeholder="Escribe tu contraseña aquí" required>
        </div>
        <button type="submit">Generar e Insertar Hash</button>
    </form>
</div>

</body>
</html>