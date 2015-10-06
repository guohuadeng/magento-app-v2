<?php
/**
* @author Amasty
* @copyright Amasty
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Block_Adminhtml_Renderer_File extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        if (!$currentData = $row->getData($this->getColumn()->getIndex())) {
            return 'No Uploaded File';
        }
        
        $downloadUrl = Mage::helper('amcustomerattr')->getAttributeFileUrl($currentData, true);
        $fileName = Mage::helper('amcustomerattr')->cleanFileName($currentData);
        return '<a href="'. $downloadUrl .'">' . $fileName[3] . '</a>';
    }
}