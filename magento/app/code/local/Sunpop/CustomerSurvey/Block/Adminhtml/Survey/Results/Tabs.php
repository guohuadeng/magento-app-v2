<?php

class Sunpop_CustomerSurvey_Block_Adminhtml_Survey_Results_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('customersurvey_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('CustomerSurvey')->__('Survey Results'));
    }
 
    protected function _beforeToHtml()
    {
		
      $this->addTab('form_section1', array(
            'label'     => Mage::helper('CustomerSurvey')->__('General'),
            'title'     => Mage::helper('CustomerSurvey')->__('General'),
            'content'   => $this->getLayout()->createBlock('customersurvey/adminhtml_survey_results_tab_general')->toHtml(),
        ));
		
		$this->addTab('form_section2', array(
            'label'     => Mage::helper('CustomerSurvey')->__('Graphical'),
            'title'     => Mage::helper('CustomerSurvey')->__('Graphical'),
            'content'   => $this->getLayout()->createBlock('customersurvey/adminhtml_survey_results_tab_graphical')->toHtml(),
        ));
       
       return parent::_beforeToHtml();
    }
	
}