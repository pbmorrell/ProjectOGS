-- phpMyAdmin SQL Dump
-- version 4.4.13.1
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Mar 26, 2016 at 05:40 PM
-- Server version: 5.6.26
-- PHP Version: 5.5.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `raiden01_projectogs`
--

-- --------------------------------------------------------

--
-- Table structure for table `administration.logging`
--

CREATE TABLE IF NOT EXISTS `administration.logging` (
  `ID` int(11) NOT NULL,
  `Category` varchar(35) DEFAULT NULL,
  `Title` varchar(50) DEFAULT NULL,
  `Message` text,
  `EntryTimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `configuration.gamegenres`
--

CREATE TABLE IF NOT EXISTS `configuration.gamegenres` (
  `ID` int(11) NOT NULL,
  `FK_Game_ID` int(11) NOT NULL,
  `FK_Genre_ID` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=256 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `configuration.gamegenres`
--

INSERT INTO `configuration.gamegenres` (`ID`, `FK_Game_ID`, `FK_Genre_ID`) VALUES
(1, 12, 38),
(2, 12, 39),
(3, 12, 42),
(4, 12, 52),
(5, 12, 55),
(6, 12, 56),
(8, 13, 38),
(9, 13, 44),
(10, 13, 49),
(11, 13, 56),
(12, 13, 57),
(15, 14, 38),
(16, 14, 44),
(17, 14, 49),
(18, 14, 56),
(19, 14, 57),
(22, 15, 43),
(23, 16, 47),
(24, 16, 52),
(25, 16, 56),
(26, 17, 38),
(27, 17, 44),
(28, 17, 52),
(29, 17, 55),
(30, 17, 57),
(33, 18, 38),
(34, 18, 44),
(35, 18, 52),
(36, 18, 55),
(37, 18, 57),
(40, 19, 38),
(41, 19, 44),
(42, 19, 49),
(43, 19, 56),
(44, 19, 57),
(47, 20, 38),
(48, 20, 44),
(49, 20, 49),
(50, 20, 56),
(51, 20, 57),
(54, 21, 38),
(55, 21, 44),
(56, 21, 49),
(57, 21, 56),
(58, 21, 57),
(61, 22, 39),
(62, 22, 52),
(63, 22, 56),
(64, 22, 57),
(65, 22, 63),
(68, 23, 50),
(69, 23, 52),
(70, 23, 55),
(71, 23, 56),
(75, 24, 43),
(76, 25, 44),
(77, 25, 52),
(78, 25, 55),
(79, 25, 56),
(80, 25, 57),
(83, 26, 38),
(84, 26, 46),
(85, 26, 55),
(86, 27, 44),
(87, 27, 50),
(88, 27, 52),
(89, 28, 45),
(90, 28, 51),
(92, 29, 43),
(93, 30, 44),
(94, 30, 60),
(95, 30, 65),
(96, 31, 50),
(97, 31, 60),
(99, 32, 48),
(100, 32, 58),
(101, 32, 59),
(102, 32, 61),
(106, 33, 50),
(107, 33, 55),
(109, 34, 48),
(110, 34, 58),
(111, 34, 59),
(112, 34, 61),
(116, 35, 38),
(117, 35, 39),
(118, 35, 52),
(119, 35, 56),
(123, 36, 50),
(124, 36, 55),
(126, 37, 43),
(127, 38, 39),
(128, 38, 52),
(129, 38, 56),
(130, 38, 57),
(131, 38, 63),
(134, 39, 44),
(135, 39, 57),
(137, 40, 41),
(138, 40, 62),
(140, 41, 43),
(141, 42, 43),
(142, 43, 43),
(143, 44, 45),
(144, 44, 51),
(146, 45, 41),
(147, 45, 62),
(149, 46, 42),
(150, 46, 52),
(151, 46, 56),
(152, 46, 63),
(156, 47, 43),
(157, 48, 40),
(158, 48, 48),
(159, 48, 58),
(160, 48, 61),
(164, 49, 38),
(165, 49, 55),
(167, 50, 44),
(168, 50, 52),
(169, 50, 57),
(170, 51, 44),
(171, 51, 50),
(173, 52, 45),
(174, 52, 51),
(176, 53, 38),
(177, 53, 53),
(178, 53, 59),
(179, 53, 61),
(183, 54, 50),
(184, 54, 55),
(186, 55, 42),
(187, 55, 52),
(188, 55, 56),
(189, 55, 57),
(190, 55, 63),
(193, 56, 62),
(194, 56, 64),
(195, 56, 66),
(196, 57, 45),
(197, 57, 51),
(199, 58, 50),
(200, 58, 57),
(202, 59, 54),
(203, 60, 50),
(204, 60, 55),
(206, 61, 43),
(207, 62, 43),
(208, 63, 50),
(209, 63, 55),
(210, 63, 57),
(211, 64, 38),
(212, 64, 44),
(213, 64, 45),
(214, 64, 57),
(218, 65, 39),
(219, 65, 42),
(220, 65, 56),
(221, 66, 50),
(222, 66, 55),
(224, 67, 38),
(225, 67, 39),
(226, 67, 42),
(227, 67, 63),
(231, 68, 44),
(232, 68, 45),
(233, 68, 49),
(234, 68, 57),
(235, 68, 67),
(238, 69, 38),
(239, 69, 45),
(240, 69, 55),
(241, 69, 57),
(245, 70, 38),
(246, 70, 50),
(248, 71, 50),
(249, 71, 55),
(251, 72, 38),
(252, 72, 50),
(254, 73, 62),
(255, 73, 66);

-- --------------------------------------------------------

--
-- Table structure for table `configuration.gameplatforms`
--

CREATE TABLE IF NOT EXISTS `configuration.gameplatforms` (
  `ID` int(11) NOT NULL,
  `FK_Platform_ID` int(11) NOT NULL,
  `FK_Game_ID` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=207 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `configuration.gameplatforms`
--

INSERT INTO `configuration.gameplatforms` (`ID`, `FK_Platform_ID`, `FK_Game_ID`) VALUES
(1, 9, 12),
(2, 9, 13),
(3, 10, 13),
(4, 11, 13),
(5, 13, 13),
(6, 14, 13),
(9, 9, 14),
(10, 10, 14),
(11, 11, 14),
(12, 13, 14),
(13, 14, 14),
(16, 10, 15),
(17, 11, 15),
(18, 14, 15),
(19, 11, 16),
(20, 9, 17),
(21, 10, 17),
(22, 11, 17),
(23, 13, 17),
(24, 14, 17),
(27, 9, 18),
(28, 10, 18),
(29, 11, 18),
(30, 13, 18),
(31, 14, 18),
(34, 9, 19),
(35, 10, 19),
(36, 11, 19),
(37, 13, 19),
(38, 14, 19),
(41, 9, 20),
(42, 10, 20),
(43, 11, 20),
(44, 13, 20),
(45, 14, 20),
(48, 9, 21),
(49, 10, 21),
(50, 13, 21),
(51, 9, 22),
(52, 9, 23),
(53, 10, 23),
(54, 11, 23),
(55, 9, 24),
(56, 10, 24),
(57, 11, 24),
(58, 13, 24),
(59, 14, 24),
(62, 10, 25),
(63, 11, 25),
(64, 13, 25),
(65, 14, 25),
(69, 9, 26),
(70, 10, 26),
(71, 11, 26),
(72, 13, 26),
(73, 14, 26),
(76, 11, 27),
(77, 14, 27),
(79, 9, 28),
(80, 10, 29),
(81, 11, 29),
(82, 13, 29),
(83, 14, 29),
(87, 9, 30),
(88, 9, 31),
(89, 9, 32),
(90, 10, 32),
(91, 11, 32),
(92, 13, 32),
(93, 14, 32),
(96, 9, 33),
(97, 10, 33),
(98, 11, 33),
(99, 9, 34),
(100, 9, 35),
(101, 10, 35),
(102, 11, 35),
(103, 13, 35),
(104, 14, 35),
(107, 9, 36),
(108, 10, 37),
(109, 11, 37),
(111, 9, 38),
(112, 14, 39),
(113, 9, 40),
(114, 9, 41),
(115, 10, 41),
(116, 11, 41),
(117, 12, 41),
(118, 13, 41),
(119, 14, 41),
(121, 10, 42),
(122, 11, 42),
(124, 14, 43),
(125, 9, 44),
(126, 9, 45),
(127, 9, 46),
(128, 10, 46),
(129, 11, 46),
(130, 13, 46),
(131, 14, 46),
(134, 9, 47),
(135, 10, 47),
(136, 11, 47),
(137, 13, 47),
(138, 14, 47),
(141, 9, 48),
(142, 10, 48),
(143, 11, 48),
(144, 13, 48),
(145, 14, 48),
(148, 9, 49),
(149, 9, 50),
(150, 10, 50),
(151, 11, 50),
(152, 13, 50),
(153, 14, 50),
(156, 9, 51),
(157, 11, 51),
(158, 14, 51),
(159, 9, 52),
(160, 9, 53),
(161, 11, 53),
(163, 9, 54),
(164, 9, 55),
(165, 9, 56),
(166, 9, 57),
(167, 14, 57),
(169, 12, 58),
(170, 9, 59),
(171, 9, 60),
(172, 9, 61),
(173, 10, 61),
(174, 11, 61),
(175, 13, 61),
(179, 9, 62),
(180, 11, 62),
(182, 11, 63),
(183, 14, 63),
(185, 9, 64),
(186, 9, 65),
(187, 10, 65),
(188, 11, 65),
(189, 12, 65),
(190, 13, 65),
(191, 14, 65),
(193, 9, 66),
(194, 11, 66),
(195, 14, 66),
(196, 10, 67),
(197, 11, 67),
(199, 9, 68),
(200, 9, 69),
(201, 11, 69),
(202, 14, 69),
(203, 9, 70),
(204, 9, 71),
(205, 9, 72),
(206, 9, 73);

-- --------------------------------------------------------

--
-- Table structure for table `configuration.games`
--

CREATE TABLE IF NOT EXISTS `configuration.games` (
  `ID` int(11) NOT NULL,
  `Name` varchar(50) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `configuration.games`
--

INSERT INTO `configuration.games` (`ID`, `Name`) VALUES
(12, 'ARK: Survival Evolved'),
(13, 'Battlefield 4'),
(14, 'Battlefield: Hardline'),
(15, 'BlazBlue: Chrono Phantasma Extend'),
(16, 'Bloodborne'),
(17, 'Borderlands 2'),
(18, 'Borderlands: The Pre-Sequel'),
(19, 'Call Of Duty: Advance Warfare'),
(20, 'Call Of Duty: Black Ops 3'),
(21, 'Counter-Strike: Global Offensive'),
(22, 'DayZ'),
(23, 'DC Universe Online'),
(24, 'Dead or Alive 5: Last Round'),
(25, 'Destiny'),
(26, 'Diablo III'),
(27, 'Doom'),
(28, 'DOTA 2'),
(29, 'Dragon Ball Xenoverse'),
(30, 'Elite: Dangerous'),
(31, 'EVE Online'),
(32, 'FIFA 15'),
(33, 'Final Fantasy XIV: A Realm Reborn'),
(34, 'Football Manager 2015'),
(35, 'Grand Theft Auto V'),
(36, 'Guild Wars 2'),
(37, 'Guilty Gear Xrd Sign'),
(38, 'H1Z1'),
(39, 'Halo: The Master Chief Collection'),
(40, 'Hearthstone: Heros of Warcraft'),
(41, 'Injustice: Gods Among US'),
(42, 'J-Stars Victory VS+'),
(43, 'Killer Instinct '),
(44, 'League of Legends'),
(45, 'Magic: The Gathering'),
(46, 'Minecraft'),
(47, 'Mortal Kombat X'),
(48, 'NBA 2K16'),
(49, 'Path of Exile'),
(50, 'Pay Day 2'),
(51, 'PlanetSide 2'),
(52, 'Prime World'),
(53, 'Rocket League'),
(54, 'Runescape'),
(55, 'Rust'),
(56, 'Sid Meier''s Civilization'),
(57, 'Smite'),
(58, 'Splatoon'),
(59, 'Star Craft II'),
(60, 'Star Wars: The Old Republic'),
(61, 'Street Fighter IV'),
(62, 'Street Fighter V'),
(63, 'The Division'),
(64, 'Team Fortress 2'),
(65, 'Terraria'),
(66, 'The Elder Scrolls Online: Tamriel Unlimited'),
(67, 'The Last of Us'),
(68, 'War Thunder'),
(69, 'Warframe'),
(70, 'World of Tanks'),
(71, 'World of Warcraft'),
(72, 'World of Warships'),
(73, 'XCOM: 2');

-- --------------------------------------------------------

--
-- Table structure for table `configuration.genres`
--

CREATE TABLE IF NOT EXISTS `configuration.genres` (
  `ID` int(11) NOT NULL,
  `Name` varchar(50) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `configuration.genres`
--

INSERT INTO `configuration.genres` (`ID`, `Name`) VALUES
(38, 'Action'),
(39, 'Adventure'),
(40, 'Basketball'),
(41, 'Card Game'),
(42, 'Crafting'),
(43, 'Fighting'),
(44, 'FPS'),
(45, 'Free to Play'),
(46, 'Hack and Slash'),
(47, 'Horror'),
(48, 'Management'),
(49, 'Military'),
(50, 'MMO'),
(51, 'MOBA'),
(52, 'Open World'),
(53, 'Racing'),
(54, 'Real Time Strategy'),
(55, 'RPG'),
(56, 'Sandbox'),
(57, 'Shooter'),
(58, 'Simulation'),
(59, 'Soccer'),
(60, 'Space simulation'),
(61, 'Sports'),
(62, 'Strategy'),
(63, 'Survival'),
(64, 'Tactical'),
(65, 'Trading'),
(66, 'Turn Based'),
(67, 'World War 2');

-- --------------------------------------------------------

--
-- Table structure for table `configuration.platforms`
--

CREATE TABLE IF NOT EXISTS `configuration.platforms` (
  `ID` int(11) NOT NULL,
  `Name` varchar(35) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `configuration.platforms`
--

INSERT INTO `configuration.platforms` (`ID`, `Name`) VALUES
(9, 'PC/MAC'),
(10, 'PlayStation 3'),
(11, 'PlayStation 4'),
(12, 'Wii U'),
(13, 'Xbox 360'),
(14, 'Xbox One');

-- --------------------------------------------------------

--
-- Table structure for table `configuration.timezones`
--

CREATE TABLE IF NOT EXISTS `configuration.timezones` (
  `ID` int(11) NOT NULL,
  `Abbreviation` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
  `Description` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `SortOrder` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=207 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `configuration.timezones`
--

INSERT INTO `configuration.timezones` (`ID`, `Abbreviation`, `Description`, `SortOrder`) VALUES
(142, 'EST', 'US/Eastern', 1),
(143, 'CST', 'US/Central', 2),
(144, 'MST', 'US/Mountain', 3),
(145, 'PST', 'US/Pacific', 4),
(146, 'AKST', 'US/Alaska', 5),
(150, NULL, 'Pacific/Guam', 9),
(151, NULL, 'Pacific/Midway', 10),
(152, NULL, 'Pacific/Samoa', 11),
(153, NULL, 'Pacific/Tahiti', 12),
(154, NULL, 'Asia/Baghdad', 13),
(155, NULL, 'Asia/Bangkok', 14),
(156, NULL, 'Asia/Beirut', 15),
(157, NULL, 'Asia/Calcutta', 16),
(158, NULL, 'Asia/Dubai', 17),
(159, NULL, 'Asia/Gaza', 18),
(160, NULL, 'Asia/Ho_Chi_Minh', 19),
(161, NULL, 'Asia/Hong_Kong', 20),
(162, NULL, 'Asia/Istanbul', 21),
(163, NULL, 'Asia/Jakarta', 22),
(164, NULL, 'Asia/Jerusalem', 23),
(165, NULL, 'Asia/Kabul', 24),
(166, NULL, 'Asia/Macau', 25),
(167, NULL, 'Asia/Qatar', 26),
(168, NULL, 'Asia/Saigon', 27),
(169, NULL, 'Asia/Seoul', 28),
(170, NULL, 'Asia/Shanghai', 29),
(171, NULL, 'Asia/Singapore', 30),
(172, NULL, 'Asia/Taipei', 31),
(173, NULL, 'Asia/Tehran', 32),
(174, NULL, 'Asia/Tel_Aviv', 33),
(175, NULL, 'Asia/Tokyo', 34),
(176, NULL, 'Australia/Adelaide', 35),
(177, NULL, 'Australia/Brisbane', 36),
(178, NULL, 'Australia/Perth', 37),
(179, NULL, 'Australia/Sydney', 38),
(180, NULL, 'Canada/Atlantic', 39),
(181, NULL, 'Canada/Central', 40),
(182, NULL, 'Canada/Eastern', 41),
(183, NULL, 'Canada/Mountain', 42),
(184, NULL, 'Canada/Newfoundland', 43),
(185, NULL, 'Canada/Pacific', 44),
(186, NULL, 'Europe/Berlin', 45),
(187, NULL, 'Europe/Copenhagen', 46),
(188, NULL, 'Europe/Dublin', 47),
(189, NULL, 'Europe/London', 48),
(190, NULL, 'Europe/Luxembourg', 49),
(191, NULL, 'Europe/Madrid', 50),
(192, NULL, 'Europe/Moscow', 51),
(193, NULL, 'Europe/Paris', 52),
(194, NULL, 'Europe/Prague', 53),
(195, NULL, 'Europe/Rome', 54),
(196, NULL, 'Europe/Vatican', 55),
(197, NULL, 'Mexico/BajaNorte', 56),
(198, NULL, 'Mexico/BajaSur', 57),
(199, NULL, 'Mexico/General', 58),
(200, NULL, 'NZ', 59),
(201, NULL, 'NZ-CHAT', 60),
(202, 'GMT', 'GMT', 61),
(203, 'UTC', 'UTC', 62),
(204, NULL, 'US/Aleutian', 6),
(205, NULL, 'US/Hawaii', 7),
(206, NULL, 'US/Samoa', 8);

-- --------------------------------------------------------

--
-- Table structure for table `gaming.eventallowedmembers`
--

CREATE TABLE IF NOT EXISTS `gaming.eventallowedmembers` (
  `ID` bigint(20) NOT NULL,
  `FK_Event_ID` bigint(20) NOT NULL,
  `FK_User_ID` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `gaming.eventmembers`
--

CREATE TABLE IF NOT EXISTS `gaming.eventmembers` (
  `ID` bigint(20) NOT NULL,
  `FK_Event_ID` bigint(20) NOT NULL,
  `FK_User_ID` bigint(20) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `gaming.events`
--

CREATE TABLE IF NOT EXISTS `gaming.events` (
  `ID` bigint(20) NOT NULL,
  `FK_User_ID_EventCreator` bigint(20) NOT NULL,
  `FK_Game_ID` int(11) DEFAULT NULL,
  `FK_Genre_ID` int(11) DEFAULT NULL,
  `FK_Platform_ID` int(11) NOT NULL,
  `FK_Timezone_ID` int(11) NOT NULL,
  `EventCreatedDate` datetime NOT NULL,
  `EventModifiedDate` datetime NOT NULL,
  `EventScheduledForDate` datetime DEFAULT NULL,
  `RequiredMemberCount` int(11) NOT NULL,
  `IsActive` tinyint(1) NOT NULL DEFAULT '0',
  `IsPublic` tinyint(1) NOT NULL DEFAULT '0',
  `FK_UserGames_ID` bigint(20) DEFAULT NULL,
  `Notes` text NOT NULL,
  `DisplayDate` date DEFAULT NULL,
  `DisplayTime` time NOT NULL,
  `DateReminderSent` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `gaming.eventsubscriptions`
--

CREATE TABLE IF NOT EXISTS `gaming.eventsubscriptions` (
  `ID` int(11) NOT NULL,
  `FK_User_ID_EventSubscriber` bigint(20) NOT NULL,
  `FK_Genre_ID` int(11) NOT NULL,
  `FK_Game_ID` int(11) NOT NULL,
  `FK_Platform_ID` int(11) NOT NULL,
  `FK_Timezone_ID` int(11) NOT NULL,
  `SubscriptionRanking` int(11) NOT NULL DEFAULT '0' COMMENT 'Ranking of this subscription relative to this user''s other subscriptions, defining order/priority that matching events appear in their event view'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `gaming.reminderemailbatch`
--

CREATE TABLE IF NOT EXISTS `gaming.reminderemailbatch` (
  `ID` bigint(20) NOT NULL,
  `FK_User_ID` bigint(20) NOT NULL,
  `FK_Event_ID` bigint(20) NOT NULL,
  `EmailAddress` varchar(100) NOT NULL,
  `DateBatched` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `DateToSend` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `DateSendAttempted` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `IsProcessed` tinyint(4) NOT NULL DEFAULT '0',
  `SendSuccess` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `gaming.userfriendinvitations`
--

CREATE TABLE IF NOT EXISTS `gaming.userfriendinvitations` (
  `ID` bigint(20) NOT NULL,
  `FK_User_ID_Inviter` bigint(20) NOT NULL,
  `FK_User_ID_Invitee` bigint(20) NOT NULL,
  `IsRejected` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `gaming.userfriends`
--

CREATE TABLE IF NOT EXISTS `gaming.userfriends` (
  `ID` bigint(20) NOT NULL,
  `FK_User_ID_ThisUser` bigint(20) NOT NULL,
  `FK_User_ID_Friend` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `gaming.usergamertags`
--

CREATE TABLE IF NOT EXISTS `gaming.usergamertags` (
  `ID` int(11) NOT NULL,
  `FK_User_ID` bigint(20) DEFAULT NULL,
  `FK_Platform_ID` int(11) DEFAULT NULL,
  `GamerTagName` varchar(50) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `gaming.usergames`
--

CREATE TABLE IF NOT EXISTS `gaming.usergames` (
  `ID` bigint(20) NOT NULL,
  `FK_User_ID` bigint(11) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `CreatedDate` datetime NOT NULL,
  `ModifiedDate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `gaming.userplatforms`
--

CREATE TABLE IF NOT EXISTS `gaming.userplatforms` (
  `ID` bigint(20) NOT NULL,
  `FK_User_ID` bigint(11) NOT NULL,
  `FK_Platform_ID` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=209 DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `payments.paypaltransactions`
--

CREATE TABLE IF NOT EXISTS `payments.paypaltransactions` (
  `ID` bigint(20) NOT NULL,
  `TxnId` varchar(35) NOT NULL,
  `PayerId` varchar(25) DEFAULT NULL,
  `SubscriptionID` varchar(35) NOT NULL,
  `TxnType` varchar(25) DEFAULT NULL,
  `PDTOperation` varchar(25) DEFAULT NULL,
  `PaymentStatus` varchar(25) NOT NULL,
  `NotificationType` varchar(5) DEFAULT NULL,
  `NotificationDate` datetime DEFAULT NULL,
  `PayPalMsgData` text,
  `TransactionDate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `payments.paypalusers`
--

CREATE TABLE IF NOT EXISTS `payments.paypalusers` (
  `ID` bigint(20) NOT NULL,
  `FK_User_ID` bigint(20) DEFAULT NULL,
  `PayerId` varchar(25) DEFAULT NULL,
  `SubscriptionID` varchar(35) NOT NULL,
  `SubscriptionType` varchar(25) DEFAULT NULL,
  `SubscriptionAmtTotal` decimal(6,2) DEFAULT NULL,
  `SubscriptionAmtPaidLastCycle` decimal(6,2) DEFAULT NULL,
  `LastBillDate` datetime DEFAULT NULL,
  `MembershipExpirationDate` datetime DEFAULT NULL,
  `ExtendedMembershipDays` int(11) NOT NULL DEFAULT '0',
  `IsRecurring` tinyint(1) NOT NULL DEFAULT '0',
  `IsActive` tinyint(1) NOT NULL DEFAULT '0',
  `SubscriptionStartedDate` datetime DEFAULT NULL,
  `SubscriptionModifiedDate` datetime DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `security.pageroles`
--

CREATE TABLE IF NOT EXISTS `security.pageroles` (
  `ID` int(11) NOT NULL,
  `FK_Page_ID` int(11) NOT NULL,
  `FK_Role_ID` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `security.pageroles`
--

INSERT INTO `security.pageroles` (`ID`, `FK_Page_ID`, `FK_Role_ID`) VALUES
(1, 7, 4),
(2, 8, 3),
(3, 9, 3),
(4, 10, 4),
(5, 11, 4),
(6, 12, 4),
(7, 13, 3),
(8, 14, 4),
(9, 15, 4),
(10, 16, 3),
(11, 17, 4);

-- --------------------------------------------------------

--
-- Table structure for table `security.pages`
--

CREATE TABLE IF NOT EXISTS `security.pages` (
  `ID` int(11) NOT NULL,
  `Name` varchar(35) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `security.pages`
--

INSERT INTO `security.pages` (`ID`, `Name`) VALUES
(7, 'index'),
(8, 'MemberHome'),
(9, 'EditProfile'),
(10, 'MobileLogin'),
(11, 'About'),
(12, 'TermsPri'),
(13, 'AccountManagement'),
(14, 'DeveloperBlog'),
(15, 'Faq'),
(16, 'FindFriends'),
(17, 'PasswordRecovery');

-- --------------------------------------------------------

--
-- Table structure for table `security.passwordrecoverysession`
--

CREATE TABLE IF NOT EXISTS `security.passwordrecoverysession` (
  `ID` bigint(20) NOT NULL,
  `FK_User_ID` bigint(20) NOT NULL,
  `SessionId` varchar(32) NOT NULL,
  `ExpirationTimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `security.roles`
--

CREATE TABLE IF NOT EXISTS `security.roles` (
  `ID` int(11) NOT NULL,
  `Name` varchar(35) NOT NULL,
  `SecurityLevel` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `security.roles`
--

INSERT INTO `security.roles` (`ID`, `Name`, `SecurityLevel`) VALUES
(1, 'Admin', 1),
(2, 'PremiumMember', 2),
(3, 'BasicMember', 3),
(4, 'Public', 4);

-- --------------------------------------------------------

--
-- Table structure for table `security.userroles`
--

CREATE TABLE IF NOT EXISTS `security.userroles` (
  `ID` bigint(20) NOT NULL,
  `FK_User_ID` bigint(20) NOT NULL,
  `FK_Role_ID` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=109 DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `security.users`
--

CREATE TABLE IF NOT EXISTS `security.users` (
  `ID` bigint(11) NOT NULL,
  `FK_Timezone_ID` int(11) DEFAULT NULL,
  `UserName` varchar(100) NOT NULL,
  `FirstName` varchar(30) NOT NULL,
  `LastName` varchar(45) NOT NULL,
  `EmailAddress` varchar(100) NOT NULL,
  `IsPremiumMember` tinyint(1) NOT NULL DEFAULT '0',
  `Password` varchar(255) NOT NULL,
  `Gender` char(1) DEFAULT NULL,
  `Birthdate` date DEFAULT NULL,
  `Autobiography` text,
  `IsActive` tinyint(1) NOT NULL DEFAULT '1',
  `SendEventReminderEmails` tinyint(4) NOT NULL DEFAULT '0',
  `SendEventReminderEmailsToAddress` varchar(100) DEFAULT NULL,
  `SendEventReminderEmailMinutesBeforeEvent` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `security.usersessions`
--

CREATE TABLE IF NOT EXISTS `security.usersessions` (
  `ID` varchar(32) NOT NULL,
  `LastAccess` int(10) unsigned DEFAULT NULL,
  `LastAccessTimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `SessionData` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `administration.logging`
--
ALTER TABLE `administration.logging`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `IDX_Category` (`Category`);

--
-- Indexes for table `configuration.gamegenres`
--
ALTER TABLE `configuration.gamegenres`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `IDX_GameGenres_GameID` (`FK_Game_ID`),
  ADD KEY `IDX_GameGenres_GenreID` (`FK_Genre_ID`);

--
-- Indexes for table `configuration.gameplatforms`
--
ALTER TABLE `configuration.gameplatforms`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `IDX_GamePlatforms_PlatformID` (`FK_Platform_ID`),
  ADD KEY `IDX_GamePlatforms_GameID` (`FK_Game_ID`);

--
-- Indexes for table `configuration.games`
--
ALTER TABLE `configuration.games`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `configuration.genres`
--
ALTER TABLE `configuration.genres`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `configuration.platforms`
--
ALTER TABLE `configuration.platforms`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `configuration.timezones`
--
ALTER TABLE `configuration.timezones`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `gaming.eventallowedmembers`
--
ALTER TABLE `gaming.eventallowedmembers`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `IDX_EventAllowedMembers_EventID` (`FK_Event_ID`),
  ADD KEY `IDX_EventAllowedMembers_UserID` (`FK_User_ID`);

--
-- Indexes for table `gaming.eventmembers`
--
ALTER TABLE `gaming.eventmembers`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `IDX_EventMembers_EventID` (`FK_Event_ID`),
  ADD KEY `IDX_EventMembers_UserID` (`FK_User_ID`);

--
-- Indexes for table `gaming.events`
--
ALTER TABLE `gaming.events`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `IDX_Events_UserID` (`FK_User_ID_EventCreator`),
  ADD KEY `IDX_Events_GameID` (`FK_Game_ID`),
  ADD KEY `IDX_Events_GenreID` (`FK_Genre_ID`),
  ADD KEY `IDX_Events_PlatformID` (`FK_Platform_ID`),
  ADD KEY `IDX_Events_TimezoneID` (`FK_Timezone_ID`),
  ADD KEY `FK_Events_UserGamesID_idx` (`FK_UserGames_ID`);

--
-- Indexes for table `gaming.eventsubscriptions`
--
ALTER TABLE `gaming.eventsubscriptions`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `IDX_EventSubscriptions_UserID` (`FK_User_ID_EventSubscriber`),
  ADD KEY `IDX_EventSubscriptions_GameID` (`FK_Game_ID`),
  ADD KEY `IDX_EventSubscriptions_PlatformID` (`FK_Platform_ID`),
  ADD KEY `IDX_EventSubscriptions_TimezoneID` (`FK_Timezone_ID`),
  ADD KEY `IDX_EventSubscriptions_GenreID` (`FK_Genre_ID`);

--
-- Indexes for table `gaming.reminderemailbatch`
--
ALTER TABLE `gaming.reminderemailbatch`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `IDX_ReminderEmailBatch_FK_User_ID` (`FK_User_ID`),
  ADD KEY `IDX_ReminderEmailBatch_FK_Event_ID` (`FK_Event_ID`);

--
-- Indexes for table `gaming.userfriendinvitations`
--
ALTER TABLE `gaming.userfriendinvitations`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `IDX_UserFriendInvitations_FK_User_ID_Invitee` (`FK_User_ID_Invitee`),
  ADD KEY `IDX_UserFriendInvitations_FK_User_ID_Inviter` (`FK_User_ID_Inviter`);

--
-- Indexes for table `gaming.userfriends`
--
ALTER TABLE `gaming.userfriends`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `IDX_UserFriends_UserID_ThisUser` (`FK_User_ID_ThisUser`),
  ADD KEY `IDX_UserFriends_UserID_Friend` (`FK_User_ID_Friend`);

--
-- Indexes for table `gaming.usergamertags`
--
ALTER TABLE `gaming.usergamertags`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `ID_UNIQUE` (`ID`),
  ADD KEY `IDX_FK_User_ID` (`FK_User_ID`),
  ADD KEY `IDX_FK_Platform_ID` (`FK_Platform_ID`);

--
-- Indexes for table `gaming.usergames`
--
ALTER TABLE `gaming.usergames`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `IDX_Unique_UserID_Name` (`FK_User_ID`,`Name`),
  ADD KEY `IDX_UserGames_UserID` (`FK_User_ID`);

--
-- Indexes for table `gaming.userplatforms`
--
ALTER TABLE `gaming.userplatforms`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `IDX_UserPlatforms_UserID` (`FK_User_ID`),
  ADD KEY `IDX_UserPlatforms_PlatformID` (`FK_Platform_ID`);

--
-- Indexes for table `payments.paypaltransactions`
--
ALTER TABLE `payments.paypaltransactions`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `IDX_SubscriptionID_TxnId_TxnType_PaymentStatus_UNIQUE` (`SubscriptionID`,`TxnId`,`TxnType`,`PaymentStatus`);

--
-- Indexes for table `payments.paypalusers`
--
ALTER TABLE `payments.paypalusers`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `IDX_PayPalUsers_FK_User_ID` (`FK_User_ID`);

--
-- Indexes for table `security.pageroles`
--
ALTER TABLE `security.pageroles`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `IDX_Page_ID` (`FK_Page_ID`),
  ADD KEY `IDX_Role_ID` (`FK_Role_ID`);

--
-- Indexes for table `security.pages`
--
ALTER TABLE `security.pages`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `security.passwordrecoverysession`
--
ALTER TABLE `security.passwordrecoverysession`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `IDX_PasswordRecoverySession_FK_User_ID` (`FK_User_ID`);

--
-- Indexes for table `security.roles`
--
ALTER TABLE `security.roles`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `security.userroles`
--
ALTER TABLE `security.userroles`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `IDX_User_ID` (`FK_User_ID`),
  ADD KEY `IDX_Role_ID` (`FK_Role_ID`);

--
-- Indexes for table `security.users`
--
ALTER TABLE `security.users`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `IDX_TimeZoneID` (`FK_Timezone_ID`),
  ADD KEY `IDX_IsActive` (`IsActive`);

--
-- Indexes for table `security.usersessions`
--
ALTER TABLE `security.usersessions`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `administration.logging`
--
ALTER TABLE `administration.logging`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=37;
--
-- AUTO_INCREMENT for table `configuration.gamegenres`
--
ALTER TABLE `configuration.gamegenres`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=256;
--
-- AUTO_INCREMENT for table `configuration.gameplatforms`
--
ALTER TABLE `configuration.gameplatforms`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=207;
--
-- AUTO_INCREMENT for table `configuration.games`
--
ALTER TABLE `configuration.games`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=74;
--
-- AUTO_INCREMENT for table `configuration.genres`
--
ALTER TABLE `configuration.genres`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=68;
--
-- AUTO_INCREMENT for table `configuration.platforms`
--
ALTER TABLE `configuration.platforms`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT for table `configuration.timezones`
--
ALTER TABLE `configuration.timezones`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=207;
--
-- AUTO_INCREMENT for table `gaming.eventallowedmembers`
--
ALTER TABLE `gaming.eventallowedmembers`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `gaming.eventmembers`
--
ALTER TABLE `gaming.eventmembers`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `gaming.events`
--
ALTER TABLE `gaming.events`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `gaming.eventsubscriptions`
--
ALTER TABLE `gaming.eventsubscriptions`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `gaming.reminderemailbatch`
--
ALTER TABLE `gaming.reminderemailbatch`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `gaming.userfriendinvitations`
--
ALTER TABLE `gaming.userfriendinvitations`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `gaming.userfriends`
--
ALTER TABLE `gaming.userfriends`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `gaming.usergamertags`
--
ALTER TABLE `gaming.usergamertags`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=28;
--
-- AUTO_INCREMENT for table `gaming.usergames`
--
ALTER TABLE `gaming.usergames`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `gaming.userplatforms`
--
ALTER TABLE `gaming.userplatforms`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=209;
--
-- AUTO_INCREMENT for table `payments.paypaltransactions`
--
ALTER TABLE `payments.paypaltransactions`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `payments.paypalusers`
--
ALTER TABLE `payments.paypalusers`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=38;
--
-- AUTO_INCREMENT for table `security.pageroles`
--
ALTER TABLE `security.pageroles`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT for table `security.pages`
--
ALTER TABLE `security.pages`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=18;
--
-- AUTO_INCREMENT for table `security.passwordrecoverysession`
--
ALTER TABLE `security.passwordrecoverysession`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT for table `security.roles`
--
ALTER TABLE `security.roles`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `security.userroles`
--
ALTER TABLE `security.userroles`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=109;
--
-- AUTO_INCREMENT for table `security.users`
--
ALTER TABLE `security.users`
  MODIFY `ID` bigint(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=106;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `configuration.gamegenres`
--
ALTER TABLE `configuration.gamegenres`
  ADD CONSTRAINT `FK_GameGenres_GameID` FOREIGN KEY (`FK_Game_ID`) REFERENCES `configuration.games` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_GameGenres_GenreID` FOREIGN KEY (`FK_Genre_ID`) REFERENCES `configuration.genres` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `configuration.gameplatforms`
--
ALTER TABLE `configuration.gameplatforms`
  ADD CONSTRAINT `FK_GamePlatforms_Game_ID` FOREIGN KEY (`FK_Game_ID`) REFERENCES `configuration.games` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_GamePlatforms_Platform_ID` FOREIGN KEY (`FK_Platform_ID`) REFERENCES `configuration.platforms` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `gaming.eventallowedmembers`
--
ALTER TABLE `gaming.eventallowedmembers`
  ADD CONSTRAINT `FK_EventAllowedMembers_EventID` FOREIGN KEY (`FK_Event_ID`) REFERENCES `gaming.events` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_EventAllowedMembers_UserID` FOREIGN KEY (`FK_User_ID`) REFERENCES `security.users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `gaming.eventmembers`
--
ALTER TABLE `gaming.eventmembers`
  ADD CONSTRAINT `FK_EventMembers_EventID` FOREIGN KEY (`FK_Event_ID`) REFERENCES `gaming.events` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_EventMembers_UserID` FOREIGN KEY (`FK_User_ID`) REFERENCES `security.users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `gaming.events`
--
ALTER TABLE `gaming.events`
  ADD CONSTRAINT `FK_Events_GameID` FOREIGN KEY (`FK_Game_ID`) REFERENCES `configuration.games` (`ID`),
  ADD CONSTRAINT `FK_Events_GenreID` FOREIGN KEY (`FK_Genre_ID`) REFERENCES `configuration.genres` (`ID`),
  ADD CONSTRAINT `FK_Events_PlatformID` FOREIGN KEY (`FK_Platform_ID`) REFERENCES `configuration.platforms` (`ID`),
  ADD CONSTRAINT `FK_Events_TimeZoneID` FOREIGN KEY (`FK_Timezone_ID`) REFERENCES `configuration.timezones` (`ID`),
  ADD CONSTRAINT `FK_Events_UserGamesID` FOREIGN KEY (`FK_UserGames_ID`) REFERENCES `gaming.usergames` (`ID`) ON DELETE SET NULL ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_Events_UserID` FOREIGN KEY (`FK_User_ID_EventCreator`) REFERENCES `security.users` (`ID`);

--
-- Constraints for table `gaming.eventsubscriptions`
--
ALTER TABLE `gaming.eventsubscriptions`
  ADD CONSTRAINT `FK_EventSubscriptions_GameID` FOREIGN KEY (`FK_Game_ID`) REFERENCES `configuration.games` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_EventSubscriptions_GenreID` FOREIGN KEY (`FK_Genre_ID`) REFERENCES `configuration.genres` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_EventSubscriptions_PlatformID` FOREIGN KEY (`FK_Platform_ID`) REFERENCES `configuration.platforms` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_EventSubscriptions_TimezoneID` FOREIGN KEY (`FK_Timezone_ID`) REFERENCES `configuration.timezones` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_EventSubscriptions_UserID` FOREIGN KEY (`FK_User_ID_EventSubscriber`) REFERENCES `security.users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `gaming.reminderemailbatch`
--
ALTER TABLE `gaming.reminderemailbatch`
  ADD CONSTRAINT `FK_ReminderEmailBatch_EventID` FOREIGN KEY (`FK_Event_ID`) REFERENCES `gaming.events` (`ID`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_ReminderEmailBatch_UserID` FOREIGN KEY (`FK_User_ID`) REFERENCES `security.users` (`ID`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `gaming.userfriendinvitations`
--
ALTER TABLE `gaming.userfriendinvitations`
  ADD CONSTRAINT `FK_UserFriendInvitations_UserID_Invitee` FOREIGN KEY (`FK_User_ID_Invitee`) REFERENCES `security.users` (`ID`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_UserFriendInvitations_UserID_Inviter` FOREIGN KEY (`FK_User_ID_Inviter`) REFERENCES `security.users` (`ID`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `gaming.userfriends`
--
ALTER TABLE `gaming.userfriends`
  ADD CONSTRAINT `FK_UserFriends_Users_Friend` FOREIGN KEY (`FK_User_ID_Friend`) REFERENCES `security.users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_UserFriends_Users_ThisUser` FOREIGN KEY (`FK_User_ID_ThisUser`) REFERENCES `security.users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `gaming.usergamertags`
--
ALTER TABLE `gaming.usergamertags`
  ADD CONSTRAINT `FK_UserGamerTags_PlatformID` FOREIGN KEY (`FK_Platform_ID`) REFERENCES `configuration.platforms` (`ID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_UserGamerTags_UserID` FOREIGN KEY (`FK_User_ID`) REFERENCES `security.users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `gaming.usergames`
--
ALTER TABLE `gaming.usergames`
  ADD CONSTRAINT `FK_UserGames_User_ID` FOREIGN KEY (`FK_User_ID`) REFERENCES `security.users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `gaming.userplatforms`
--
ALTER TABLE `gaming.userplatforms`
  ADD CONSTRAINT `FK_UserPlatforms_Platform_ID` FOREIGN KEY (`FK_Platform_ID`) REFERENCES `configuration.platforms` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_UserPlatforms_User_ID` FOREIGN KEY (`FK_User_ID`) REFERENCES `security.users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payments.paypalusers`
--
ALTER TABLE `payments.paypalusers`
  ADD CONSTRAINT `FK_PayPalUsers_UserID` FOREIGN KEY (`FK_User_ID`) REFERENCES `security.users` (`ID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `security.pageroles`
--
ALTER TABLE `security.pageroles`
  ADD CONSTRAINT `FK_PageRoles_Pages` FOREIGN KEY (`FK_Page_ID`) REFERENCES `security.pages` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_PageRoles_Roles` FOREIGN KEY (`FK_Role_ID`) REFERENCES `security.roles` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `security.passwordrecoverysession`
--
ALTER TABLE `security.passwordrecoverysession`
  ADD CONSTRAINT `FK_PasswordRecoverySession_UserID` FOREIGN KEY (`FK_User_ID`) REFERENCES `security.users` (`ID`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `security.userroles`
--
ALTER TABLE `security.userroles`
  ADD CONSTRAINT `FK_UserRoles_Roles` FOREIGN KEY (`FK_Role_ID`) REFERENCES `security.roles` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_UserRoles_Users` FOREIGN KEY (`FK_User_ID`) REFERENCES `security.users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `security.users`
--
ALTER TABLE `security.users`
  ADD CONSTRAINT `FK_Users_TimeZones` FOREIGN KEY (`FK_Timezone_ID`) REFERENCES `configuration.timezones` (`ID`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
