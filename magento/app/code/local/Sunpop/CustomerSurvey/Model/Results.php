<?php

class Sunpop_CustomerSurvey_Model_Results extends Mage_Core_Model_Abstract
{

   public function _construct()
    {
        parent::_construct();
		$this->_init('customersurvey/results', 'result_id');
    }
	
}