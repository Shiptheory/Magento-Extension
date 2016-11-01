<?php

/**
 * @category   Shiptheory
 * @package    Shiptheory_Shippinglabels
 */
$installer = $this;

$installer->startSetup();

$installer->run("
	
	ALTER TABLE {$this->getTable('shippinglabels/history')} ADD COLUMN `shipment_id` varchar(60) default NULL;	
    ");

$installer->endSetup();
