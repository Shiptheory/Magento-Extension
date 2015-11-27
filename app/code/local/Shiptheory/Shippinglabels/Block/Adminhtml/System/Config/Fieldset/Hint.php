<?php
/**
 * @category   Shiptheory
 * @package    Shiptheory_Shippinglabels
 */
class Shiptheory_Shippinglabels_Block_Adminhtml_System_Config_Fieldset_Hint extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{

	/**
	 * hint template file
	 */
	
    protected $_template = 'shiptheory/system/config/fieldset/hint.phtml';

    /**
     * Render hint
     */
    
    public function render(Varien_Data_Form_Element_Abstract $element) {
    	
        return $this->toHtml();
        
    }

}
