<?php
/**
 * @category   Shiptheory
 * @package    Shiptheory_Shippinglabels
 */
class Shiptheory_Shippinglabels_Model_Config extends Mage_Core_Model_Abstract
{
	
    protected function _construct()
    {
		$this->_init('shippinglabels/config');
    }
    
    /**
     * Load config data
     *
     * @return array
	 * @param string
     */
    
    public function load($api_key, $field = null)
    {

		$data = array();
		$data['websites']    = $this->getWebsiteData();
		$data['status']      = $this->getStatusData();
		$data['shipping']	 = $this->getShippingMethodData();
		$data['shiptheory_url']  = $this->getUrl();		
		$data['shiptheory_user']  = $this->getApiUser();
		$data['shiptheory_pass']  = $api_key;
		return $data;

    }
    
    /**
     * Create a Shiptheory Config Update
     *
     * @param array config_data
     **/
    
    public function create($config_data)
    {
    	 
    	try{
			
    		$post = Mage::getModel("shippinglabels/webservice")->config($config_data);
    
    	}catch (Exception $e) {
    		Mage::helper('shippinglabels')->log("Failed communicating with Shiptheory: ".$e);
    	}
    
    	if(!$post){
    		Mage::helper('shippinglabels')->log("Failed to communicate with Shippinglabels");
    		return false;
    	}
    
    	return true;
    
    }    
    
    /**
     * Get website setup
     *
     * @return array
     */
    
    protected function getWebsiteData()
    {
    	
		$stores = Mage::getModel('core/store')->getCollection()->getData();
		return array(Mage::getModel('core/website')->getCollection()->getData(), $stores);
	
    }
    
    /**
     * Get core shipping method data
     *
     * @return array
     */
    
    public function getShippingMethodData()
    {
    	 
		$carriers = Mage::getSingleton('shipping/config')->getActiveCarriers();
		$shipping_methods = array();
		
		foreach ($carriers as $shipping_code => $shipping_model)
		{

			$shipping_title = Mage::getStoreConfig('carriers/'.$shipping_code.'/title');
			$shipping_methods[] = array(
				'code' => $shipping_code,
				'title' => $shipping_title,
			);

		}
		
		return $shipping_methods;
    
    }

    /**
     * Get statuses
     *
     * @return array
     */
    
    protected function getStatusData()
    {
    
    	return Mage::getModel('sales/order_status')->getCollection()->getData();
    
    }
    
    /**
     * Get Shippinglabels API User information
     *
     * @return array
     */
    
    protected function getApiUser()
    {

    	return Mage::getStoreConfig('shippinglabels/default/apiusername');
    
    }    

	 /**
     * Get URL
     *
     * @return string
     */
	 
	protected function getUrl()
	{
		return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
	}

}