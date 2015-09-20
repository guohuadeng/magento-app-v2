<?php

class Sunpop_CustomerSurvey_Model_Mysql4_Questions extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('customersurvey/questions', 'question_id');
    }
	
}