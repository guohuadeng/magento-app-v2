<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
* @package Amasty_Customerattr
*/ 
class Amasty_Customerattr_Model_Relation extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('amcustomerattr/relation');
        
        $this->tableName = Mage::getSingleton('core/resource')->getTableName('amcustomerattr/relation');        
    }
    
    /**
     * Enter description here ...
     * @param unknown_type $attributeId
     * @param unknown_type $asCollection
     * @return Ambiguous|multitype:
     */
    public function getAttributeValues($attributeId)
    {
		$model = Mage::getModel('catalog/entity_attribute');
		$model->load($attributeId);
		
		$options = $model->getSource()->getAllOptions(true, true);
		return $options;
    }
    
    public function getElementsRelation()
    {
    	 $relations = Mage::getModel('amcustomerattr/relation')->getResourceCollection()
        	->getElementsRelation();
        return  $relations;
        	
    }
    
    /**
     * Return attributes as collection
     * @param bool $asCollection
     * @param bool $listOnly
     * @return mixed
     */
    public function getUserDefinedAttributes($asCollection = false, $listOnly = true)
    {
    	$collection = Mage::getModel('customer/attribute')->getCollection();
        $alias = Mage::helper('amcustomerattr')->getProperAlias($collection->getSelect()->getPart('from'), 'eav_attribute');
        $collection->getSelect()
            ->where($alias . 'is_user_defined = ?', 1)
            ->where($alias . 'attribute_code != ?', 'customer_activated');
            
        if ($listOnly) {
           $collection->getSelect()->where($alias . 'frontend_input in (?)', array('multiselect', 'select'));
        }

        if ($asCollection) {
        	return $collection;
        }
        
		$attributes = array();        
        if ($collection) {
        	foreach ($collection as $attribute) {
        		$label = $attribute->getFrontendLabel();
        		if (!$attribute->getIsVisibleOnFront()) {
        			$label .= ' - ' . Mage::helper('amcustomerattr')->__('Not Visible');
        		}
        		$attributes[] = array(
        			'value' => $attribute->getAttributeId(),
        			'label' => $label
        		);
        	}	
        }
        return $attributes;
    }
    
    public function fastDelete($ids)
    {
        $db    = Mage::getSingleton('core/resource')->getConnection('core_write');  
        $table = Mage::getSingleton('core/resource')->getTableName('amcustomerattr/relation');        
        $db->delete($table, $db->quoteInto('id IN(?)', $ids));
    }
}