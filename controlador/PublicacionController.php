<?php
    session_start();
    //Cargamos el modelo de las publicaciones
    require_once __DIR__ . '/../modelo/Publicacion.php';

    $accion = $_POST['accion'] ?? $_GET['accion'] ?? '';
    $adminId = $_SESSION['admin_id'] ?? null;

    //Todas las acciones requieren sesión de administrador
    if ($accion !== '' && $adminId === null) {
        header('Location: ../vista/admin.php?error=unauthorized');
        exit;
    }
    
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && $accion === 'guardar_publicacion') {
        //Obtenemos los datos del formulario
        $titulo = trim($_POST['titulo'] ?? '');
        $texto = trim($_POST['texto'] ?? '');

        //Comprobamos si los datos estan vacios
        if ($titulo === '' || $texto === '') {
            header('Location: ../vista/crear_publicacion.php?error=1');
            exit;
        }

        $archivoImagen = $_FILES['imagen'] ?? null;
        $errImagen = is_array($archivoImagen) ? ($archivoImagen['error'] ?? UPLOAD_ERR_NO_FILE) : UPLOAD_ERR_NO_FILE;
        if ($errImagen !== UPLOAD_ERR_OK && $errImagen !== UPLOAD_ERR_NO_FILE) {
            header('Location: ../vista/crear_publicacion.php?error=1');
            exit;
        }

        require_once __DIR__ . '/../modelo/Imagen.php';
        $imagenModel = new Imagen();
        if ($errImagen === UPLOAD_ERR_OK && !$imagenModel->validar($archivoImagen)) {
            header('Location: ../vista/crear_publicacion.php?error=1');
            exit;
        }

        //Creamos una nueva instancia del modelo de las publicaciones
        $publicacionModel = new Publicacion();
        $nuevoId = $publicacionModel->crear($titulo, $texto, $adminId, $adminId);

        if ($nuevoId) {
            if ($errImagen === UPLOAD_ERR_OK) {
                $imagenModel->subir($archivoImagen, (int) $nuevoId);
            }
            header('Location: ../vista/admin.php?status=success');
            exit;
        }
        //Redirigimos a la vista de creación de publicacion con un error
        header('Location: ../vista/crear_publicacion.php?error=1');
        exit;
    }

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && $accion === 'eliminar_imagen') {
        $imagenId = (int) ($_POST['imagen_id'] ?? 0);
        $pubId = (int) ($_POST['publicacion_id'] ?? 0);
        if ($imagenId <= 0 || $pubId <= 0) {
            header('Location: ../vista/admin.php?error=1');
            exit;
        }
        require_once __DIR__ . '/../modelo/Imagen.php';
        $imagenModel = new Imagen();
        $fila = $imagenModel->obtenerPorId($imagenId);
        if (is_array($fila) && (int) ($fila['publicacion_id'] ?? 0) === $pubId) {
            $imagenModel->eliminar($imagenId);
        }
        header('Location: ../vista/editar_publicacion.php?id=' . $pubId);
        exit;
    }

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && $accion === 'editar_publicacion') {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $titulo = trim($_POST['titulo'] ?? '');
        $texto = trim($_POST['texto'] ?? '');

        if ($id <= 0 || $titulo === '' || $texto === '') {
            header('Location: ../vista/admin.php?error=1');
            exit;
        }

        $archivoImagen = $_FILES['imagen'] ?? null;
        $errImagen = is_array($archivoImagen) ? ($archivoImagen['error'] ?? UPLOAD_ERR_NO_FILE) : UPLOAD_ERR_NO_FILE;
        if ($errImagen !== UPLOAD_ERR_OK && $errImagen !== UPLOAD_ERR_NO_FILE) {
            header('Location: ../vista/editar_publicacion.php?id=' . $id . '&error=1');
            exit;
        }

        require_once __DIR__ . '/../modelo/Imagen.php';
        $imagenModel = new Imagen();
        if ($errImagen === UPLOAD_ERR_OK && !$imagenModel->validar($archivoImagen)) {
            header('Location: ../vista/editar_publicacion.php?id=' . $id . '&error=1');
            exit;
        }

        $publicacionModel = new Publicacion();
        $publicacionModel->actualizar($id, $titulo, $texto);

        if ($errImagen === UPLOAD_ERR_OK) {
            $imagenModel->subir($archivoImagen, $id);
        }

        header('Location: ../vista/editar_publicacion.php?id=' . $id . '&guardado=1');
        exit;
    }
    //Al pulsar el enlace de "Eliminar" en la tabla de publicaciones eliminamos la publicacion mediante su ID
    if ($accion === 'eliminar_publicacion') {
        //Obtenemos el ID tanto por POST como por GET y si no se obtiene por ningun lado es 0
        $id = isset($_POST['id']) ? (int) $_POST['id'] : (isset($_GET['id']) ? (int) $_GET['id'] : 0);
        //Si el id es 0 detenemos el proceso de eliminar
        if ($id <= 0) {
            header('Location: ../vista/admin.php?error=1');
            exit;
        }
        //Creamos una nueva instancia
        $publicacionModel = new Publicacion();
        //Ejecutamos la sentecia SQL para eliminar la publicacion
        $publicacionModel->eliminar($id);
        //Volvemos al panel de administracion
        header('Location: ../vista/admin.php');
        exit;
    }

    //Redirigimos a la vista de administración
    header('Location: ../vista/admin.php');
    exit;

?>
