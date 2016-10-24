
create database phpunit_cache;
use phpunit_cache;


CREATE TABLE IF NOT EXISTS `image_cache` (
  `wid` int(11) NOT NULL COMMENT 'Not Auto increment ',
  `pid` int(11) NOT NULL COMMENT 'Not Auto increment ',
  `time` int(11) NOT NULL COMMENT 'Not Auto increment ',
  `image` mediumblob NOT NULL,
  `image_link` varchar(300) NOT NULL,
  `image_type` int(11),
  PRIMARY KEY (`wid`,`pid`)
 
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;