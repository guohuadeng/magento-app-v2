<?php
class Amasty_Customerattr_Model_Rewrite_Customer_Entity_Attribute extends Mage_Customer_Model_Entity_Attribute
{
    public function saveAttributeConfiguration($attributeId, $configuration)
    {
        $this->_getWriteAdapter()->update($this->getTable('customer/eav_attribute'), $configuration, 'attribute_id = "' . $attributeId . '"');
    }
    
    protected function _saveOption(Mage_Core_Model_Abstract $object)
    {
        $option = $object->getOption();
        if (!isset($option['parent'])&&!isset($option['group_id']))
        {
            return parent::_saveOption($object);
        }
        if (is_array($option)) {
            $write = $this->_getWriteAdapter();
            $optionTable        = $this->getTable('attribute_option');
            $optionValueTable   = $this->getTable('attribute_option_value');
            $stores = Mage::getModel('core/store')
                ->getResourceCollection()
                ->setLoadDefault(true)
                ->load();

            if (isset($option['value'])) {
                $attributeDefaultValue = array();
                if (!is_array($object->getDefault())) {
                    $object->setDefault(array());
                }

                foreach ($option['value'] as $optionId => $values) {
                    $intOptionId = (int) $optionId;
                    if (!empty($option['delete'][$optionId])) {
                        if ($intOptionId) {
                            $condition = $write->quoteInto('option_id=?', $intOptionId);
                            $write->delete($optionTable, $condition);
                        }

                        continue;
                    }

                    if (!$intOptionId) {
                        $data = array(
                           'attribute_id'     => $object->getId(),
                           'sort_order'          => isset($option['order'][$optionId]) ? $option['order'][$optionId] : 0,
                        );
                        if (isset($option['parent']))
                        {
                           $data['parent_option_id'] = isset($option['parent'][$optionId]) ? $option['parent'][$optionId] : 0;
                        }
                        else if(isset($option['group_id']))
                        {
                           $data['group_id'] = isset($option['group_id'][$optionId]) ? $option['group_id'][$optionId] : 0;
                        }
                        $write->insert($optionTable, $data);
                        $intOptionId = $write->lastInsertId();
                    }
                    else {
                        $data = array(
                           'sort_order'    => isset($option['order'][$optionId]) ? $option['order'][$optionId] : 0,
                        );
                        if (isset($option['parent']))
                        {
                           $data['parent_option_id'] = isset($option['parent'][$optionId]) ? $option['parent'][$optionId] : 0;
                        }
                        else if(isset($option['group_id']))
                        {
                           $data['group_id'] = isset($option['group_id'][$optionId]) ? $option['group_id'][$optionId] : 0;
                        }
                        $write->update($optionTable, $data, $write->quoteInto('option_id=?', $intOptionId));
                    }

                    if (in_array($optionId, $object->getDefault())) {
                        if ($object->getFrontendInput() == 'multiselect') {
                            $attributeDefaultValue[] = $intOptionId;
                        } else if ($object->getFrontendInput() == 'select') {
                            $attributeDefaultValue = array($intOptionId);
                        }
                    }


                    // Default value
                    if (!isset($values[0])) {
                        Mage::throwException(Mage::helper('eav')->__('Default option value is not defined.'));
                    }

                    $write->delete($optionValueTable, $write->quoteInto('option_id=?', $intOptionId));
                    foreach ($stores as $store) {
                        if (isset($values[$store->getId()]) && (!empty($values[$store->getId()]) || $values[$store->getId()] == "0")) {
                            $data = array(
                                'option_id' => $intOptionId,
                                'store_id'  => $store->getId(),
                                'value'     => $values[$store->getId()],
                            );
                            $write->insert($optionValueTable, $data);
                        }
                    }
                }

                $write->update($this->getMainTable(), array(
                    'default_value' => implode(',', $attributeDefaultValue)
                ), $write->quoteInto($this->getIdFieldName() . '=?', $object->getId()));
            }
        }
        return $this;
    }
}