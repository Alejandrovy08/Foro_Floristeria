<?php
//Carga el archivo Database.php
require_once __DIR__ . '/../config/Database.php';

class Usuario {
    public function login($correo, $password) {
        $database = new Database();
        $db = $database->getConnection();
        // Buscamos el correo tanto en USUARIO como en ADMINISTRADOR.
        // Así, la consulta "trae la columna" que identifica el rol del login (`tipo`).
        $query = "
            (SELECT id, nombre, correo, contrasena, 'usuario' AS tipo
             FROM USUARIO
             WHERE correo = :correo)
            UNION ALL
            (SELECT id, nombre, correo, contrasena, 'administrador' AS tipo
             FROM ADMINISTRADOR
             WHERE correo = :correo)
            LIMIT 1
        ";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
        $stmt->execute();

        $fila = $stmt->fetch();
        if (!$fila) {
            return false;
        }

        // Comprobamos si la contraseña es correcta (se asume hash con password_hash()).
        if (!isset($fila['contrasena']) || !password_verify($password, $fila['contrasena'])) {
            return false;
        }

        return [//Devuelve un array con los datos del usuario   
            'id' => $fila['id'],
            'nombre' => $fila['nombre'],
            'tipo' => $fila['tipo'],
        ];
    }

    //Funcion con la que registraremos nuevos usuarios
    public function registrar($nombre, $correo, $password) {
        $database = new Database();
        $db = $database->getConnection();
        //Comprobamos que la contraseña se ha encriptado correctamente
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        if ($passwordHash === false) {
            return false;
        }

        //Creamos una consulta que recibe los datos introducidos para registrar al usuario
        $query = "INSERT INTO USUARIO (nombre, correo, contrasena) VALUES (:nombre, :correo, :contrasena)";
        //Evitamos errores al momento de guardar los datos(correos duplicados...)
        try {
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
            $stmt->bindParam(':contrasena', $passwordHash, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    //Funcion con la que actualizaremos los datos con los nuevos introducidos
    public function actualizarDatos($id, $nombre, $correo) {
        $database = new Database();
        $db = $database->getConnection();
        //Creamos una consulta que recibe los datos con los que actualizamos los datos introducidos en el formulario
        $query = "UPDATE USUARIO SET nombre = :nombre, correo = :correo WHERE id = :id";
        
        try {
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    //Funcion con la que actualizamos lla contraseña por una nueva introducida
    public function actualizarPassword($id, $nuevaPassword) {
        $database = new Database();
        $db = $database->getConnection();


        $passwordHash = password_hash($nuevaPassword, PASSWORD_BCRYPT);
        //Comprobamos que la contraseña se ha encriptado correctamente
        if ($passwordHash === false) {
            return false;
        }
        //Creamos una consulta que recibe la contraseña nueva para actualizarla
        $query = "UPDATE USUARIO SET contrasena = :contrasena WHERE id = :id";

        try {
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':contrasena', $passwordHash, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    //Funcion con la que eliminaremos al usuario
    public function eliminarUsuario($id) {
        $database = new Database();
        $db = $database->getConnection();
        //Creamos una consulta que recibe el ID del usuario que se va a eliminar
        $query = "DELETE FROM USUARIO WHERE id = :id";

        try {
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    //Funcion con la que obtendremos los datos de un usuario por su ID
    public function obtenerPorId($id) {
        $database = new Database();
        $db = $database->getConnection();
        //Creamos una consulta que recibe los datos del usuario al que le pertenece el ID
        $query = "SELECT id, nombre, correo FROM USUARIO WHERE id = :id LIMIT 1";

        try {
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$usuario) {
                return false;
            }

            return $usuario;
        } catch (PDOException $e) {
            return false;
        }
    }

    //Funcion con la que listaremos todos los usuarios por fecha de registro en orden descendente
    public function listarTodos() {
        $database = new Database();
        $db = $database->getConnection();
        //Creamos una consulta con la que obtenemos los datos de los usuarios por orden descendete de registro 
        $query = "SELECT id, nombre, correo FROM USUARIO ORDER BY id DESC";

        try {
            $stmt = $db->prepare($query);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Funcion para registrar un administrador de forma nativa e interna
    public function registrarAdmin($nombre, $correo, $password, $telefono) {
        $database = new Database();
        $db = $database->getConnection();

        // Encriptamos usando PASSWORD_BCRYPT (genera un hash de 60 caracteres perfecto)
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        if ($passwordHash === false) {
            return false;
        }

        $query = "INSERT INTO ADMINISTRADOR (nombre, correo, contrasena, telefono) VALUES (:nombre, :correo, :contrasena, :telefono)";

        try {
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
            $stmt->bindParam(':contrasena', $passwordHash, PDO::PARAM_STR);
            $stmt->bindParam(':telefono', $telefono, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    
}


?>