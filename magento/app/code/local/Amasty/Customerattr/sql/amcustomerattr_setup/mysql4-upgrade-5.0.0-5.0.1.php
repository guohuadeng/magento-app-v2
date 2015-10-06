<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
* @package Amasty_Customerattr
*/
$installer = $this;

$installer->startSetup();

$installer->run("
    CREATE TABLE `temp_am_customerattr_relation_details` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `attribute_id` smallint(5) unsigned NOT NULL DEFAULT '0',
      `option_id` int(10) unsigned NOT NULL DEFAULT '0',
      `dependent_attribute_id` smallint(5) unsigned NOT NULL DEFAULT '0',
      `relation_id` int(10) unsigned NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    INSERT INTO `temp_am_customerattr_relation_details` (`attribute_id`, `option_id`, `dependent_attribute_id`, `relation_id`)
    SELECT `attribute_id`, `option_id`, `dependent_attribute_id`, `relation_id` FROM `{$this->getTable('amcustomerattr/details')}`;
    DROP TABLE `{$this->getTable('amcustomerattr/details')}`;
    RENAME TABLE `temp_am_customerattr_relation_details` TO `{$this->getTable('amcustomerattr/details')}`;
");

$installer->endSetup();