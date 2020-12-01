-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3307
-- Tiempo de generación: 30-11-2020 a las 22:14:22
-- Versión del servidor: 10.4.10-MariaDB
-- Versión de PHP: 7.3.12

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividad_protocolo`
--

CREATE TABLE `actividad_protocolo` (
  `actividad_procotolo_id` int(11) NOT NULL,
  `protocolo_id` int(11) DEFAULT NULL,
  `actividad_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  `orden` int(11) NOT NULL,
  `es_local` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  `puntaje` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `protocolo`
--

INSERT INTO `protocolo` (`protocolo_id`, `responsable_id`, `proyecto_id`, `nombre`, `fecha_inicio`, `fecha_fin`, `orden`, `es_local`, `puntaje`) VALUES
(1, 1, NULL, 'Protocolo 1', NULL, NULL, 3, 'S', NULL);

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
(1, 2, 'APROBACION ANALGESICO', '2020-11-29 12:42:03', '2020-12-05 00:00:00');

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
(1, 'responsable', 'responsable@gmail.com', 'responsable', 'Eti36Ru/pWG6WfoIPiDFUBxUuyvgMA4L8+LLuGbGyqV9ATuT9brCWPchBqX5vFTF+DgntacecW+sSGD+GZts2A==', '[\"ROLE_RESPONSABLE_PROTOCOLO\"]', '2020-11-29 11:47:21', '2020-11-29 11:47:21'),
(2, 'JEFE', 'jefe@gmail.com', 'jefe', 'Eti36Ru/pWG6WfoIPiDFUBxUuyvgMA4L8+LLuGbGyqV9ATuT9brCWPchBqX5vFTF+DgntacecW+sSGD+GZts2A==', '[\"ROLE_JEFE_PROYECTO\"]', '2020-11-29 13:00:48', '2020-11-29 13:00:48');

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
  MODIFY `actividad_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `actividad_protocolo`
--
ALTER TABLE `actividad_protocolo`
  MODIFY `actividad_procotolo_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `protocolo`
--
ALTER TABLE `protocolo`
  MODIFY `protocolo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `proyecto`
--
ALTER TABLE `proyecto`
  MODIFY `proyecto_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
