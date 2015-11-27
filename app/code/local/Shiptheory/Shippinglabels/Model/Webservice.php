<?php
/**
 * @category   Shiptheory
 * @package    Shiptheory_Shippinglabels
 */
class Shiptheory_Shippinglabels_Model_Webservice extends Mage_Core_Model_Abstract 
{		

    protected function _construct()
    {

        $this->_init('shippinglabels/token');
    }
	
	/**
	 * Prepares Order POST
	 *
	 * @param array
	 **/
	
	public function order($data)
	{
	
		$order_endpoint = Mage::getStoreConfig('shippinglabels/endpoint/order');
		return $this->get($data, $order_endpoint);
	
	}	
	
	/**
	 * Prepares Configuration POST
	 *
	 * @return array
	 **/
	
	public function config($data)
	{
	
		$config_endpoint = Mage::getStoreConfig('shippinglabels/endpoint/configuration');
		return $this->post($data, $config_endpoint);
	
	}
	
	/**
	 * GET Shiptheory
	 *
	 * @param array
	 * @param string endpoint
	 **/
	
	protected function get($data, $endpoint)
	{
		
		$extension_version = (string)Mage::helper('shippinglabels')->getExtensionVersion();
		$endpoint = Mage::getStoreConfig('shippinglabels/endpoint/order');
		
		try{
			$client = new Zend_Http_Client($endpoint);		
		
		}catch(Zend_Http_Client_Exception $e){
			Mage::helper('shippinglabels')->log("Unable to create Zend_Http_Client: ".$e);
			return false;
		}
				
		$ident = Mage::getStoreConfig('shippinglabels/misc/apikey');
		$client->setHeaders(array(
				'Shiptheory-ident' => $ident,
				'X-Powered-By' => 'Shiptheory Magento Extension v'.$extension_version
			)
		);

		$client->setParameterGet(array(
				'channel' => substr($ident, 0, -4)."/Magento/".$this->formatUrl()."/".$data['shipment_id']."/order/event"
		));

		$data['timestamp'] = $this->getTimestamp();
		$json = $this->formatJson($data);
                
		$client->setRawData($json, 'application/json');
        Mage::helper('shippinglabels')->log("Sending Json ".$json);
		$response = $client->request(Zend_Http_Client::GET);
                
		return $response;	
		
	}	
	
	/**
	 * POST Shiptheory
	 *
	 * @param array
	 * @param string endpoint
	 **/
	
	protected function post($data, $endpoint)
	{
		
		$extension_version = (string)Mage::helper('shippinglabels')->getExtensionVersion();
		$endpoint = Mage::getStoreConfig('shippinglabels/endpoint/setup');
		
		try{
			$client = new Zend_Http_Client($endpoint);		
		
		}catch(Zend_Http_Client_Exception $e){
			Mage::helper('shippinglabels')->log("Unable to create Zend_Http_Client: ".$e);
			return false;
		}
				
		$client->setHeaders(array(
				'Shiptheory-ident' => Mage::getStoreConfig('shippinglabels/misc/apikey'),
				'X-Powered-By' => 'Shiptheory Magento Extension v'.$extension_version
			)
		);
		
		$data['timestamp'] = $this->getTimestamp();
		$json = $this->formatJson($data);
		$client->setParameterPost('magento_data', $json);
		$response = $client->request(Zend_Http_Client::POST);
		return true;
	}	
	
	/**
	 * Formats JSON for POST
	 *
	 * @param array
	 * @return string json
	 **/
	
	protected function formatJson($data)
	{
		return str_replace("\u0000*\u0000_", "", Mage::helper('core')->jsonEncode((array) $data ));
	}

	/**
	 * Returned formatted base url
	 *
	 **/
	
	protected function formatUrl()
	{
		$url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
		$url = str_replace("http://", "", $url);
		$parts = explode("/", $url);
		return $parts[0];
	}		
	
	/**
	 * Timestamp
	 *
	 **/	
	
	protected function getTimestamp()
	{
		$timezone = new DateTime('now', new DateTimeZone(Mage::getStoreConfig('general/locale/timezone')));
		return $currentDate = date('Y-m-d\\TH:i:s', Mage::getModel('core/date')->timestamp(time())).$timezone->format('P');
	}
				
}
