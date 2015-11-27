<?php
/**
 * @category   Shiptheory
 * @package    Shiptheory_Shippinglabels
 */
class Shiptheory_Shippinglabels_Block_Button_New extends Mage_Adminhtml_Block_System_Config_Form_Field
{

	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
	{
		
		$this->setElement($element);

		$html = $this->getLayout()->createBlock('adminhtml/widget_button')
		->setType('button')
		->setClass('scalable')
		->setLabel('Register Free')
		->setOnClick("window.open('https://shiptheory.com/register','_blank')")
		->toHtml();

		return $html;
		
	}
}
