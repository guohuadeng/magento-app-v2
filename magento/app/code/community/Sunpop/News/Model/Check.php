<?php
/**
 * Sunpop Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Sunpop License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://commerce-lab.com/LICENSE.txt
 *
 * @category   Sunpop
 * @package    Sunpop_News
 * @copyright  Copyright (c) 2012 Sunpop Co. (http://commerce-lab.com)
 * @license    http://commerce-lab.com/LICENSE.txt
 */

class Sunpop_News_Model_Check extends Mage_Core_Model_Abstract
{

    public function checkExtensions()
    {
        $modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());
        sort($modules);

        $magentoPlatform = Sunpop_News_Helper_Versions::getPlatform();
        foreach ($modules as $extensionName) {
            if (strstr($extensionName, 'Sunpop_') === false) {
                continue;
            }
            if ($extensionName == 'Sunpop_Core' || $extensionName == 'Sunpop_All') {
                continue;
            }
            if ($platformNode = $this->getExtensionPlatform($extensionName)) {
                $extensionPlatform = Sunpop_News_Helper_Versions::convertPlatform($platformNode);
                if ($extensionPlatform < $magentoPlatform) {
                    $this->disableExtensionOutput($extensionName);
                    Mage::getSingleton('adminhtml/session')
                    ->addError(Mage::helper('clnews')->__('Platform version is not correct for News module!'));
                    return;
                }
            }
        }
        return $this;
    }

    public function getExtensionPlatform($extensionName)
    {
        try {
            if ($platform = Mage::getConfig()->getNode("modules/$extensionName/platform")) {
                $platform = strtolower($platform);
                return $platform;
            } else {
                throw new Exception();
            }
        } catch (Exception $e) {
            return false;
        }
    }


    public function disableExtensionOutput($extensionName)
    {
        $coll = Mage::getModel('core/config_data')->getCollection();
        $coll->getSelect()->where("path='advanced/modules_disable_output/$extensionName'");
        $i = 0;
        foreach ($coll as $cd) {
            $i++;
            $cd->setValue(1)->save();
        }
        if ($i == 0) {
            Mage::getModel('core/config_data')
                    ->setPath("advanced/modules_disable_output/$extensionName")
                    ->setValue(1)
                    ->save();
        }
        return $this;
    }

    public function checkConfiguration()
    {
        $coll = Mage::getModel('core/config_data')->getCollection();
        $coll->getSelect()->where("path='clnews/news/showrightblock'");
        foreach ($coll as $cd) {
            if ($cd->getValue() == 1) {
                $loll = Mage::getModel('core/config_data')->getCollection();
                $loll->getSelect()->where("path='clnews/news/showleftblock'");
                foreach ($loll as $ld) {
                    if ($ld->getValue() == 1) {
                        $ld->setValue(0)->save();
                        Mage::getSingleton('adminhtml/session')
                        ->addSuccess(Mage::helper('clnews')->__('News category tree can be shown only in one column'));
                    }
                }
            }
        }
        return $this;
    }


}
