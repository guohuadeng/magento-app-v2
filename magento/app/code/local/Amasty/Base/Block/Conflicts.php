<?php
/**
 * @copyright   Copyright (c) 2010 Amasty
 */ 
class Amasty_Base_Block_Conflicts extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $autoload = Mage::app()->getRequest()->getParam('autoload');
        
        $helper = Mage::helper("ambase");
        $html = $this->_getHeaderHtml($element);
        
        $ajaxUrl = Mage::helper("adminhtml")->getUrl("ambase/adminhtml_base/ajax");
        $html.= '<div id="ambase_conflicts_container"></div>';
        $html.= '<button id="ambase_conflicts_show" type="button" class="scalable" onclick="ambaseShow(\''.$ajaxUrl.'\')" style=""><span><span><span>'.$helper->__("Show").'</span></span></span></button>&nbsp;&nbsp;&nbsp;';
        
        if ($autoload){
            $html .= "<script>
                Event.observe(window, 'load', function(){
                    $('ambase_conflicts-head').click();
                    $('ambase_conflicts_show').click();
                });
                </script>";
        }
        
//        $html .= Mage::getUrl('adminhtml/ambase/download');
//	$html.= Amasty_Base_Model_Conflicts::run();
        $html .= $this->_getFooterHtml($element);
        return $html;
    }
}