<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Adminhtml_ManageController extends Mage_Adminhtml_Controller_Action
{
    protected $_entityTypeId;

    public function preDispatch()
    {
        parent::preDispatch();
        $this->_entityTypeId = Mage::getModel('eav/entity')->setType('customer')->getTypeId();
    }

    protected function _initAction()
    {
        if($this->getRequest()->getParam('popup')) {
            $this->loadLayout('popup');
        } else {
            $this->loadLayout()
                ->_setActiveMenu('customer/customerattr')
                ->_addBreadcrumb(Mage::helper('customer')->__('Customers'), Mage::helper('customer')->__('Customers'))
                ->_addBreadcrumb(Mage::helper('customer')->__('Manage Customer Attributes'), Mage::helper('customer')->__('Manage Customer Attributes'))
            ;
        }
        return $this;
    }

    public function indexAction()
    {
    	$this->_title($this->__('View Customer Attributes'));
    	
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('amcustomerattr/adminhtml_customer_attribute'))
            ->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('attribute_id');
        $model = Mage::getModel('catalog/entity_attribute');

        if ($id) {
            $model->load($id);

            if (! $model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('This attribute no longer exists'));
                $this->_redirect('*/*/');
                return;
            }

            // entity type check
            if ($model->getEntityTypeId() != $this->_entityTypeId) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('You cannot edit this attribute'));
                $this->_redirect('*/*/');
                return;
            }
        }

        // set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getAttributeData(true);
       
        if (! empty($data)) {
            $model->setData($data);
        }
        
        if ($model->getTypeInternal())
        {
            // will switch back again on save
            $model->setFrontendInput($model->getTypeInternal());
        }

        Mage::register('entity_attribute', $model);
        
		$this->_title($this->__('Manage Customer Attribute'));
             
             
        

        $this->_initAction()
            ->_addBreadcrumb($id ? Mage::helper('amcustomerattr')->__('Edit Customer Attribute') : Mage::helper('amcustomerattr')->__('New Customer Attribute'), $id ? Mage::helper('catalog')->__('Edit Product Attribute') : Mage::helper('catalog')->__('New Product Attribute'))
            ->_addContent($this->getLayout()->createBlock('amcustomerattr/adminhtml_customer_attribute_edit')->setData('action', $this->getUrl('*/catalog_product_attribute/save')))
            ->_addLeft($this->getLayout()->createBlock('amcustomerattr/adminhtml_customer_attribute_edit_tabs'))
            ->_addJs(
                $this->getLayout()->createBlock('adminhtml/template')
                    ->setIsPopup((bool)$this->getRequest()->getParam('popup'))
                    ->setTemplate('amasty/amcustomerattr/attribute/js.phtml')
            )
            ->renderLayout();
    }

    public function validateAction()
    {
        $response = new Varien_Object();
        $response->setError(false);

        $attributeCode  = $this->getRequest()->getParam('attribute_code');
        $attributeId    = $this->getRequest()->getParam('attribute_id');
        $attribute = Mage::getModel('catalog/entity_attribute')
            ->loadByCode($this->_entityTypeId, $attributeCode);

        if ($attribute->getId() && !$attributeId) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('Attribute with the same code already exists'));
            $this->_initLayoutMessages('adminhtml/session');
            $response->setError(true);
            $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
        }

        $this->getResponse()->setBody($response->toJson());
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $addToSet       = false;
            
            $redirectBack   = $this->getRequest()->getParam('back', false);
            $model = Mage::getModel('customer/attribute');
            
//            $model = Mage::getModel('catalog/entity_attribute');
            /* @var $model Mage_Catalog_Model_Entity_Attribute */

            if ($id = $this->getRequest()->getParam('attribute_id')) {

                $model->load($id);

                // entity type check
                if ($model->getEntityTypeId() != $this->_entityTypeId) {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('You cannot update this attribute'));
                    Mage::getSingleton('adminhtml/session')->setAttributeData($data);
                    $this->_redirect('*/*/');
                    return;
                }

                $data['attribute_code']  = $model->getAttributeCode();
                $data['is_user_defined'] = $model->getIsUserDefined();
                $data['frontend_input']  = $model->getFrontendInput();
            }
            
            if ('multiselectimg' == $data['frontend_input'])
            {
                $data['frontend_input'] = 'multiselect';
                $data['type_internal'] = 'multiselectimg';
            }
            
            if ('selectimg' == $data['frontend_input'])
            {
                $data['frontend_input'] = 'select';
                $data['type_internal'] = 'selectimg';
            } 
            
            if ('selectgroup' == $data['frontend_input'])
            {
                $data['frontend_input'] = 'select';
                $data['type_internal'] = 'selectgroup';
            }
            

            /*
             * Read Only Attribute
             */

            if (!isset($data['is_configurable'])) {
                $data['is_configurable'] = 0;
            }

            if (is_null($model->getIsUserDefined()) || $model->getIsUserDefined() != 0) {
                $data['backend_type'] = $model->getBackendTypeByInput($data['frontend_input']);
            }
            
            if(!isset($data['apply_to'])) {
                $data['apply_to'] = array();
            }

            if ('boolean' == $data['frontend_input'])
            {
                $data['source_model'] = 'eav/entity_attribute_source_boolean';
            }
            
            if ('multiselect' == $data['frontend_input'])
            {
                $data['source_model'] = 'eav/entity_attribute_source_table';
            }
            
        	if ('statictext' == $data['frontend_input'])
            {
            	$data['frontend_input'] = 'textarea'; 
            	$data['type_internal'] = 'statictext';
                $data['backend_type'] = 'text';
            }
            
            $defaultValueField = $model->getDefaultValueByInput($data['frontend_input']);
            if ($defaultValueField) {
                $data['default_value'] = $this->getRequest()->getParam($defaultValueField);
            }
            
            if ('file' == $data['frontend_input'])
            {
                $data['frontend_input'] = 'file'; 
                $data['type_internal'] = 'file';
                $data['backend_type'] = 'varchar';
            }
            
            $data['store_ids'] = '';
            
            if ($data['stores'])
            {
                if (is_array($data['stores']))
                {
                    $data['store_ids'] = implode(',', $data['stores']);
                } else 
                {
                    $data['store_ids'] = $data['stores'];
                }
                unset($data['stores']);
            }
            
            $requiredOnFront = false;
            if (2 == $data['is_required']) {
                $requiredOnFront = true;
                $data['is_required'] = 0;
            }
                
            /**
             * @todo need specify relations for properties
             */
            if (isset($data['frontend_input']) && ($data['frontend_input'] == 'multiselect')) {
                $data['backend_model'] = 'eav/entity_attribute_backend_array';
            }
            

            $model->addData($data);
            
            if (!$id) {
                $model->setEntityTypeId($this->_entityTypeId);
                $model->setIsUserDefined(1);
                $addToSet = true;
            }


            if($this->getRequest()->getParam('set') && $this->getRequest()->getParam('group')) {
                // For creating product attribute on product page we need specify attribute set and group
                $model->setAttributeSetId($this->getRequest()->getParam('set'));
                $model->setAttributeGroupId($this->getRequest()->getParam('group'));
            }

            try {
                $model->save();
                
                
                // saving Show on Manage Customers Grid, Show on Orders Grid and Show on Billing During Checkout
                $configuration = array(
                    'is_filterable_in_search'   => Mage::app()->getRequest()->getPost('is_filterable_in_search'),
                    'used_in_product_listing'   => Mage::app()->getRequest()->getPost('used_in_product_listing'),
                    'used_in_order_grid'        => Mage::app()->getRequest()->getPost('used_in_order_grid'),
                    'store_ids'                 => $data['store_ids'],
                    'is_visible_on_front' => $data['is_visible_on_front'],
                );
                if ($requiredOnFront) {
                    $configuration['required_on_front'] = 1;
                } else {
                    $configuration['required_on_front'] = 0;
                }
                
                $model->getResource()->saveAttributeConfiguration($model->getId(), $configuration);
                
                // adding attribute to set
                if ($addToSet)
                {
                    $setup     = new Mage_Eav_Model_Entity_Setup('amcustomerattr');
                    $attrSetId = Mage::getModel('customer/customer')->getResource()->getEntityType()->getDefaultAttributeSetId();
                    $setup->addAttributeToSet('customer', $attrSetId, 'General', $model->getAttributeCode());
                }
                
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('amcustomerattr')->__('Customer attribute was successfully saved'));

                /**
                 * Clear translation cache because attribute labels are stored in translation
                 */
                Mage::app()->cleanCache(array(Mage_Core_Model_Translate::CACHE_TAG));
                Mage::getSingleton('adminhtml/session')->setAttributeData(false);
                if ($this->getRequest()->getParam('popup')) {
                    $this->_redirect('adminhtml/catalog_product/addAttribute', array(
                        'id'       => $this->getRequest()->getParam('product'),
                        'attribute'=> $model->getId(),
                        '_current' => true
                    ));
                } elseif ($redirectBack) {
                    $this->_redirect('*/*/edit', array('attribute_id' => $model->getId(),'_current'=>true));
                } else {
                    $this->_redirect('*/*/', array());
                }
                return;
            } catch (Exception $e) {
                if (false !== strpos($e->getMessage(), 'Setup.php')) {
                    $this->_redirect('*/*/', array());
                    return;
                }
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setAttributeData($data);
                $this->_redirect('*/*/edit', array('_current' => true));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('attribute_id')) {
            $model = Mage::getModel('catalog/entity_attribute');

            // entity type check
            $model->load($id);
            if ($model->getEntityTypeId() != $this->_entityTypeId) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('You cannot delete this attribute'));
                $this->_redirect('*/*/');
                return;
            }

            try {
                $model->delete();
                $relationDetails = Mage::getModel('amcustomerattr/details')->usedInRelation($id);
                if ($relationDetails->getSize() > 0) {
                    $relationIds = array();
                    $deleteRelationDetails = array();
                    foreach ($relationDetails as $relation) {
                        if (!in_array($relation->getRelationId(), $relationIds)) {
                            $relationIds[] = $relation->getRelationId();
                        }
                        $deleteRelationDetails[] = $relation->getId();
                    }
                    Mage::getModel('amcustomerattr/details')->fastDelete($deleteRelationDetails);
                    $deleteRelationNames = array();
                    foreach ($relationIds as $relationId) {
                        if (!Mage::getModel('amcustomerattr/details')->haveDetails($relationId)) {
                            $deleteRelationNames[] = $relationId;
                        }
                    }
                    if ($deleteRelationNames) {
                        Mage::getModel('amcustomerattr/relation')->fastDelete($deleteRelationNames);
                    }
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('amcustomerattr')->__('Customer attribute was successfully deleted (this attribute`s relations were successfully deleted, too)'));
                } else {
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('amcustomerattr')->__('Customer attribute was successfully deleted'));
                }
                $this->_redirect('*/*/');
                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('attribute_id' => $this->getRequest()->getParam('attribute_id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('Unable to find an attribute to delete'));
        $this->_redirect('*/*/');
    }
}