<?php
/**
 * @category   Shiptheory
 * @package    Shiptheory_Shippinglabels
 */
class Shiptheory_Shippinglabels_Block_Adminhtml_Shippinglabels extends Mage_Adminhtml_Block_Widget_Grid_Container
{

	public function __construct()
	{

		$this->_blockGroup = 'shippinglabels';
		$this->_controller = 'adminhtml_shippinglabels';
		$this->_headerText = $this->__('Shiptheory History');
		 
		//$this->_addButton('retry', array(
		//		'label'     => Mage::helper('shippinglabels')->__('Retry All'),
		//		'onclick'   => "setLocation('".$this->getUrl('*/*/retry')."')",
		//));
		
		//$this->_addButton('refresh', array(
		//		'label'     => Mage::helper('shippinglabels')->__('Refresh Config'),
		//		'onclick'   => "setLocation('".$this->getUrl('*/*/refresh')."')",
		//));
		
		
		parent::__construct();
		
		$this->removeButton('add');

	}
}