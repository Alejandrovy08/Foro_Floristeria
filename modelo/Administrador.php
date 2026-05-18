<?php

require_once __DIR__ . '/../config/Database.php';

class Administrador
{
    //Funcion para comprobar si ha iniciado sesion un usuario y es administracion
    public function login($correo, $password) {
        //Crea una nueva instancia de la clase Database
        $database = new Database();
        $db = $database->getConnection();
        //Crea una consulta SQL para seleccionar el id,nombre,correo y contraseña del administrador
        $query = "SELECT id, nombre, correo, contrasena FROM ADMINISTRADOR WHERE correo = :correo LIMIT 1";
        //Prepara la consulta SQL
        $stmt = $db->prepare($query);
        //Asigna el valor del correo a la consulta
        $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
        //Ejecuta la consulta
        $stmt->execute();
        //Obtiene el resultado de la consulta
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        //Comprueba si el administrador no existe
        if (!$admin) {
            return false;
        }
        //Comprueba si la contraseña esta vacia o no es correcta
        if (!isset($admin['contrasena']) || !password_verify($password, $admin['contrasena'])) {
            return false;
        }
        //Devuelve el id y el nombre del administrador
        return [
            'id' => $admin['id'],
            'nombre' => $admin['nombre']
        ];
    }

    //Funcion con la que obtenemos los datos de un usuario mediante su ID
    public function obtenerPorId($id) {
        $database = new Database();
        $db = $database->getConnection();
        //Creamos la consulta con la que obtenemos los datos
        $query = "SELECT id, nombre, correo, telefono FROM ADMINISTRADOR WHERE id = :id LIMIT 1";

        try {
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            //Array con los datos obtenidos
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$admin) {
                return false;
            }
            //Devuelve el array
            return $admin;
        } catch (PDOException $e) {
            return false;
        }
    }

    //Funcion para actualizar los datos por los introducidos en el formulario
    public function actualizarDatos($id, $nombre, $correo, $telefono) {
        $database = new Database();
        $db = $database->getConnection();
        //Creamos la consulta que actualiza los datos del administrador
        $query = "UPDATE ADMINISTRADOR SET nombre = :nombre, correo = :correo, telefono = :telefono WHERE id = :id";

        try {
            //Vinculamos los datos recibidos
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
            $stmt->bindParam(':telefono', $telefono, PDO::PARAM_STR);
            //Devuelve true si los datos se actualizaron correctamente
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    //Funcion para actualizar la contraseña por la introducida en el formulario
    public function actualizarPassword($id, $nuevaPassword) {
        $database = new Database();
        $db = $database->getConnection();
        //Encriptamos la contraseña
        $passwordHash = password_hash($nuevaPassword, PASSWORD_BCRYPT);
        if ($passwordHash === false) {
            return false;
        }
        //Consulta con la que actualizamos la contraseña
        $query = "UPDATE ADMINISTRADOR SET contrasena = :contrasena WHERE id = :id";

        try {
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':contrasena', $passwordHash, PDO::PARAM_STR);
            //Devuelve true si la contraseña se actualizo correctamente
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}

?>
