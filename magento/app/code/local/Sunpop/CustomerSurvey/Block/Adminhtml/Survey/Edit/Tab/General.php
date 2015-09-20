<?php

class Sunpop_CustomerSurvey_Block_Adminhtml_Survey_Edit_Tab_General extends Mage_Adminhtml_Block_Widget
{
	public function __construct()
    {
        parent::__construct();
        $this->setTemplate('customersurvey/general.phtml');
    }
	
	public function isReadOnly() {
		return false;	
	}
	
	public function getSurvey() {
		$customersurveyId = $this->getRequest()->getParam('id');

		$survey  = Mage::getModel('customersurvey/survey')->load($customersurveyId);
		
		return $survey;	
	}
	
	public function getSurveyID() {
		$customersurveyId = $this->getRequest()->getParam('id');

		return $customersurveyId;	
	}
	
}