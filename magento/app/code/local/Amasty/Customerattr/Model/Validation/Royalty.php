<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Model_Validation_Royalty
{
    protected $_value = 'validate-royalty';
    protected $_checkOkBeginnings = true;
    protected $_okBeginnings = '[\'703240\'];';
    protected $_checkNotOkBeginnings = true;
    protected $_notOkBeginnings = '[\'7032400\', \'7032404\'];';
    
    /**
     * Retrieve custom values
     *
     * @return array
     */
    public function getValues()
    {
        $values = array('value' => $this->_value,
                        'label' => Mage::helper('amcustomerattr')->__('Royalty Card Number')
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
        $message = Mage::helper('amcustomerattr')->__('Sorry, that is not a valid card number');
        
        $js = '
        //Test validity by Luhn-algorithm
        function checkLuhn(input)
        {
            var sum = 0;
            var numdigits = input.length;
            var parity = numdigits % 2;
            for(var i=0; i < numdigits; i++) {
                var digit = parseInt(input.charAt(i));
                if(i % 2 == parity) {
                    digit *= 2;
                }
                if(digit > 9) {
                    digit -= 9;
                }
                sum += digit;
            }
            return (sum % 10) == 0;
        }
        
        function validate_royalty(number)
        {
            //Check if it not contains any other characters than digits
            var digits = \'0123456789\';
            for(var i=0; i < number.length; i++) {
                if (digits.indexOf(number.charAt(i)) < 0) {
                    return false;
                }
            }
            //Check the number length
            if (number.length != 16) {
                return false;
            }';
        if ($this->_checkOkBeginnings || $this->_checkNotOkBeginnings) {
            $js .= '
            //Check for allowable card number beginnings
            passed = false;';
            if ($this->_checkOkBeginnings) {
                $js .= '
                //List of allowable card number beginnings
                OKBeginnings = '.$this->_okBeginnings.' 
                OKBeginnings.each(function(value) {
                    if (number.indexOf(value) == 0) {
                        passed = true;
                    }
                });';
            }
            if ($this->_checkNotOkBeginnings) {
                $js .= '
                //List of unallowed card number beginnings
                NotOKBeginnings = '.$this->_notOkBeginnings.' 
                NotOKBeginnings.each(function(value) {
                    if (number.indexOf(value) == 0) {
                        passed = false;
                    }
                });';
            }
            $js .= '
            if (!passed) {
                return false;
            }';
        }
        $js .= '
        if (!checkLuhn(number)) {
                return false;
            }
            return true;
        }
        Validation.addAllThese([
            [\''.$this->_value.'\', \''.$message.'\', function(v) {
                return validate_royalty(v);
            }]
        ]);';

        return $js;
    }
}