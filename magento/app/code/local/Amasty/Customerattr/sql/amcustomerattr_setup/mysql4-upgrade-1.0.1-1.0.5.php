<?php
$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE `{$this->getTable('customer/eav_attribute')}` ADD `store_ids` VARCHAR( 255 ) NOT NULL ;

");

$installer->endSetup(); 