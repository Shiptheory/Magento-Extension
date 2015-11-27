<?php
/**
 * @category   Shiptheory
 * @package    Shiptheory_Shippinglabels
 */
class Shiptheory_Shippinglabels_Model_History extends Mage_Core_Model_Abstract 
{
	
    protected function _construct()
    {
		$this->_init('shippinglabels/history');
    }
    
    /**
     * Retries POST request from queue to Shippinglabels
     *
     * @param int $type
     * @param int $id
     * @param mixed $response
     **/
    
    public function reprocess($type, $id = null){
    	
    	$response = false;

    	//TODO store type ids in the config or as constants
    	if($type == Mage::getStoreConfig('shippinglabels/constants/queue_type/order')){
    			
    		//get Order
    		$_shippinglabelsModel = 'order';
    		$post_data = Mage::getModel("sales/order")->load($id);
    			
    		if(!$post_data->getId()){
    			Mage::helper('shippinglabels')->log('Unable to load order '.$id);
    		}

    	}elseif($type == Mage::getStoreConfig('shippinglabels/constants/queue_type/product')){
    			
    		//get Product
    		$_shippinglabelsModel = 'product';
    		$product = Mage::getModel("catalog/product")->load($id);
    	
    		if(!$product->getId()){
    			Mage::helper('shippinglabels')->log('Unable to load product '.$id);
    		}
    	
    		$post_data = array(
    				'sku'         => $product->getSKU(),
    				/*'oldsku'      => $this->_previousSku,*/
    				'website_ids' => $product->getWebsiteIds()
    		);

    	}elseif($type == Mage::getStoreConfig('shippinglabels/constants/queue_type/config')){

    		//get Configuration
    		$_shippinglabelsModel = 'config';
    		$post_data = Mage::getModel('shippinglabels/config')->load();

    	}else{
            Mage::helper('shippinglabels')->log("Unknown Message Queue type.");
        }
    	
    	//we have the payload, rePOST it to Shippinglabels
    	try{
    	
    		$response = Mage::getModel("shippinglabels/{$_shippinglabelsModel}")->create($post_data);
    			
    	}catch (Exception $e){
    		Mage::helper('shippinglabels')->log('Failed to repost payload to Shippinglabels'.$e);
    	}
    	
    	return $response;
    	 
    }  

	
    /**
     * Checks for local queue data
     *
     **/
    
    public function loadArchive($id = null){
				
		$archive = Mage::getModel('shippinglabels/history');
		$archive->load($id, 'internal_id');
			
		if(!$archive->getId()){
			return false;
		}
			
		return $archive->getId();	
	
	}
    
}