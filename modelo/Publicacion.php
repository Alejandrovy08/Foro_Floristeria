<?php
require_once __DIR__ . '/../config/Database.php';

class Publicacion {
    public function crear($titulo, $texto, $autor_id, $admin_id) {
        //Crea una nueva instancia en la base de datos
        $database = new Database();
        //Crea una nueva conexión a la base de datos
        $db = $database->getConnection();
        //Crea una consulta SQL para insertar los datos de la publicación
        $query = "INSERT INTO PUBLICACION (titulo, texto, autor_id, admin_id)
                  VALUES (:titulo, :texto, :autor_id, :admin_id)";
        //Prepara la consulta SQL
        try {
            $stmt = $db->prepare($query);
            //Asignamos los valores obtenidos a la consulta
            $stmt->bindParam(':titulo', $titulo, PDO::PARAM_STR);
            $stmt->bindParam(':texto', $texto, PDO::PARAM_STR);
            $stmt->bindParam(':autor_id', $autor_id, PDO::PARAM_INT);
            $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
            if (!$stmt->execute()) {
                return false;
            }
            $nuevoId = (int) $db->lastInsertId();
            return $nuevoId > 0 ? $nuevoId : false;
        } catch (PDOException $e) {
            return false;
        }
    }

    //Funcion con la que listamos todas las publicaciones
    public function listarTodas() {
        $database = new Database();
        $db = $database->getConnection();
        //Crea una consulta para seleccionar los datos de las publicaciones
        $query = "SELECT p.id, p.titulo, p.texto, p.fecha_publicacion, p.valoracion, u.nombre AS nombre_autor,
                         i.ruta_archivo
                  FROM PUBLICACION p
                  INNER JOIN ADMINISTRADOR u ON p.autor_id = u.id
                  LEFT JOIN (
                      SELECT publicacion_id, MAX(id) AS max_img_id
                      FROM IMAGEN
                      GROUP BY publicacion_id
                  ) img ON img.publicacion_id = p.id
                  LEFT JOIN IMAGEN i ON i.id = img.max_img_id
                  ORDER BY p.fecha_publicacion DESC";
        
        //Preparamos la consulta
        try {
            $stmt = $db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    //Funcion con la que obtenemos una publicacion por su ID
    public function obtenerPorId($id) {
        $database = new Database();
        $db = $database->getConnection();
        //Creamos una consulta para seleccionar los datos de la publicacion por su ID
        $query = "SELECT p.*, u.nombre AS nombre_autor, i.ruta_archivo
                  FROM PUBLICACION p
                  INNER JOIN ADMINISTRADOR u ON p.autor_id = u.id
                  LEFT JOIN (
                      SELECT publicacion_id, MAX(id) AS max_img_id
                      FROM IMAGEN
                      GROUP BY publicacion_id
                  ) img ON img.publicacion_id = p.id
                  LEFT JOIN IMAGEN i ON i.id = img.max_img_id
                  WHERE p.id = :id
                  LIMIT 1";

        //Preparamos la consulta
        try {
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    //Funcion para obtener los datos de las Imagenes y mostrarlas
    public function obtenerImagenes($publicacionId) {
        $publicacionId = (int) $publicacionId;
        //Comprobamos que el ID es mayor que 0
        if ($publicacionId <= 0) {
            return [];
        }

        $database = new Database();
        $db = $database->getConnection();
        //Creamos la consulta para obtener los datos de la imagen
        $query = "SELECT id, ruta_archivo, tamano, publicacion_id
                  FROM IMAGEN
                  WHERE publicacion_id = :publicacion_id
                  ORDER BY id ASC";

        //Ejecutamos la consulta
        try {
            $stmt = $db->prepare($query);
            $stmt->bindParam(':publicacion_id', $publicacionId, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return is_array($rows) ? $rows : [];
        } catch (PDOException $e) {
            return [];
        }
    }

    //Funcion con la que actualizamos una publicacion
    public function actualizar($id, $titulo, $texto) {
        $database = new Database();
        $db = $database->getConnection();
        //Creamos una consulta para actualizar los datos por los introducidos
        $query = "UPDATE PUBLICACION 
                  SET titulo = :titulo, texto = :texto
                  WHERE id = :id";

        //Preparamos la consulta
        try {
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':titulo', $titulo, PDO::PARAM_STR);
            $stmt->bindParam(':texto', $texto, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    //Funcion con la que eliminamos una publicacion
    public function eliminar($id) {
        $database = new Database();
        $db = $database->getConnection();
        //Creamos una consulta para eliminar una publicacion
        $query = "DELETE FROM PUBLICACION WHERE id = :id";

        //Preparamos la consulta
        try {
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}

?>
