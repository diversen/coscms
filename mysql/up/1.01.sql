CREATE TABLE IF NOT EXISTS `video` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) NOT NULL,
  `title` varchar (255) NOT NULL,
  `reference` varchar (255) NOT NULL,
  `mimetype` varchar (255) NOT NULL,
  `abstract` text NOT NULL,
  `published` boolean NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `full_path` varchar(1024) NOT NULL,
  `web_path` varchar(1024) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;

CREATE INDEX `reference_index` ON `video` (parent_id, reference);
