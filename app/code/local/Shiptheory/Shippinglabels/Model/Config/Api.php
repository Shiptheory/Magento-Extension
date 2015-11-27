<?php
/**
 * @category   Shiptheory
 * @package    Shiptheory_Shippinglabels
 */
class Shiptheory_Shippinglabels_Model_Config_Api extends Mage_Api_Model_Resource_Abstract {


    public function getShippingMethods() {

		return Mage::getModel('shippinglabels/config')->getShippingMethodData();	
		
    }

}