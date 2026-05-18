CREATE DATABASE IF NOT EXISTS foro_floristeria;
USE foro_floristeria;

-- 1. Tabla Administrador
CREATE TABLE ADMINISTRADOR (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) UNIQUE NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    contrasena VARCHAR(255) NOT NULL
);

-- 2. Tabla Usuario
CREATE TABLE USUARIO (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL
);

-- 3. Tabla Publicación
CREATE TABLE PUBLICACION (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    texto TEXT NOT NULL,
    fecha_publicacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    valoracion INT DEFAULT 0,
    autor_id INT NOT NULL,
    admin_id INT NOT NULL,
    FOREIGN KEY (admin_id) REFERENCES ADMINISTRADOR(id) ON DELETE CASCADE
);

-- 4. Tabla Comentario
CREATE TABLE COMENTARIO (
    id INT AUTO_INCREMENT PRIMARY KEY,
    texto TEXT NOT NULL,
    fecha_publicacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    valoracion INT DEFAULT 0,
    usuario_id INT NOT NULL,
    publicacion_id INT NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES USUARIO(id) ON DELETE CASCADE,
    FOREIGN KEY (publicacion_id) REFERENCES PUBLICACION(id) ON DELETE CASCADE
);

-- 5. Tabla Imagen
CREATE TABLE IMAGEN (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tamano VARCHAR(50),
    descripcion TEXT,
    ruta_archivo VARCHAR(255) NOT NULL,
    publicacion_id INT NOT NULL,
    FOREIGN KEY (publicacion_id) REFERENCES PUBLICACION(id) ON DELETE CASCADE
);
--Cambios en el sql para no crear conflictos en los comentarios
-- 1. Permitir que usuario_id sea opcional (para cuando comente un admin)
ALTER TABLE comentario MODIFY usuario_id INT NULL;

-- 2. Añadir la columna para el ID del administrador
ALTER TABLE comentario ADD COLUMN admin_id INT NULL AFTER usuario_id;

-- 3. Crear la relación (Foreign Key) para la nueva columna admin_id
ALTER TABLE comentario 
ADD CONSTRAINT fk_comentario_admin 
FOREIGN KEY (admin_id) REFERENCES administrador(id) 
ON DELETE CASCADE ON UPDATE CASCADE;

-- 4. Eliminar la restricción antigua que está bloqueando los nulos
ALTER TABLE comentario DROP FOREIGN KEY comentario_ibfk_1;

-- 5. Volver a crearla pero permitiendo que sea opcional
ALTER TABLE comentario 
ADD CONSTRAINT fk_comentario_usuario 
FOREIGN KEY (usuario_id) REFERENCES usuario(id) 
ON DELETE CASCADE ON UPDATE CASCADE;

-- 6. Tabla Mensajes
CREATE TABLE MENSAJE (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    admin_id INT NOT NULL,
    texto TEXT NOT NULL,
    enviado_por ENUM('usuario', 'admin') NOT NULL,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    leido BOOLEAN DEFAULT FALSE,
    CONSTRAINT fk_mensaje_usuario FOREIGN KEY (usuario_id) REFERENCES USUARIO(id) ON DELETE CASCADE,
    CONSTRAINT fk_mensaje_admin FOREIGN KEY (admin_id) REFERENCES ADMINISTRADOR(id) ON DELETE CASCADE
);