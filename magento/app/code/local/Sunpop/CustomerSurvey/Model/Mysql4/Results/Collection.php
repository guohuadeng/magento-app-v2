<?php

class Sunpop_CustomerSurvey_Model_Mysql4_Results_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('customersurvey/results');
    }
}