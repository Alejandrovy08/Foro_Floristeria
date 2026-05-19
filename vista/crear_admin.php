<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    // Usamos el DSN público completo combinando tus datos exactos de Railway
    // Esto evita que el proxy de Railway rechace la conexión externa de Vercel
    $dsn = "mysql:host=centerbeam.proxy.rlwy.net;port=41820;dbname=railway;charset=utf8";
    $user = "root";
    $pass = "UCAdYQU1ZzrbVYQHCuKHJyrQkzsuXGLl"; 

    // Conexión externa oficial
    $db = new PDO($dsn, $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Ampliamos la columna a VARCHAR(255) de manera real e interna en el servidor
    $db->exec("ALTER TABLE ADMINISTRADOR MODIFY contrasena VARCHAR(255) NOT NULL");

    // 2. Limpiamos registros anteriores
    $db->exec("DELETE FROM ADMINISTRADOR");

    // 3. Generamos el hash completo de 60 caracteres directamente en el entorno PHP del servidor
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
    echo "<p>El script ha burlado el bloqueo del proxy, ha ensanchado la columna e insertado el administrador completo.</p>";
    echo "<p>Ya puedes cerrar esto y probar el login en tu web.</p>";

} catch (Exception $e) {
    echo "<h1>Error en la automatización:</h1>" . $e->getMessage();
}