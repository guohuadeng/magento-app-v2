<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2012 Amasty (http://www.amasty.com)
 */
class Amasty_Customerattr_Model_Mysql4_Details_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    /**
     * Define resource model
     *
     */
    protected function _construct()
    {
        $this->_init('amcustomerattr/details', 'id');
    }
    
    public function getByRelation($relationId)
    {
    	 $this->getSelect()
    	 	->where('relation_id = ?', $relationId);
    	 return $this;
    }
    
    
}
