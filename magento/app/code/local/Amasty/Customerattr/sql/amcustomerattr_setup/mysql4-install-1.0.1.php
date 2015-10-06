<?php
$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE `{$this->getTable('customer/eav_attribute')}` ADD `is_filterable_in_search` TINYINT( 1 ) UNSIGNED NOT NULL ,
ADD `used_in_product_listing` TINYINT( 1 ) UNSIGNED NOT NULL ;

");

$installer->endSetup(); 