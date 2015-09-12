<?php
//安装3，生成目录属性，主要是确定哪个是虚拟产品目录
require_once('app/Mage.php');
Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));
$installer = new Mage_Catalog_Model_Resource_Setup;
$attribute  = array(
    'type' => 'int',
    'label'=> '是否虚拟产品目录',
    'input' => 'select',
    'source' => 'eav/entity_attribute_source_boolean',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible' => true,
    'required' => false,
    'user_defined' => true,
    'default' => '',
    'group' => 'General Information'
);
$installer->removeAttribute('catalog_category', 'a_cat_virtual');
$installer->addAttribute('catalog_category', 'a_cat_virtual', $attribute);

$installer->endSetup();
echo __file__;
echo '3增加目录属性成功';
?>