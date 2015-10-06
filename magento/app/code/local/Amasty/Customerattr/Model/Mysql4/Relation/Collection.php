<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
* @package Amasty_Customerattr
*/ 
class Amasty_Customerattr_Model_Mysql4_Relation_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    /**
     * Define resource model
     *
     */
    protected function _construct()
    {
        $this->_init('amcustomerattr/relation', 'id');
    }
    
    public function addRelations()
    {
    	 $this->getSelect()
    	 		->joinInner(array('v'=> $this->getTable('amcustomerattr/details')), 'main_table.id = v.relation_id', array('v.attribute_id'))
    	 		->joinInner(array('a'=> $this->getTable('eav/attribute')), 'v.attribute_id = a.attribute_id', array('a.frontend_label as parent_label', 'CONCAT(a.attribute_code, ",", GROUP_CONCAT(d.attribute_code)) as attribute_codes'))
                ->joinInner(array('d'=> $this->getTable('eav/attribute')), 'v.dependent_attribute_id = d.attribute_id', array('GROUP_CONCAT(d.frontend_label) as dependent_label'))
                ->group('main_table.id');
         return $this;            
    }
    
    public function getElementsRelation()
    {
    	 $this->getSelect()
    	 		->reset('columns')
    	 		->columns(array('v.option_id'))
    	 		->joinInner(array('v'=> $this->getTable('amcustomerattr/details')), 'main_table.id = v.relation_id', null)
    	 		->joinInner(array('a'=> $this->getTable('eav/attribute')), 'v.attribute_id = a.attribute_id', array('a.attribute_code as parent_code'))
                ->joinInner(array('d'=> $this->getTable('eav/attribute')), 'v.dependent_attribute_id = d.attribute_id', array('d.attribute_code as dependent_code'));
                
         return $this;    
    }
    
}
