<?php
/**
 * Sunpop Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Sunpop License Agreement
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://commerce-lab.com/LICENSE.txt
 *
 * @category   Sunpop
 * @package    Sunpop_News
 * @copyright  Copyright (c) 2012 Sunpop Co. (http://commerce-lab.com)
 * @license    http://commerce-lab.com/LICENSE.txt
 */

class Sunpop_News_Model_Mysql4_Category_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract{
    public function _construct(){
        parent::_construct();
        $this->_init('clnews/category');
    }

    /*
    public function toOptionArray(){
        return $this->_toOptionArray('identifier', 'title');
    }

    protected function _afterLoad(){
        $items = $this->getColumnValues('identifier');
        if (count($items)) {
            $select = $this->getConnection()->select()
                    ->from($this->getTable('cat'));
            if ($result = $this->getConnection()->fetchPairs($select)) {
                foreach ($this as $item) {
                    if (!isset($result[$item->getData('identifier')])) {
                        continue;
                    }
                }
            }
        }

        parent::_afterLoad();
    }

    public function addCatFilter($catId)
    {
        if (!Mage::app()->isSingleStoreMode()) {
            $this->getSelect()->join(
                array('cat_table' => $this->getTable('post_cat')),
                'main_table.post_id = cat_table.post_id',
                array()
            )
            ->where('cat_table.cat_id = ?', $catId);

            return $this;
        }
        return $this;
    }
    */
    public function addStoreFilter($store){
        if (!Mage::app()->isSingleStoreMode()) {
            if ($store instanceof Mage_Core_Model_Store) {
                $store = array($store->getId());
            }

            $this->getSelect()->joinLeft(
                array('store_table' => $this->getTable('category_store')),
                'main_table.category_id = store_table.category_id',
                array()
            )
            ->where('store_table.store_id = 0
                    OR store_table.store_id = \''.$store.'\'
                    OR store_table.store_id IS NULL
            ');

            return $this;
        }
        return $this;
    }
    /*
    public function addPostFilter($postId){
        $this->getSelect()->join(
            array('cat_table' => $this->getTable('post_cat')),
            'main_table.cat_id = cat_table.cat_id',
            array()
        )
        ->where('cat_table.post_id = ?', $postId);

        return $this;
    }
    */

    protected function _afterLoad()
    {
        foreach($this as $item) {
            $stores = $this->lookupStoreIds($item->getId());
            $item->setData('store_id', $stores);
        }
        return parent::_afterLoad();
    }
    
    public function lookupStoreIds($objectId)
    {
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');
    
        $tableName = Mage::getSingleton('core/resource')->getTableName('clnews_category_store');
        $select  = $adapter->select()
        ->from($tableName, 'store_id')
        ->where('category_id = ?',(int)$objectId);
    
        return $adapter->fetchCol($select);
    }
}
