<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is under the Magento root directory in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category    Sunpop
 * @package     Sunpop_Storelocator
 * @copyright   Copyright (c) 2015 Ivan Deng. (http://www.sunpop.cn)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Setup */

$installer->startSetup();

/**
 * Create table 'storelocator/storelocator'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('storelocator'))
    ->addColumn('storelocator_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Store Locator ID')
    ->addColumn('store_name', Varien_Db_Ddl_Table::TYPE_TEXT, 60, array(
        ), 'Store Name')

    ->addColumn('address', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Address')

    ->addColumn('city', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'City')       
        
    ->addColumn('telephone', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Telephone')
    ->addColumn('fax', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Fax')
    ->addColumn('email', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Email')
    ->addColumn('website', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Website')
        
    ->addColumn('lat', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Lat')    
    ->addColumn('lng', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Lng')

    ->addColumn('other_information', Varien_Db_Ddl_Table::TYPE_TEXT, '2M', array(
        ), 'Other Information');
        
$installer->getConnection()->createTable($table);

$installer->endSetup();
?>