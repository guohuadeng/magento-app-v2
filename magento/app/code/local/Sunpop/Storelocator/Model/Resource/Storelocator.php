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
 
/**
 * Store Locator Resource Model
 *
 * @author     Qun WU <info@Sunpopwebsolutions.com>
 */
class Sunpop_Storelocator_Model_Resource_Storelocator extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Storelocator table
     *
     * @var string
     */
    protected $_storelocatorTable;

    /**
     * Cache of deleted rating data
     *
     * @var array
     */
    private $_deleteCache   = array();

    /**
     * Define main table. Define other tables name
     *
     */
    protected function _construct()
    {
        $this->_init('storelocator/storelocator', 'storelocator_id');
        $this->_storelocatorTable = $this->getTable('storelocator/storelocator');
 
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param unknown_type $object
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        return $select;
    }

    /**
     * Perform actions before object save
     *
     * @param Varien_Object $object
     * @return Mage_Storelocator_Model_Resource_Storelocator
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        return $this;
    }

    /**
     * Perform actions after object save
     *
     * @param Varien_Object $object
     * @return Mage_Storelocator_Model_Resource_Storelocator
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $adapter = $this->_getWriteAdapter();
        /**
         * save detail
         */
        $data = array(
            'store_name'     => $object->getStoreName(),
            'address'    => $object->getAddress(),
		    'city'    => $object->getCity(),
            'lat'  => $object->getLat(),
            'lng'  => $object->getLng()            
        );
        $select = $adapter->select()
            ->from($this->_storelocatorTable, 'storelocator_id')
            ->where('storelocator_id = ' . $object->getId());
        $storelocatorId = $adapter->fetchOne($select);

        if ($storelocatorId) {
            $condition = array("storelocator_id = ?" => $storelocatorId);
            $adapter->update($this->_storelocatorTable, $data, $condition);
        } else {

            $adapter->insert($this->_storelocatorTable, $data);
        }

        return $this;
    }

    /**
     * Perform actions after object load
     *
     * @param Varien_Object $object
     * @return Mage_Storelocator_Model_Resource_Storelocator
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        return $this;
    }

    /**
     * Action before delete
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Storelocator_Model_Resource_Storelocator
     */
    protected function _beforeDelete(Mage_Core_Model_Abstract $object)
    {
        // prepare rating ids, that depend on storelocator
        $this->_deleteCache = array(
        );
        return $this;
    }

    /**
     * Perform actions after object delete
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Storelocator_Model_Resource_Storelocator
     */
    public function afterDeleteCommit(Mage_Core_Model_Abstract $object)
    {
        $this->_deleteCache = array();

        return $this;
    }

    /**
     * Get storelocator entity type id by code
     *
     * @param string $entityCode
     * @return int|bool
     */
    public function getEntityIdByCode($entityCode)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->_storelocatorEntityTable, array('entity_id'))
            ->where('entity_code = :entity_code');
        return $adapter->fetchOne($select, array(':entity_code' => $entityCode));
    }

    /**
     * Delete storelocators by product id.
     * Better to call this method in transaction, because operation performed on two separated tables
     *
     * @param int $productId
     * @return Mage_Storelocator_Model_Resource_Storelocator
     */
    public function deleteStorelocatorsByProductId($productId)
    {
        $this->_getWriteAdapter()->delete($this->_storelocatorTable, array(
            'entity_pk_value=?' => $productId,
            'entity_id=?' => $this->getEntityIdByCode(Mage_Storelocator_Model_Storelocator::ENTITY_PRODUCT_CODE)
        ));
        $this->_getWriteAdapter()->delete($this->getTable('storelocator/storelocator_aggregate'), array(
            'entity_pk_value=?' => $productId,
            'entity_type=?' => $this->getEntityIdByCode(Mage_Storelocator_Model_Storelocator::ENTITY_PRODUCT_CODE)
        ));
        return $this;
    }
}
