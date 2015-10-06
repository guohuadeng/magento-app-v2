<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Block_Adminhtml_Customer_Form_Element_File extends Mage_Adminhtml_Block_Customer_Form_Element_File
{
    protected function _getDeleteCheckboxHtml()
    {
        return '';
    }

    /**
     * Return File preview link HTML
     *
     * @return string
     */
    protected function _getPreviewHtml()
    {
        $html = '';
        if ($this->getValue() && !is_array($this->getValue())) {
            $image = array(
                'alt'   => Mage::helper('adminhtml')->__('Download'),
                'title' => Mage::helper('adminhtml')->__('Download'),
                'src'   => Mage::getDesign()->getSkinUrl('images/fam_bullet_disk.gif'),
                'class' => 'v-middle'
            );
            $url = $this->_getPreviewUrl();
            $html .= '<span>';
            $html .= '<a href="' . $url . '">' . $this->_drawElementHtml('img', $image) . '</a> ';
            $html .= '<a href="' . $url . '">' . $this->getValue() . '</a>';
            $html .= '</span>';
        }
        return $html;
    }

    /**
     * Return Preview/Download URL
     *
     * @return string
     */
    protected function _getPreviewUrl()
    {
        $customerId = Mage::registry('current_customer')->getId();
        $attributeId = Mage::getModel('eav/entity_attribute')->getIdByCode('customer', $this->getId());
        return Mage::helper('amcustomerattr')->getAttributeFileUrl($customerId, $attributeId, $this->getValue(), true);
    }
}
