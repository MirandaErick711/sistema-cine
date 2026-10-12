-- =======================================================
-- Base de Datos: cine_db
-- Proyecto: Sistema Web de Cine - Cine UNS
-- Compatible con XAMPP / MariaDB / MySQL y phpMyAdmin
-- =======================================================

CREATE DATABASE IF NOT EXISTS `cine_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `cine_db`;

-- Desactivar temporalmente revision de claves foraneas para creacion limpia
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `detalle_reserva`;
DROP TABLE IF EXISTS `reservas`;
DROP TABLE IF EXISTS `funciones`;
DROP TABLE IF EXISTS `peliculas`;
DROP TABLE IF EXISTS `usuarios`;

SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- 1. Tabla de Usuarios
-- Roles permitidos: 'admin', 'cliente'
-- Estado: 'activo', 'inactivo'
-- --------------------------------------------------------
CREATE TABLE `usuarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `correo` VARCHAR(120) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `telefono` VARCHAR(20) DEFAULT NULL,
  `rol` ENUM('admin', 'cliente') NOT NULL DEFAULT 'cliente',
  `estado` ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
  `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. Tabla de Peliculas
-- Estado: 'activa', 'inactiva' (permite retirar de cartelera sin eliminar registros)
-- --------------------------------------------------------
CREATE TABLE `peliculas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `titulo` VARCHAR(150) NOT NULL,
  `sinopsis` TEXT DEFAULT NULL,
  `genero` VARCHAR(80) NOT NULL,
  `duracion` VARCHAR(50) NOT NULL,
  `precio` DECIMAL(10,2) NOT NULL,
  `imagen` VARCHAR(255) NOT NULL,
  `trailer` VARCHAR(255) DEFAULT NULL,
  `estado` ENUM('activa', 'inactiva') NOT NULL DEFAULT 'activa',
  `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. Tabla de Funciones
-- Relacion con pelicula, fecha, hora, sala, capacidad y estado
-- --------------------------------------------------------
CREATE TABLE `funciones` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `pelicula_id` INT NOT NULL,
  `sala` VARCHAR(50) NOT NULL DEFAULT 'Sala 1',
  `fecha` DATE NOT NULL,
  `hora` TIME NOT NULL,
  `capacidad` INT NOT NULL DEFAULT 30,
  `estado` ENUM('disponible', 'agotada', 'cancelada') NOT NULL DEFAULT 'disponible',
  `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`pelicula_id`) REFERENCES `peliculas`(`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. Tabla de Reservas
-- Relacion con usuario y funcion, cantidad de entradas, total, codigo y estado
-- --------------------------------------------------------
CREATE TABLE `reservas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `codigo` VARCHAR(20) NOT NULL UNIQUE,
  `usuario_id` INT NOT NULL,
  `funcion_id` INT NOT NULL,
  `cantidad_entradas` INT NOT NULL,
  `total` DECIMAL(10,2) NOT NULL,
  `tipo_entrada` ENUM('normal', 'estudiante') NOT NULL DEFAULT 'normal',
  `estado` ENUM('confirmada', 'cancelada', 'finalizada') NOT NULL DEFAULT 'confirmada',
  `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
  FOREIGN KEY (`funcion_id`) REFERENCES `funciones`(`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 5. Tabla de Detalle de Reserva (Asientos)
-- Restriccion UNIQUE(funcion_id, asiento) para evitar doble reserva del mismo asiento
-- --------------------------------------------------------
CREATE TABLE `detalle_reserva` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `reserva_id` INT NOT NULL,
  `funcion_id` INT NOT NULL,
  `asiento` VARCHAR(10) NOT NULL,
  `precio_unitario` DECIMAL(10,2) NOT NULL,
  UNIQUE KEY `uk_funcion_asiento` (`funcion_id`, `asiento`),
  FOREIGN KEY (`reserva_id`) REFERENCES `reservas`(`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`funcion_id`) REFERENCES `funciones`(`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =======================================================
-- DATOS INICIALES / SEMILLA (Basados en el proyecto actual)
-- =======================================================

-- 1. Peliculas existentes
INSERT INTO `peliculas` (`id`, `titulo`, `sinopsis`, `genero`, `duracion`, `precio`, `imagen`, `trailer`, `estado`) VALUES
(1, 'Demon Slayer: Castillo Infinito', 'Tanjiro y el Cuerpo de Exterminadores de Demonios se adentran en el misterioso e infinito Castillo para enfrentar a Muzan Kibutsuji y las Lunas Superiores en la batalla decisiva.', 'Anime / Accion', '155 min', 18.00, 'img/demon.jpg', 'https://www.youtube.com/watch?v=1iBnWJKbvHI', 'activa'),
(2, 'Avengers: Doomsday', 'Los heroes mas poderosos de la Tierra y el multiverso se enfrentan a una amenaza cosmica sin precedentes comandada por el temible Doctor Doom.', 'Accion / Ciencia ficcion', '150 min', 20.00, 'img/avengers.jpg', 'https://www.youtube.com/watch?v=lAr_uspgHm8', 'activa'),
(3, 'Coyote vs. Acme', 'Wile E. Coyote decide demandar legalmente a la corporacion Acme luego de que todos sus productos fallaran constantemente en su intento por atrapar al Correcaminos.', 'Comedia / Aventura', '120 min', 16.00, 'img/coyote.jpg', 'https://www.youtube.com/watch?v=DIqJb0LwSho', 'activa');

-- 2. Funciones iniciales para hoy y proximos dias
INSERT INTO `funciones` (`id`, `pelicula_id`, `sala`, `fecha`, `hora`, `capacidad`, `estado`) VALUES
(1, 1, 'Sala 1', CURDATE(), '16:00:00', 30, 'disponible'),
(2, 1, 'Sala 1', CURDATE(), '19:30:00', 30, 'disponible'),
(3, 1, 'Sala 2', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '18:00:00', 30, 'disponible'),
(4, 2, 'Sala 3', CURDATE(), '17:00:00', 30, 'disponible'),
(5, 2, 'Sala 3', CURDATE(), '20:30:00', 30, 'disponible'),
(6, 2, 'Sala 1', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '21:00:00', 30, 'disponible'),
(7, 3, 'Sala 2', CURDATE(), '15:00:00', 30, 'disponible'),
(8, 3, 'Sala 2', CURDATE(), '18:30:00', 30, 'disponible'),
(9, 3, 'Sala 3', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '16:30:00', 30, 'disponible');

-- 3. Usuario administrador inicial
-- Correo: admin@cine.com | Contrasena: Admin123*
-- Hash generado de forma segura mediante password_hash() con BCRYPT:
INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `password`, `telefono`, `rol`, `estado`) VALUES
(1, 'Administrador del Cine', 'admin@cine.com', '$2y$12$N3gU6H8sjLzCXJWS0fR61.cwFfCDIdV1Wx6RRbX2/ZVO5DG8b0seW', '987654321', 'admin', 'activo');
