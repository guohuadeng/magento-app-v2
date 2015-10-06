<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Adminhtml_ActivationController extends Mage_Adminhtml_Controller_Action
{

 	public function massDeactivateAction() {
        $customerIds = $this->getRequest()->getParam('customer');
        if(!is_array($customerIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amcustomerattr')->__('Please select item(s)'));
        } else {
            try {
                foreach ($customerIds as $customerId) {
                    $customer = Mage::getModel('customer/customer')->load($customerId);
                    $customer->setAmIsActivated('1');
                    $customer->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d customers(s) were successfully deactivated', count($customerIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('adminhtml/customer/index');
    }
 	public function massActivateAction() {
        $customerIds = $this->getRequest()->getParam('customer');
        if(!is_array($customerIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amcustomerattr')->__('Please select item(s)'));
        } else {
            try {
                foreach ($customerIds as $customerId) {
                    $customer = Mage::getModel('customer/customer')->load($customerId);
                    $customer->setAmIsActivated('2');
                    $customer->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d customers(s) were successfully activated', count($customerIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('adminhtml/customer/index');
    }


}