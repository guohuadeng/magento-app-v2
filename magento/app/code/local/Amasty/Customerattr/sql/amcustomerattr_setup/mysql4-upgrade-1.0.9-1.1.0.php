<?php
$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE `{$this->getTable('customer/eav_attribute')}` ADD  `sorting_order` SMALLINT UNSIGNED NOT NULL ;

");

$installer->endSetup(); 