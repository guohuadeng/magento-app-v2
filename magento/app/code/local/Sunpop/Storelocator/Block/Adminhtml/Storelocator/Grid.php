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
 * Store Locator Adminhtml Grid Block
 *
 * @author     Qun WU <info@Sunpopwebsolutions.com>
 */
class Sunpop_Storelocator_Block_Adminhtml_Storelocator_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('storelocatorGrid');
        // This is the primary key of the database
        $this->setDefaultSort('storelocator_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
 
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('storelocator/storelocator')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
 
    protected function _prepareColumns()
    {
        $this->addColumn('storelocator_id', array(
            'header'    => Mage::helper('storelocator')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'storelocator_id',
        ));
 
        $this->addColumn('name', array(
            'header'    => Mage::helper('storelocator')->__('Store Name'),
            'align'     =>'left',
            'index'     => 'store_name',
        ));
 
        $this->addColumn('address', array(
            'header'    => Mage::helper('storelocator')->__('Address'),
            'align'     => 'left',
            'index'     => 'address',
        ));
        
        $this->addColumn('city', array(
            'header'    => Mage::helper('storelocator')->__('City'),
            'align'     => 'left',
            'index'     => 'city',
        ));
 
         $this->addColumn('lat', array(
            'header'    => Mage::helper('storelocator')->__('Latitude'),
            'align'     => 'left',     
            'index'     => 'lat',
        ));   
  
        $this->addColumn('lng', array(
 
            'header'    => Mage::helper('storelocator')->__('Longitude'),
            'align'     => 'left',
            'index'     => 'lng'
        ));
 
         $this->addExportType('*/*/exportCsv', Mage::helper('storelocator')->__('CSV')); 
        $this->addExportType('*/*/exportExcel', Mage::helper('storelocator')->__('Excel'));
 
        return parent::_prepareColumns();
    }
 
     protected function _prepareMassaction()
    {
        $this->setMassactionIdField('storelocator_id');

        $this->setMassactionIdFieldOnlyIndexValue(true);
        $this->getMassactionBlock()->setFormFieldName('storelocator');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'=> Mage::helper('storelocator')->__('Delete'),
            'url'  => $this->getUrl(
                '*/*/massDelete',
                array('ret' => Mage::registry('usePendingFilter') ? 'pending' : 'index')
            ),
            'confirm' => Mage::helper('storelocator')->__('Are you sure?')
        ));


    }
 
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
 
    public function getGridUrl()
    {
      return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}
 