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

class Sunpop_News_Block_Adminhtml_News_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('newsGrid');
        $this->setDefaultSort('news_time');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('clnews/news')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('news_id', array(
            'header'    => Mage::helper('clnews')->__('ID'),
            'align'     =>'right',
            'width'     => '50',
            'index'     => 'news_id',
        ));

        $this->addColumn('title', array(
            'header'    => Mage::helper('clnews')->__('Title'),
            'align'     =>'left',
            'index'     => 'title',
        ));

        $this->addColumn('url_key', array(
            'header'    => Mage::helper('clnews')->__('URL Key'),
            'align'     => 'left',
            'index'     => 'url_key',
        ));

        $this->addColumn('author', array(
            'header'    => Mage::helper('clnews')->__('Author'),
            'index'     => 'author',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'        => Mage::helper('cms')->__('Store View'),
                'index'         => 'store_id',
                'type'          => 'store',
                'store_all'     => true,
                'store_view'    => true,
                'sortable'      => false,
                'filter_condition_callback'
                                => array($this, '_filterStoreCondition'),
            ));
        }

        $this->addColumn('created_time', array(
            'header'    => Mage::helper('clnews')->__('Created'),
            'align'     => 'left',
            'width'     => '100',
            'type'      => 'datetime',
            'default'   => '--',
            'index'     => 'created_time',
        ));

        $this->addColumn('update_time', array(
            'header'    => Mage::helper('clnews')->__('Updated'),
            'align'     => 'left',
            'width'     => '100',
            'type'      => 'datetime',
            'default'   => '--',
            'index'     => 'update_time',
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('clnews')->__('Status'),
            'align'     => 'left',
            'width'     => '70',
            'index'     => 'status',
            'type'      => 'options',
            'options'   => array(
                1 => Mage::helper('clnews')->__('Enabled'),
                2 => Mage::helper('clnews')->__('Disabled')
            ),
        ));

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('clnews')->__('Action'),
                'width'     => '60',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('clnews')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    ),
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

        $this->addColumn('view_comments',
            array(
                'header'    =>  Mage::helper('clnews')->__('Comments'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('clnews')->__('View comments'),
                        'url'       => array('base'=> 'clnews/adminhtml_comment/index'),
                        'field'     => 'news_id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('news_id');
        $this->getMassactionBlock()->setFormFieldName('clnews');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('clnews')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('clnews')->__('Are you sure?')
        ));

        $statuses = array(
              1 => Mage::helper('clnews')->__('Enabled'),
              0 => Mage::helper('clnews')->__('Disabled')
        );
        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('clnews')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('clnews')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}
