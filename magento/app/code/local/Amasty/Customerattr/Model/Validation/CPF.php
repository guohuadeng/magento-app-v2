<?php
/**
* @author Alberto Camin
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Model_Validation_CPF
{
    protected $_value = 'validate-cpf';
    
    /**
     * Retrieve custom values
     *
     * @return array
     */
    public function getValues()
    {
        $values = array('value' => $this->_value,
                        'label' => Mage::helper('amcustomerattr')->__('CPF Validation')
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
        $message = Mage::helper('amcustomerattr')->__('Please fill the CPF correctly.');
        
        $js = '
		function validate_cpf(Strcpf)
		{
			// valida somente os caracteres numericos
			Strcpf = Strcpf.replace(/\b[^0-9kK]+\b/g,\'\');
		
            // verifica os CPFs invalidos conhecidos
			if (Strcpf.length != 11
			|| Strcpf == "00000000000"
			|| Strcpf == "11111111111"	
			|| Strcpf == "22222222222"
			|| Strcpf == "33333333333"	
			|| Strcpf == "44444444444"
			|| Strcpf == "55555555555"
			|| Strcpf == "66666666666"
			|| Strcpf == "77777777777"
			|| Strcpf == "88888888888"
			|| Strcpf == "99999999999")
			return false;
			
			// Valida 1o digito
			add = 0;
			for (i=0; i < 9; i ++)
				add += parseInt(Strcpf.charAt(i)) * (10 - i);
			rev = 11 - (add % 11);
			if (rev == 10 || rev == 11)
				rev = 0;
			if (rev != parseInt(Strcpf.charAt(9)))
			return false;
			// Valida 2o digito
			add = 0;
			for (i = 0; i < 10; i ++)
				add += parseInt(Strcpf.charAt(i)) * (11 - i);
			rev = 11 - (add % 11);
			if (rev == 10 || rev == 11)
				rev = 0;
			if (rev != parseInt(Strcpf.charAt(10)))
			return false;
            return true;
		};
        Validation.addAllThese([
            [\''.$this->_value.'\', \''.$message.'\', function(v) {
                return validate_cpf(v);
            }]
        ]);';
        
        return $js;
    }
}
