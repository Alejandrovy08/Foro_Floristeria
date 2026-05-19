<?php
// Forzar a que muestre errores por si algo falla
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Importamos la configuración de tu base de datos (ajusta la ruta si tu archivo de conexión se llama diferente)
// Si usas variables de entorno de Vercel/Railway, PDO las leerá automáticamente.
try {
    // Intentamos conectar usando las variables estándar de Railway/Vercel
    $host = getenv('MYSQLHOST') ?: 'localhost';
    $dbname = getenv('MYSQLDATABASE') ?: 'railway';
    $user = getenv('MYSQLUSER') ?: 'root';
    $pass = getenv('MYSQLPASSWORD') ?: '';
    $port = getenv('MYSQLPORT') ?: '3306';

    $db = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Forzar que la columna sea grande por código
    $db->exec("ALTER TABLE ADMINISTRADOR MODIFY contrasena VARCHAR(255) NOT NULL");

    // 2. Limpiar administradores viejos para evitar duplicados
    $db->exec("DELETE FROM ADMINISTRADOR");

    // 3. Insertar el nuevo administrador con el hash bien encriptado desde PHP
    $password_claro = 'Admin1234*';
    $password_encriptado = password_hash($password_claro, PASSWORD_DEFAULT);

    $stmt = $db->prepare("INSERT INTO ADMINISTRADOR (nombre, correo, contrasena, telefono) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        'Alejandro Administrador',
        'admin@yerga.com',
        $password_encriptado,
        '600123456'
    ]);

    echo "<h1>¡ÉXITO ROTUNDO!</h1>";
    echo "<p>La columna se ha ampliado y el administrador se ha creado correctamente desde el servidor.</p>";
    echo "<p>Ya puedes borrar este archivo por seguridad e iniciar sesión en tu web.</p>";

} catch (Exception $e) {
    echo "<h1>Error en la automatización:</h1>" . $e->getMessage();
}