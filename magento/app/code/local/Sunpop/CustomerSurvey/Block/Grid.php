<?php

class Sunpop_CustomerSurvey_Block_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('customersurveyGrid');
      $this->setDefaultSort('id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('customersurvey/survey')->getCollection();	  
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {

      $this->addColumn('customersurvey_id', array(
          'header'    => Mage::helper('CustomerSurvey')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'customersurvey_id',
      ));
	  
      $this->addColumn('title', array(
          'header'    => Mage::helper('CustomerSurvey')->__('Title'),
          'align'     =>'left',
          'index'     => 'title',
      ));

      $this->addColumn('enabled', array(
          'header'    => Mage::helper('CustomerSurvey')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'enabled',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              0 => 'Disabled',
          ),
      ));
	  
      $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('CustomerSurvey')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('CustomerSurvey')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    ),
					array(
                        'caption'   => Mage::helper('CustomerSurvey')->__('Results'),
                        'url'       => array('base'=> '*/*/results'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
	
      return parent::_prepareColumns();
  }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}