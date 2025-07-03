-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Erstellungszeit: 30. Jun 2025 um 16:15
-- Server-Version: 10.3.39-MariaDB-0+deb10u2
-- PHP-Version: 7.3.31-1~deb10u7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `environment-monitoring-simple`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `gebaeude`
--

CREATE TABLE `gebaeude` (
  `gebaeude_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `postleitzahl` int(11) NOT NULL,
  `ort` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `gebaeude`
--

INSERT INTO `gebaeude` (`gebaeude_id`, `name`, `adresse`, `postleitzahl`, `ort`) VALUES
(1, 'Zentrum für berufliche Weiterbildung', 'Gaiserwaldstrasse 6', 9015, 'Sankt Gallen');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `messung`
--

CREATE TABLE `messung` (
  `messung_id` bigint(20) NOT NULL,
  `board_id` int(11) NOT NULL,
  `sensortyp_id` int(11) NOT NULL,
  `zeitstempel` datetime NOT NULL,
  `messwert` decimal(10,3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `raum`
--

CREATE TABLE `raum` (
  `raum_id` int(11) NOT NULL,
  `gebaeude_id` int(11) NOT NULL,
  `bezeichnung` varchar(100) DEFAULT NULL,
  `etage` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `raum`
--

INSERT INTO `raum` (`raum_id`, `gebaeude_id`, `bezeichnung`, `etage`) VALUES
(1, 1, '310', '3'),
(2, 1, '325', '3'),
(3, 1, '201', '2'),
(4, 1, '225', '2');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `sensorboard`
--

CREATE TABLE `sensorboard` (
  `board_id` int(11) NOT NULL,
  `raum_id` int(11) NOT NULL,
  `seriennummer` varchar(100) NOT NULL,
  `mac` varchar(100) DEFAULT NULL,
  `installationsdatum` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `sensorboard`
--

INSERT INTO `sensorboard` (`board_id`, `raum_id`, `seriennummer`, `mac`, `installationsdatum`) VALUES
(1, 1, '1001', 'd8:3a:dd:94:ac:28', '2025-06-30'),
(2, 2, '1002', 'd8:3a:dd:94:ab:ef', '2025-06-30');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `sensortyp`
--

CREATE TABLE `sensortyp` (
  `sensortyp_id` int(11) NOT NULL,
  `bezeichnung` varchar(100) NOT NULL,
  `einheit` varchar(50) DEFAULT NULL,
  `beschreibung` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `sensortyp`
--

INSERT INTO `sensortyp` (`sensortyp_id`, `bezeichnung`, `einheit`, `beschreibung`) VALUES
(1, 'temp', '°C', 'Temperatur'),
(2, 'hum', '%', 'Relative Luftfeuchtigkeit'),
(3, 'pres', 'hPa', 'Luftdruck'),
(4, 'lum', 'Lx', 'Lichtstärke'),
(5, 'lou', 'dB', 'Lautstärke');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `gebaeude`
--
ALTER TABLE `gebaeude`
  ADD PRIMARY KEY (`gebaeude_id`);

--
-- Indizes für die Tabelle `messung`
--
ALTER TABLE `messung`
  ADD PRIMARY KEY (`messung_id`),
  ADD KEY `sensortyp_id` (`sensortyp_id`),
  ADD KEY `board_id` (`board_id`,`zeitstempel`);

--
-- Indizes für die Tabelle `raum`
--
ALTER TABLE `raum`
  ADD PRIMARY KEY (`raum_id`),
  ADD KEY `gebaeude_id` (`gebaeude_id`);

--
-- Indizes für die Tabelle `sensorboard`
--
ALTER TABLE `sensorboard`
  ADD PRIMARY KEY (`board_id`),
  ADD UNIQUE KEY `seriennummer` (`seriennummer`),
  ADD KEY `raum_id` (`raum_id`);

--
-- Indizes für die Tabelle `sensortyp`
--
ALTER TABLE `sensortyp`
  ADD PRIMARY KEY (`sensortyp_id`),
  ADD UNIQUE KEY `bezeichnung` (`bezeichnung`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `gebaeude`
--
ALTER TABLE `gebaeude`
  MODIFY `gebaeude_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT für Tabelle `messung`
--
ALTER TABLE `messung`
  MODIFY `messung_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `raum`
--
ALTER TABLE `raum`
  MODIFY `raum_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT für Tabelle `sensorboard`
--
ALTER TABLE `sensorboard`
  MODIFY `board_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT für Tabelle `sensortyp`
--
ALTER TABLE `sensortyp`
  MODIFY `sensortyp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `messung`
--
ALTER TABLE `messung`
  ADD CONSTRAINT `messung_ibfk_1` FOREIGN KEY (`board_id`) REFERENCES `sensorboard` (`board_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `messung_ibfk_2` FOREIGN KEY (`sensortyp_id`) REFERENCES `sensortyp` (`sensortyp_id`) ON UPDATE CASCADE;

--
-- Constraints der Tabelle `raum`
--
ALTER TABLE `raum`
  ADD CONSTRAINT `raum_ibfk_1` FOREIGN KEY (`gebaeude_id`) REFERENCES `gebaeude` (`gebaeude_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints der Tabelle `sensorboard`
--
ALTER TABLE `sensorboard`
  ADD CONSTRAINT `sensorboard_ibfk_1` FOREIGN KEY (`raum_id`) REFERENCES `raum` (`raum_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
