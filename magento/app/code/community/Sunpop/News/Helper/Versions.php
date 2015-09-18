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


class Sunpop_News_Helper_Versions extends Mage_Core_Helper_Abstract
{

    const EE_PLATFORM = 100;
    const PE_PLATFORM = 10;
    const CE_PLATFORM = 0;

    const ENTERPRISE_DETECT_COMPANY = 'Enterprise';
    const ENTERPRISE_DETECT_EXTENSION = 'Enterprise';
    const ENTERPRISE_DESIGN_NAME = "enterprise";
    const PROFESSIONAL_DESIGN_NAME = "pro";

    protected static $_platform = -1;

    /**
     * Checks which edition is used
     * @return int
     */
    public static function getPlatform()
    {
        if (self::$_platform == -1) {
            $pathToClaim = BP . DS . "app" . DS . "etc" . DS . "modules" . DS . self::ENTERPRISE_DETECT_COMPANY . "_" . self::ENTERPRISE_DETECT_EXTENSION .  ".xml";
            $pathToEEConfig = BP . DS . "app" . DS . "code" . DS . "core" . DS . self::ENTERPRISE_DETECT_COMPANY . DS . self::ENTERPRISE_DETECT_EXTENSION . DS . "etc" . DS . "config.xml";
            $isCommunity = !file_exists($pathToClaim) || !file_exists($pathToEEConfig);
            if ($isCommunity) {
                 self::$_platform = self::CE_PLATFORM;
            } else {
                $_xml = @simplexml_load_file($pathToEEConfig,'SimpleXMLElement', LIBXML_NOCDATA);
                if(!$_xml===FALSE) {
                    $package = (string)$_xml->default->design->package->name;
                    $theme = (string)$_xml->install->design->theme->default;
                    $skin = (string)$_xml->stores->admin->design->theme->skin;
                    $isProffessional = ($package == self::PROFESSIONAL_DESIGN_NAME) && ($theme == self::PROFESSIONAL_DESIGN_NAME) && ($skin == self::PROFESSIONAL_DESIGN_NAME);
                    if ($isProffessional) {
                        self::$_platform = self::PE_PLATFORM;
                        return self::$_platform;
                    }
                    self::$_platform = self::EE_PLATFORM;
                    return self::$_platform;
                }
                self::$_platform = self::EE_PLATFORM;
            }
        }
        return self::$_platform;
    }

    /**
     * Convert platform from string to int and backwards
     * @static
     * @param $platformCode
     * @return int|string
     */
    public static function convertPlatform($platformCode)
    {
        if (is_numeric($platformCode)) {
            // Convert predefined to letters code
            $platform = ($platformCode == self::EE_PLATFORM ? 'ee' : ($platformCode == self::PE_PLATFORM ? 'pe'
                    : 'ce'));
        } elseif (is_string($platformCode)) {
            $platformCode = strtolower($platformCode);
            $platform = ($platformCode == 'ee' ? self::EE_PLATFORM : ($platformCode == 'pe' ? self::PE_PLATFORM
                    : self::CE_PLATFORM));
        }else{$platform = self::CE_PLATFORM;}
        return $platform;
    }

    public static function convertVersion($v)
    {
        $digits = @explode(".", $v);
        $version = 0;
        if (is_array($digits)) {
            foreach ($digits as $k => $v) {
                $version += ($v * pow(10, max(0, (3 - $k))));
            }

        }
        return $version;
    }
}
