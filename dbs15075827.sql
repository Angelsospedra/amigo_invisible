-- phpMyAdmin SQL Dump
-- version 4.9.11
-- https://www.phpmyadmin.net/
--
-- Host: db5019207052.hosting-data.io
-- Generation Time: Dec 15, 2025 at 11:07 AM
-- Server version: 8.0.36
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dbs15075827`
--

-- --------------------------------------------------------

--
-- Table structure for table `grupos`
--

CREATE TABLE `grupos` (
  `id` int NOT NULL,
  `nombre` varchar(500) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grupos`
--

INSERT INTO `grupos` (`id`, `nombre`) VALUES
(3, '1_DAM/DAW_2025'),
(4, '1_MARKETING_2025'),
(6, '1_TRANSPORTE_2025'),
(1, '2_COMERCIO_2025'),
(7, '2_DAM_2025'),
(5, '2_DAW_2025'),
(2, '2_MARKETING_2025'),
(8, '2_TRANSPORTE_2025'),
(9, 'PROFESORES');

-- --------------------------------------------------------

--
-- Table structure for table `participantes`
--

CREATE TABLE `participantes` (
  `id` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `apellido` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gender` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `hobbies` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `fecha_registro` timestamp NOT NULL
) ;

--
-- Dumping data for table `participantes`
--

INSERT INTO `participantes` (`id`, `nombre`, `apellido`, `email`, `password`, `gender`, `foto`, `hobbies`, `fecha_registro`) VALUES
(12, 'Adrián', 'Rivas', 'arivas@campuscamarafp.com', '$2y$10$Ldwl0MS1SdlSLXEZ35qHqevg7XqJuDhBVN9vvcoqD9kHNd8haVf12', NULL, NULL, NULL, '2025-12-13 16:22:31'),
(13, 'Alberto', 'Sanchis', 'alberto.sanchis@campuscamarafp.com', '$2y$10$vEBKpVRGlp1CkfexlLtaGu0dWVhX/XKAKz8/D3AgaSWQ0o7sfC1Jq', 'masculino', 'foto_1765791647_3541.jpg', '[\"tocar la guitarra\",\"jugar a futbol\",\"programaci\\u00f3n inform\\u00e1rtica\"]', '2025-12-13 16:22:31'),
(14, 'Amado', 'Sancho', 'asancho@campuscamarafp.com', '$2y$10$J9Gmu92hv4rKDnQoUIPCfujUGk6KCUfMUAq9160.fkpsCBrgN3jzS', NULL, NULL, NULL, '2025-12-13 16:22:31'),
(15, 'Andrea', 'Vicente', 'avicente@campuscamarafp.com', '$2y$10$uUtAS8QBnAbTyYNVqEsVtOlPKq2aV70.BvFWI.FbQpkMAa3Q6/Aqu', NULL, NULL, NULL, '2025-12-13 16:22:31'),
(16, 'Beatriz', 'Menéndez', 'bmenendez@campuscamarafp.com', '$2y$10$1.6UtEoqmDsuALSa.KEWGevoW/fOtBtBRysLGVYaczOSfLHkczHU2', NULL, NULL, NULL, '2025-12-13 16:22:31'),
(17, 'Berta', 'Aliaga', 'baliaga@campuscamarafp.com', '$2y$10$CvJUiTucv1dmvS4G89EcAOlqZJaXK6aaH9/62pSnAuhaUQ8TyYiDC', NULL, NULL, NULL, '2025-12-13 16:22:31'),
(18, 'Cristina', 'Vicente', 'cvicente@campuscamarafp.com', '$2y$10$f3cMV6Q8a0.RayUBD04zO.YFp38AuizmM/EiGuL.o63yQ3Cs/Q8iy', NULL, NULL, NULL, '2025-12-13 16:22:31'),
(19, 'Diego', 'Santamaría', 'dsantamaria@campuscamarafp.com', '', NULL, NULL, NULL, '2025-12-13 16:22:31'),
(20, 'Elena', 'Grech', 'egrech@campuscamarafp.com', '$2y$10$vxCVYCXNlvP2uLZ44h.6UOyN1F.oaDGTdqNrtP2utszjUIp.PXZbm', NULL, NULL, NULL, '2025-12-13 16:22:31'),
(21, 'Fran', 'Blay', 'fblay@campuscamarafp.com', '$2y$10$ZO1XriYfUGNoXtkRtqEs/.LsuUv36ZZhichVMo1aZrF8X/QGqMbD2', NULL, NULL, NULL, '2025-12-13 16:22:31'),
(22, 'Isaac', 'Jijón', 'ijijon@campuscamarafp.com', '$2y$10$tuBg2t43GlwqjWC/NsyckuHv9xABj.DaQbJXQXyfoUqug3rW/.EAe', NULL, NULL, NULL, '2025-12-13 16:22:31'),
(23, 'Jesús', 'Pérez', 'jperez@campuscamarafp.com', '', NULL, NULL, NULL, '2025-12-13 16:22:31'),
(24, 'María José', 'Moreno', 'mmoreno@campuscamarafp.com', '$2y$10$OUlS21tyN1vHII6ne6b6D.ekfpBoM9Fye1VYE1kxWqy9WC0gUa/Ee', NULL, NULL, NULL, '2025-12-13 16:22:31'),
(25, 'Marga', 'Domingo', 'mdomingo@campuscamarafp.com', '', NULL, NULL, NULL, '2025-12-13 16:22:31'),
(26, 'Pilar', 'Ruiz', 'mruiz@campuscamarafp.com', '', NULL, NULL, NULL, '2025-12-13 16:22:31'),
(27, 'Enrique', 'González', 'enrique.gonzalez@campuscamarafp.com', '$2y$10$EB.XfNmXEoD1uMqeoR1xne0axWBvOf9nZ5JV.9qHMBKRw2lfIlfNy', NULL, NULL, NULL, '2025-12-13 16:22:31'),
(28, 'Rubén', 'Belenguer', 'rbelenguer@campuscamarafp.com', '', NULL, NULL, NULL, '2025-12-13 16:22:31'),
(29, 'Luz', 'García', 'lgarcia@campuscamarafp.com', '$2y$10$lgecBgkHxn6QzG8c4A/HBO5/3uCMTEdUVCT3H94k47KCkRg2a8fjy', NULL, NULL, NULL, '2025-12-13 16:22:31'),
(30, 'Ricardo', 'Zaplana', 'rzaplana@campuscamarafp.com', '', NULL, NULL, NULL, '2025-12-14 08:21:46');

-- --------------------------------------------------------

--
-- Table structure for table `participante_grupo`
--

CREATE TABLE `participante_grupo` (
  `id` int NOT NULL,
  `id_participante` int NOT NULL,
  `id_grupo` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `participante_grupo`
--

INSERT INTO `participante_grupo` (`id`, `id_participante`, `id_grupo`) VALUES
(1, 12, 9),
(2, 13, 9),
(3, 14, 9),
(4, 15, 9),
(5, 16, 9),
(6, 17, 9),
(7, 18, 9),
(8, 19, 9),
(9, 20, 9),
(10, 21, 9),
(11, 22, 9),
(12, 23, 9),
(13, 24, 9),
(14, 25, 9),
(15, 26, 9),
(16, 27, 9),
(17, 28, 9),
(18, 29, 9),
(19, 30, 9);

-- --------------------------------------------------------

--
-- Table structure for table `regalos`
--

CREATE TABLE `regalos` (
  `id` int NOT NULL,
  `id_dador` int NOT NULL,
  `id_receptor` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `regalos`
--

INSERT INTO `regalos` (`id`, `id_dador`, `id_receptor`) VALUES
(1, 30, 12),
(2, 12, 15),
(3, 15, 27),
(4, 27, 28),
(5, 28, 14),
(6, 14, 18),
(7, 18, 24),
(8, 24, 29),
(9, 29, 26),
(10, 26, 25),
(11, 25, 13),
(12, 13, 20),
(13, 20, 17),
(14, 17, 22),
(15, 22, 19),
(16, 19, 23),
(17, 23, 16),
(18, 16, 21),
(19, 21, 30);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `grupos`
--
ALTER TABLE `grupos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indexes for table `participantes`
--
ALTER TABLE `participantes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `participante_grupo`
--
ALTER TABLE `participante_grupo`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `regalos`
--
ALTER TABLE `regalos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_dador` (`id_dador`),
  ADD KEY `fk_receptor` (`id_receptor`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `grupos`
--
ALTER TABLE `grupos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `participantes`
--
ALTER TABLE `participantes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `participante_grupo`
--
ALTER TABLE `participante_grupo`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `regalos`
--
ALTER TABLE `regalos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `regalos`
--
ALTER TABLE `regalos`
  ADD CONSTRAINT `fk_dador` FOREIGN KEY (`id_dador`) REFERENCES `participantes` (`id`),
  ADD CONSTRAINT `fk_receptor` FOREIGN KEY (`id_receptor`) REFERENCES `participantes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
