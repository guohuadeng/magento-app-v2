<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2012 Amasty (http://www.amasty.com)
 */
class Amasty_Customerattr_Model_Mysql4_Relation extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
    {
        $this->_init('amcustomerattr/relation', 'id');
    }
}
