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
 * Store Locator Model
 *
 * @author     Qun WU <info@Sunpopwebsolutions.com>
 */
class Sunpop_Storelocator_Model_Storelocator extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('storelocator/storelocator');
    }
    
    public function setDefault()
    {
        $data = $this->getData();
        
        if (!isset($data['monday_open_time']))
            $this->setMondayOpenTime('00:00');
    
        if (!isset($data['monday_close_time']))
            $this->setMondayCloseTime('00:00');
        
        if (!isset($data['tuesday_open_time']))
            $this->setTuesdayOpenTime('00:00');
    
        if (!isset($data['tuesday_close_time']))
            $this->setTuesdayCloseTime('00:00');
            
        if (!isset($data['wednesday_open_time']))
            $this->setWednesdayOpenTime('00:00');
    
        if (!isset($data['wednesday_close_time']))
            $this->setWednesdayCloseTime('00:00');
            
        if (!isset($data['thursday_open_time']))
            $this->setThursdayOpenTime('00:00');
    
        if (!isset($data['thursday_close_time']))
            $this->setThursdayCloseTime('00:00');
            
        if (!isset($data['friday_open_time']))
            $this->setFridayOpenTime('00:00');
    
        if (!isset($data['friday_close_time']))
            $this->setFridayCloseTime('00:00');
            
        if (!isset($data['saturday_open_time']))
            $this->setSaturdayOpenTime('00:00');
    
        if (!isset($data['saturday_close_time']))
            $this->setSaturdayCloseTime('00:00');
            
        if (!isset($data['sunday_open_time']))
            $this->setSundayOpenTime('00:00');
    
        if (!isset($data['sunday_close_time']))
            $this->setSundayCloseTime('00:00');
            
        return $this;
    }
}