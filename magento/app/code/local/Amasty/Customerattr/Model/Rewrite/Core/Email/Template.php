<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Model_Rewrite_Core_Email_Template extends Mage_Core_Model_Email_Template
{
    public function sendTransactional($templateId, $sender, $email, $name, $vars=array(), $storeId=null)
    {
        if (isset($vars['order']) && !isset($vars['customer']))
        {
            // will try to add customer object
            $order = $vars['order'];
            if ($order->getCustomerId())
            {
                $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
                if ($customer->getId())
                {
                    $vars['customer'] = $customer;
                }
            }
        }
        return parent::sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
    }
}