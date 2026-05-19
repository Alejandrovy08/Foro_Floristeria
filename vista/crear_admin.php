<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    // Credenciales públicas reales extraídas de tu panel de Railway
    $host = 'centerbeam.proxy.rlwy.net'; 
    $port = '41820'; 
    $dbname = 'railway'; 
    $user = 'root'; 
    $pass = 'UCAdYQU1ZzrbVYQHCuKHJyrQkzsuXGLl'; 

    // Conexión directa desde servidores externos (Vercel)
    $db = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Ampliamos la columna contrasena a VARCHAR(255) para que no la recorte jamás
    $db->exec("ALTER TABLE ADMINISTRADOR MODIFY contrasena VARCHAR(255) NOT NULL");

    // 2. Limpiamos registros huérfanos o mal guardados
    $db->exec("DELETE FROM ADMINISTRADOR");

    // 3. Generamos e insertamos el hash completo de forma nativa desde PHP
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
    echo "<p>La tabla se ha adaptado y el Administrador se ha guardado con su hash completo de PHP sin recortes.</p>";
    echo "<p>Ya puedes cerrar esta pestaña e ir a iniciar sesión en la web.</p>";

} catch (Exception $e) {
    echo "<h1>Error en la automatización:</h1>" . $e->getMessage();
}