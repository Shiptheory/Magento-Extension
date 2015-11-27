<?php
/**
 * @category   Shiptheory
 * @package    Shiptheory_Shippinglabels
 */
class Shiptheory_Shippinglabels_Adminhtml_ShippinglabelsController extends Mage_Adminhtml_Controller_Action
{
	
	/**
	 * Shippinglabels Model
	 */
	protected $_shippinglabelsModel = false;
	
	
    public function indexAction()
    {  
    	$this->loadLayout();
    	$this->_setActiveMenu('sales');
		$this->_addContent($this->getLayout()->createBlock('shippinglabels/adminhtml_shippinglabels'));
        $this->renderLayout();
    }  
     
    /**
     * Trigger queue retry
     *
     **/    
    
    public function retryAction()
    {

    	if($this->getRequest()->getParam('id')){
    		
    		//resend 1 queued message
    		$ids = array($this->getRequest()->getParam('id'));
    		
    	}else{
    		
    		//resend entire queue
    		$collection = Mage::getModel('shippinglabels/history')->getCollection();
    		$collection->addFieldToSelect('id');
    		//only retry failed or pending
    		$collection->addFieldToFilter('status', array('in' => array(1,0)));
    		$ids = $collection->getData();
    		$queued = $collection->count();
    		$failed_count = 0;$success_count = 0;
    		
    	}
    	
    	foreach($ids as $id)
    	{
    		
    		try{
    			 
    			$queued_order = Mage::getModel("shippinglabels/history")->load($id);
    			 
    		}catch(Exception $e){
    			Mage::helper('shippinglabels')->log($e);
    		}    		
    		
    		if(!$post_data = $this->sendRetry($queued_order)){
    			$this->_getSession()->addError($this->__("Message not found"));
    			$failed_count++;
    		}else{
    			$success_count++;
    		}
    		
    		unset($queued_order);
    		
    	}		
    
    	if(isset($queued) && $success_count>0){
    		
    		//overwrite Success session messages, replace with counts
    		$this->_getSession()->getMessages(true);
    		$this->_getSession()->addSuccess($this->__($success_count."/".$queued." sent successfully"));

    	}elseif(isset($queued) && $failed_count>0){
    		$this->_getSession()->getMessages(true);
    		$this->_getSession()->addError($this->__($failed_count."/".$queued." failed to send"));
    	}
    	
    	
		return $this->_redirect('*/*/');
    
    }
    
    /**
     * Repost queue retry
     *
     **/    
    
    protected function sendRetry($queued_order){
    	
    	$post_data = false;
    	
    	if(!$queued_order->internalId && $queued_order->getType() != Mage::getStoreConfig('shippinglabels/constants/queue_type/config')){
    		 
    		$this->_getSession()->addError($this->__("Record not found"));
    		return false;
    		
    	}
    	
        $queue_id = $queued_order->getId();
    	if($queued_order->getType() == Mage::getStoreConfig('shippinglabels/constants/queue_type/order')){
    		 
    		//get Order
    		$_shippinglabelsModel = 'order';
    		$post_data = Mage::getModel("sales/order")->load($queued_order->internalId);
    		 
    		if(!$post_data->getId()){
    			$this->_getSession()->addError($this->__("Order ".$queued_order->internalId." not found"));
    			return;
    		}
    	
    	}elseif($queued_order->getType() == Mage::getStoreConfig('shippinglabels/constants/queue_type/product')){
    		 
    		//get Product
    		$_shippinglabelsModel = 'product';  
                
                $product = Mage::getModel("catalog/product")->load($queued_order->internalId);
                // prepare data based on action type
                switch($queued_order->getAction())
                {
                    case 'Add':
                        if(!$product->getId()){                             
                               $this->_getSession()->addError($this->__("Product ".$queued_order->internalId." not found"));
                                return;
                        }
                        $post_data = array(
    				'sku'         => $product->getSKU(),  
                                'message_id'  => Mage::helper('shippinglabels')->getMessageId()
    				//'website_ids' => $product->getWebsiteIds()
                        );
                        break;
                    case 'Update':
                        if(!$product->getId()){
                                
                                $this->_getSession()->addError($this->__("Product ".$queued_order->internalId." not found"));
                                return;
                        }
                        $post_data = array(
    				'sku'         => $product->getSKU(),
    				'oldsku'      => $queued_order->getPreviousReference(),
                                'message_id'  => Mage::helper('shippinglabels')->getMessageId()
    				//'website_ids' => $product->getWebsiteIds()
                        );
                        break;
                    case 'Delete':
                        $post_data = array(
    				'sku'         => $queued_order->getReference(),
                                'message_id'  => Mage::helper('shippinglabels')->getMessageId(),
                                'delete'        => true
    				//'website_ids' => array()
                        );
                        break;
                }
    	
    	}elseif($queued_order->getType() == Mage::getStoreConfig('shippinglabels/constants/queue_type/config')){
    		 
    		//get Configuration
    		$_shippinglabelsModel = 'config';
    		$post_data = Mage::getModel('shippinglabels/config')->load();
                $post_data['message_id'] = Mage::helper('shippinglabels')->getMessageId();
    	
    	}else{
            Mage::helper('shippinglabels')->log("Unknown Message Queue type.");
        }    	
    	
    	//we have the payload, rePOST it to Shippinglabels
    	try{
    	
    		$shippinglabels_order = Mage::getModel("shippinglabels/{$_shippinglabelsModel}")->create($post_data, $queue_id);
    		 
    	}catch (Exception $e){
    		Mage::helper('shippinglabels')->log("Failed to add order to queue: ".$e);
    	}    	
    	
    	//update retry attempts
    	$queued_order->setAttempts(($queued_order->getAttempts()+1))->save();
    	
    	if($shippinglabels_order){
    		 
    		//update the local Shippinglabels order queue status
    		$queued_order->setStatus(Mage::getStoreConfig('shippinglabels/constants/queue_status/complete'))->save();
    		$this->_getSession()->addSuccess($this->__("Transmitted to Shippinglabels"));
    		return $post_data;
    	
    	}
    	
    	//if we are here, something went wrong POSTING to Shippinglabels
    	$this->_getSession()->addError($this->__("Transmission to Shippinglabels Failed"));
    	return false;
    	
    }
	
	/** 
	 * Mass ship orders
	 */
	
    public function shipAction()
    { 

		$order_ids = $this->getRequest()->getParam('order_ids');				
		foreach($order_ids as $order_id){
			$order = Mage::getModel('sales/order')->load($order_id);
			if($order->canShip()){
			
				$item_qty =  $order->getItemsCollection()->count();
				$shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($item_qty);
				$shipment = new Mage_Sales_Model_Order_Shipment_Api();
				
				$success = false;
				try{
					$shipment_id = $shipment->create($order->getIncrementId());
					$success = true;
									
				}catch (Exception $e) {
					$this->_getSession()->addError($this->__("Order #".$order->getIncrementId()." Failed to Ship"));
					Mage::log($e,null,'madcapsule_ordership.log'); 
				}
				
				if($success){
					$this->_getSession()->addSuccess($this->__("Order #".$order->getIncrementId()." Successfully Shipped"));			
				}
				
			}else{
				$this->_getSession()->addError($this->__("Order #".$order->getIncrementId()." is Not Available to Ship. Have you already shipped it?"));
			}
		}
		
		$this->_redirect('adminhtml/sales_order/');
    }	
     

    protected function _initAction()
    {
 
        $this->loadLayout()
            ->_setActiveMenu('sales/shippinglabels')
            ->_title($this->__('Sales'))->_title($this->__('Shiptheory Messages'))
            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))
            ->_addBreadcrumb($this->__('Shiptheory Messages'), $this->__('Shipteory Messages'));
         
        return $this;

    }
     
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/shippinglabels');
    }
}