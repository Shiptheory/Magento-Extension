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
        if(get_class($block) =='Mage_Adminhtml_Block_Widget_Grid_Massaction'
            && $block->getRequest()->getControllerName() == 'sales_order')
        {
            $block->addItem('shippinglabels', array(
                'label' => 'Ship Orders',
                'url' => Mage::app()->getStore()->getUrl('shippinglabels/adminhtml_shippinglabels/ship')
            ));
        }
    }
}
