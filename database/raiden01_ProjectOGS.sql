-- phpMyAdmin SQL Dump
-- version 4.3.8
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 29, 2015 at 12:54 AM
-- Server version: 5.5.42-37.1
-- PHP Version: 5.4.23
USE raiden01_ProjectOGS;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `raiden01_ProjectOGS`
--

-- --------------------------------------------------------

--
-- Table structure for table `Administration.Logging`
--

CREATE TABLE IF NOT EXISTS `Administration.Logging` (
  `ID` int(11) NOT NULL,
  `Category` varchar(35) DEFAULT NULL,
  `Title` varchar(50) DEFAULT NULL,
  `Message` text,
  `EntryTimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8mb4;



-- --------------------------------------------------------

--
-- Table structure for table `Configuration.GameGenres`
--

CREATE TABLE IF NOT EXISTS `Configuration.GameGenres` (
  `ID` int(11) NOT NULL,
  `FK_Game_ID` int(11) NOT NULL,
  `FK_Genre_ID` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Configuration.GameGenres`
--

INSERT INTO `Configuration.GameGenres` (`ID`, `FK_Game_ID`, `FK_Genre_ID`) VALUES
(1, 1, 5),
(2, 2, 5),
(3, 3, 5),
(4, 4, 5),
(5, 5, 5),
(6, 1, 7),
(7, 2, 7),
(8, 3, 7),
(9, 4, 7),
(10, 5, 7),
(11, 6, 5),
(12, 7, 5),
(13, 8, 3),
(14, 9, 3);

-- --------------------------------------------------------

--
-- Table structure for table `Configuration.GamePlatforms`
--

CREATE TABLE IF NOT EXISTS `Configuration.GamePlatforms` (
  `ID` int(11) NOT NULL,
  `FK_Platform_ID` int(11) NOT NULL,
  `FK_Game_ID` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Configuration.GamePlatforms`
--

INSERT INTO `Configuration.GamePlatforms` (`ID`, `FK_Platform_ID`, `FK_Game_ID`) VALUES
(23, 13, 1),
(24, 13, 2),
(25, 13, 3),
(26, 13, 4),
(27, 13, 5),
(28, 14, 1),
(29, 14, 2),
(30, 14, 3),
(31, 14, 4),
(32, 14, 5),
(33, 13, 6),
(34, 13, 7),
(35, 14, 6),
(36, 14, 7),
(37, 10, 6),
(38, 10, 7),
(39, 11, 6),
(40, 11, 7),
(41, 9, 6),
(42, 9, 7),
(43, 9, 8),
(44, 9, 9);

-- --------------------------------------------------------

--
-- Table structure for table `Configuration.Games`
--

CREATE TABLE IF NOT EXISTS `Configuration.Games` (
  `ID` int(11) NOT NULL,
  `Name` varchar(50) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Configuration.Games`
--

INSERT INTO `Configuration.Games` (`ID`, `Name`) VALUES
(1, 'Halo'),
(2, 'Halo 2'),
(3, 'Halo 3'),
(4, 'Halo 4'),
(5, 'Halo 5'),
(6, 'Call Of Duty'),
(7, 'Call Of Duty 2'),
(8, 'EverQuest II'),
(9, 'ArcheAge');

-- --------------------------------------------------------

--
-- Table structure for table `Configuration.Genres`
--

CREATE TABLE IF NOT EXISTS `Configuration.Genres` (
  `ID` int(11) NOT NULL,
  `Name` varchar(50) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Configuration.Genres`
--

INSERT INTO `Configuration.Genres` (`ID`, `Name`) VALUES
(1, 'Aircraft Racing'),
(2, 'Car Racing'),
(3, 'Fantasy'),
(4, 'Flight Simulators'),
(5, 'Military'),
(6, 'Puzzles'),
(7, 'Science Fiction');

-- --------------------------------------------------------

--
-- Table structure for table `Configuration.Platforms`
--

CREATE TABLE IF NOT EXISTS `Configuration.Platforms` (
  `ID` int(11) NOT NULL,
  `Name` varchar(35) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Configuration.Platforms`
--

INSERT INTO `Configuration.Platforms` (`ID`, `Name`) VALUES
(9, 'PC/MAC'),
(10, 'PlayStation 3'),
(11, 'PlayStation 4'),
(12, 'Wii'),
(13, 'Xbox 360'),
(14, 'Xbox One');

-- --------------------------------------------------------

--
-- Table structure for table `Configuration.TimeZones`
--

CREATE TABLE IF NOT EXISTS `Configuration.TimeZones` (
  `ID` int(11) NOT NULL,
  `Abbreviation` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
  `Description` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `SortOrder` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=207 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `Configuration.TimeZones`
--

INSERT INTO `Configuration.TimeZones` (`ID`, `Abbreviation`, `Description`, `SortOrder`) VALUES
(142, 'EST', 'US/Eastern', 1),
(143, 'CST', 'US/Central', 2),
(144, 'MST', 'US/Mountain', 3),
(145, 'PST', 'US/Pacific', 4),
(146, 'AKST', 'US/Alaska', 5),
(150, '', 'Pacific/Guam', 9),
(151, '', 'Pacific/Midway', 10),
(152, '', 'Pacific/Samoa', 11),
(153, '', 'Pacific/Tahiti', 12),
(154, '', 'Asia/Baghdad', 13),
(155, '', 'Asia/Bangkok', 14),
(156, '', 'Asia/Beirut', 15),
(157, '', 'Asia/Calcutta', 16),
(158, '', 'Asia/Dubai', 17),
(159, '', 'Asia/Gaza', 18),
(160, '', 'Asia/Ho_Chi_Minh', 19),
(161, '', 'Asia/Hong_Kong', 20),
(162, '', 'Asia/Istanbul', 21),
(163, '', 'Asia/Jakarta', 22),
(164, '', 'Asia/Jerusalem', 23),
(165, '', 'Asia/Kabul', 24),
(166, '', 'Asia/Macau', 25),
(167, '', 'Asia/Qatar', 26),
(168, '', 'Asia/Saigon', 27),
(169, '', 'Asia/Seoul', 28),
(170, '', 'Asia/Shanghai', 29),
(171, '', 'Asia/Singapore', 30),
(172, '', 'Asia/Taipei', 31),
(173, '', 'Asia/Tehran', 32),
(174, '', 'Asia/Tel_Aviv', 33),
(175, '', 'Asia/Tokyo', 34),
(176, '', 'Australia/Adelaide', 35),
(177, '', 'Australia/Brisbane', 36),
(178, '', 'Australia/Perth', 37),
(179, '', 'Australia/Sydney', 38),
(180, '', 'Canada/Atlantic', 39),
(181, '', 'Canada/Central', 40),
(182, '', 'Canada/Eastern', 41),
(183, '', 'Canada/Mountain', 42),
(184, '', 'Canada/Newfoundland', 43),
(185, '', 'Canada/Pacific', 44),
(186, '', 'Europe/Berlin', 45),
(187, '', 'Europe/Copenhagen', 46),
(188, '', 'Europe/Dublin', 47),
(189, '', 'Europe/London', 48),
(190, '', 'Europe/Luxembourg', 49),
(191, '', 'Europe/Madrid', 50),
(192, '', 'Europe/Moscow', 51),
(193, '', 'Europe/Paris', 52),
(194, '', 'Europe/Prague', 53),
(195, '', 'Europe/Rome', 54),
(196, '', 'Europe/Vatican', 55),
(197, '', 'Mexico/BajaNorte', 56),
(198, '', 'Mexico/BajaSur', 57),
(199, '', 'Mexico/General', 58),
(200, '', 'NZ', 59),
(201, '', 'NZ-CHAT', 60),
(202, 'GMT', 'GMT', 61),
(203, 'UTC', 'UTC', 62),
(204, NULL, 'US/Aleutian', 6),
(205, NULL, 'US/Hawaii', 7),
(206, NULL, 'US/Samoa', 8);

-- --------------------------------------------------------

--
-- Table structure for table `Gaming.EventMembers`
--

CREATE TABLE IF NOT EXISTS `Gaming.EventMembers` (
  `ID` bigint(20) NOT NULL,
  `FK_Event_ID` bigint(20) NOT NULL,
  `FK_User_ID` bigint(20) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Gaming.EventMembers`
--

INSERT INTO `Gaming.EventMembers` (`ID`, `FK_Event_ID`, `FK_User_ID`) VALUES
(3, 67, 44),
(4, 68, 44),
(5, 69, 44),
(6, 70, 44),
(7, 71, 44),
(8, 72, 44);

-- --------------------------------------------------------

--
-- Table structure for table `Gaming.EventAllowedMembers`
--

CREATE TABLE IF NOT EXISTS `Gaming.EventAllowedMembers` (
  `ID` bigint(20) NOT NULL,
  `FK_Event_ID` bigint(20) NOT NULL,
  `FK_User_ID` bigint(20) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

--
-- Table structure for table `Gaming.Events`
--

CREATE TABLE IF NOT EXISTS `Gaming.Events` (
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
  `DisplayTime` time NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Gaming.Events`
--

INSERT INTO `Gaming.Events` (`ID`, `FK_User_ID_EventCreator`, `FK_Game_ID`, `FK_Genre_ID`, `FK_Platform_ID`, `FK_Timezone_ID`, `EventCreatedDate`, `EventModifiedDate`, `EventScheduledForDate`, `RequiredMemberCount`, `IsActive`, `IsPublic`, `FK_UserGames_ID`, `Notes`, `DisplayDate`, `DisplayTime`) VALUES
(37, 29, NULL, NULL, 11, 142, '2015-08-24 06:31:08', '2015-08-24 06:31:08', '2015-08-26 12:30:00', 5, 1, 1, 3, 'Looking to play through some team deathmatch! On the road to improve my overall k/d!', '2015-08-25', '20:30:00'),
(67, 44, NULL, NULL, 9, 142, '2015-08-26 14:36:00', '2015-08-26 14:36:00', '2015-08-27 20:00:00', 7, 1, 1, 15, 'Play me, please!!', '2015-08-27', '16:00:00'),
(68, 44, NULL, NULL, 13, 143, '2015-08-26 14:37:10', '2015-08-26 14:37:10', '2015-08-27 22:30:00', 3, 1, 1, 24, 'You just gotta play me now!!', '2015-08-27', '17:30:00'),
(69, 44, 7, 5, 9, 142, '2015-08-26 15:11:37', '2015-08-26 15:11:37', '2015-08-29 23:00:00', 2, 1, 1, NULL, 'Event with existing game', '2015-08-29', '19:00:00'),
(70, 44, NULL, NULL, 12, 142, '2015-08-26 15:19:13', '2015-08-26 15:19:13', '2015-08-29 02:00:00', 2, 1, 1, 25, 'Time for a fun game', '2015-08-28', '22:00:00'),
(71, 44, NULL, NULL, 12, 142, '2015-08-26 15:20:00', '2015-08-26 15:20:00', '2015-08-30 01:00:00', 2, 1, 1, 25, 'Time for a rematch!', '2015-08-29', '21:00:00'),
(72, 44, 5, 5, 13, 142, '2015-08-27 16:33:42', '2015-08-27 16:33:42', '2015-08-31 01:00:00', 5, 1, 1, NULL, '''Slayer'' action, 25 kills to win...join up everyone!!!', '2015-08-30', '21:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `Gaming.EventSubscriptions`
--

CREATE TABLE IF NOT EXISTS `Gaming.EventSubscriptions` (
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
-- Table structure for table `Gaming.UserFriends`
--

CREATE TABLE IF NOT EXISTS `Gaming.UserFriends` (
  `ID` bigint(20) NOT NULL,
  `FK_User_ID_ThisUser` bigint(20) NOT NULL,
  `FK_User_ID_Friend` bigint(20) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Gaming.UserFriends`
--

INSERT INTO `Gaming.UserFriends` (`ID`, `FK_User_ID_ThisUser`, `FK_User_ID_Friend`) VALUES
(2, 10, 29),
(3, 10, 31),
(4, 10, 44),
(7, 10, 79);

-- --------------------------------------------------------

--
-- Table structure for table `Gaming.UserGames`
--

CREATE TABLE IF NOT EXISTS `Gaming.UserGames` (
  `ID` bigint(20) NOT NULL,
  `FK_User_ID` bigint(11) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `CreatedDate` datetime NOT NULL,
  `ModifiedDate` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Gaming.UserGames`
--

INSERT INTO `Gaming.UserGames` (`ID`, `FK_User_ID`, `Name`) VALUES
(1, 10, 'Madden 2015'),
(2, 79, 'TestGame1'),
(3, 29, 'Call of Duty: Black Ops III'),
(4, 10, 'TestGame2'),
(5, 79, 'TestGame2'),
(15, 44, 'Gamer2''s Event'),
(23, 44, 'Gamer2''s New Event'),
(24, 44, 'Made-Up Game'),
(25, 44, 'Candyland');

-- --------------------------------------------------------

--
-- Table structure for table `Gaming.UserGamerTags`
--
CREATE TABLE `Gaming.UserGamerTags` (
  `ID` INT NOT NULL AUTO_INCREMENT COMMENT '',
  `FK_User_ID` BIGINT NULL COMMENT '',
  `FK_Platform_ID` INT NULL COMMENT '',
  `GamerTagName` VARCHAR(50) NOT NULL COMMENT '',
  PRIMARY KEY (`ID`)  COMMENT '',
  UNIQUE INDEX `ID_UNIQUE` (`ID` ASC)  COMMENT '',
  INDEX `IDX_FK_User_ID` (`FK_User_ID` ASC)  COMMENT '',
  INDEX `IDX_FK_Platform_ID` (`FK_Platform_ID` ASC)  COMMENT '',
  CONSTRAINT `FK_UserGamerTags_UserID`
    FOREIGN KEY (`FK_User_ID`)
    REFERENCES `Security.Users` (`ID`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `FK_UserGamerTags_PlatformID`
    FOREIGN KEY (`FK_Platform_ID`)
    REFERENCES `Configuration.Platforms` (`ID`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Gaming.UserPlatforms`
--

CREATE TABLE IF NOT EXISTS `Gaming.UserPlatforms` (
  `ID` bigint(20) NOT NULL,
  `FK_User_ID` bigint(11) NOT NULL,
  `FK_Platform_ID` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Gaming.UserPlatforms`
--

INSERT INTO `Gaming.UserPlatforms` (`ID`, `FK_User_ID`, `FK_Platform_ID`) VALUES
(91, 79, 9),
(92, 79, 13),
(93, 86, 9),
(94, 86, 11),
(96, 29, 11),
(97, 87, 12);

-- --------------------------------------------------------

--
-- Table structure for table `Payments.PayPalTransactions`
--

CREATE TABLE `Payments.PayPalTransactions` (
  `ID` BIGINT NOT NULL AUTO_INCREMENT COMMENT '',
  `TxnId` VARCHAR(35) NOT NULL COMMENT '',
  `PayerId` VARCHAR(25) NULL COMMENT '',
  `SubscriptionID` VARCHAR(35) NOT NULL COMMENT '',
  `TxnType` VARCHAR(25) NULL COMMENT '',
  `PDTOperation` VARCHAR(25) NULL COMMENT '',
  `PaymentStatus` VARCHAR(25) NOT NULL COMMENT '',
  `NotificationType` VARCHAR(5) NULL COMMENT '',
  `NotificationDate` datetime NULL,
  `PayPalMsgData` text,
  `TransactionDate` datetime NULL,
  PRIMARY KEY (`ID`)  COMMENT '',
  UNIQUE INDEX `IDX_SubscriptionID_TxnId_TxnType_PaymentStatus_UNIQUE` (`SubscriptionID` ASC, `TxnId` ASC, `TxnType` ASC, `PaymentStatus` ASC) COMMENT '')
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;
-- --------------------------------------------------------

--
-- Table structure for table `Payments.PayPalUsers`
--

CREATE TABLE `Payments.PayPalUsers` (
  `ID` BIGINT NOT NULL AUTO_INCREMENT COMMENT '',
  `FK_User_ID` BIGINT NULL COMMENT '',
  `PayerId` VARCHAR(25) NULL COMMENT '',
  `SubscriptionID` VARCHAR(35) NOT NULL COMMENT '',
  `SubscriptionType` VARCHAR(25) NULL COMMENT '',
  `SubscriptionAmtTotal` DECIMAL(6,2) NULL COMMENT '',
  `SubscriptionAmtPaidLastCycle` DECIMAL(6,2) NULL COMMENT '',
  `LastBillDate` datetime NULL,
  `MembershipExpirationDate` datetime NULL,
  `ExtendedMembershipDays` INT NOT NULL DEFAULT '0' COMMENT '',
  `IsRecurring` tinyint(1) NOT NULL DEFAULT '0',
  `IsActive` tinyint(1) NOT NULL DEFAULT '0',
  `SubscriptionStartedDate` datetime NULL,
  `SubscriptionModifiedDate` datetime NULL,
  PRIMARY KEY (`ID`)  COMMENT '',
  INDEX `IDX_PayPalUsers_FK_User_ID` (`FK_User_ID` ASC)  COMMENT '',
  CONSTRAINT `FK_PayPalUsers_UserID`
        FOREIGN KEY (`FK_User_ID`)
        REFERENCES `Security.Users` (`ID`)
        ON DELETE SET NULL
        ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

--
-- Table structure for table `Security.PageRoles`
--

CREATE TABLE IF NOT EXISTS `Security.PageRoles` (
  `ID` int(11) NOT NULL,
  `FK_Page_ID` int(11) NOT NULL,
  `FK_Role_ID` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Security.PageRoles`
--

INSERT INTO `Security.PageRoles` (`ID`, `FK_Page_ID`, `FK_Role_ID`) VALUES
(1, 7, 4),
(2, 8, 3),
(3, 9, 3),
(4, 10, 4),
(5, 11, 4),
(6, 12, 4),
(7, 13, 3),
(8, 14, 4),
(9, 15, 4),
(10, 16, 3);

-- --------------------------------------------------------

--
-- Table structure for table `Security.Pages`
--

CREATE TABLE IF NOT EXISTS `Security.Pages` (
  `ID` int(11) NOT NULL,
  `Name` varchar(35) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Security.Pages`
--

INSERT INTO `Security.Pages` (`ID`, `Name`) VALUES
(7, 'Index'),
(8, 'MemberHome'),
(9, 'EditProfile'),
(10, 'MobileLogin'),
(11, 'About'),
(12, 'TermsPri'),
(13, 'AccountManagement'),
(14, 'DeveloperBlog'),
(15, 'Faq'),
(16, 'FindFriends');

-- --------------------------------------------------------

--
-- Table structure for table `Security.Roles`
--

CREATE TABLE IF NOT EXISTS `Security.Roles` (
  `ID` int(11) NOT NULL,
  `Name` varchar(35) NOT NULL,
  `SecurityLevel` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Security.Roles`
--

INSERT INTO `Security.Roles` (`ID`, `Name`, `SecurityLevel`) VALUES
(1, 'Admin', 1),
(2, 'PremiumMember', 2),
(3, 'BasicMember', 3),
(4, 'Public', 4);

-- --------------------------------------------------------

--
-- Table structure for table `Security.UserRoles`
--

CREATE TABLE IF NOT EXISTS `Security.UserRoles` (
  `ID` bigint(20) NOT NULL,
  `FK_User_ID` bigint(20) NOT NULL,
  `FK_Role_ID` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Security.UserRoles`
--

INSERT INTO `Security.UserRoles` (`ID`, `FK_User_ID`, `FK_Role_ID`) VALUES
(13, 10, 2),
(32, 29, 3),
(34, 31, 3),
(47, 44, 3),
(62, 59, 3),
(82, 79, 2),
(85, 82, 3),
(86, 83, 3),
(89, 86, 3),
(90, 87, 3),
(91, 88, 3);

-- --------------------------------------------------------

--
-- Table structure for table `Security.Users`
--

CREATE TABLE IF NOT EXISTS `Security.Users` (
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
  `IsActive` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `Security.Users`
--

INSERT INTO `Security.Users` (`ID`, `FK_Timezone_ID`, `UserName`, `FirstName`, `LastName`, `EmailAddress`, `IsPremiumMember`, `Password`, `Gender`, `Birthdate`, `Autobiography`) VALUES
(10, 142, 'sgilesTest', 'Stephen', 'Giles', 'sgilestest@morrellweb.com', 1, '$2a$15$IQyMRpBDO7uFbRVp4qxDFuP5Wkn/uCssysDMfYgt3edoc/kz8s.Q2', 'M', '1983-11-14', 'My name is ''Stephen"'),
(29, 142, 'Paul Morrell', 'Paul', 'Morrell', 'pbmorrell@att.net', 0, '$2y$10$7BNqIlWNifBCvlVowyzWVuIPlVomA.bbKwQE88EVeTbA.acmUtGDy', 'M', '1983-11-27', 'Adult gamer, Looking for other friends to play with. Must have a mic!'),
(31, 142, 'gamerTest', 'Gamer', 'Test', 'gamerTest@ogs.com', 0, '$2y$10$WKKurt8XlSKH.0wAnDhX2etlBYghz7z1xliFZvRYrhAA2UvYPG1Si', 'M', '2013-07-31', 'my bio'),
(44, 144, 'gamerTest2', 'Gamer2', 'Test', 'gamerTest2@ogs.com', 0, '$2y$10$UwLWePLi.rkexdOFdWCOSeAvlf/U1v/fsZ29Cns7Y7vQJj8gNLV8m', 'M', '1985-07-21', 'here''s my bio'),
(79, 143, 'gamerTest3', 'Gamer', 'Test3', 'gamertest3@gamers.com', 1, '$2y$10$cyaJMPsmQO4zwXMLxvzylec9byh854QcsWc9UcqnRvnSTU.Xw24xW', 'M', '2013-08-20', 'Bio'),
(82, 163, 'pbmorrell', 'Pablo', 'Morello', 'pbmorrell@att2.net', 0, '$2y$10$nQmCZOkK.eEhPTQ4AinMpeQZ6kRRd.1gE0S3HBCdZdarfY6GW0jte', 'F', '1952-11-13', 'I am the Unicorn Master of the Universe.'),
(83, 144, 'theGiles01', 'The', 'Giles', 'thegiles01@gmail.com', 0, '$2y$10$TIzWuxcK9fCryA3xYSo0w.H.2RrWPsY9MjcAxex7VZBM7Dv9o2/j6', 'M', '1986-08-20', 'I am the Master of the Universe (no unicorns)'),
(86, 142, 'gamerTest4', 'Gamer', 'Test4', 'gamertest4@ogs.com', 0, '$2y$10$3XpsJ1cJN2.iHCGmg8KOXupw.BW.2UTp8GpZZsaXp5bW4IWG1cbda', 'F', '1984-08-15', 'Biography is this'),
(87, 158, 'donkeymaster@donkey.com', 'Pablo', 'Morrello', 'donkeymaster@donkey.com', 0, '$2y$10$5SnRaby1SzCXj4KsFDb/2OzQ9jfkqtMjwdJC2vbX32SVL0Ysjojsi', 'M', '1996-09-11', 'I like donkeys'),
(88, NULL, 'trialanderror@ogs.com', '', '', 'trialanderror@ogs.com', 0, '$2y$10$r0ueC7gIxgXpYTU4T/Q54OzvTzToA8avIGqbbx5eBnP0cQPoKJOb.', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Security.UserSessions`
--

CREATE TABLE IF NOT EXISTS `Security.UserSessions` (
  `ID` varchar(32) NOT NULL,
  `LastAccess` int(10) unsigned DEFAULT NULL,
  `LastAccessTimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `SessionData` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Administration.Logging`
--
ALTER TABLE `Administration.Logging`
  ADD PRIMARY KEY (`ID`), ADD KEY `IDX_Category` (`Category`);

--
-- Indexes for table `Configuration.GameGenres`
--
ALTER TABLE `Configuration.GameGenres`
  ADD PRIMARY KEY (`ID`), ADD KEY `IDX_GameGenres_GameID` (`FK_Game_ID`), ADD KEY `IDX_GameGenres_GenreID` (`FK_Genre_ID`);

--
-- Indexes for table `Configuration.GamePlatforms`
--
ALTER TABLE `Configuration.GamePlatforms`
  ADD PRIMARY KEY (`ID`), ADD KEY `IDX_GamePlatforms_PlatformID` (`FK_Platform_ID`), ADD KEY `IDX_GamePlatforms_GameID` (`FK_Game_ID`);

--
-- Indexes for table `Configuration.Games`
--
ALTER TABLE `Configuration.Games`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `Configuration.Genres`
--
ALTER TABLE `Configuration.Genres`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `Configuration.Platforms`
--
ALTER TABLE `Configuration.Platforms`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `Configuration.TimeZones`
--
ALTER TABLE `Configuration.TimeZones`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `Gaming.EventMembers`
--
ALTER TABLE `Gaming.EventMembers`
  ADD PRIMARY KEY (`ID`), ADD KEY `IDX_EventMembers_EventID` (`FK_Event_ID`), ADD KEY `IDX_EventMembers_UserID` (`FK_User_ID`);

--
-- Indexes for table `Gaming.EventMembers`
--
ALTER TABLE `Gaming.EventAllowedMembers`
  ADD PRIMARY KEY (`ID`), ADD KEY `IDX_EventAllowedMembers_EventID` (`FK_Event_ID`), ADD KEY `IDX_EventAllowedMembers_UserID` (`FK_User_ID`);
  
--
-- Indexes for table `Gaming.Events`
--
ALTER TABLE `Gaming.Events`
  ADD PRIMARY KEY (`ID`), ADD KEY `IDX_Events_UserID` (`FK_User_ID_EventCreator`), ADD KEY `IDX_Events_GameID` (`FK_Game_ID`), ADD KEY `IDX_Events_GenreID` (`FK_Genre_ID`), ADD KEY `IDX_Events_PlatformID` (`FK_Platform_ID`), ADD KEY `IDX_Events_TimezoneID` (`FK_Timezone_ID`), ADD KEY `FK_Events_UserGamesID_idx` (`FK_UserGames_ID`);

--
-- Indexes for table `Gaming.EventSubscriptions`
--
ALTER TABLE `Gaming.EventSubscriptions`
  ADD PRIMARY KEY (`ID`), ADD KEY `IDX_EventSubscriptions_UserID` (`FK_User_ID_EventSubscriber`), ADD KEY `IDX_EventSubscriptions_GameID` (`FK_Game_ID`), ADD KEY `IDX_EventSubscriptions_PlatformID` (`FK_Platform_ID`), ADD KEY `IDX_EventSubscriptions_TimezoneID` (`FK_Timezone_ID`), ADD KEY `IDX_EventSubscriptions_GenreID` (`FK_Genre_ID`);

--
-- Indexes for table `Gaming.UserFriends`
--
ALTER TABLE `Gaming.UserFriends`
  ADD PRIMARY KEY (`ID`), ADD KEY `IDX_UserFriends_UserID_ThisUser` (`FK_User_ID_ThisUser`), ADD KEY `IDX_UserFriends_UserID_Friend` (`FK_User_ID_Friend`);

--
-- Indexes for table `Gaming.UserGames`
--
ALTER TABLE `Gaming.UserGames`
  ADD PRIMARY KEY (`ID`), ADD KEY `IDX_UserGames_UserID` (`FK_User_ID`),  ADD UNIQUE `IDX_Unique_UserID_Name`(`FK_User_ID`, `Name`);

--
-- Indexes for table `Gaming.UserPlatforms`
--
ALTER TABLE `Gaming.UserPlatforms`
  ADD PRIMARY KEY (`ID`), ADD KEY `IDX_UserPlatforms_UserID` (`FK_User_ID`), ADD KEY `IDX_UserPlatforms_PlatformID` (`FK_Platform_ID`);

--
-- Indexes for table `Security.PageRoles`
--
ALTER TABLE `Security.PageRoles`
  ADD PRIMARY KEY (`ID`), ADD KEY `IDX_Page_ID` (`FK_Page_ID`), ADD KEY `IDX_Role_ID` (`FK_Role_ID`);

--
-- Indexes for table `Security.Pages`
--
ALTER TABLE `Security.Pages`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `Security.Roles`
--
ALTER TABLE `Security.Roles`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `Security.UserRoles`
--
ALTER TABLE `Security.UserRoles`
  ADD PRIMARY KEY (`ID`), ADD KEY `IDX_User_ID` (`FK_User_ID`), ADD KEY `IDX_Role_ID` (`FK_Role_ID`);

--
-- Indexes for table `Security.Users`
--
ALTER TABLE `Security.Users`
  ADD PRIMARY KEY (`ID`), ADD KEY `IDX_TimeZoneID` (`FK_Timezone_ID`), ADD KEY `IDX_IsActive` (`IsActive`);

--
-- Indexes for table `Security.UserSessions`
--
ALTER TABLE `Security.UserSessions`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Administration.Logging`
--
ALTER TABLE `Administration.Logging`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=104;
--
-- AUTO_INCREMENT for table `Configuration.GameGenres`
--
ALTER TABLE `Configuration.GameGenres`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT for table `Configuration.GamePlatforms`
--
ALTER TABLE `Configuration.GamePlatforms`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=45;
--
-- AUTO_INCREMENT for table `Configuration.Games`
--
ALTER TABLE `Configuration.Games`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT for table `Configuration.Genres`
--
ALTER TABLE `Configuration.Genres`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `Configuration.Platforms`
--
ALTER TABLE `Configuration.Platforms`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT for table `Configuration.TimeZones`
--
ALTER TABLE `Configuration.TimeZones`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=207;
--
-- AUTO_INCREMENT for table `Gaming.EventMembers`
--
ALTER TABLE `Gaming.EventMembers`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `Gaming.EventAllowedMembers`
--
ALTER TABLE `Gaming.EventAllowedMembers`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `Gaming.Events`
--
ALTER TABLE `Gaming.Events`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=73;
--
-- AUTO_INCREMENT for table `Gaming.EventSubscriptions`
--
ALTER TABLE `Gaming.EventSubscriptions`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `Gaming.UserFriends`
--
ALTER TABLE `Gaming.UserFriends`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `Gaming.UserGames`
--
ALTER TABLE `Gaming.UserGames`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=26;
--
-- AUTO_INCREMENT for table `Gaming.UserPlatforms`
--
ALTER TABLE `Gaming.UserPlatforms`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=98;
--
-- AUTO_INCREMENT for table `Security.PageRoles`
--
ALTER TABLE `Security.PageRoles`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `Security.Pages`
--
ALTER TABLE `Security.Pages`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT for table `Security.Roles`
--
ALTER TABLE `Security.Roles`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `Security.UserRoles`
--
ALTER TABLE `Security.UserRoles`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=92;
--
-- AUTO_INCREMENT for table `Security.Users`
--
ALTER TABLE `Security.Users`
  MODIFY `ID` bigint(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=89;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `Configuration.GameGenres`
--
ALTER TABLE `Configuration.GameGenres`
ADD CONSTRAINT `FK_GameGenres_GameID` FOREIGN KEY (`FK_Game_ID`) REFERENCES `Configuration.Games` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `FK_GameGenres_GenreID` FOREIGN KEY (`FK_Genre_ID`) REFERENCES `Configuration.Genres` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Configuration.GamePlatforms`
--
ALTER TABLE `Configuration.GamePlatforms`
ADD CONSTRAINT `FK_GamePlatforms_Game_ID` FOREIGN KEY (`FK_Game_ID`) REFERENCES `Configuration.Games` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `FK_GamePlatforms_Platform_ID` FOREIGN KEY (`FK_Platform_ID`) REFERENCES `Configuration.Platforms` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Gaming.EventMembers`
--
ALTER TABLE `Gaming.EventMembers`
ADD CONSTRAINT `FK_EventMembers_EventID` FOREIGN KEY (`FK_Event_ID`) REFERENCES `Gaming.Events` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `FK_EventMembers_UserID` FOREIGN KEY (`FK_User_ID`) REFERENCES `Security.Users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Gaming.EventAllowedMembers`
--
ALTER TABLE `Gaming.EventAllowedMembers`
ADD CONSTRAINT `FK_EventAllowedMembers_EventID` FOREIGN KEY (`FK_Event_ID`) REFERENCES `Gaming.Events` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `FK_EventAllowedMembers_UserID` FOREIGN KEY (`FK_User_ID`) REFERENCES `Security.Users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Gaming.Events`
--
ALTER TABLE `Gaming.Events`
ADD CONSTRAINT `FK_Events_GameID` FOREIGN KEY (`FK_Game_ID`) REFERENCES `Configuration.Games` (`ID`),
ADD CONSTRAINT `FK_Events_GenreID` FOREIGN KEY (`FK_Genre_ID`) REFERENCES `Configuration.Genres` (`ID`),
ADD CONSTRAINT `FK_Events_PlatformID` FOREIGN KEY (`FK_Platform_ID`) REFERENCES `Configuration.Platforms` (`ID`),
ADD CONSTRAINT `FK_Events_TimeZoneID` FOREIGN KEY (`FK_Timezone_ID`) REFERENCES `Configuration.TimeZones` (`ID`),
ADD CONSTRAINT `FK_Events_UserGamesID` FOREIGN KEY (`FK_UserGames_ID`) REFERENCES `Gaming.UserGames` (`ID`) ON DELETE SET NULL ON UPDATE NO ACTION,
ADD CONSTRAINT `FK_Events_UserID` FOREIGN KEY (`FK_User_ID_EventCreator`) REFERENCES `Security.Users` (`ID`);

--
-- Constraints for table `Gaming.EventSubscriptions`
--
ALTER TABLE `Gaming.EventSubscriptions`
ADD CONSTRAINT `FK_EventSubscriptions_GameID` FOREIGN KEY (`FK_Game_ID`) REFERENCES `Configuration.Games` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `FK_EventSubscriptions_GenreID` FOREIGN KEY (`FK_Genre_ID`) REFERENCES `Configuration.Genres` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `FK_EventSubscriptions_PlatformID` FOREIGN KEY (`FK_Platform_ID`) REFERENCES `Configuration.Platforms` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `FK_EventSubscriptions_TimezoneID` FOREIGN KEY (`FK_Timezone_ID`) REFERENCES `Configuration.TimeZones` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `FK_EventSubscriptions_UserID` FOREIGN KEY (`FK_User_ID_EventSubscriber`) REFERENCES `Security.Users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Gaming.UserFriends`
--
ALTER TABLE `Gaming.UserFriends`
ADD CONSTRAINT `FK_UserFriends_Users_Friend` FOREIGN KEY (`FK_User_ID_Friend`) REFERENCES `Security.Users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `FK_UserFriends_Users_ThisUser` FOREIGN KEY (`FK_User_ID_ThisUser`) REFERENCES `Security.Users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Gaming.UserGames`
--
ALTER TABLE `Gaming.UserGames`
ADD CONSTRAINT `FK_UserGames_User_ID` FOREIGN KEY (`FK_User_ID`) REFERENCES `Security.Users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Gaming.UserPlatforms`
--
ALTER TABLE `Gaming.UserPlatforms`
ADD CONSTRAINT `FK_UserPlatforms_Platform_ID` FOREIGN KEY (`FK_Platform_ID`) REFERENCES `Configuration.Platforms` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `FK_UserPlatforms_User_ID` FOREIGN KEY (`FK_User_ID`) REFERENCES `Security.Users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Security.PageRoles`
--
ALTER TABLE `Security.PageRoles`
ADD CONSTRAINT `FK_PageRoles_Pages` FOREIGN KEY (`FK_Page_ID`) REFERENCES `Security.Pages` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `FK_PageRoles_Roles` FOREIGN KEY (`FK_Role_ID`) REFERENCES `Security.Roles` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Security.UserRoles`
--
ALTER TABLE `Security.UserRoles`
ADD CONSTRAINT `FK_UserRoles_Roles` FOREIGN KEY (`FK_Role_ID`) REFERENCES `Security.Roles` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `FK_UserRoles_Users` FOREIGN KEY (`FK_User_ID`) REFERENCES `Security.Users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Security.Users`
--
ALTER TABLE `Security.Users`
ADD CONSTRAINT `FK_Users_TimeZones` FOREIGN KEY (`FK_Timezone_ID`) REFERENCES `Configuration.TimeZones` (`ID`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
