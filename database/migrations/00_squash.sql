SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `users` (
  `id` smallint(6) UNSIGNED NOT NULL,
  `parent` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `lastname` varchar(30) NOT NULL DEFAULT '',
  `firstname` varchar(30) NOT NULL DEFAULT '',
  `gender` enum('M','W') NOT NULL DEFAULT 'M',
  `licence` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `sportident` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `login` varchar(24) NOT NULL DEFAULT '',
  `password` varchar(32) NOT NULL DEFAULT '',
  `permission` enum('USER','STAFF','COACH','ROOT','GUEST','COACHSTAFF') NOT NULL DEFAULT 'USER',
  `lastlog` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `adresse1` varchar(50) NOT NULL DEFAULT '',
  `adresse2` varchar(50) NOT NULL DEFAULT '',
  `postal_code` varchar(8) NOT NULL DEFAULT '',
  `city` varchar(32) NOT NULL DEFAULT '',
  `birthdate` date NOT NULL DEFAULT '0000-00-00',
  `nose_email` varchar(50) NOT NULL DEFAULT '',
  `real_email` varchar(50) NOT NULL DEFAULT '',
  `phone` varchar(20) NOT NULL DEFAULT '',
  `last_ip` varchar(40) NOT NULL DEFAULT '0',
  `mlnose` tinyint(1) NOT NULL DEFAULT 0,
  `mlcomite` tinyint(1) NOT NULL DEFAULT 0,
  `mlcoachs` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_bin;

-- events

CREATE TABLE `events` (
  `id` smallint(6) UNSIGNED NOT NULL,
  `name` varchar(64) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `start_date` date NOT NULL DEFAULT current_timestamp(),
  `end_date` date NOT NULL DEFAULT current_timestamp(),
  `limit_date` date NOT NULL DEFAULT current_timestamp(),
  `open` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `attachment` smallint(5) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_bin;


CREATE TABLE `races` (
  `id` smallint(6) UNSIGNED NOT NULL,
  `date` date NOT NULL DEFAULT current_timestamp(),
  `name` varchar(32) NOT NULL DEFAULT '',
  `place` varchar(32) NOT NULL DEFAULT '',
  `type` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `circuits` tinyint(4) NOT NULL DEFAULT 0,
  `event_id` smallint(6) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_bin;


CREATE TABLE `event_entries` (
  `event_id` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `user_id` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `present` tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
  `transport` tinyint(1) NOT NULL DEFAULT 0,
  `accomodation` tinyint(1) NOT NULL DEFAULT 0,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `comment` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_bin;


CREATE TABLE `race_entries` (
  `race_id` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `user_id` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `licence` mediumint(9) UNSIGNED NOT NULL DEFAULT 0,
  `sportident` mediumint(9) UNSIGNED NOT NULL DEFAULT 0,
  `present` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `upgraded` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `comment` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_bin;