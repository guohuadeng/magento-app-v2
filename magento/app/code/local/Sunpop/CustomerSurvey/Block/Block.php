<?php
class Sunpop_CustomerSurvey_Block_Block extends Mage_Core_Block_Template
{
	protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('customersurvey/block.phtml');
    }
	
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->setTitle($this->__('List Customer Surveys'));
        }
    }
	
	public function getSurveys()     
	{ 
		return  Mage::getModel('customersurvey/survey')->getCollection();
	}
	
	
}