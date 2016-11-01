<?php

/**
 * @category   Shiptheory
 * @package    Shiptheory_Shippinglabels
 */
class Shiptheory_Shippinglabels_Model_Order_Observer {

    protected $_Id = null;
    protected $_failMsg = 'Failed to send to Shiptheory';
    protected $_successMsg = 'Sent to Shiptheory successfully';
    protected $_failStatus = 'failed';
    protected $_successStatus = 'success';

    /**
     * Listens to shipment save event
     *
     * @param object $observer
     * */
    public function shipment($observer) {
        $order = Mage::getModel('sales/order')->load($observer->getEvent()->getShipment()->getOrderId());
        $data = array(
            'order_id' => $observer->getEvent()->getShipment()->getOrderId(),
            'shipment_id' => $observer->getEvent()->getShipment()->getIncrementId()
        );

        return $this->queue($order, $data);
    }

    /**
     * Queues and POSTS Shippinglabels order
     *
     * @param object $order
     * @param array $data
     * */
    protected function queue($order, $data) {

        /* if(Mage::registry('sales_order_save_commit_after_executed')){
          return;
          }

          Mage::register('sales_order_save_commit_after_executed', true); */

        if (!Mage::getStoreConfig('shippinglabels/misc/enabled')) {
            return;
        }

        $order_id = $order->getId();
        
        if(empty($data['shipment_id'])){
            Mage::helper('shippinglabels')->log("Failed to find Shipment ID for Order " .$order_id);
            return;
        }
        
        $shipment_id = $data['shipment_id'];
        
        $this->_Id = Mage::getModel('shippinglabels/history')->loadArchive($shipment_id);

        //save the new order to the local Shiptheory queue
        $pending_order = Mage::getModel('shippinglabels/history');
        if ($this->_Id) {
            $pending_order->setId($this->_Id);
            return; //overwrites the above line, will be used for retry in a future version
        }

        try {

            $pending_order
                ->setReference($order->getIncrementId())
                ->setShipmentId($shipment_id)
                ->save();
        } catch (Exception $e) {
            Mage::helper('shippinglabels')->log($e);
        }

        try {

            $shippinglabels_order = Mage::getModel("shippinglabels/order")->create($data);
        } catch (Exception $e) {
            Mage::helper('shippinglabels')->log("Failed to create queued order: " . $e);
        }

        if (isset($shippinglabels_order)) {

            //update the local Shiptheory order queue status to complete
            $pending_order
                    ->setStatus($this->_successStatus)
                    ->setMessage($this->_successMsg)
                    ->save();
        } else {

            //update the local Shiptheory order queue status to failed
            $pending_order
                    ->setStatus($this->_failStatus)
                    ->setMessage($this->_failMsg)
                    ->save();
        }
    }

}
