<?php

class Sunpop_CustomerSurvey_Block_Adminhtml_Survey_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();
		$this->_objectId = 'customersurvey_id';
		$this->_blockGroup = 'customersurvey';
		$this->_controller = 'adminhtml_survey';
		$this->_updateButton('save', 'label', Mage::helper('CustomerSurvey')->__('Save Survey'));
		$this->_updateButton('delete', 'label', Mage::helper('CustomerSurvey')->__('Delete Survey'));
	}
		
	public function getHeaderText()
	{
		if( Mage::registry('customersurvey_data') && Mage::registry('customersurvey_data')->getId() ) {
			return Mage::helper('CustomerSurvey')->__("Editing Survey");
		} else {
			return Mage::helper('CustomerSurvey')->__('Add Survey');
		}
	}
}