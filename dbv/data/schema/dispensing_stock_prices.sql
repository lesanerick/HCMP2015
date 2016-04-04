CREATE TABLE `dispensing_stock_prices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `facility_code` int(11) NOT NULL,
  `commodity_id` int(11) NOT NULL,
  `commodity_name` varchar(200) NOT NULL,
  `price` int(11) NOT NULL,
  `price_per` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1