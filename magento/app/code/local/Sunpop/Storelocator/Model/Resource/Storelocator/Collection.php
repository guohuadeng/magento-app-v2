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
 * Store Locator Resource Collection Model
 *
 * @author     Qun WU <info@Sunpopwebsolutions.com>
 */
class Sunpop_Storelocator_Model_Resource_Storelocator_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Storelocator table
     *
     * @var string
     */
    protected $_storelocatorTable;

    /**
     * Storelocator detail table
     *
     * @var string
     */
    protected $_storelocatorDetailTable;

    /**
     * Storelocator status table
     *
     * @var string
     */
    protected $_storelocatorStatusTable;

    /**
     * Storelocator entity table
     *
     * @var string
     */
    protected $_storelocatorEntityTable;

    /**
     * Storelocator store table
     *
     * @var string
     */
    protected $_storelocatorStoreTable;

    /**
     * Add store data flag
     * @var bool
     */
    protected $_addStoreDataFlag   = false;

    /**
     * Define module
     *
     */
    protected function _construct()
    {
        $this->_init('storelocator/storelocator');
        $this->_storelocatorTable         = $this->getTable('storelocator/storelocator');
    }

    /**
     * init select
     *
     * @return Mage_Storelocator_Model_Resource_Storelocator_Product_Collection
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        return $this;
    }


    /**
     * Add store filter
     *
     * @param int|array $storeId
     * @return Mage_Storelocator_Model_Resource_Storelocator_Collection
     */
    public function addStoreFilter($storeId)
    {
        $inCond = $this->getConnection()->prepareSqlCondition('store.store_id', array('in' => $storeId));
        $this->getSelect()->join(array('store'=>$this->_storelocatorStoreTable),
            'main_table.storelocator_id=store.storelocator_id',
            array());
        $this->getSelect()->where($inCond);
        return $this;
    }

    /**
     * Add stores data
     *
     * @return Mage_Storelocator_Model_Resource_Storelocator_Collection
     */
    public function addStoreData()
    {
        $this->_addStoreDataFlag = true;
        return $this;
    }

    /**
     * Add status filter
     *
     * @param int|string $status
     * @return Mage_Storelocator_Model_Resource_Storelocator_Collection
     */
    public function addStatusFilter($status)
    {
        if (is_numeric($status)) {
            $this->addFilter('status',
                $this->getConnection()->quoteInto('main_table.status_id=?', $status),
                'string');
        } elseif (is_string($status)) {
            $this->_select->join($this->_storelocatorStatusTable,
                'main_table.status_id='.$this->_storelocatorStatusTable.'.status_id',
                array('status_code'));

            $this->addFilter('status',
                $this->getConnection()->quoteInto($this->_storelocatorStatusTable.'.status_code=?', $status),
                'string');
        }
        return $this;
    }

    /**
     * Set date order
     *
     * @param string $dir
     * @return Mage_Storelocator_Model_Resource_Storelocator_Collection
     */
    public function setDateOrder($dir = 'DESC')
    {
        $this->setOrder('main_table.created_at', $dir);
        return $this;
    }

    /**
     * Load data
     *
     * @param boolean $printQuery
     * @param boolean $logQuery
     * @return Mage_Storelocator_Model_Resource_Storelocator_Collection
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }
        Mage::dispatchEvent('storelocator_storelocator_collection_load_before', array('collection' => $this));
        parent::load($printQuery, $logQuery);
        if ($this->_addStoreDataFlag) {
            $this->_addStoreData();
        }
        return $this;
    }

    /**
     * Add store data
     *
     */
    protected function _addStoreData()
    {
        $adapter = $this->getConnection();

        $storelocatorsIds = $this->getColumnValues('storelocator_id');
        $storesToStorelocators = array();
        if (count($storelocatorsIds)>0) {
            $inCond = $adapter->prepareSqlCondition('storelocator_id', array('in' => $storelocatorsIds));
            $select = $adapter->select()
                ->from($this->_storelocatorStoreTable)
                ->where($inCond);
            $result = $adapter->fetchAll($select);
            foreach ($result as $row) {
                if (!isset($storesToStorelocators[$row['storelocator_id']])) {
                    $storesToStorelocators[$row['storelocator_id']] = array();
                }
                $storesToStorelocators[$row['storelocator_id']][] = $row['store_id'];
            }
        }

        foreach ($this as $item) {
            if (isset($storesToStorelocators[$item->getId()])) {
                $item->setStores($storesToStorelocators[$item->getId()]);
            } else {
                $item->setStores(array());
            }
        }
    }
}
