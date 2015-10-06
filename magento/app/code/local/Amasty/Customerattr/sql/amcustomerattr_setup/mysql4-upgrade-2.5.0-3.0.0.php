<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
* @package Amasty_Customerattr
*/
$installer = $this;

$installer->startSetup();

$installer->run("

CREATE TABLE `{$this->getTable('amcustomerattr/relation')}` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE  `{$this->getTable('amcustomerattr/details')}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `attribute_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `option_id` int(10) unsigned NOT NULL DEFAULT '0',
  `dependent_attribute_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `relation_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_am_customerattr_relation_1` (`attribute_id`),
  KEY `FK_am_customerattr_relation_2` (`dependent_attribute_id`),
  KEY `FK_am_customerattr_relation_details_4` (`relation_id`),
  CONSTRAINT `FK_am_customerattr_relation_1` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute_option` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_am_customerattr_relation_2` FOREIGN KEY (`dependent_attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_am_customerattr_relation_details_3` FOREIGN KEY (`relation_id`) REFERENCES `am_customerattr_relation` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_am_customerattr_relation_details_4` FOREIGN KEY (`relation_id`) REFERENCES `am_customerattr_relation` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->run("
    ALTER TABLE `{$this->getTable('customer/eav_attribute')}` ADD  `is_read_only` TINYINT( 1 ) UNSIGNED NOT NULL ; 
");

$installer->endSetup(); 
