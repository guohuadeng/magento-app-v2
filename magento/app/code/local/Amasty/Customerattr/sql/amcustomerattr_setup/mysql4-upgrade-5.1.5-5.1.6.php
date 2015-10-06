<?php
/**
* @author Amasty Team
* @copyright Copyright (c) Amasty (http://www.amasty.com)
* @package Amasty_Customerattr
*/
$installer = $this;

$installer->startSetup();

$installer->run("
    ALTER TABLE `{$this->getTable('customer/eav_attribute')}` ADD `account_filled` TINYINT( 1 ) UNSIGNED NOT NULL ;
    ALTER TABLE `{$this->getTable('customer/eav_attribute')}` ADD `billing_filled` TINYINT( 1 ) UNSIGNED NOT NULL ;
");

$installer->endSetup(); 