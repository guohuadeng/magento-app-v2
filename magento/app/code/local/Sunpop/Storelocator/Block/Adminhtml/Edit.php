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
 * Store Locator Adminhtml Edit Block
 *
 * @author     Qun WU <info@Sunpopwebsolutions.com>
 */
class Sunpop_Storelocator_Block_Adminhtml_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Edit Block model
     *
     * @var bool
     */
    protected $_editMode = false;
    protected $_blockGroup = 'storelocator';
    protected $_controller = 'adminhtml';
    
    public function __construct()
    {
        $this->_objectId   = 'storelocator_id';
        parent::__construct();
        $this->_updateButton('save', 'onclick', 'searchLocation()');   
    }    

    public function getModel()
    {
        return Mage::registry('storelocator_data');
    }

    protected function _toHtml()
    {
        $javascript = <<<EOT
        
            <script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=xvedUiOsv2sIGuG5Fuh0lk4D"></script>
            <script type="text/javascript">
                function searchLocation() {
                    var city = document.getElementById("storelocator_city").value;
                    var address = document.getElementById("storelocator_address").value; 
                    
                    // 创建地址解析器实例
                    var myGeo = new BMap.Geocoder();
    // 将地址解析结果显示在地图上,并调整地图视野
    myGeo.getPoint(address, function(point){
        if (point) {
            document.getElementById("storelocator_lat").value = point.lat;
            document.getElementById("storelocator_lng").value = point.lng;
            editForm.submit();
        }else{
            alert("The address can not be found!");
        }
    }, city);

   
                }
            </script>
EOT;

        return $javascript . parent::_toHtml();
    }
    
    protected function _prepareLayout()
    {        
        $this->setChild('back_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('storelocator')->__('Back'),
                    'onclick'   => "window.location.href = '" . $this->getUrl('*/*') . "'",
                    'class'     => 'back'
                ))
        );

        $this->setChild('reset_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('storelocator')->__('Reset'),
                    'onclick'   => 'window.location.href = window.location.href'
                ))
        );
        
        $this->setChild('save_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('storelocator')->__('Save'),
                    'onclick'   => 'storelocatorControl.save();',
                    'class'     => 'save'
                ))
        );
        
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('storelocator')->__('Delete'),
                    'onclick'   => 'storelocatorControl._delete();',
                    'class'     => 'delete'
                ))
        );

        return parent::_prepareLayout();
    }

    /**
     * Retrieve Back Button HTML
     *
     * @return string
     */
    public function getBackButtonHtml()
    {
        return $this->getChildHtml('back_button');
    }

    /**
     * Retrieve Reset Button HTML
     *
     * @return string
     */
    public function getResetButtonHtml()
    {
        return $this->getChildHtml('reset_button');
    }

    /**
     * Retrieve Save Button HTML
     *
     * @return string
     */
    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    /**
     * Retrieve Delete Button HTML
     *
     * @return string
     */
    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    public function setEditMode($value = true)
    {
        $this->_editMode = (bool)$value;
        return $this;
    }

    /**
     * Return edit flag for block
     *
     * @return boolean
     */
    public function getEditMode()
    {
        return $this->_editMode;
    }

    /**
     * Return header text for form
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->getEditMode()) {
            return Mage::helper('storelocator')->__('Edit Store');
        }

        return  Mage::helper('storelocator')->__('New Storelocator');
    }

    /**
     * Return form block HTML
     *
     * @return string
     */
    public function getForm()
    {
        return $this->getLayout()
            ->createBlock('storelocator/adminhtml_edit_form')
            ->toHtml();
    }

    /**
     * Return return template name for JS
     *
     * @return string
     */
    public function getJsTemplateName()
    {
        return addcslashes($this->getModel()->getTemplateCode(), "\"\r\n\\");
    }

    /**
     * Return action url for form
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save');
    }

    /**
     * Check Template Type is Plain Text
     *
     * @return bool
     */
    public function isTextType()
    {
        return $this->getModel()->isPlain();
    }

    /**
     * Return delete url for customer group
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', array('id' => $this->getRequest()->getParam('id')));
    }

    /**
     * Retrieve Save As Flag
     *
     * @return int
     */
    public function getSaveAsFlag()
    {
        return $this->getRequest()->getParam('_save_as_flag') ? '1' : '';
    }

    /**
     * Getter for single store mode check
     *
     * @return boolean
     */
    protected function isSingleStoreMode()
    {
        return Mage::app()->isSingleStoreMode();
    }

    /**
     * Getter for id of current store (the only one in single-store mode and current in multi-stores mode)
     *
     * @return boolean
     */
    protected function getStoreId()
    {
        return Mage::app()->getStore(true)->getId();
    }
}
