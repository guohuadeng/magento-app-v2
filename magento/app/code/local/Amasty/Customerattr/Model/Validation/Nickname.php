<?php
/**
* @author Amasty Team
* @copyright Copyright (c) Amasty (http://www.amasty.com)
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Model_Validation_Nickname
{
    protected $_value = 'validate-nickname';
    
    /**
     * Retrieve custom values
     *
     * @return array
     */
    public function getValues()
    {
        $values = array('value' => $this->_value,
                        'label' => Mage::helper('amcustomerattr')->__('Nickname validation')
                        );
        return $values;
    }
    
    /**
     * Retrieve JS code
     *
     * @return string
     */
    public function getJS()
    {
        $message = Mage::helper('amcustomerattr')->__('Please use only letters (a-z or A-Z), numbers (0-9), "_" and "-" symbols.');
        
        $js = '
        Validation.addAllThese([
            [\''.$this->_value.'\', \''.$message.'\', function(v) {
                return Validation.get(\'IsEmpty\').test(v) ||  /^[-0-9A-Za-z_\s]+$/.test(v);
            }]
        ]);';
        
        return $js;
    }
}