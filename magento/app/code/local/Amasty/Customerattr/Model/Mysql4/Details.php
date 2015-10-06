<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2012 Amasty (http://www.amasty.com)
 */
class Amasty_Customerattr_Model_Mysql4_Details extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
    {
        $this->_init('amcustomerattr/details', 'id');
    }
    
	public function saveDetails($data) 
    {
    	/*
    	 *  $details = array(
            	'relation_id' => $id,
            	'attribute_id' => $data['attribute_id'],
            	'option_id' => $data['option_id'],
            	'dependend_attribute_id' => $data['dependend_attribute_id']
            );
    	 */
    	
    	$relation_id = $data['relation_id'];
    	$attribute_ids = $data['attribute_id'];
    	$option_ids = $data['option_id'];
    	$dependent_ids = $data['dependend_attribute_id'];
    	    	
    	$relationDetailsTable = $this->getTable('amcustomerattr/details');
    	
    	/*
    	 * Delete data for relation first
    	 */
    	$clearCondition = array(
			'relation_id = ?' => $relation_id                    
		);
    	$this->_getWriteAdapter()->delete($relationDetailsTable, $clearCondition);
    	
    	/*
    	 * Insert new data 
    	 */
    	$insertData = array();
    	foreach ($attribute_ids as $attr) {
    		foreach ($option_ids as $option) {
    			foreach ($dependent_ids as $dep) {
					$insertData[] = array(
						'relation_id' => $relation_id,
						'option_id' => $option,
						'dependent_attribute_id' => $dep,
						'attribute_id' => $attr,
					);
    			}
    		}
    	}
    	
    	if (count($insertData)) {    		
    		$this->_getWriteAdapter()->insertMultiple($relationDetailsTable, $insertData);
    	}
    	
    	
    	/*
    	 if (!$object->getEntityAttributeId()) {
            return $this;
        }

        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('eav/entity_attribute'))
            ->where('entity_attribute_id = ?', (int)$object->getEntityAttributeId());
        $result = $this->_getReadAdapter()->fetchRow($select);

        if ($result) {
            $attribute = Mage::getSingleton('eav/config')
                ->getAttribute(Mage_Catalog_Model_Product::ENTITY, $result['attribute_id']);

            if ($this->isUsedBySuperProducts($attribute, $result['attribute_set_id'])) {
                Mage::throwException(Mage::helper('eav')->__("Attribute '%s' used in configurable products", $attribute->getAttributeCode()));
            }
            $backendTable = $attribute->getBackend()->getTable();
            if ($backendTable) {
                $select = $this->_getWriteAdapter()->select()
                    ->from($attribute->getEntity()->getEntityTable(), 'entity_id')
                    ->where('attribute_set_id = ?', $result['attribute_set_id']);

                $clearCondition = array(
                    'entity_type_id =?' => $attribute->getEntityTypeId(),
                    'attribute_id =?'   => $attribute->getId(),
                    'entity_id IN (?)'  => $select
                );
                $this->_getWriteAdapter()->delete($backendTable, $clearCondition);
            }
        }

        $condition = array('entity_attribute_id = ?' => $object->getEntityAttributeId());
        $this->_getWriteAdapter()->delete($this->getTable('entity_attribute'), $condition);

        return $this;*/
    }
}
