<?php
$installer = $this;

$installer->startSetup();

$installer->run("
    ALTER TABLE `{$this->getTable('customer/eav_attribute')}` ADD `on_registration` TINYINT( 1 ) UNSIGNED NOT NULL ;
");

$installer->endSetup(); 