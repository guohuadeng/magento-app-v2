<?php

class Sunpop_CustomerSurvey_Block_Adminhtml_Survey_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
  
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
                                        'id' => 'edit_form',
                                        'action' => $this->getUrl('*/*/save', array('ID' => $this->getRequest()->getParam('ID'))),
                                        'method' => 'post',
                                     )
        );
 
        $form->setUseContainer(true);
        $this->setForm($form);
		
        return parent::_prepareForm();
    }
	
}