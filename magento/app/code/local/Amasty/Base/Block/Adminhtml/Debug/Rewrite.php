<?php
/**
 * @copyright   Copyright (c) 2010 Amasty (http://www.amasty.com)
 */ 
class Amasty_Base_Block_Adminhtml_Debug_Rewrite extends Amasty_Base_Block_Adminhtml_Debug_Base
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amasty/ambase/debug/rewrite.phtml');        
    }
    
    function getRewritesList(){
        return Mage::helper("ambase")->getRewritesList();
    }
}
?>