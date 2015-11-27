<?php
/**
 * @category   Shiptheory
 * @package    Shiptheory_Shippinglabels
 */
class Shiptheory_Shippinglabels_Triggers
{
    public function toOptionArray()
    {
        return array(
				array('value' => 'order', 'label'=>Mage::helper('adminhtml')->__('when orders are created')),
				array('value' => 'shipment', 'label'=>Mage::helper('adminhtml')->__('when orders are shipped'))
				);
    }
}