<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
* @package Amasty_Orderattach
*/
class Amasty_Customerattr_AttachmentController extends Mage_Core_Controller_Front_Action
{
    public function downloadAction()
    {
        $customerId = $this->getRequest()->getParam('customer');
        if (Mage::getSingleton('customer/session')->isLoggedIn() && $customerId == Mage::getSingleton('customer/session')->getCustomer()->getId()) {
            $fileName = $this->getRequest()->getParam('file');
            $fileName = Mage::helper('core')->urlDecode($fileName);
            $downloadPath = Mage::helper('amcustomerattr')->getAttributeFileUrl($fileName);
            $fileName = Mage::helper('amcustomerattr')->cleanFileName($fileName);
            $downloadPath = $downloadPath . $fileName[1] . DS . $fileName[2] . DS;
            if (file_exists($downloadPath . $fileName[3]))
            {
                header('Content-Disposition: attachment; filename="' . $fileName[3] . '"');               
                if(function_exists('mime_content_type')) 
                {
                    header('Content-Type: ' . mime_content_type($downloadPath . $fileName[3]));                    
                }
                else if(class_exists('finfo'))
                {
                     $finfo = new finfo(FILEINFO_MIME);
                     $mimetype = $finfo->file($downloadPath . $fileName[3]);
                     header('Content-Type: ' . $mimetype);
                }                
                readfile($downloadPath . $fileName[3]); 
            }
        }
        exit;
    }

    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }
    
    public function forgotPasswordPostAction()
    {
        $email = $this->getRequest()->getPost('email');
        if ($email) {
            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByEmail($email);

            if ($customer->getId()) {
                try {
                    $newPassword = $customer->generatePassword();
                    $customer->changePassword($newPassword, false);
                    $customer->sendPasswordReminderEmail();

                    $this->_getSession()->addSuccess($this->__('Forgot password email has been sent to the email address that you used for registration.'));

                    $this->getResponse()->setRedirect(Mage::getUrl('customer/account/login'));
                    return;
                }
                catch (Exception $e){
                    $this->_getSession()->addError($e->getMessage());
                }
            } else {
                $this->_getSession()->addError($this->__('The customer, that associated with this value, was not found in our records.'));
                $this->_getSession()->setForgottenEmail($email);
            }
        } else {
            $this->_getSession()->addError($this->__('Please fill the field.'));
            $this->getResponse()->setRedirect(Mage::getUrl('customer/account/forgotpassword'));
            return;
        }

        $this->getResponse()->setRedirect(Mage::getUrl('customer/account/forgotpassword'));
    }
}