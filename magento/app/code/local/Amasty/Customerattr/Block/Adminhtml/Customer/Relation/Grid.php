<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Block_Adminhtml_Customer_Relation_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('relationGrid');
        $this->setDefaultSort('attribute_id');
        $this->setDefaultDir('ASC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('amcustomerattr/relation')->getResourceCollection()
        	->addRelations();
        $this->setCollection($collection);
        
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header'   => Mage::helper('amcustomerattr')->__('Relation Name'),
            'sortable' => false,
            'index'    => 'name',
        ));

        $this->addColumn('parent_label', array(
            'header'   => Mage::helper('amcustomerattr')->__('Parent Attribute'),
            'sortable' => false,
            'index'    => 'parent_label',
        ));
        
        $this->addColumn('dependent_label', array(
            'header'   => Mage::helper('amcustomerattr')->__('Dependent Attributes'),
            'sortable' => false,
            'index'    => 'dependent_label',
            'renderer' => 'amcustomerattr/adminhtml_customer_relation_grid_renderer_label',
        ));    
        
        $this->addColumn('attribute_codes', array(
            'header'   => Mage::helper('amcustomerattr')->__('Attribute Codes'),
            'sortable' => false,
            'index'    => 'attribute_codes',
            'renderer' => 'amcustomerattr/adminhtml_customer_relation_grid_renderer_code',
        ));
        
        $this->addColumn('action', 
            array(
            	'header'  => Mage::helper('amcustomerattr')->__('Action'), 
            	'width'   => '100', 
                'type'    => 'action', 
                'getter'  => 'getId', 
                'actions' => array(
                    array(
                    	'caption' => Mage::helper('amcustomerattr')->__('Edit'), 
        				'url' => array('base' => '*/*/edit'), 
        				'field' => 'id'
                    )
                ), 
    			'filter'    => false, 
    			'sortable'  => false, 
    			'index'     => 'stores', 
    			'is_system' => true,
            )
        ); 

        return parent::_prepareColumns();
    }
    
	protected function _prepareMassaction ()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('relation');
        
        $this->getMassactionBlock()->addItem('delete', 
            array(
            	'label'   => Mage::helper('amcustomerattr')->__('Delete'), 
        		'url'     => $this->getUrl('*/*/massDelete'), 
            	'confirm' => Mage::helper('amcustomerattr')->__('Are you sure?')));
            
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}