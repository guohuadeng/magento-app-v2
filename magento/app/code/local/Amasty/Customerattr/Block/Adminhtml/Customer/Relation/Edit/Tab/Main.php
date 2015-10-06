<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Block_Adminhtml_Customer_Relation_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $model = Mage::registry('entity_relation');

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getData('action'),
            'method' => 'post'
        ));

        $fieldset = $form->addFieldset('base_fieldset',
            array('legend'=>Mage::helper('catalog')->__('Relation Properties'))
        );
        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array(
                'name' => 'id',
            ));
        }
        $this->_addElementTypes($fieldset);
        
        /* @var $relationModel Amasty_Customerattr_Model_Relation */
        $relationModel = Mage::getModel('amcustomerattr/relation');
        
        /*
         * Get list only attributes
         */
        $attributes = $relationModel->getUserDefinedAttributes();
        
        
        $fieldset->addField('name', 'text', array(
            'name'  => 'name',
            'label' => Mage::helper('catalog')->__('Relation name'),
            'title' => Mage::helper('catalog')->__('Relation name'),
            'note'  => Mage::helper('catalog')->__('For internal use'),
            'required' => true,
        ));
        
     	$attributeValues = array();
        
        $relationDetails = Mage::registry('entity_relation_details');
        
        $attribute_ids = array();        
        $option_ids = array();
        $dependent_attributes_ids = array();
        
        
        $currentAttributeId = null;
        
        if ($relationDetails && $relationDetails->count() > 0) {
	        foreach ($relationDetails as $relation) {
	        	$option_ids[] = $relation->getOptionId();
	        	$attribute_ids[] = $relation->getAttributeId();
	        	$dependent_attributes_ids[] = $relation->getDependentAttributeId();
	        }
	        $currentAttributeId = $attribute_ids[0];
        } else {
        	$currentAttributeId = $attributes[0]['value'];
        }
        
        $attributeValues = $relationModel->getAttributeValues($currentAttributeId);

        $fieldset->addField('attribute_id', 'select', array(
            'name' => 'attribute_id',
            'label' => Mage::helper('catalog')->__('Parent Attribute'),
            'title' => Mage::helper('catalog')->__('Parent Attribute'),
            'values' => $attributes,
        	'value' => $attribute_ids,
        	'required' => true,
        ));
               
        
        $fieldset->addField('option_id', 'multiselect', array(
            'name'      => 'option_id',
            'label'     => Mage::helper('catalog')->__('Attribute Options'),
            'title'     => Mage::helper('catalog')->__('Attribute Options'),
            'values'    => $attributeValues,
        	'value'		=> $option_ids,
        	'required' => true,
        ));
        
        /*
         * Get all user defined attributes
         */
        $attributes = $relationModel->getUserDefinedAttributes(false, false);
        
        /*
         * Unset Current Attribute
         */
        foreach ($attributes as $key => $attribute) {
        	if ($attribute['value'] == $currentAttributeId) {
        		unset($attributes[$key]);
        	}	
        }
        $fieldset->addField('dependend_attribute_id', 'multiselect', array(
            'name'      => 'dependend_attribute_id',
            'label'     => Mage::helper('catalog')->__('Dependent Attributes'),
            'title'     => Mage::helper('catalog')->__('Dependent Attributes'),
            'values'    => $attributes,
        	'value'		=> $dependent_attributes_ids,
        	'required' => true,
        ));

        $form->addValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
