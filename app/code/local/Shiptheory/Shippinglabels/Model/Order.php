<?php
/**
 * @category   Shiptheory
 * @package    Shiptheory_Shippinglabels
 */
class Shiptheory_Shippinglabels_Model_Order extends Mage_Core_Model_Abstract 
{
	
    protected function _construct()
    {
		$this->_init('shippinglabels/order');
    }

    /**
     * Create a POST request
     *
     * @param object $order
     **/    
    
    public function create($order){
    	
		try{
			
			$response = Mage::getModel("shippinglabels/webservice")->order($order);

		}catch (Exception $e) {
			Mage::helper('shippinglabels')->log("Failed calling Webservice: ".$e);
		}
		
		if($response){
            if(!($response->getStatus() >= 200 && $response->getStatus() <= 204)){
                Mage::helper('shippinglabels')->log("Failed to communicate with Shiptheory.com. ".$response->getMessage());
                return false;
            }
        }else{
            Mage::helper('shippinglabels')->log("Failed to communicate with Shiptheory.com. ");
            return false;
        }
		
		return true;
		
	}
			
}
