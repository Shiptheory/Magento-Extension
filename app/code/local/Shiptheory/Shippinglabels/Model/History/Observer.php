<?php
/**
 * @category   Shiptheory
 * @package    Shiptheory_Shippinglabels
 */
class Shiptheory_Shippinglabels_Model_Queue_Observer
{
	
	/**
	 * Retries pending and failed queued items
	 *
	 **/
	
	public function reprocess()
	{
		
		Mage::helper('shippinglabels')->log("Reprocessing queue running");
		
		// find all pending and failed queued jobs where retry count < limit
		$queue = Mage::getModel("shippinglabels/queue")->getCollection();
		$queue->addFieldToFilter('status', array('in' => array(1,0)));
		$queue->addFieldToFilter('attempts', array('lteq' => Mage::getStoreConfig('shippinglabels/default/retrylimit')));
		
		foreach($queue as $job)
		{
			
			Mage::helper('shippinglabels')->log("Reprocessing Job id: ".$job->getID());
			$shippinglabels_post = Mage::getModel('shippinglabels/queue')->reprocess($job->getTypeId(), $job->getInternalId());
			Mage::helper('shippinglabels')->log("Result: ".$shippinglabels_post);
			
			$queued_job = Mage::getModel("shippinglabels/queue")->load($id);
			$queued_job->setAttempts(($queued_job->getAttempts()+1))->save();
			$queued_job->setStatus(Mage::getStoreConfig('shippinglabels/constants/queue_status/complete'))->save();
			
		}
		
		

	}
	
	/**
	 * Sends daily product digest to Shippinglabels
	 *
	 **/	
	
	public function digest()
	{
		
		$product_data = array();
		
		$products = Mage::getModel('catalog/product')->getCollection()
		->addAttributeToSelect('*');
		
		foreach($products as $product) {
		
			$core_product_data = array();
			$tier_product_data = array();
			$group_product_data = array();
			
			$product_detail = Mage::getModel('catalog/product')->load($product->getId());
		
			
			//set core product attributes
			$core_product_data = array(
					'product_id'  => $product->getId(),
					'sku'         => $product->getSku(),
					'website_ids' => $product->getWebsiteIds(),
					'qty' => $product_detail->getStockItem()->getQty(),
					'price' => $product->getPrice(),
					'special_price' => $product->getSpecialPrice()
						
			);
		
			//get tier prices
			if($product_detail->getTierPrice()){
		
				foreach($product_detail->getTierPrice() as $tier){
		
					$tier_product_data[] = array(
							'customer_group_id' => $tier['cust_group'],
							'qty' => $tier['price_qty'],
							'price' => $tier['price']
					);
						
				}
		
			}
		
			//get group prices
			if(is_array($product_detail->getGroupPrice())){
		
				foreach($product_detail->getGroupPrice() as $group){
		
					$group_product_data[] = array(
							'customer_group_id' => $group['cust_group'],
							'price' => $group['price']
					);
		
				}
		
			}
		
			$product_data[] = $core_product_data + array('tier_price' => $tier_product_data) + array('group_price' => $group_product_data);		
		
		}

		try{

			$queue = Mage::getModel("shippinglabels/webservice")->digest(json_decode($product_data));
		
		}catch (Exception $e) {
			Mage::helper('shippinglabels')->log("Failed to call Webservice: ".$e);
		}	
		
	}
	
}