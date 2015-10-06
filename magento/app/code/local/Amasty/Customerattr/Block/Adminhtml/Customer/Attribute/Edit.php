<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Block_Adminhtml_Customer_Attribute_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'amcustomerattr';
        $this->_objectId = 'attribute_id';
        $this->_controller = 'adminhtml_customer_attribute';

        parent::__construct();

        if($this->getRequest()->getParam('popup')) {
            $this->_removeButton('back');
            $this->_addButton(
                'close',
                array(
                    'label'     => Mage::helper('catalog')->__('Close Window'),
                    'class'     => 'cancel',
                    'onclick'   => 'window.close()',
                    'level'     => -1
                )
            );
        }
        
        $this->_removeButton('reset');
        
        $this->_updateButton('save', 'label', Mage::helper('catalog')->__('Save Attribute'));
        $this->_addButton(
            'save_and_edit_button',
            array(
                'label'     => Mage::helper('catalog')->__('Save And Continue Edit'),
                'onclick'   => 'saveAndContinueEdit()',
                'class'     => 'save'
            ),
            100
        );

        if (! Mage::registry('entity_attribute')->getIsUserDefined()) {
            $this->_removeButton('delete');
        } else {
            $this->_updateButton('delete', 'label', Mage::helper('catalog')->__('Delete Attribute'));
            if (Mage::getModel('amcustomerattr/details')->usedInRelation($this->getRequest()->getParam($this->_objectId))->getSize() > 0) {
                $this->_updateButton('delete', 'onclick', 'deleteConfirm(\''. Mage::helper('amcustomerattr')->__('This attribute are using in `Customer Attribute Relation`. Are you sure you want to delete it?') . '\', \'' . $this->getDeleteUrl() . '\')');
            }
        }
    }

    public function getHeaderText()
    {
        if (Mage::registry('entity_attribute')->getId()) {
            $frontendLabel = Mage::registry('entity_attribute')->getFrontendLabel();
            if (is_array($frontendLabel)) {
                $frontendLabel = $frontendLabel[0];
            }
            return Mage::helper('catalog')->__('Edit Customer Attribute "%s"', $this->htmlEscape($frontendLabel));
        }
        else {
            return Mage::helper('catalog')->__('New Customer Attribute');
        }
    }

    public function getValidationUrl()
    {
        return $this->getUrl('*/*/validate', array('_current'=>true));
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('_current'=>true, 'back'=>null));
    }
}
