<?php
/**
 * @category   Shiptheory
 * @package    Shiptheory_Shippinglabels
 */
$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('shippinglabels/history')};
CREATE TABLE {$this->getTable('shippinglabels/history')} (
  `id` int(11) NOT NULL auto_increment,
  `internal_id` int(11) default NULL,
  `reference` varchar(60) default NULL,
  `message` varchar(200) default NULL,
  `attempts` tinyint(3) default '0',  
  `created` timestamp NULL default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` varchar(12) default 'processing',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

$installer->endSetup();