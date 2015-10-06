<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Model_Validation_Rut
{
    protected $_value = 'validate-rut';
    
    /**
     * Retrieve custom values
     *
     * @return array
     */
    public function getValues()
    {
        $values = array('value' => $this->_value,
                        'label' => Mage::helper('amcustomerattr')->__('RUT code validation')
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
        $message = Mage::helper('amcustomerattr')->__('Please use the value in the RUT format');
        
        $js = '
        function validate_rut(d)
        {
            var r = false, t = d.replace(/\b[^0-9kK]+\b/g,\'\');
            if (t.length == 8) {
                t = 0+t;
            }
            if (t.length == 9) {
                var a = t.substring(t.length-1,-1), b = t.charAt(t.length-1);
                if (b == \'k\') {
                    b = \'K\'
                }
                if (!isNaN(a)) {
                    var s = 0, m = 2, x = \'0\', e = 0;
                    for(var i=a.length-1; i >= 0; i--) {
                        s = s + a.charAt(i) * m;
                        if (m == 7) {
                            m = 2;
                        } else {
                            m++;
                        }
                    }
                    var y = s % 11;
                    if (y == 1) {
                        x = \'K\';
                    } else {
                        if (y == 0) {
                            x = \'0\';
                        } else {
                            e = 11 - y;
                            x = e + \'\';
                        }
                    }
                    if (x == b) {
                        r = true;
                        d = a.substring(0,2) + \'.\' + a.substring(2,5) + \'.\' + a.substring(5,8) + \'-\' + b;
                    }
                }
            }
            return r;
        }
        Validation.addAllThese([
            [\''.$this->_value.'\', \''.$message.'\', function(v) {
                return validate_rut(v);
            }]
        ]);';
        
        return $js;
    }
}