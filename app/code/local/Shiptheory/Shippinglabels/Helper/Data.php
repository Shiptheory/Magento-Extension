<?php
/**
 * @category   Shiptheory
 * @package    Shiptheory_Shippinglabels
 */
class Shiptheory_Shippinglabels_Helper_Data extends Mage_Core_Helper_Abstract
{
	
	/**
	 * Current extension version
	 *
	 * @return string
	 **/
	
	public function getExtensionVersion()
	{
		return (string) Mage::getConfig()->getNode()->modules->Shiptheory_Shippinglabels->version;
	}
	
	/**
	 * Logger
	 *
	 * @param string
	 **/	
	
	public function log($message)
	{
		
		if(Mage::getStoreConfig('shippinglabels/advanced/log')){
			
			Mage::log($message, null, 'Shiptheory.log');
			
		}
		
	}
	
	/**
	 * Changes extension status
	 *
	 * @param string new status
	 * @return string current status
	 **/	
	
	public function status($status = null)
	{
		$current_status = Mage::getStoreConfig('shippinglabels/default/status');
		
		//return current status
		if(is_null($status)){
			return $current_status;
		}
		
		//update status
		$config = new Mage_Core_Model_Config();
		$config->saveConfig('shippinglabels/default/status', $status, 'default', 0);
			
	}
        
}
