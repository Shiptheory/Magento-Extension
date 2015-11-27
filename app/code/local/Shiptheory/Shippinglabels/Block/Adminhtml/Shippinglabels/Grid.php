<?php
/**
 * @category   Shiptheory
 * @package    Shiptheory_Shippinglabels
 */
class Shiptheory_Shippinglabels_Block_Adminhtml_Shippinglabels_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('shippinglabels_queue_grid');
        $this->setDefaultSort('created');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }
 
    protected function _prepareCollection()
    {

    	$this->setDefaultFilter(array('status' => array("1","0")));
        $collection = Mage::getModel('shippinglabels/history')->getCollection();
		$collection->addFieldToFilter('status', array('in' => array(1,2,0)));
        $this->setCollection($collection);
        return parent::_prepareCollection();

    }   
 
    protected function _prepareColumns()
    {  	
        
        $this->addColumn('reference', array(
        
        		'header'    => Mage::helper('shippinglabels')->__('Id'),
        		'align'     =>'right',
        		'width'     => '100px',
        		'index'     => 'reference'
        ));               
        
        $this->addColumn('status', array(
        
        		'header'    => Mage::helper('shippinglabels')->__('Status'),
        		'align'     => 'left',
        		'width'     => '150px',
        		'index'     => 'status',
        		'type'      => 'options',
        		'options'   => array(
        				'failed' => 'Failed',
        				'success' => 'Complete',
        				'pending' => 'Pending'
        		),
        		'filter_condition_callback' => array($this, '_filterStatusCondition')
        ));       
        
        $this->addColumn('attempts', array(
        		
			'header'    => Mage::helper('shippinglabels')->__('# Retries'),
			'align'     =>'right',
			'width'     => '10px',
  			'index'     => 'attempts'
        ));        
        
        $this->addColumn('message', array(
        		
			'header'    => Mage::helper('shippinglabels')->__('Message'),
			'align'     =>'right',
			'width'     => '300px',
			'index'     => 'message',
        ));              
		
        $this->addColumn('created', array(
            'header'    => Mage::helper('shippinglabels')->__('Created'),
            'align'     => 'left',
            'width'     => '120px',
            'type'      => 'datetime',
            'default'   => '--',
            'index'     => 'created',
        ));	      

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('shippinglabels')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(

                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'shippinglabels',
                'is_system' => true,
        ));		
	   
 
 
        return parent::_prepareColumns();
    }
 
    public function getRowUrl($row)
    {
        
    }
    
    protected function _filterStatusCondition($collection, $column)
    {
    	
    	if(!$status = $column->getFilter()->getValue()){
    		return;
    	}
    
    	if(is_array($status)){
    		
    		$this->getCollection()->addFieldToFilter('status', array('in' => $status));

    	} else {
    		
    		$this->getCollection()->addFieldToFilter('status', $status);

    	}
    }

 
}