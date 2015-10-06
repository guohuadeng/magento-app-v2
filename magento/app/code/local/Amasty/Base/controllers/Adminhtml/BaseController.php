<?php
class Amasty_Base_Adminhtml_BaseController extends Mage_Adminhtml_Controller_Action
{
    public function ajaxAction()
    {
        $helper = Mage::helper("ambase");
        print $helper->ajaxHtml();
    }
    
    public function fixAction()
    {
        $object = Mage::app()->getRequest()->getParam('object');
        $module = Mage::app()->getRequest()->getParam('module');
        $rewrite = Mage::app()->getRequest()->getParam('rewrite');
        if ($module && $rewrite && $object){
            
            try {
                $conflict = Mage::getModel("ambase/conflict");
                $conflict->fix($object, $module, $rewrite);
                
                foreach($conflict->log() as $m)
                    Mage::getSingleton('adminhtml/session')->addNotice($m);
                        
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
            
            
            $this->_redirect("adminhtml/system_config/edit", array(
                "section" => "ambase",
                "autoload" => 1
            ));
        }
    }
    
    public function rollbackAction()
    {
        $object = Mage::app()->getRequest()->getParam('object');
        $module = Mage::app()->getRequest()->getParam('module');
        $rewrite = Mage::app()->getRequest()->getParam('rewrite');
        if ($module && $rewrite && $object){
            try {
                $conflict = Mage::getModel("ambase/conflict");
                $conflict->rollback($object, $module, $rewrite);
                
                foreach($conflict->log() as $m)
                    Mage::getSingleton('adminhtml/session')->addNotice($m);
                
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
                        
            $this->_redirect("adminhtml/system_config/edit", array(
                "section" => "ambase",
                "autoload" => 1
            ));
        }
    }
}  
?>