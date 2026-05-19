<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    // Datos para conexión externa obligatoria desde Vercel
    $host = 'centerbeam.proxy.rlwy.net'; 
    $port = '41820'; 
    $dbname = 'railway'; 
    
    // CAMBIO CRUCIAL: El usuario root se bloquea desde fuera. Usamos las credenciales de Railway.
    $user = 'root'; 
    $pass = 'UCAdYQU1ZzrbVYQHCuKHJyrQkzsuXGLl'; 

    // Intentamos la conexión PDO con el juego de caracteres correcto
    $db = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Forzar de verdad la estructura correcta de la tabla
    $db->exec("ALTER TABLE ADMINISTRADOR MODIFY contrasena VARCHAR(255) NOT NULL");

    // 2. Limpiar registros rotos anteriores
    $db->exec("DELETE FROM ADMINISTRADOR");

    // 3. Insertar el administrador seguro desde el entorno PHP de Vercel
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
    echo "<p>El servidor ha aceptado la conexión remota, ha ensanchado la columna e insertado el hash completo.</p>";
    echo "<p>Ya puedes probar a iniciar sesión como administrador en tu web.</p>";

} catch (Exception $e) {
    echo "<h1>Error en la automatización:</h1>" . $e->getMessage();
}