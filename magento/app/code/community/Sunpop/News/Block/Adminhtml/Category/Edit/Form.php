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

class Sunpop_News_Block_Adminhtml_Category_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $this->getPosition();
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post',
        ));

        $fieldset = $form->addFieldset('category_form',
            array('legend'=>Mage::helper('clnews')->__('Category Information')));

        $fieldset->addField('title', 'text', array(
            'label'     => Mage::helper('clnews')->__('Title'),
            'title'     => Mage::helper('clnews')->__('Title'),
            'name'      => 'title',
            'required'  => true
        ));

        $fieldset->addField('url_key', 'text', array(
            'label'     => Mage::helper('clnews')->__('URL Key'),
            'title'     => Mage::helper('clnews')->__('URL Key'),
            'name'      => 'url_key',
            'required'  => true
        ));

        $fieldset->addField('sort_order', 'text', array(
            'label'     => Mage::helper('clnews')->__('Sort Order'),
            'name'      => 'sort_order',
        ));

        $fieldset->addField('sort_id', 'hidden', array(
                'name' => 'sort_id',
                'values' => $this->getPosition(),
        ));
        if ($this->getRequest()->getParam('parent_id') == null) {
            if (Mage::getSingleton('adminhtml/session')->getNewsData()) {
                    $data = array('sort_id' => $this->getPosition());
                    Mage::getSingleton('adminhtml/session')->setNewsData($data);
            } else if ($data = Mage::registry('clnews_data')) {
                if ($data->getSortId() == null) {
                    $params = array('sort_id' => $this->getPosition());
                    $data->setData($params);
                    Mage::unregister('clnews_data');
                    Mage::register('clnews_data', $data);
                }
            }
        }

        /**
         * Check is single store mode
         */
        $parentStore = array();
        if ($pid = $this->getRequest()->getParam('parent_id')) {
            $fieldset->addField('parent_id', 'hidden', array(
                'name' => 'parent_id',
                'values' => $pid,
            ));
            $category = Mage::getModel('clnews/category')->load($pid);
            if ($lev = $category->getLevel()) {
                $level = $lev + 1;
            } else {
                $level = '1';
            }
            $fieldset->addField('level', 'hidden', array(
                'name' => 'level',
                'values' => $level,
            ));
            if (Mage::getSingleton('adminhtml/session')->getNewsData()) {
                $data = array('parent_id' => $pid, 'level' => $level, 'sort_id' => $this->getPosition());
                Mage::getSingleton('adminhtml/session')->setNewsData($data);
            } else if ($data = Mage::registry('clnews_data')) {
                $params = array('parent_id' => $pid, 'level' => $level, 'sort_id' => $this->getPosition());
                $data->setData($params);
                Mage::unregister('clnews_data');
                Mage::register('clnews_data', $data);
            }
            $store = $category->getStoreId();

            $stores = Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true);
            foreach ($stores as $val) {
                if (is_array($val['value'])) {
                    foreach ($val['value'] as $st) {
                        if ($st['value'] == $store[0]) {
                            $parentStore[] = $st;
                        }
                    }
                } else {
                    if ($val['value'] == $store[0]) {
                        $parentStore[] = $val;
                    }
                }
            }

        } else {
            $fieldset->addField('level', 'hidden', array(
                'name' => 'level',
                'values' => 0,
            ));

            $fieldset->addField('parent_id', 'hidden', array(
                'name' => 'parent_id',
                'values' => 0,
            ));
        }

        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_id', 'multiselect', array(
                'name'      => 'stores[]',
                'label'     => Mage::helper('cms')->__('Store View'),
                'title'     => Mage::helper('cms')->__('Store View'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            ));
        }

        $fieldset->addField('meta_keywords', 'editor', array(
            'name' => 'meta_keywords',
            'label' => Mage::helper('clnews')->__('Keywords'),
            'title' => Mage::helper('clnews')->__('Meta Keywords'),
        ));

        $fieldset->addField('meta_description', 'editor', array(
            'name' => 'meta_description',
            'label' => Mage::helper('clnews')->__('Description'),
            'title' => Mage::helper('clnews')->__('Meta Description'),
        ));

        if ( Mage::getSingleton('adminhtml/session')->getNewsData() ) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getNewsData());
            Mage::getSingleton('adminhtml/session')->setNewsData(null);
        } elseif ( Mage::registry('clnews_data') ) {
            $form->setValues(Mage::registry('clnews_data')->getData());
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function getPosition()
    {
        if ($id = $this->getRequest()->getParam('parent_id')) {
            $collection = Mage::getModel('clnews/category')->getCollection();
            $collection->getSelect()->order('main_table.sort_id DESC');
            $collection->getSelect()->where('main_table.parent_id =?', $id);
            $collection->getSelect()->limit(1);
            $sortId = $collection->getData('sort_id');
            if (count($sortId) < 1) {
                unset($sortId);
                $collectionNew = Mage::getModel('clnews/category')->getCollection();
                $collectionNew->getSelect()->where('main_table.category_id =?', $id);

            $sortId = $collectionNew->getData('sort_id');
            }
            $position = $sortId[0]['sort_id'] + 1;
            if (count($this->checkPosition($position)) > 0) {
                $this->updatePosition($position);
            }
            return $position;
        } else if ($id = $this->getRequest()->getParam('id')) {
            $collection = Mage::getModel('clnews/category')->getCollection();
            $collection->getSelect()->where('main_table.category_id =?', $id);
            $sortId = $collection->getData('sort_id');
            $position = $sortId[0]['sort_id'];
            return $position;
        } else {
            $collection = Mage::getModel('clnews/category')->getCollection();
            $collection->getSelect()->order('main_table.sort_id DESC');
            $collection->getSelect()->limit(1);
            if (count($collection) < 1) {
                $position = 0;
            } else {
                $sortId = $collection->getData('sort_id');
                $position = $sortId[0]['sort_id'] + 1;
                if (count($this->checkPosition($position)) > 0) {
                    $this->updatePosition($position);
                }
            }
            return $position;
        }
    }

    protected function checkPosition($pos)
    {
        $collection = Mage::getModel('clnews/category')->getCollection();
        $collection->getSelect()->where('sort_id =?', $pos);
        return $collection->getData();
    }

    protected function updatePosition($pos)
    {
        $collection = Mage::getModel('clnews/category')->getCollection();

        foreach ($collection as $category) {
            if ($category->getSortId() >= $pos) {
                $category->setSortId($category->getSortId() + 10);
                try {
                    $category->save();
                } catch (Exception $ex) {
                    echo 'you have a problem with saving category!!!!' . "\n";
                }
            }
        }
    }
}
