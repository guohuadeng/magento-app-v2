<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is under the Magento root directory in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category    Sunpop
 * @package     Sunpop_Storelocator
 * @copyright   Copyright (c) 2015 Ivan Deng. (http://www.sunpop.cn)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
/**
 * Store Locator Adminhtml Block
 *
 * @author     Qun WU <info@Sunpopwebsolutions.com>
 */
class Sunpop_Storelocator_Block_Adminhtml_Storelocator extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_storelocator';
        $this->_blockGroup = 'storelocator';
        $this->_headerText = Mage::helper('storelocator')->__('Store Manager');
        $this->_addButtonLabel = Mage::helper('storelocator')->__('Add Store');
        parent::__construct();
    }
}