-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-12-2020 a las 05:02:12
-- Versión del servidor: 10.4.11-MariaDB
-- Versión de PHP: 7.4.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `laboratorio-api`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividad`
--

CREATE TABLE `actividad` (
  `actividad_id` int(11) NOT NULL,
  `nombre` varchar(100) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `actividad`
--

INSERT INTO `actividad` (`actividad_id`, `nombre`) VALUES
(1, 'Analizar compuestos quimicos'),
(2, 'Hacer pruebas en ratas'),
(3, 'Evaluar efectividad'),
(4, 'Control de efectos adversos');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividad_protocolo`
--

CREATE TABLE `actividad_protocolo` (
  `actividad_procotolo_id` int(11) NOT NULL,
  `protocolo_id` int(11) DEFAULT NULL,
  `actividad_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `actividad_protocolo`
--

INSERT INTO `actividad_protocolo` (`actividad_procotolo_id`, `protocolo_id`, `actividad_id`) VALUES
(1, 1, 1),
(2, 2, 1),
(3, 2, 2),
(4, 3, 1),
(5, 3, 2),
(6, 3, 3),
(7, 4, 1),
(8, 4, 2),
(9, 4, 3),
(10, 4, 4),
(11, 5, 2),
(12, 5, 4),
(13, 6, 4),
(14, 6, 1),
(15, 7, 3),
(16, 7, 4),
(17, 8, 3),
(18, 8, 4),
(19, 8, 2),
(20, 8, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `protocolo`
--

CREATE TABLE `protocolo` (
  `protocolo_id` int(11) NOT NULL,
  `responsable_id` int(11) DEFAULT NULL,
  `proyecto_id` int(11) DEFAULT NULL,
  `nombre` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `fecha_inicio` datetime DEFAULT NULL,
  `fecha_fin` datetime DEFAULT NULL,
  `orden` int(11) DEFAULT NULL,
  `es_local` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `puntaje` int(11) DEFAULT NULL,
  `actual` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `protocolo`
--

INSERT INTO `protocolo` (`protocolo_id`, `responsable_id`, `proyecto_id`, `nombre`, `fecha_inicio`, `fecha_fin`, `orden`, `es_local`, `puntaje`, `actual`) VALUES
(1, 6, 1, 'Protocolo A', NULL, NULL, 1, 'N', NULL, 'S'),
(2, 5, 1, 'Protocolo B', NULL, NULL, 2, 'N', NULL, 'N'),
(3, 5, 2, 'Protocolo C', NULL, NULL, 1, 'N', NULL, 'N'),
(4, 6, 2, 'Protocolo D', NULL, NULL, 2, 'N', NULL, 'N'),
(5, 7, 3, 'Protocolo E', NULL, NULL, 1, 'N', NULL, 'N'),
(6, 8, 4, 'Protocolo F', NULL, NULL, 1, 'N', NULL, 'N'),
(7, NULL, NULL, 'Protocolo G', NULL, NULL, NULL, 'N', NULL, NULL),
(8, NULL, NULL, 'Protocolo H', NULL, NULL, NULL, 'N', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proyecto`
--

CREATE TABLE `proyecto` (
  `proyecto_id` int(11) NOT NULL,
  `responsable_id` int(11) DEFAULT NULL,
  `nombre` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `proyecto`
--

INSERT INTO `proyecto` (`proyecto_id`, `responsable_id`, `nombre`, `fecha_inicio`, `fecha_fin`) VALUES
(1, 9, 'Hepatalgina', '2020-12-04 04:46:34', '2020-12-09 00:00:00'),
(2, 9, 'Mejoralito', '2020-12-04 04:47:16', '2020-12-17 00:00:00'),
(3, 9, 'Ibupirac', '2020-12-04 04:47:40', '2020-12-15 00:00:00'),
(4, 9, 'Pastilla de carbon', '2020-12-04 04:47:59', '2020-12-18 00:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `roles` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:json)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `user`
--

INSERT INTO `user` (`id`, `name`, `email`, `username`, `password`, `roles`, `created_at`, `updated_at`) VALUES
(5, 'Fátima Zárate', 'fatima.zarate@gmail.com', 'fatima.zarate', 'wS9C9c2aSG8L5V/QhH3CZbkLilDIU8A21bfQMCwryOBAY0ER4+cLYjTohMj9dFRo3F5rJWrRl6IQiHhXMd0M7w==', '[\"ROLE_RESPONSABLE_PROTOCOLO\"]', '2020-12-04 03:18:30', '2020-12-04 03:18:30'),
(6, 'Juan Pérez', 'juan.perez@gmail.com', 'juan.perez', 'wS9C9c2aSG8L5V/QhH3CZbkLilDIU8A21bfQMCwryOBAY0ER4+cLYjTohMj9dFRo3F5rJWrRl6IQiHhXMd0M7w==', '[\"ROLE_RESPONSABLE_PROTOCOLO\"]', '2020-12-04 03:19:02', '2020-12-04 03:19:02'),
(7, 'Martin Baez', 'martin.baez@gmail.com', 'martin.baez', 'wS9C9c2aSG8L5V/QhH3CZbkLilDIU8A21bfQMCwryOBAY0ER4+cLYjTohMj9dFRo3F5rJWrRl6IQiHhXMd0M7w==', '[\"ROLE_RESPONSABLE_PROTOCOLO\"]', '2020-12-04 03:19:29', '2020-12-04 03:19:29'),
(8, 'Pedro Gonzalez', 'pedro.gonzalez@gmail.com', 'pedro.gonzalez', 'wS9C9c2aSG8L5V/QhH3CZbkLilDIU8A21bfQMCwryOBAY0ER4+cLYjTohMj9dFRo3F5rJWrRl6IQiHhXMd0M7w==', '[\"ROLE_RESPONSABLE_PROTOCOLO\"]', '2020-12-04 03:19:57', '2020-12-04 03:19:57'),
(9, 'Jefe Proyecto', 'jefe.proyecto@gmail.com', 'jefe.proyecto', 'wS9C9c2aSG8L5V/QhH3CZbkLilDIU8A21bfQMCwryOBAY0ER4+cLYjTohMj9dFRo3F5rJWrRl6IQiHhXMd0M7w==', '[\"ROLE_JEFE_PROYECTO\"]', '2020-12-04 03:20:45', '2020-12-04 03:20:45');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actividad`
--
ALTER TABLE `actividad`
  ADD PRIMARY KEY (`actividad_id`);

--
-- Indices de la tabla `actividad_protocolo`
--
ALTER TABLE `actividad_protocolo`
  ADD PRIMARY KEY (`actividad_procotolo_id`),
  ADD KEY `fk_actividad` (`actividad_id`),
  ADD KEY `protocolo_id` (`protocolo_id`);

--
-- Indices de la tabla `protocolo`
--
ALTER TABLE `protocolo`
  ADD PRIMARY KEY (`protocolo_id`),
  ADD KEY `protocolo_proyecto` (`proyecto_id`),
  ADD KEY `responsable_id` (`responsable_id`);

--
-- Indices de la tabla `proyecto`
--
ALTER TABLE `proyecto`
  ADD PRIMARY KEY (`proyecto_id`),
  ADD KEY `proyecto_responsable` (`responsable_id`);

--
-- Indices de la tabla `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`),
  ADD UNIQUE KEY `UNIQ_8D93D649F85E0677` (`username`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actividad`
--
ALTER TABLE `actividad`
  MODIFY `actividad_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `actividad_protocolo`
--
ALTER TABLE `actividad_protocolo`
  MODIFY `actividad_procotolo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `protocolo`
--
ALTER TABLE `protocolo`
  MODIFY `protocolo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `proyecto`
--
ALTER TABLE `proyecto`
  MODIFY `proyecto_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `actividad_protocolo`
--
ALTER TABLE `actividad_protocolo`
  ADD CONSTRAINT `FK_DD8B30D66014FACA` FOREIGN KEY (`actividad_id`) REFERENCES `actividad` (`actividad_id`),
  ADD CONSTRAINT `FK_DD8B30D698C25956` FOREIGN KEY (`protocolo_id`) REFERENCES `protocolo` (`protocolo_id`);

--
-- Filtros para la tabla `protocolo`
--
ALTER TABLE `protocolo`
  ADD CONSTRAINT `FK_70AD5E4353C59D72` FOREIGN KEY (`responsable_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_70AD5E43F625D1BA` FOREIGN KEY (`proyecto_id`) REFERENCES `proyecto` (`proyecto_id`);

--
-- Filtros para la tabla `proyecto`
--
ALTER TABLE `proyecto`
  ADD CONSTRAINT `FK_6FD202B953C59D72` FOREIGN KEY (`responsable_id`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
