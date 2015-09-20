<?php

class Sunpop_CustomerSurvey_Model_Mysql4_Questions_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('customersurvey/questions');
    }
	
	public function addAttributeToSort($attribute, $dir='asc')
    {
        if (!is_string($attribute)) {
            return $this;
        }
        $this->setOrder($attribute, $dir);
        return $this;
    }
}