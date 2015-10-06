<?php
/**
 * @copyright   Copyright (c) 2010 Amasty (http://www.amasty.com)
 */ 
class Amasty_Base_Block_Adminhtml_Debug_Base extends Mage_Adminhtml_Block_Widget_Form
{
    function getClassPath($rewrites, $codePool, $rewriteIndex){
        return Amasty_Base_Model_Conflict::getClassPath($codePool[$rewriteIndex], $rewrites[$rewriteIndex]);
    }
}