DROP TABLE IF EXISTS `modules`;

CREATE TABLE `modules` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `module_name` varchar(128) NOT NULL DEFAULT '',
    `module_version` varchar(128) NOT NULL DEFAULT '',
    `menu_item` int(1) NOT NULL DEFAULT '0',
    `run_level` varchar(32) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;


DROP TABLE IF EXISTS `menus`;

CREATE TABLE `menus` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(128) NOT NULL DEFAULT '',
    `url` varchar(512) NOT NULL DEFAULT '',
    `auth` varchar(128) NOT NULL DEFAULT '',
    `module_name` varchar(128) NOT NULL DEFAULT '',
    `parent` int(10) NOT NULL DEFAULT 0,
    `weight` int(10) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;

DROP TABLE IF EXISTS `language`;

CREATE TABLE `language` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `language` varchar(8) NOT NULL DEFAULT '',
    `module_name` varchar(256) NOT NULL DEFAULT '',
    `translation` text,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;
