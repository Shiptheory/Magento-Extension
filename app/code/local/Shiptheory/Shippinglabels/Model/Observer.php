<?php
/**
 * @category   Shiptheory
 * @package    Shiptheory_Shippinglabels
 */
class Shiptheory_Shippinglabels_Model_Observer
{
    public function addShippingAction($observer)
    {

	    if(!Mage::getStoreConfig('shippinglabels/misc/enabled')){
		return;
	    }

        $block = $observer->getEvent()->getBlock();
        if($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction_Abstract
            && $block->getRequest()->getControllerName() == 'sales_order')
        {
            $block->addItem('shippinglabels', array(
                'label' => 'Ship Orders',
                'url' => Mage::app()->getStore()->getUrl('adminhtml/shippinglabels_shippinglabels/ship')
            ));
        }
    }
}
