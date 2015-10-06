<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
* @package Amasty_Customerattr
*/ 
class Amasty_Customerattr_Model_Details extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('amcustomerattr/details');
    }
    
    public function usedInRelation($attributeId)
    {
        $collection = $this->getCollection();
        $collection->getSelect()
            ->where('main_table.attribute_id = ?', $attributeId)
            ->orWhere('main_table.dependent_attribute_id = ?', $attributeId);
        return $collection;
    }
    
    public function fastDelete($ids)
    {
        $db    = Mage::getSingleton('core/resource')->getConnection('core_write');  
        $table = Mage::getSingleton('core/resource')->getTableName('amcustomerattr/details');        
        $db->delete($table, $db->quoteInto('id IN(?)', $ids));
    }
    
    public function haveDetails($relationId)
    {
        $collection = $this->getCollection();
        $collection->getSelect()
            ->where('main_table.relation_id = ?', $relationId);
        return ($collection->getSize() > 0);
    }
}