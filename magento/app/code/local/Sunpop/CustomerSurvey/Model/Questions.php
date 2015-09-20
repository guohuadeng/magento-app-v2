<?php

class Sunpop_CustomerSurvey_Model_Questions extends Mage_Core_Model_Abstract
{

   public function _construct()
    {
        parent::_construct();
		$this->_init('customersurvey/questions', 'question_id');
    }
	
}