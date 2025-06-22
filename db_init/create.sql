SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS INCIDENCIES
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;



-- Donem permisos a l'usuari 'usuari' per accedir a la base de dades 'persones'
-- sinó, aquest usuari no podrà veure la base de dades i no podrà accedir a les taules
GRANT ALL PRIVILEGES ON INCIDENCIES.* TO 'usuari'@'%';
FLUSH PRIVILEGES;

USE INCIDENCIES;

-- Tablas importadas desde phpMyAdmin

-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Temps de generació: 12-05-2025 a les 08:01:24
-- Versió del servidor: 10.11.10-MariaDB-ubu2204
-- Versió de PHP: 8.2.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de dades: `a24alvsalalv_daw`
--
-- --------------------------------------------------------

--
-- Estructura de la taula `ACTUACIO`
--

CREATE TABLE `ACTUACIO` (
  `id_actuacio` int(11) NOT NULL,
  `descripcio` text NOT NULL,
  `data_creacio` datetime DEFAULT current_timestamp(),
  `visible_usuari` tinyint(1) DEFAULT 1,
  `tecnic_id` int(11) NOT NULL,
  `incidencia_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Bolcament de dades per a la taula `ACTUACIO`
--

INSERT INTO `ACTUACIO` (`id_actuacio`, `descripcio`, `data_creacio`, `visible_usuari`, `tecnic_id`, `incidencia_id`) VALUES
(62, ' gfghfg', '2025-05-18 14:20:32', 0, 22, 85),
(63, 'fdgdgd', '2025-05-18 14:20:35', 0, 22, 85),
(64, 'dgfdgdf', '2025-05-18 14:20:39', 1, 22, 85),
(67, 'VALE', '2025-05-18 14:48:38', 1, 22, 87),
(69, 's\' ha realitzat el primer diagnostic del ordinador', '2025-05-18 16:41:44', 0, 28, 89),
(70, 'ordinador arreglat!', '2025-05-18 16:41:54', 1, 28, 89),
(71, 'primer diagnostic', '2025-05-18 16:57:53', 0, 28, 90),
(72, 'problema solucionat!', '2025-05-18 16:58:02', 1, 28, 90),
(73, 'es canvia el teclat per un altre per comproabr que no es el usb del ordinador', '2025-05-18 18:18:52', 0, 28, 91),
(74, 'es fica un nou teclat al ordinador 212, incidencia resolta!', '2025-05-18 18:19:12', 1, 28, 91);

-- --------------------------------------------------------

--
-- Estructura de la taula `DEPARTAMENT`
--

CREATE TABLE `DEPARTAMENT` (
  `id_departament` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Bolcament de dades per a la taula `DEPARTAMENT`
--

INSERT INTO `DEPARTAMENT` (`id_departament`, `nom`) VALUES
(1, 'Professorat'),
(2, 'Alumnat'),
(3, 'Secretaria / Administració'),
(4, 'Informàtica / TIC'),
(5, 'Direcció / Coordinació'),
(6, 'Consergeria / Serveis generals'),
(7, 'Altres');

-- --------------------------------------------------------

--
-- Estructura de la taula `INCIDENCIA`
--

CREATE TABLE `INCIDENCIA` (
  `id_incidencia` int(11) NOT NULL,
  `descripcio` text DEFAULT NULL,
  `estat` enum('pendent','resolta','en procés') DEFAULT 'pendent',
  `prioritat` enum('Alta','Mitja','Baixa') DEFAULT NULL,
  `data_creacio` datetime DEFAULT current_timestamp(),
  `usuari_id` int(11) NOT NULL,
  `tipus_id` int(11) DEFAULT NULL,
  `tecnic_id` int(11) DEFAULT NULL,
  `eliminat` tinyint(4) DEFAULT 0,
  `resolta` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Bolcament de dades per a la taula `INCIDENCIA`
--

INSERT INTO `INCIDENCIA` (`id_incidencia`, `descripcio`, `estat`, `prioritat`, `data_creacio`, `usuari_id`, `tipus_id`, `tecnic_id`, `eliminat`, `resolta`) VALUES
(81, 'QUIERO SOPA DE MACACO', 'pendent', NULL, '2025-05-16 10:37:27', 31, 4, NULL, 0, 0),
(82, 'asd', 'resolta', NULL, '2025-05-16 14:52:15', 20, 13, NULL, 0, 0),
(83, 'dsad', 'pendent', 'Alta', '2025-05-16 15:56:48', 27, 2, 28, 0, 0),
(84, 'fsdfds', 'pendent', 'Alta', '2025-05-17 15:01:17', 27, 4, NULL, 0, 0),
(85, 'dasdasd', 'resolta', NULL, '2025-05-17 17:26:31', 27, 3, 22, 0, 1),
(86, 'dadsada', 'pendent', NULL, '2025-05-17 17:36:50', 34, 3, NULL, 1, 0),
(87, 'no puc imprimir cap paper', 'pendent', 'Mitja', '2025-05-18 14:40:31', 39, 12, 26, 0, 0),
(88, 'drfgdfg', 'pendent', 'Baixa', '2025-05-18 15:40:35', 42, 5, 28, 0, 0),
(89, 'L&#039;ordinador de la aula 210 va molt lent i fa un soroll extrany', 'resolta', 'Mitja', '2025-05-18 16:39:11', 43, 8, 26, 0, 1),
(90, 'el meu oridinador va molt lent i te problemes amb els pantallazos blaus', 'resolta', 'Baixa', '2025-05-18 16:57:11', 45, 8, 26, 0, 1),
(91, 'el teclat no escriu correctament del ordinador 212', 'resolta', 'Alta', '2025-05-18 18:18:02', 47, 11, 22, 0, 1),
(92, 'El projector de la clase B no engega', 'pendent', NULL, '2025-05-18 20:49:22', 31, 3, NULL, 0, 0);

-- --------------------------------------------------------

--
-- Estructura de la taula `TECNIC`
--

CREATE TABLE `TECNIC` (
  `id_tecnic` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `contrasenya` varchar(255) NOT NULL,
  `administrador` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Bolcament de dades per a la taula `TECNIC`
--

INSERT INTO `TECNIC` (`id_tecnic`, `nom`, `email`, `contrasenya`, `administrador`) VALUES
(22, 'Bryan Ruzafa', 'a24bryruzgon@inspedralbes.cat', '$2y$10$uVF9YOgWbkivXCkOpdsNqeNQwMwzsVgA/SqY8HTNT4Gy94cCGythu', 1),
(26, 'Alvaro Saldaña', 'a24alvsalalv@inspedralbes.cat', '$2y$10$hxDW4wNU2kTF4EUm0n8EEeMrjXbx..MSXuY3UBcjYvwJQ4QHWlegK', 1),
(28, 'profes', 'profes@gmail.com', '$2y$10$3bztLUBTyS.mATabFavWm.sHd9fTx/aqaWLHIXYJ0NgcHP9yQLq.6', 0),
(29, 'admin', 'admin@gmail.com', '$2y$10$JtXl9g0NJm7tsfQJ4zTUo.NpYsl.0P5vXFugYHypXqqNYcy9zI4Yu', 1);

--
-- Disparadors `TECNIC`
--
DELIMITER $$
CREATE TRIGGER `afegir_tecnic_a_usuaris` AFTER INSERT ON `TECNIC` FOR EACH ROW BEGIN
    INSERT INTO USUARI (nom, email, contrasenya, rol)
    VALUES (
        NEW.nom,
        NEW.email,
        NEW.contrasenya,
        IF(NEW.administrador = 1, 'admin', 'tecnic')
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de la taula `TIPUS_INCIDENCIA`
--

CREATE TABLE `TIPUS_INCIDENCIA` (
  `id_tipus` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Bolcament de dades per a la taula `TIPUS_INCIDENCIA`
--

INSERT INTO `TIPUS_INCIDENCIA` (`id_tipus`, `nom`) VALUES
(1, 'L’ordinador no encén'),
(2, 'Sense connexió a internet'),
(3, 'El projector no funciona'),
(4, 'Problemes d\'accés a plataformes educatives'),
(5, 'Error d’inici de sessió'),
(6, 'Problemes amb el correu institucional'),
(7, 'La impressora no funciona'),
(8, 'Ordinador lent'),
(9, 'Aplicació educativa no respon'),
(10, 'Problemes amb la connexió Wi-Fi'),
(11, 'Teclat o ratolí no funcionen'),
(12, 'No es pot imprimir'),
(13, 'Problemes amb la pantalla digital'),
(14, 'Error amb programari educatiu'),
(15, 'Problemes amb el sistema operatiu'),
(16, 'Altres');

-- --------------------------------------------------------

--
-- Estructura de la taula `USUARI`
--

CREATE TABLE `USUARI` (
  `id_usuari` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `departament_id` int(11) DEFAULT NULL,
  `contrasenya` varchar(255) NOT NULL,
  `rol` enum('usuari','tecnic','admin') DEFAULT 'usuari'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Bolcament de dades per a la taula `USUARI`
--

INSERT INTO `USUARI` (`id_usuari`, `nom`, `email`, `departament_id`, `contrasenya`, `rol`) VALUES
(20, 'bryan', 'bryannrg10@gmail.com', NULL, '$2y$10$N9xAaw7V1J9Wq1oz34mSAuRhevGDfh6y3fKvfRdwkNaXz1OxbrO0i', 'usuari'),
(21, 'Bryan', 'Ruzafa@gmail.com', NULL, '$2y$10$XKJwR2mtq0BXehg4YuIZUeESFsDtErizPSOVsMWWYshLvgUISyZ8W', 'usuari'),
(22, 'bryan', 'bryan@gmail.com', NULL, '1234', 'admin'),
(23, 'bryan', 'brya2n@gmail.com', NULL, '1234', 'admin'),
(27, 'Bryan Ruiz', 'a24bryruzgon@inspedralbes.cat', NULL, '$2y$10$uVF9YOgWbkivXCkOpdsNqeNQwMwzsVgA/SqY8HTNT4Gy94cCGythu', 'admin'),
(31, 'Alvaro Salas', 'a24alvsalalv@inspedralbes.cat', NULL, '$2y$10$hxDW4wNU2kTF4EUm0n8EEeMrjXbx..MSXuY3UBcjYvwJQ4QHWlegK', 'admin'),
(32, 'pepe', 'pepe@gmail.com', NULL, '$2y$10$WCu4OmnC21P0oNnRQYz/uOH1eQAcCbSiTTmfip6efd1xK.uQI9lJO', 'usuari'),
(33, 'porova', 'prova@gmail.com', NULL, '$2y$10$fp8jukmHYl4uznQ59G5PjeaS5oa7F7A7DAlQP8byw.KmBQ0qz6CbW', 'usuari'),
(34, 'prova2', 'prova2@gmail.com', NULL, '$2y$10$tYrESnvFTGRd33gt0eCXEe8M/QQtRyLA5EcMqC/MFk.ByzpZmzwkK', 'usuari'),
(35, 'hola', 'hola@gmail.com', NULL, '$2y$10$4d0MgMDkXwv9eeuiiFnO6eitDfPeKawPGEPHZGu5uyonPXIhdM3kS', 'usuari'),
(36, 'josep', 'josep@gmail.com', NULL, '$2y$10$/jBPS5oad5YFVR9FHZv/XuzjvfK.zUil6xAtQzYPI2cJhtDP2GF4G', 'tecnic'),
(39, 'marcos', 'arco@gmail.com', NULL, '$2y$10$Q1VYkFrU1Ink9XA5I59Mc.Hb2xjgAviu8Gs3ATiqos65WB45vxpYa', 'usuari'),
(40, 'profes', 'profes@gmail.com', NULL, '$2y$10$3bztLUBTyS.mATabFavWm.sHd9fTx/aqaWLHIXYJ0NgcHP9yQLq.6', 'tecnic'),
(41, 'admin', 'admin@gmail.com', NULL, '$2y$10$JtXl9g0NJm7tsfQJ4zTUo.NpYsl.0P5vXFugYHypXqqNYcy9zI4Yu', 'admin'),
(42, 'joan', 'joanet@gmail.com', NULL, '$2y$10$YlzCtJSUjBIT6G0om9TaWeZCaLuuMRP0XMyKA1P8HJh1lRNaGlpTK', 'usuari'),
(43, 'usuari_prova', 'usuari_prova@gmail.com', NULL, '$2y$10$vqazj9b2JHp.zQQNFMWo.ui5bxCE5/tAdj7UMZ4SWRMbif9G961F6', 'usuari'),
(44, 'tecnic2', 'tecnic2@gmail.com', NULL, '$2y$10$7ntZ/4NGWdDlRNzxOp0OBOZtWmfixOL/Bz1FyFPboxaCJ1NaK2cSW', 'tecnic'),
(45, 'prova1', 'prova1@gmail.com', NULL, '$2y$10$r/3iAcYPo7BqZ1ChrIByB.lelLpHhGiJC.VzpF7c2mTaX9lOM.nlm', 'usuari'),
(46, 'administrador', 'administrador@gmail.com', NULL, '$2y$10$DtJ8ZTjBGBhwSdyMgjutkeVCijGzzE9.3nc/Cbm9UQycUfLY1AnNa', 'tecnic'),
(47, 'miquel', 'miquel@gmail.com', NULL, '$2y$10$fiGKnKVImu345g8m9ZUNJun5YOjv2UI1a65ffAXXEvwLD6veJ1uIe', 'usuari'),
(48, 'tecnic3', 'tecnic3@gmail.com', NULL, '$2y$10$dhhF3gtHFGda97X/W4puiOzSMJFPx/w7810Y7NzYApbjsSvHDO9my', 'tecnic');

--
-- Índexs per a les taules bolcades
--

--
-- Índexs per a la taula `ACTUACIO`
--
ALTER TABLE `ACTUACIO`
  ADD PRIMARY KEY (`id_actuacio`),
  ADD KEY `ACTUACIO_ibfk_2` (`incidencia_id`),
  ADD KEY `fk_tecnic_actuacio` (`tecnic_id`);

--
-- Índexs per a la taula `DEPARTAMENT`
--
ALTER TABLE `DEPARTAMENT`
  ADD PRIMARY KEY (`id_departament`);

--
-- Índexs per a la taula `INCIDENCIA`
--
ALTER TABLE `INCIDENCIA`
  ADD PRIMARY KEY (`id_incidencia`),
  ADD KEY `usuari_id` (`usuari_id`),
  ADD KEY `tipus_id` (`tipus_id`),
  ADD KEY `tecnic_id` (`tecnic_id`);

--
-- Índexs per a la taula `TECNIC`
--
ALTER TABLE `TECNIC`
  ADD PRIMARY KEY (`id_tecnic`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índexs per a la taula `TIPUS_INCIDENCIA`
--
ALTER TABLE `TIPUS_INCIDENCIA`
  ADD PRIMARY KEY (`id_tipus`);

--
-- Índexs per a la taula `USUARI`
--
ALTER TABLE `USUARI`
  ADD PRIMARY KEY (`id_usuari`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `email_2` (`email`),
  ADD KEY `departament_id` (`departament_id`);

--
-- AUTO_INCREMENT per les taules bolcades
--

--
-- AUTO_INCREMENT per la taula `ACTUACIO`
--
ALTER TABLE `ACTUACIO`
  MODIFY `id_actuacio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT per la taula `DEPARTAMENT`
--
ALTER TABLE `DEPARTAMENT`
  MODIFY `id_departament` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT per la taula `INCIDENCIA`
--
ALTER TABLE `INCIDENCIA`
  MODIFY `id_incidencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT per la taula `TECNIC`
--
ALTER TABLE `TECNIC`
  MODIFY `id_tecnic` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT per la taula `TIPUS_INCIDENCIA`
--
ALTER TABLE `TIPUS_INCIDENCIA`
  MODIFY `id_tipus` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT per la taula `USUARI`
--
ALTER TABLE `USUARI`
  MODIFY `id_usuari` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- Restriccions per a les taules bolcades
--

--
-- Restriccions per a la taula `ACTUACIO`
--
ALTER TABLE `ACTUACIO`
  ADD CONSTRAINT `ACTUACIO_ibfk_2` FOREIGN KEY (`incidencia_id`) REFERENCES `INCIDENCIA` (`id_incidencia`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tecnic_actuacio` FOREIGN KEY (`tecnic_id`) REFERENCES `TECNIC` (`id_tecnic`) ON DELETE CASCADE;

--
-- Restriccions per a la taula `INCIDENCIA`
--
ALTER TABLE `INCIDENCIA`
  ADD CONSTRAINT `INCIDENCIA_ibfk_1` FOREIGN KEY (`usuari_id`) REFERENCES `USUARI` (`id_usuari`),
  ADD CONSTRAINT `INCIDENCIA_ibfk_2` FOREIGN KEY (`tipus_id`) REFERENCES `TIPUS_INCIDENCIA` (`id_tipus`),
  ADD CONSTRAINT `INCIDENCIA_ibfk_3` FOREIGN KEY (`tecnic_id`) REFERENCES `TECNIC` (`id_tecnic`);

--
-- Restriccions per a la taula `USUARI`
--
ALTER TABLE `USUARI`
  ADD CONSTRAINT `USUARI_ibfk_1` FOREIGN KEY (`departament_id`) REFERENCES `DEPARTAMENT` (`id_departament`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
