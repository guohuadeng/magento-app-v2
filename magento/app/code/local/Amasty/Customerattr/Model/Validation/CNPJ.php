<?php
/**
* @author Alberto Camin
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Model_Validation_CNPJ
{
    protected $_value = 'validate-cnpj';
    
    /**
     * Retrieve custom values
     *
     * @return array
     */
    public function getValues()
    {
        $values = array('value' => $this->_value,
                        'label' => Mage::helper('amcustomerattr')->__('CNPJ Validation')
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
        $message = Mage::helper('amcustomerattr')->__('Please fill the CNPJ correctly.');
        
        $js = '
		function validate_cnpj(Strcnpj)
		{
			// valida somente os caracteres numericos
			Strcnpj = Strcnpj.replace(/\b[^0-9kK]+\b/g,\'\');
		
            // verifica a quantidade de caracteres
			if (Strcnpj.length != 14
			|| Strcnpj == "00000000000000"
			|| Strcnpj == "11111111111111"
			|| Strcnpj == "22222222222222"
			|| Strcnpj == "33333333333333"
			|| Strcnpj == "44444444444444"
			|| Strcnpj == "55555555555555"
			|| Strcnpj == "66666666666666"
			|| Strcnpj == "77777777777777"
			|| Strcnpj == "88888888888888"
			|| Strcnpj == "99999999999999")
			return false;
			
			// Valida DVs
			tamanho = Strcnpj.length - 2
			numeros = Strcnpj.substring(0,tamanho);
			digitos = Strcnpj.substring(tamanho);
			soma = 0;
			pos = tamanho - 7;
			for (i = tamanho; i >= 1; i--) {
			  soma += numeros.charAt(tamanho - i) * pos--;
			  if (pos < 2)
					pos = 9;
			}
			resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
			if (resultado != digitos.charAt(0))
				return false;
		 
			tamanho = tamanho + 1;
			numeros = Strcnpj.substring(0,tamanho);
			soma = 0;
			pos = tamanho - 7;
			for (i = tamanho; i >= 1; i--) {
			  soma += numeros.charAt(tamanho - i) * pos--;
			  if (pos < 2)
					pos = 9;
			}
			resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
			if (resultado != digitos.charAt(1))
				  return false;
		   
			return true;
		};
        Validation.addAllThese([
            [\''.$this->_value.'\', \''.$message.'\', function(v) {
                return validate_cnpj(v);
            }]
        ]);';
        
        return $js;
    }
}
