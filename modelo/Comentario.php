<?php
require_once __DIR__ . '/../config/Database.php';

class Comentario {
    public function obtenerPorPublicacion($publicacionId) {
        $database = new Database();
        $db = $database->getConnection();
        //Creamos la consulta que obtiene los datos de la publicacion
        $query = "SELECT c.*, COALESCE(u.nombre, a.nombre) AS nombre_usuario
                  FROM COMENTARIO c
                  LEFT JOIN USUARIO u ON c.usuario_id = u.id
                  LEFT JOIN ADMINISTRADOR a ON c.admin_id = a.id
                  WHERE c.publicacion_id = :publicacion_id
                  ORDER BY c.fecha_publicacion DESC";

        //Ejecutamos la sentencia
        try {
            $stmt = $db->prepare($query);
            $stmt->bindParam(':publicacion_id', $publicacionId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    
    public function crear($texto, $usuarioId, $publicacionId, $adminId = null) {
        $database = new Database();
        $db = $database->getConnection();
        //Creamos la consulta para crear un comentario
        $query = "INSERT INTO COMENTARIO (texto, usuario_id, admin_id, publicacion_id)
                  VALUES (:texto, :usuario_id, :admin_id, :publicacion_id)";

        try {
            //Si el ID del usuario o del admin es 0 este adquiere un valor null
            if ($usuarioId === 0) {
                $usuarioId = null;
            }
            if ($adminId === 0) {
                $adminId = null;
            }
            //Preparamos la consulta enviandole los datos 
            $stmt = $db->prepare($query);
            $stmt->bindParam(':texto', $texto, PDO::PARAM_STR);
            /*
            Si el ID es null lo indica a la base de datos mediante PDO::PARAM_NULL.
            Si el ID tiene un numero este se envia a un entero mediante PDO::PARAM_INT
            */
            $stmt->bindParam(':usuario_id', $usuarioId, $usuarioId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindParam(':admin_id', $adminId, $adminId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindParam(':publicacion_id', $publicacionId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw $e;
        }
    }
}

?>
