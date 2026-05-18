<?php
    //Cargamos la base de datos
    require_once __DIR__ . '/../config/Database.php';

    class Mensaje {

        //Funcion para enviar un mensaje
        public function enviar($usuario_id, $admin_id, $texto, $enviado_por) {
            $usuario_id = (int) $usuario_id;
            $admin_id = (int) $admin_id;
            $texto = trim((string) $texto);
            $enviado_por = $enviado_por === 'admin' ? 'admin' : 'usuario';

            //Comprobamos que el usuario, el admin y el texto no estan vacios
            if ($usuario_id <= 0 || $admin_id <= 0 || $texto === '') {
                return false;
            }

            $database = new Database();
            $db = $database->getConnection();
            //Creamos la consulta para insertar los datos del mensaje
            $query = "INSERT INTO MENSAJE (usuario_id, admin_id, texto, enviado_por)
                    VALUES (:usuario_id, :admin_id, :texto, :enviado_por)";

            //Ejecutamos la consulta
            try {
                $stmt = $db->prepare($query);
                $stmt->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
                $stmt->bindValue(':admin_id', $admin_id, PDO::PARAM_INT);
                $stmt->bindValue(':texto', $texto, PDO::PARAM_STR);
                $stmt->bindValue(':enviado_por', $enviado_por, PDO::PARAM_STR);
                return $stmt->execute();
            } catch (PDOException $e) {
                return false;
            }
        }

        //Funcion para obtener la conversacion de un usuario
        public function obtenerConversacion($usuario_id) {
            $usuario_id = (int) $usuario_id;
            //Comprobamos que el id del usuario es un numero entero
            if ($usuario_id <= 0) {
                return [];
            }

            $database = new Database();
            $db = $database->getConnection();
            //Creamos la consulta para obtener los datos de la conversacion
            $query = "SELECT m.id, m.usuario_id, m.admin_id, m.texto, m.enviado_por, m.fecha_envio, m.leido,
                            u.nombre AS nombre_usuario, a.nombre AS nombre_admin
                    FROM MENSAJE m
                    INNER JOIN USUARIO u ON m.usuario_id = u.id
                    INNER JOIN ADMINISTRADOR a ON m.admin_id = a.id
                    WHERE m.usuario_id = :usuario_id
                    ORDER BY m.fecha_envio ASC";

            //Ejecutamos la consulta
            try {
                $stmt = $db->prepare($query);
                $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return is_array($rows) ? $rows : [];
            } catch (PDOException $e) {
                return [];
            }
        }

        //Funcion para listar las conversaciones
        public function listarConversaciones() {
            $database = new Database();
            $db = $database->getConnection();
            //Creamos la consulta para obtener los datos de las conversaciones
            $query = "SELECT u.id AS usuario_id, u.nombre,
                            lm.texto AS ultimo_mensaje,
                            lm.fecha_envio AS fecha_ultimo,
                            lm.enviado_por AS ultimo_enviado_por
                    FROM USUARIO u
                    INNER JOIN (
                        SELECT m1.usuario_id, m1.texto, m1.fecha_envio, m1.enviado_por
                        FROM MENSAJE m1
                        INNER JOIN (
                            SELECT usuario_id, MAX(id) AS max_id
                            FROM MENSAJE
                            GROUP BY usuario_id
                        ) ult ON ult.usuario_id = m1.usuario_id AND ult.max_id = m1.id
                    ) lm ON lm.usuario_id = u.id
                    ORDER BY lm.fecha_envio DESC";

            //Ejecutamos la consulta
            try {
                $stmt = $db->prepare($query);
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return is_array($rows) ? $rows : [];
            } catch (PDOException $e) {
                return [];
            }
        }

        //Funcion para listar los chats activos
        public function listarChatsActivos() {
            return $this->listarConversaciones();
        }

        //Funcion para obtener el id del primer administrador
        public function obtenerPrimerAdministradorId() {
            $database = new Database();
            $db = $database->getConnection();
            //Creamos la consulta para obtener el id del primer administrador
            $query = "SELECT id FROM ADMINISTRADOR ORDER BY id ASC LIMIT 1";
            //Ejecutamos la consulta
            try {
                $stmt = $db->prepare($query);
                $stmt->execute();
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                return $admin ? (int) $admin['id'] : 0;
            } catch (PDOException $e) {
                return 0;
            }
        }

        //Funcion para obtener el id del administrador para un usuario
        public function obtenerAdminIdParaUsuario($usuario_id) {
            $usuario_id = (int) $usuario_id;
            //Comprobamos que el id del usuario es un numero entero
            if ($usuario_id <= 0) {
                return 0;
            }

            $database = new Database();
            $db = $database->getConnection();
            //Creamos la consulta para obtener el id del administrador para un usuario
            $queryUltimo = "SELECT admin_id FROM MENSAJE
                            WHERE usuario_id = :usuario_id
                            ORDER BY id DESC
                            LIMIT 1";
            //Ejecutamos la consulta
            try {
                $stmt = $db->prepare($queryUltimo);
                $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row && !empty($row['admin_id'])) {
                    return (int) $row['admin_id'];
                }
            } catch (PDOException $e) {
                return 0;
            }

            return $this->obtenerPrimerAdministradorId();
        }
    }
?>