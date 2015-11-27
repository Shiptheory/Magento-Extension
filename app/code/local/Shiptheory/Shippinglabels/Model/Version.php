<?php
/**
 * @category   Shiptheory
 * @package    Shiptheory_Shippinglabels
 */
class Shiptheory_Shippinglabels_Model_Version extends Mage_Core_Model_Abstract 
{
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return (string) Mage::helper('shippinglabels')->getExtensionVersion();
    }
				
}
