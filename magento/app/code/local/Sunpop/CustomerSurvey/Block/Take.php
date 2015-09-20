<?php
class Sunpop_CustomerSurvey_Block_Take extends Mage_Core_Block_Template
{
	var $survey_id;
	
	protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('customersurvey/take.phtml');
    }
	
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->setTitle($this->__('Customer Survey'));
        }
    }
	
	public function setDataByKey($key, $value) {
		if($key == "survey_id") {
			$this->survey_id = $value;
		}
	}
	
	public function getCurrentSurvey()     
   { 
		if(!$this->survey_id) {
			$this->survey_id = $this->getRequest()->getParam('id');
		}
		
		if(!$this->survey_id) {
			$this->survey_id = $this->getID();
		} 
		
		return Mage::getModel('customersurvey/survey')->load($this->survey_id);      
   }
	
	public function getMyQuestions() {
		if($this->survey_id) {
			$questions  = Mage::getModel('customersurvey/questions')->getCollection()->addFieldToFilter('customersurvey_id', $this->survey_id);
			$questions = $questions->addAttributeToSort('sort_order', 'ASC');
		}
		else
		{
			$questions = array();	
		}

		return $questions;
	}
	
	public function getSaveUrl() {
		return $this->getUrl('customersurvey/index/save', '');	
	}
	
	protected function _toHtml()
	{
		$this->survey_id = $this->getID();
		$this->_construct();
		$this->survey_id = $this->getID();
		$this->_prepareLayout();

		return parent::_toHtml();
	}
	
}