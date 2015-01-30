CREATE TABLE IF NOT EXISTS `barcodes` (
  `barcode` varchar(100) COLLATE latin1_general_ci NOT NULL COMMENT 'Barcode ID',
  `ucinetid` varchar(8) COLLATE latin1_general_ci NOT NULL COMMENT 'UCInetID associated to Barcode',
  `date` date NOT NULL,
  `time` time NOT NULL,
  `volunteer` varchar(8) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`barcode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

CREATE TABLE IF NOT EXISTS `errors` (
  `eid` bigint(64) NOT NULL AUTO_INCREMENT,
  `message` varchar(500) COLLATE latin1_general_ci NOT NULL,
  `status` varchar(50) COLLATE latin1_general_ci NOT NULL,
  `ucinetid` varchar(8) COLLATE latin1_general_ci NOT NULL,
  `page` varchar(50) COLLATE latin1_general_ci NOT NULL,
  `referer` varchar(100) COLLATE latin1_general_ci NOT NULL,
  `browser` varchar(250) COLLATE latin1_general_ci NOT NULL,
  `ip` varchar(20) COLLATE latin1_general_ci NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  PRIMARY KEY (`eid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ; 

CREATE TABLE IF NOT EXISTS `events` (
  `eid` mediumint(8) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE latin1_general_ci NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `host` varchar(50) COLLATE latin1_general_ci NOT NULL,
  `prize` mediumint(8) NOT NULL,
  `description` varchar(1000) COLLATE latin1_general_ci NOT NULL,
  `volunteer` varchar(8) COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`eid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ; 

CREATE TABLE IF NOT EXISTS `logon` (
  `ucinetid` varchar(8) COLLATE latin1_general_ci NOT NULL,
  `password` varchar(50) COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `last_login` datetime NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  PRIMARY KEY (`ucinetid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci; 

CREATE TABLE IF NOT EXISTS `pages` (
  `ID` mediumint(3) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE latin1_general_ci NOT NULL,
  `title` varchar(30) COLLATE latin1_general_ci NOT NULL,
  `css` varchar(30) COLLATE latin1_general_ci NOT NULL,
  `url` varchar(100) COLLATE latin1_general_ci NOT NULL,
  `views` mediumint(5) NOT NULL DEFAULT '0',
  `access` tinyint(1) NOT NULL DEFAULT '0',
  `tab` varchar(20) COLLATE latin1_general_ci NOT NULL,
  `description` mediumtext COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;
INSERT IGNORE INTO `pages` VALUES(1, 'index', 'Home', '0', '/index.php', 0, 0, 'index', 'ESCan-Electronic tracking system for E-Week. In order to win prizes, attend events, and compete in competitions, you must register your UCInetID with a wristband.');
INSERT IGNORE INTO `pages` VALUES(2, 'error', 'Error', '0', '/error.php', 0, 0, 'index', '');
INSERT IGNORE INTO `pages` VALUES(3, 'scan', 'Scan', '', '/scan.php', 0, 4, 'Scan', '');
INSERT IGNORE INTO `pages` VALUES(4, 'webmaster', 'Webmaster', '', '/webmaster.php', 0, 8, 'WebMaster', '');
INSERT IGNORE INTO `pages` VALUES(5, 'login', 'Sign In', '0', '/login.php', 0, 0, 'login', '');
INSERT IGNORE INTO `pages` VALUES(6, 'signup', 'Sign Up', '0', '/signup.php', 0, 0, 'Sign Up', '');
INSERT IGNORE INTO `pages` VALUES(7, 'logout', 'Logout', '0', '/logout.php', 0, 2, '', '');
INSERT IGNORE INTO `pages` VALUES(8, 'events', 'Events', '0', '/events.php', 0, 0, 'events', '');
INSERT IGNORE INTO `pages` VALUES(9, 'iforgot', 'Forgot Password', '0', '/iforgot.php', 0, 0, '', '');
INSERT IGNORE INTO `pages` VALUES(10, 'recover', 'Recover', '0', '/recover.php', 0, 2, '', '');
INSERT IGNORE INTO `pages` VALUES(11, 'settings', 'Settings', '0', '/settings.php', 0, 2, '', '');
INSERT IGNORE INTO `pages` VALUES(12, 'register', 'Register', '', '/register.php', 0, 0, 'Register', '');
INSERT IGNORE INTO `pages` VALUES(13, 'admin', 'Admin', '0', '/admin.php', 0, 6, 'admin', '');
INSERT IGNORE INTO `pages` VALUES(14, 'statistics', 'Statistics', '', '/statistics.php', 0, 0, 'Statistics', '');
INSERT IGNORE INTO `pages` VALUES(15, 'instructions', 'Instructions', '', '/instructions.php', 0, 0, 'instructions', '');

CREATE TABLE IF NOT EXISTS `reset` (
  `ucinetid` varchar(8) COLLATE latin1_general_ci NOT NULL,
  `secret` varchar(32) COLLATE latin1_general_ci NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ucinetid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

CREATE TABLE IF NOT EXISTS `scans` (
  `sid` bigint(32) NOT NULL AUTO_INCREMENT COMMENT 'Scan ID',
  `eid` varchar(8) COLLATE latin1_general_ci NOT NULL COMMENT 'Event ID',
  `barcode` varchar(100) COLLATE latin1_general_ci NOT NULL COMMENT 'Barcode ID',
  `volunteer` varchar(8) COLLATE latin1_general_ci NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  PRIMARY KEY (`sid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `settings` (
  `name` varchar(20) NOT NULL,
  `value` varchar(8) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci ;
INSERT IGNORE INTO `settings` VALUES ('eweekstart', '2015-W09');
CREATE TABLE IF NOT EXISTS `tabs` (
  `tid` mediumint(9) NOT NULL AUTO_INCREMENT,
  `page` varchar(20) COLLATE latin1_general_ci NOT NULL,
  `title` varchar(20) COLLATE latin1_general_ci NOT NULL,
  `access` tinyint(1) NOT NULL DEFAULT '0',
  `public_only` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;
INSERT IGNORE INTO `tabs` VALUES(1, 'index', 'Home', 0, 0);
INSERT IGNORE INTO `tabs` VALUES(2, 'events', 'Events', 0, 0);
INSERT IGNORE INTO `tabs` VALUES(3, 'login', 'Sign In', 0, 1);
INSERT IGNORE INTO `tabs` VALUES(9, 'register', 'Register', 0, 0);
INSERT IGNORE INTO `tabs` VALUES(5, 'scan', 'Scan', 3, 0);
INSERT IGNORE INTO `tabs` VALUES(6, 'statistics', 'Statistics', 0, 0);
INSERT IGNORE INTO `tabs` VALUES(7, 'admin', 'Admin', 6, 0);
INSERT IGNORE INTO `tabs` VALUES(11, 'instructions', 'Instructions', 0, 0);
INSERT IGNORE INTO `tabs` VALUES(12, 'webmaster', 'Webmaster', 8, 0);
CREATE TABLE IF NOT EXISTS `users` (
  `ucinetid` varchar(8) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `barcode` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'barcode id',
  `name` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'first name',
  `email` varchar(150) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'email',
  `major` varchar(75) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'Major',
  `level` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'Year Level',
  `opt` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'opt-out mail',
  `access` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'access level',
  `elig` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'prize eligiblility',
  `date` date NOT NULL,
  `time` time NOT NULL,
  `volunteer` varchar(8) NOT NULL,
  PRIMARY KEY (`ucinetid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
ALTER TABLE `scans` ADD INDEX( `eid`);
ALTER TABLE `scans` ADD INDEX( `barcode`);
ALTER TABLE `scans` ADD INDEX( `eid`, `barcode`);
ALTER TABLE `users` ADD INDEX( `barcode`);
ALTER TABLE `users` ADD INDEX( `major`);
ALTER TABLE `users` ADD INDEX( `access`);
ALTER TABLE `users` ADD INDEX( `level`);
ALTER TABLE `barcodes` ADD INDEX( `barcode`);