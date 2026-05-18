<?php
require_once __DIR__ . '/../config/Database.php';

class Imagen {

    //Funcion para obtener los datos de una imagen mediante su ID
    public function obtenerPorId($id) {
        $id = (int) $id;
        //Comprobamos que el ID es mayor que 0
        if ($id <= 0) {
            return false;
        }
        $database = new Database();
        $db = $database->getConnection();
        //Creamos la sentecia para los datos de la imagen mediante su ID
        $query = "SELECT id, ruta_archivo, tamano, descripcion, publicacion_id
                  FROM IMAGEN WHERE id = :id LIMIT 1";
        //Ejecutamos la sentencia
        try {
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: false;
        } catch (PDOException $e) {
            return false;
        }
    }

    //Funcion con la que podremos validar el archivo que se va a subir
    public function validar(array $archivo): bool {

        //Comprobamos que el archivo se ha enviado correctamente
        if (!isset($archivo['tmp_name'], $archivo['error']) || $archivo['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        //Almacenamos la ruta donde guardamos el archivo subido
        $tmp = $archivo['tmp_name'];
        //Validamos que el archivo provenga de una subida HTTP legal
        if (!is_uploaded_file($tmp)) {
            return false;
        }
        //Obtenemos el tamaño y tipo del archivo
        $info = @getimagesize($tmp);
        //Comprobamos la lectura de la informacion de la imagen
        if ($info === false) {
            return false;
        }
        //Obtenemos el tipo de imagen
        $tipo = $info[2] ?? 0;
        //Solo permitimos archivos JPEG o PNG
        return $tipo === IMAGETYPE_JPEG || $tipo === IMAGETYPE_PNG;
    }

    //Funcion con la que podremos subir una imagen
    public function subir(array $archivo, $publicacionId): bool {
        $publicacionId = (int) $publicacionId;
        //Comprobamos que el ID de la publicacion sea mayor a 0
        if ($publicacionId <= 0) {
            return false;
        }
        //Llamamos a la funcion validar para asegurar que el archivo es apto
        if (!$this->validar($archivo)) {
            return false;
        }

        //Obtenemos los datos de la imagen
        $tmp = $archivo['tmp_name'];
        $info = getimagesize($tmp);
        //Definimos la extension segun el tipo del archivo
        $ext = ($info[2] === IMAGETYPE_PNG) ? 'png' : 'jpg';
        //Establecemos un nombre aleatorio para que no haya nombres duplicados
        $nombreUnico = uniqid('img_', true) . '.' . $ext;
        //Definimos la ruta en donde van a ir los archivos
        $dirBase = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads';
        //Si no existe la carpeta se crea una con los permisos de lectura y ejecucion
        if (!is_dir($dirBase)) {
            if (!mkdir($dirBase, 0755, true)) {
                return false;
            }
        }
        
        $rutaDestino = $dirBase . DIRECTORY_SEPARATOR . $nombreUnico;
        if (!move_uploaded_file($tmp, $rutaDestino)) {
            return false;
        }

        $rutaBd = 'uploads/' . $nombreUnico;
        $tamanoBytes = @filesize($rutaDestino);
        $tamanoStr = $tamanoBytes !== false ? (string) $tamanoBytes : '';

        $database = new Database();
        $db = $database->getConnection();
        //Creamos la consulta para guardar los datos de la imagen
        $query = "INSERT INTO IMAGEN (tamano, descripcion, ruta_archivo, publicacion_id)
                  VALUES (:tamano, :descripcion, :ruta_archivo, :publicacion_id)";

        //Ejecutamos la consulta
        try {
            $stmt = $db->prepare($query);
            $stmt->bindParam(':tamano', $tamanoStr, PDO::PARAM_STR);
            $stmt->bindValue(':descripcion', null, PDO::PARAM_NULL);
            $stmt->bindParam(':ruta_archivo', $rutaBd, PDO::PARAM_STR);
            $stmt->bindParam(':publicacion_id', $publicacionId, PDO::PARAM_INT);
            $ok = $stmt->execute();
            //Si falla la base de datos borra el archivo fisico
            if (!$ok) {
                @unlink($rutaDestino);
                return false;
            }
            return true;
        } catch (PDOException $e) {
            @unlink($rutaDestino);
            return false;
        }
    }

    //Funcion con la que eliminamos la imagen
    public function eliminar($id) {
        $id = (int) $id;
        //Comprobamos que el ID es mayor a 0
        if ($id <= 0) {
            return false;
        }
        $database = new Database();
        $db = $database->getConnection();
        //Creamos la consulta para buscar la ruta del archivo para borrarlo
        $querySelect = "SELECT ruta_archivo FROM IMAGEN WHERE id = :id LIMIT 1";
        //Ejecutamos la consulta
        try {
            $stmt = $db->prepare($querySelect);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            //Comprobamos que existe la ruta del archivo o que no este vacia
            if (!$row || empty($row['ruta_archivo'])) {
                return false;
            }
            $rutaRel = trim((string) $row['ruta_archivo']);

            if ($rutaRel === '' || strpos($rutaRel, '..') !== false) {
                return false;
            }
            $rutaNorm = str_replace('\\', '/', $rutaRel);
            //Solo se pueden eliminar los archivos de la ruta uploads
            if (strpos($rutaNorm, 'uploads/') !== 0) {
                return false;
            }
            //Convertimos la ruta de la BD en una fisica real del servidor
            $rutaAbs = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rutaNorm);
            if (is_file($rutaAbs)) {
                @unlink($rutaAbs);
            }
            //Creamos la consulta con la que eliminamos la imagen
            $queryDel = "DELETE FROM IMAGEN WHERE id = :id";
            //Ejecutamos la consulta
            $stmtDel = $db->prepare($queryDel);
            $stmtDel->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmtDel->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
