-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-12-2025 a las 20:08:01
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `amigo`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `participantes`
--

CREATE TABLE `participantes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `hobbies` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`hobbies`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `participantes`
--

INSERT INTO `participantes` (`id`, `nombre`, `apellido`, `email`, `password`, `gender`, `foto`, `hobbies`) VALUES
(8, 'Emilio', 'Cocera', 'cocerae@gmail.com', '$2y$10$L/lMivHYWuJhRBRGH.TIBevdxF8SoDgOQTRDs7aXFvP6ylm4FGMzy', 'femenino', 'foto_1765562379_8290.jpg', '[\"Peluqueria\",\"Reirse\",\"Aprender\"]'),
(9, 'Ana', 'Bello', 'ana@mail.com', '$2y$10$qYtnCxl.7Gy6FhdkJlKjU./YbSxPZ9lwcd4niLz2aE6rgllPNVtNK', 'femenino', 'foto_1765555808_3214.png', '[\"Moda\",\"Maquillaje\"]'),
(10, 'Sofia', 'Bellester', 'sofia@mail.com', '$2y$10$RvxjlZ7gh1UBFP7l0MwHKuoBJGMeaOftssJ/dFTrTJl41jgZfVWcq', 'femenino', 'foto_1765555876_8639.jpg', '[\"Dibujo\",\"Gym\"]'),
(11, 'Alejandro', 'Magno', 'ale@mail.com', '$2y$10$TqezrAM/dI/ZgfVXPqATfuHxVDPxxoWZWUGSoMhZS9m/ZyIqqsgOu', 'masculino', 'foto_1765555936_6908.jpg', '[\"Videojuegos\",\"Tecnologia\",\"Programar\"]'),
(12, 'Pepito', 'Grillo', 'pepito@mail.com', '$2y$10$GmOBBOZ12MxuzeuP0Wz98OHbQl9u4cnhHiMrDzNN1YPrSyOw7rLUG', 'masculino', 'foto_1765556250_7359.jpg', '[\"Paraguas\",\"Sombrero de copa\",\"Zapatos\"]'),
(13, 'Alice', 'Wonder', 'alice@mail.com', '$2y$10$Kazs69FO.cm2qH9c/tXLyOFKuTSzCZIc8KTyQTFLq31JPoOujI2cK', 'femenino', 'foto_1765556287_9538.jpg', '[\"Zapatos de mujer\",\"Limpieza\"]'),
(14, 'Aladdin', 'Segarro', 'aladdin@mail.com', '$2y$10$V80H8iwW3m4/56LtAGI5NOO01rc6ovkOAjIABdlXzYNLONftETYfu', 'masculino', 'foto_1765556370_8703.jpg', '[\"Machetes\",\"Cigarros\",\"Kebab\"]'),
(15, 'Pinoccio', 'Woods', 'pino@mail.com', '$2y$10$T6hNV9KG907NLaz.FDngNeXOqqqbsU0TT0T.YIXNuc/lOpAz6/qUK', 'masculino', 'foto_1765556428_9540.jpg', '[\"Carpinteria\",\"Comida\"]'),
(17, 'Didi', 'Kong', 'didi@gmail.com', '$2y$10$Zo4PwLlGGOh3ioHBhoV4letzuUSrDQ4MhP0vnDs9udtSDHGKe8Kde', 'masculino', 'foto_1765561096_5653.jpg', '[\"Platanos\",\"Videojuegos\"]');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `regalos`
--

CREATE TABLE `regalos` (
  `id` int(11) NOT NULL,
  `id_dador` int(11) NOT NULL,
  `id_receptor` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `regalos`
--

INSERT INTO `regalos` (`id`, `id_dador`, `id_receptor`) VALUES
(9, 8, 14),
(10, 9, 11),
(11, 10, 13),
(12, 11, 17),
(13, 12, 9),
(14, 13, 10),
(15, 14, 15),
(16, 15, 8),
(17, 17, 12);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `participantes`
--
ALTER TABLE `participantes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `regalos`
--
ALTER TABLE `regalos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_dador` (`id_dador`),
  ADD KEY `fk_receptor` (`id_receptor`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `participantes`
--
ALTER TABLE `participantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `regalos`
--
ALTER TABLE `regalos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `regalos`
--
ALTER TABLE `regalos`
  ADD CONSTRAINT `fk_dador` FOREIGN KEY (`id_dador`) REFERENCES `participantes` (`id`),
  ADD CONSTRAINT `fk_receptor` FOREIGN KEY (`id_receptor`) REFERENCES `participantes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
