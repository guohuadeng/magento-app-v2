<?php
class Sunpop_CustomerSurvey_Block_Complete extends Mage_Core_Block_Template
{
	protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('customersurvey/complete.phtml');
    }
	
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->setTitle($this->__('Customer Survey'));
        }
    }
	
	public function getCurrentSurvey()     
    { 
		$customersurveyId = $this->getRequest()->getParam('id');

		return Mage::getModel('customersurvey/survey')->load($customersurveyId);	
    }
}