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
class Sunpop_Storelocator_Block_Adminhtml_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
    }
    
    protected function _prepareForm()
    {
        $model  = Mage::registry('storelocator_data');

        $form   = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'));
        $form->setHtmlIdPrefix('storelocator_');
        
        $fieldset   = $form->addFieldset('general_fieldset', array(
            'legend'    => Mage::helper('storelocator')->__('General Information'),
            'class'     => 'fieldset-wide'
        ));
        
        if ($model->getStorelocatorId()) {
            $fieldset->addField('storelocator_id', 'hidden', array(
                'name' => 'storelocator_id',
            ));
        }

        $fieldset->addField('store_name', 'text', array(
            'name'      => 'store_name',
            'label'     => Mage::helper('storelocator')->__('Store Name 店名'),
            'title'     => Mage::helper('storelocator')->__('Store Name  店名'),
            'required'  => true
        ));

        $fieldset->addField('address', 'text', array(
            'name'      => 'address',
            'label'     => Mage::helper('storelocator')->__('Address 地址'),
            'title'     => Mage::helper('storelocator')->__('Address 地址'),
            'required'  => true,
            'note'        => '该参数是地理编码的必填项，可以输入三种样式的值，分别是：<br>
                            •标准的地址信息，如北京市海淀区上地十街十号; <br>
                            •名胜古迹、标志性建筑物，如天安门，百度大厦; <br>
                            • 支持 “*路与*路交叉口”描述方式，如北一环路和阜阳路的交叉路口 <br>
                            注意：后两种方式并不总是有返回结果，只有当地址库中存在该地址描述时才有返回。'  
        ));
        
   
        
        $fieldset->addField('city', 'text', array(
            'name'      => 'city',
            'label'     => Mage::helper('storelocator')->__('City 市'),
            'title'     => Mage::helper('storelocator')->__('City 市'),
            'note'      => '地址所在的城市名。该参数是可选项，用于指定上述地址所在的城市，当多个城市都有上述地址时，该参数起到过滤作用。'
        ));
 
  
        
        $fieldset->addField('telephone', 'text', array(
            'name'      => 'telephone',
            'label'     => Mage::helper('storelocator')->__('Telephone'),
            'title'     => Mage::helper('storelocator')->__('Telephone'),
            'required'  => true
        ));

        $fieldset->addField('fax', 'text', array(
            'name'      => 'fax',
            'label'     => Mage::helper('storelocator')->__('Fax'),
            'title'     => Mage::helper('storelocator')->__('Fax'),
            'note'        => 'This is an optional field. If leave blank, the label "Fax" will not show on the store detail page.'
        ));
        
        $fieldset->addField('email', 'text', array(
            'name'      => 'email',
            'label'     => Mage::helper('storelocator')->__('Email'),
            'title'     => Mage::helper('storelocator')->__('Email'),
            'note'        => 'This is an optional field. If leave blank, the label "Email" will not show on the store detail page.'
        ));
                
        $fieldset->addField('website', 'text', array(
            'name'      => 'website',
            'label'     => Mage::helper('storelocator')->__('Website'),
            'title'     => Mage::helper('storelocator')->__('Website'),
            'note'        => 'This is an optional field. If leave blank, the label "Website" will not show on the store detail page.'
        ));
        
        $fieldset->addField('other_information', 'editor', array(
            'name'      => 'other_information',
            'label'     => Mage::helper('storelocator')->__('Other Information'),
            'title'     => Mage::helper('storelocator')->__('Other Information'),
            'style'     => 'height:36em',
            'config'    => Mage::getSingleton('cms/wysiwyg_config')->getConfig(array(
                'files_browser_window_url' => Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index'),
                'directives_url'           => Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive')))
        ));
        
        $fieldset->addField('lat', 'text', array(
            'name'      => 'lat',
            'label'     => Mage::helper('storelocator')->__('Latitude'),
            'title'     => Mage::helper('storelocator')->__('Latitude')
            //'readonly'    => ''
        ));
        
        $fieldset->addField('lng', 'text', array(
            'name'      => 'lng',
            'label'     => Mage::helper('storelocator')->__('Longitude'),
            'title'     => Mage::helper('storelocator')->__('Longitude')
            //'readonly'    => ''
        ));
        
        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
