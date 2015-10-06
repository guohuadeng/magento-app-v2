<?php
$installer = $this;

$installer->startSetup();

try
{
    $installer->run("
    ALTER TABLE `{$this->getTable('customer/eav_attribute')}` ADD  `is_visible_on_front` TINYINT( 1 ) UNSIGNED NOT NULL ;
    ");
} catch (Exception $e)
{
    // do nothing. this field may already present in some older Magento versions
}

$installer->endSetup(); 