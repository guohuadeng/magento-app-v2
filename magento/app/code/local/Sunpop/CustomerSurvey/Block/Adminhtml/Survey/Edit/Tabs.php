<?php

class Sunpop_CustomerSurvey_Block_Adminhtml_Survey_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('customersurvey_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('CustomerSurvey')->__('Survey Information'));
    }
 
    protected function _beforeToHtml()
    {
      $this->addTab('form_section1', array(
            'label'     => Mage::helper('CustomerSurvey')->__('General'),
            'title'     => Mage::helper('CustomerSurvey')->__('General'),
            'content'   => $this->getLayout()->createBlock('customersurvey/adminhtml_survey_edit_tab_general')->toHtml(),
        ));
		
		$this->addTab('form_section2', array(
            'label'     => Mage::helper('CustomerSurvey')->__('Questions'),
            'title'     => Mage::helper('CustomerSurvey')->__('Questions'),
            'content'   => $this->getLayout()->createBlock('customersurvey/adminhtml_survey_edit_tab_questions')->toHtml(),
        ));
       
       return parent::_beforeToHtml();
    }
	
}