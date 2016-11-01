<?php

/**
 * @category   Shiptheory
 * @package    Shiptheory_Shippinglabels
 */
class Shiptheory_Shippinglabels_Model_History extends Mage_Core_Model_Abstract {

    protected function _construct() {
        $this->_init('shippinglabels/history');
    }

    /**
     * Checks for local queue data
     *
     * */
    public function loadArchive($id = null) {

        $archive = Mage::getModel('shippinglabels/history');
        $archive->load($id, 'internal_id');

        if (!$archive->getId()) {
            return false;
        }

        return $archive->getId();
    }

}
