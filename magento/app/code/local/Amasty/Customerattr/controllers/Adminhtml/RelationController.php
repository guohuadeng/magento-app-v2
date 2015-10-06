<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Adminhtml_RelationController extends Mage_Adminhtml_Controller_Action
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
    	$this->_title($this->__('View Attributes Relations'));
    	
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('amcustomerattr/adminhtml_customer_relation'))
            ->renderLayout();
    }
    
	public function optionsAction()
    {
     	/* @var $relationModel Amasty_Customerattr_Model_Relation */
        $relationModel = Mage::getModel('amcustomerattr/relation');
        
        $attributeId = $this->getRequest()->getParam('attributeId');
        $attributeValues = $relationModel->getAttributeValues($attributeId);

        $html = "";
        foreach ($attributeValues as $value) {
            $html .= '<option value="' . $value['value'] . '">' .  $value['label'] . '</option>';   
        }
        
        $attributes = $relationModel->getUserDefinedAttributes(false, false);
        
    	$depHtml = "";
        foreach ($attributes as $attribute) {
        	
        	/*
        	 * Skip already loaded
        	 */
        	if ($attributeId == $attribute['value']) {
        		continue;
        	}
            $depHtml .= '<option value="' . $attribute['value'] . '">' .  $attribute['label'] . '</option>';   
        }
        
        $result = array(
        	'options' => $html,
        	'dependent' => $depHtml
        );
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
    	$this->_title($this->__('Manage Attributes Relation'));
    	
    	/* @var $relationModel Amasty_Customerattr_Model_Relation */
        $relationModel = Mage::getModel('amcustomerattr/relation');        
        $attributes = $relationModel->getUserDefinedAttributes();
        
        /*
         * Don't have attributes?
         */
        if (count($attributes) == 0) {
        	$message = Mage::helper('amcustomerattr')->__('This attribute no longer exists');
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amcustomerattr')->__('There are no attributes to create relations. Please create at least one attribute of the following type: Multiple Select; Multiple Checkbox Select with Images; Dropdown; Single Radio Select with Images; Customer Group Selector. Which will be used as a parent attribute.'));
			$this->_redirect('*/adminhtml_manage/new');
			return;
        }
        
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('amcustomerattr/relation');

        if ($id) {
            $model->load($id);

            if (! $model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('This attribute no longer exists'));
                $this->_redirect('*/*/');
                return;
            }
        }
        
      	
        

        // set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getAttributeData(true);
       
        if (! empty($data)) { 
            $model->setData($data);
        }

        
        $details = Mage::getModel('amcustomerattr/details')->getResourceCollection()->getByRelation($model->getId());
        Mage::register('entity_relation', $model);
        Mage::register('entity_relation_details', $details);

        $this->_initAction()
            ->_addBreadcrumb($id ? Mage::helper('amcustomerattr')->__('Edit Relation') : Mage::helper('amcustomerattr')->__('New Relation'), $id ? Mage::helper('catalog')->__('Edit Relation') : Mage::helper('catalog')->__('New Relation'))
            ->_addContent($this->getLayout()->createBlock('amcustomerattr/adminhtml_customer_relation_edit'))
            ->_addLeft($this->getLayout()->createBlock('amcustomerattr/adminhtml_customer_relation_edit_tabs'))
            ->renderLayout();
    }

    public function validateAction()
    {
        $response = new Varien_Object();
        $response->setError(false);
        $this->getResponse()->setBody($response->toJson());
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $addToSet       = false;
            
            $redirectBack   = $this->getRequest()->getParam('back', false);
            
            /* @var $model Amasty_Customerattr_Model_Relation */
            $model = Mage::getModel('amcustomerattr/relation');
                        
            if ($id = $this->getRequest()->getParam('id')) {
                $model->load($id);
                
            }
            
            $modelData = array(
            	'name' => $data['name']
			);
            
            $model->addData($modelData);

            try {
                $model->save(); 
                
                /*
                 * Save ID for newly created relation
                 */
                $id = $model->getId();               
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setAttributeData($data);
                $this->_redirect('*/*/edit', array('_current' => true));
                return;
            }
            
            /*
             * Saving details table
             */
            if (!is_array($data['attribute_id'])) {
            	$data['attribute_id'] = array(
            		$data['attribute_id']
            	);
            }
            $details = array(
            	'relation_id' => $id,
            	'attribute_id' => $data['attribute_id'],
            	'option_id' => $data['option_id'],
            	'dependend_attribute_id' => $data['dependend_attribute_id']
            );
            
            /* @var $detailsModel Amasty_Customerattr_Model_Details */
            $detailsModel = Mage::getModel('amcustomerattr/details')->getResource()->saveDetails($details);
            
            
        }
        $this->_redirect('*/*/');
    }
    
 	public function massDeleteAction() {
        $relationIds = $this->getRequest()->getParam('relation');
        if(!is_array($relationIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('amcustomerattr')->__('Please select item(s)'));
        } else {
            try {
                foreach ($relationIds as $relationId) {
                    $menu = Mage::getModel('amcustomerattr/relation')->load($relationId);
                    $menu->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d relation(s) were successfully deleted', count($relationIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('attribute_id')) {
            $model = Mage::getModel('catalog/entity_relation');

            // entity type check
            $model->load($id);
            if ($model->getEntityTypeId() != $this->_entityTypeId) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('catalog')->__('You cannot delete this attribute'));
                $this->_redirect('*/*/');
                return;
            }

            try {
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('amcustomerattr')->__('Customer attribute was successfully deleted'));
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