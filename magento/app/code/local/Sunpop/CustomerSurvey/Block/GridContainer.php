<?php
class Sunpop_Customersurvey_Block_GridContainer extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_blockGroup = 'customersurvey';
    $this->_headerText = Mage::helper('CustomerSurvey')->__('Survey Manager');
    $this->_addButtonLabel = Mage::helper('CustomerSurvey')->__('Add Survey');
    parent::__construct();
  }
}