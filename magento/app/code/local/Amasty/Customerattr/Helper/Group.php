<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Helper_Group extends Mage_Core_Helper_Abstract
{
    protected $_isAllowed = false;
    protected $_attribute = 'tax_id';
    protected $_groupId   = 3;
    
    /**
     * Allow autoapply
     *
     * @return boolean
     */
    public function isAllowed()
    {
        return $this->_isAllowed;
    }
    
    /**
     * Retrieve specify validation
     *
     * @return string
     */
    public function getAttribute()
    {
        return $this->_attribute;
    }
    
    /**
     * Retrieve customer group id
     *
     * @return string
     */
    public function getGroupId()
    {
        return $this->_groupId;
    }
}