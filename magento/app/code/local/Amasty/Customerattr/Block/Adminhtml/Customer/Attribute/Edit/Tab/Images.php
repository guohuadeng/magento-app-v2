<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Block_Adminhtml_Customer_Attribute_Edit_Tab_Images extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amasty/amcustomerattr/attribute/images.phtml');
        $this->_doUpload();
    }
    
    protected function _doUpload()
    {
        $uploadDir = Mage::getBaseDir('media') . DIRECTORY_SEPARATOR . 
                                                    'amcustomerattr' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
                                                    
        /**
        * Deleting
        */
        $toDelete = Mage::app()->getRequest()->getPost('amcustomerattr_icon_delete');
        if ($toDelete)
        {
            foreach ($toDelete as $optionId => $del)
            {
                if ($del)
                {
                    @unlink($uploadDir . $optionId . '.jpg');
                }
            }
        }
        
        /**
        * Uploading files
        */
        if (isset($_FILES['amcustomerattr_icon']) && isset($_FILES['amcustomerattr_icon']['error']))
        {
            foreach ($_FILES['amcustomerattr_icon']['error'] as $optionId => $errorCode)
            {
                if (UPLOAD_ERR_OK == $errorCode)
                {
                    move_uploaded_file($_FILES['amcustomerattr_icon']['tmp_name'][$optionId], $uploadDir . $optionId . '.jpg');
                }
            }
        }
    }
    
    public function getOptionsCollection()
    {
        $optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->setAttributeFilter($this->getAttributeObject()->getId())
                ->setPositionOrder('desc', true)
                ->load();
        return $optionCollection;
    }
    
    public function getIcon($option)
    {
        return Mage::helper('amcustomerattr')->getAttributeImageUrl($option->getId());
    }
    
    public function getSubmitUrl()
    {
        $url = Mage::helper('core/url')->getCurrentUrl();
        if (isset($_SERVER['HTTPS']) && 'off' != $_SERVER['HTTPS'])
        {
            $url = str_replace('http:', 'https:', $url);
        }
        return $url;
    }
    
    public function getAttributeObject()
    {
        return Mage::registry('entity_attribute');
    }
}
