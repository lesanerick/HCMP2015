CREATE TABLE `commodity_source_sub_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `commodity_source_id` int(11) NOT NULL,
  `source_name` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1