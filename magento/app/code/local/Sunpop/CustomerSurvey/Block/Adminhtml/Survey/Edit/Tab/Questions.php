<?php

class Sunpop_CustomerSurvey_Block_Adminhtml_Survey_Edit_Tab_Questions extends Mage_Adminhtml_Block_Widget
{
	
	public function __construct()
    {
        parent::__construct();
        $this->setTemplate('customersurvey/questions.phtml');
    }
	
	public function isReadOnly() {
		return false;	
	}
	
	
	protected function _prepareLayout()
    {
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Delete Option'),
                    'class' => 'delete delete-product-option '
                ))
        );

        return parent::_prepareLayout();
    }
	
	
    public function getAddButtonId()
    {
        $buttonId = $this->getLayout()
                ->getBlock('admin.product.options')
                ->getChild('add_button')->getId();
        return $buttonId;
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }
	
	public function getMyQuestions() {
		$customersurveyId = $this->getRequest()->getParam('id');
		
		if($customersurveyId) {
			$questions  = Mage::getModel('customersurvey/questions')->getCollection()->addFieldToFilter('customersurvey_id', $customersurveyId);
			$questions = $questions->addAttributeToSort('sort_order', 'ASC');
		}
		else
		{
			$questions = array();	
		}
		
		
		return $questions;
		
	}
	
}