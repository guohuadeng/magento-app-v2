<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Block_Adminhtml_Customer_Relation_Grid_Renderer_Label extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
        $string = '';
        
        if ($row->getDependentLabel()) {
            $labels = explode(',', $row->getDependentLabel());
            $result = array_unique($labels);
            $string = implode(', ', $result);
        }
        
        return $string;
    }
}