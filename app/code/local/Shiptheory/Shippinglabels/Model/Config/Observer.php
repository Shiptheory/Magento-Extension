<?php
/**
 * @category   Shiptheory
 * @package    Shiptheory_Shippinglabels
 */
class Shiptheory_Shippinglabels_Model_Config_Observer
{

	public function configure($observer)
	{
	
	    if(Mage::registry('shippinglabels_config_save_after_executed')){
			return;
		}
		
		Mage::register('shippinglabels_config_save_after_executed', true);
		
		if(!Mage::getStoreConfig('shippinglabels/misc/apikey')){
			return Mage::getSingleton('core/session')->addError('You must provide a Shiptheory API Key. <a href="#" target="_blank">How do I get an API Key</a>');
		}	
			
		//check/create api user and role
		$id = Mage::getStoreConfig('shippinglabels/default/apiuserid');
		$api_user = Mage::getModel('api/user')->load($id);
		
		if(!$api_user->getData()){

			//utilise customer Model to generate a random api key
			$api_key = Mage::getModel('customer/customer')->generatePassword();
			
			$api_user = Mage::getModel('api/user')
			->setData(array(
					'username' => Mage::getStoreConfig('shippinglabels/default/apiusername'),
					'firstname' => Mage::getStoreConfig('shippinglabels/default/apiname'),
					'lastname' => Mage::getStoreConfig('shippinglabels/default/apiname'),
					'email' => Mage::getStoreConfig('shippinglabels/default/apiemail'),
					'api_key' => $api_key,
					'api_key_confirmation' => $api_key,
					'is_active' => 1,
			));
			
			//create role
			$role = Mage::getModel('api/roles')
				->setName('Shiptheory')
				->setPid(false)
				->setRoleType('G')
				->save();
			
			//assign resource
			Mage::getModel("api/rules")
				->setRoleId($role->getId())
				->setResources(array('all'))
				->saveRel();
			
			try{
				
				$api_user->save();
				$api_user->setRoleIds(array($role->getId()))
					->setRoleUserId($api_user->getUserId())
					->saveRelations();
			
			}catch (Exception $e) {
				Mage::helper('shippinglabels')->log($e);
			}
		
			//load core config data
			$config_data = Mage::getModel('shippinglabels/config')->load($api_key);		
			
			try{
					
				$config_post = Mage::getModel("shippinglabels/config")->create($config_data);
			
			}catch (Exception $e) {
				Mage::helper('shippinglabels')->log("Failed transmit setup data: ".$e);
			}
			
			if($config_post && $api_user->getId()){
			
				$auth_test = true;	
				//Update extension config to store the Shiptheory api user id
				$config = new Mage_Core_Model_Config();
				$config->saveConfig('shippinglabels/default/apiuserid', $api_user->getId(), 'default', 0);		
			
			}else{
					
				//display failed message and delete API user
				Mage::getSingleton('core/session')->addError("Unable to communicate with Shiptheory. Please try again.<br />If the problem persists, please <a href=\"http://support.shiptheory.com/\" target=\"_blank\">contact support</a>");
				$api_user->delete();
			
			}
		
		}

	    if(isset($auth_test) && Mage::getStoreConfig('shippinglabels/default/status')!='approved'){
	    	
	    	//update extension status
	    	Mage::helper('shippinglabels')->status('pending');
	    	
			Mage::getSingleton('core/session')->getMessages(true);
	    	Mage::getSingleton('core/session')->addSuccess("Success, everything is now connected. Check out your Shiptheory account to <a href=\"https://shiptheory.com/magento/edit\" target=\"_blank\">setup some shipping rules</a>.");
	    }
		
	}
}
