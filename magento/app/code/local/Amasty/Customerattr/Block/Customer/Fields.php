<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
* @package Amasty_Customerattr
*/ 
class Amasty_Customerattr_Block_Customer_Fields extends Mage_Core_Block_Template
{
    protected $_entityTypeId;
    protected $_formElements = null;
    public static $renderedElements = array();
    protected $_fieldsToRender = array();
    protected $_checkouts = array('checkout', 'onestepcheckout');
    protected $_hasRequired = false;
    protected $_hasValidation = false;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amasty/amcustomerattr/customer_fields.phtml');
        
        $this->_entityTypeId = Mage::getModel('eav/entity')->setType('customer')->getTypeId();
    }
    
    protected function _getElementsToRender()
    {
    	$elements = $this->getData('fields');
    	$newElements = array();
    	foreach ($elements as $key => $code) {
    		if (!Amasty_Customerattr_Block_Customer_Fields::elementAlreadyRendered($code)) {
	    		$newElements[] = $code;
    		}
    	}
    	$this->_fieldsToRender = $newElements;
    	return $newElements;
    }
    
    public static function elementAlreadyRendered($code)
    {
    	$elements = self::$renderedElements;
    	 
    	if (!in_array($code, $elements)) {
    		self::$renderedElements[] = $code;
    		return false;
    	} else {
    		return true;
    	}
    }
    
    
    /**
     * Enter description here ...
     * @return Ambigous <mixed, NULL, multitype:>
     */
    public function getAttributes()
    {
    	if (!Mage::registry('amcustomerattr_attributes')) {
    		$collection = Mage::getModel('customer/attribute')->getCollection();
            
            $alias = Mage::helper('amcustomerattr')->getProperAlias($collection->getSelect()->getPart('from'), 'eav_attribute');
	        $collection->addFieldToFilter($alias . 'is_user_defined', 1);
            $collection->addFieldToFilter($alias . 'entity_type_id', $this->_entityTypeId);
            
            $alias = Mage::helper('amcustomerattr')->getProperAlias($collection->getSelect()->getPart('from'), 'customer_eav_attribute');
	        if (Mage::getSingleton('customer/session')->isLoggedIn() && !Mage::getStoreConfig('amcustomerattr/general/allow_change_group')) {
	        	$collection->addFieldToFilter($alias . 'type_internal', array('neq'=> 'selectgroup'));
			}
            
            if ($this->_isCustomerEdit()) {
                $collection->addFieldToFilter($alias . 'is_visible_on_front', 1);
            }
	        
	        if ($this->_isCheckout()) {
	            // Show on Billing During Checkout
	            $collection->addFieldToFilter($alias . 'used_in_product_listing', 1);
	        }
	        
	        $collection->getSelect()->order($alias . 'sorting_order');
	        
	        if ($this->_isRegistration())
	        {
	            $collection->addFieldToFilter($alias . 'on_registration', 1);
	        }
	        
	        $attributes = $collection->load();
	        
    		Mage::register('amcustomerattr_attributes', $attributes);
    	}
    	return Mage::registry('amcustomerattr_attributes');
    }
    
    public function getFormElements()
    {
        if (!is_null($this->_formElements)) {
            return $this->_formElements;
        }
        
        $attributes = $this->getAttributes();
        
        $attributesToRender = $this->_getElementsToRender();
        
        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('amcustomerattr' . rand(0, 100) , array('class' => 'amcustomerattr'));
        
        /**
        * Loading current customer, it we are on edit page
        */
        $customer = Mage::getSingleton('customer/session')->isLoggedIn() ? Mage::getSingleton('customer/session')->getCustomer() : null;
        
        $isAnyAttributeApplies = false;
        
        $customerAttributes = Mage::getSingleton('customer/session')->getAmcustomerattr();
        
        foreach ($attributes as $attribute)
        {
        	if (!empty($attributesToRender) && !in_array($attribute->getAttributeCode(), $attributesToRender)) {
        		continue;
        	}
            if (($attribute->getAccountFilled() && $this->_isCustomerEdit() && $customer->getData($attribute->getAttributeCode())) ||
                ($attribute->getBillingFilled() && $this->_isCheckout() && $customer && $customer->getData($attribute->getAttributeCode()))) {
                continue;
            }
            $currentStore = ($customer ? $customer->getStoreId() : Mage::app()->getStore()->getId());
            $storeIds = explode(',', $attribute->getData('store_ids'));
            
            $defaultValue = $attribute->getDefaultValue();

            if (!in_array($currentStore, $storeIds) && (0 != $currentStore) && !in_array(0, $storeIds)) {
                continue;
            }
            
            $isAnyAttributeApplies = true;
            
            if ($inputType = $attribute->getFrontend()->getInputType())
            {
                if ($attribute->getTypeInternal())
                {
                	if ('statictext' == $attribute->getTypeInternal()){
                       $inputType = 'note'; 
                    } else if ('selectgroup' == $attribute->getTypeInternal()){
                       $inputType = 'select'; 
                    } else {
                        $inputType = $attribute->getTypeInternal();
                    }
                }
                $fieldType      = $inputType;
                $rendererClass  = $attribute->getFrontend()->getInputRendererClass();
                if (!empty($rendererClass)) {
                    $fieldType  = $inputType . '_' . $attribute->getAttributeCode();
                    $fieldset->addType($fieldType, $rendererClass);
                }

                $fieldName = $this->_isCheckout() 
                                    ? 'billing[amcustomerattr][' . $attribute->getAttributeCode(). ']'
                                    : 'amcustomerattr[' . $attribute->getAttributeCode() . ']';
                if ('file' == $inputType) {
                    $fieldName = 'amcustomerattr_' . $attribute->getAttributeCode();
                }
                                    
                // default_value
                $attributeValue = '';
                if ($customer) {
                    $attributeValue = $customer->getData($attribute->getAttributeCode());
                } elseif ($attribute->getData('default_value')) {
                    $attributeValue = $attribute->getData('default_value');
                }
                
                // if for example there was page reload with error, we putting attribute back from session
                if (isset($customerAttributes[$attribute->getAttributeCode()])) {
                    $attributeValue = $customerAttributes[$attribute->getAttributeCode()];
                }
                
                $fileAttributeValue = '';
                if ('file' == $inputType) {
                    $fileAttributeValue = $attributeValue;
                    $attributeValue = '';
                }
                
                // applying translations
                $translations = $attribute->getStoreLabels();
                if (isset($translations[Mage::app()->getStore()->getId()])) {
                    $attributeLabel = $translations[Mage::app()->getStore()->getId()];
                } else {
                    $attributeLabel = $attribute->getFrontend()->getLabel();
                }
                
                $required = 0;
                if ($attribute->getIsRequired() || $attribute->getRequiredOnFront()) {
                    $required = 1;
                    $this->_hasRequired = true;
                }

                if ($attribute->getFrontend()->getClass()) {
                    $this->_hasValidation = true;
                }

                $config = array(
                    'name'      => $fieldName,
                    'label'     => $attributeLabel,
                    'class'     => $attribute->getFrontend()->getClass(),
                    'required'  => $required,
                    'disabled'  => $attribute->getIsReadOnly(),
                    'note'      => $attribute->getNote(),
                    'value'     => $attributeValue,
                    'text'      => $attributeValue,
                );

                /*if ('selectgroup' == $attribute->getTypeInternal()
                    && Mage::getSingleton('customer/session')->isLoggedIn()
                    && !Mage::getStoreConfig('amcustomerattr/general/allow_change_group')) {
                    $config['disabled'] = 1;
                }*/

                $afterElementHtml = '';
                
                if ('date' == $inputType) {
                    $config['readonly'] = 1;
                    $config['onclick'] = 'amcustomerattr_trig(' . $attribute->getAttributeCode() . '_trig)';
                    $afterElementHtml .= '<script type="text/javascript">'
                                         . 'function amcustomerattr_trig(id)'
                                         . '{ $(id).click(); }'
                                         . '</script>';
                }
                
                $element = $fieldset->addField($attribute->getAttributeCode(), $fieldType, $config)->setEntityAttribute($attribute);
                
                if ('file' == $inputType) {
                    if ($fileAttributeValue) {
                        // to Controller
                        $fileName = Mage::helper('amcustomerattr')->cleanFileName($fileAttributeValue);
                        $downloadUrl = Mage::helper('amcustomerattr')->getAttributeFileUrl($fileAttributeValue, true, true, $customer->getId());
                        $afterElementHtml .= '<br /><a href="'. $downloadUrl .'"><img alt="' . Mage::helper('amcustomerattr')->__('Download File') . '" title="' . Mage::helper('amcustomerattr')->__('Download File') . '" src="' . Mage::getDesign()->getSkinUrl('images/fam_bullet_disk.gif') . '" class="v-middle"></a>'
                            . '<a href="'. $downloadUrl .'">'. $fileName[3] . '</a><br />'
                            . '<input type="checkbox" id="' . $attribute->getAttributeCode() . '_delete_file" name="amcustomerattr_delete[' . $attribute->getAttributeCode() . ']" value="' . $fileAttributeValue . '" /> Delete File'
                            . '<input type="hidden" id="' . $attribute->getAttributeCode() . '" name="amcustomerattr[' . $attribute->getAttributeCode() . ']" value="' . $fileAttributeValue . '" />'
                            . '<div style="padding: 4px;"></div>';
                    } else {
                        $afterElementHtml .= '<input type="hidden" id="' . $attribute->getAttributeCode() . '" name="amcustomerattr[' . $attribute->getAttributeCode() . ']" value="" />'
                            . '<div style="padding: 4px;"></div>';
                    }
                } else {
                    $element->setText($attributeValue);
                    $afterElementHtml .= '<div style="padding: 4px;"></div>';
                }
                
                $element->setAfterElementHtml($afterElementHtml);
                
                if ($inputType == 'select' || $inputType == 'selectimg' || $inputType == 'multiselect' || $inputType == 'multiselectimg') {
                    
                    // getting values translations
                    $valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                        ->setAttributeFilter($attribute->getId())
                        ->setStoreFilter(Mage::app()->getStore()->getId(), false)
                        ->load();
                    foreach ($valuesCollection as $item) {
                        $values[$item->getId()] = $item->getValue();
                    }
                    
                    // applying translations
                    $options = $attribute->getSource()->getAllOptions(true, true);
                    foreach ($options as $i => $option)
                    {
                        if (isset($values[$option['value']]))
                        {
                            $options[$i]['label'] = $values[$option['value']];
                        }
                        if ($defaultValue == $option['value'])
                        {
                            $options[$i]['default'] = true;
                        }
                    }
                    $element->setValues($options);
                } elseif ($inputType == 'date') {
                	$dateImage = $this->getSkinUrl('images/grid-cal.gif');
                	if ($attribute->getIsReadOnly()) {
                		$dateImage = '';
                	}
                	
                    $element->setImage($dateImage);
                    
                    $element->setFormat(Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
                }
            }
        }
        if ($isAnyAttributeApplies)
        {
            $this->_formElements = $form->getElements();
        } else 
        {
            $this->_formElements = array();
        }
        return $this->_formElements;
    }
    
    protected function _toHtml()
    {
        if (!$this->getFormElements()) {
            return '';
        }
        $html = parent::_toHtml();
        $html = str_replace('</label>', '</label><div style="clear: both;"></div>', $html);
        return $html;
    }
    
    protected function _isRegistration()
    {
        return ('create' == Mage::app()->getRequest()->getActionName());
    }
    
    protected function _isCheckout()
    {
        return in_array(Mage::app()->getRequest()->getModuleName(), $this->_checkouts);
    }
    
    public function isShowHeader()
    {
        return (!$this->_isCheckout());
    }
    
    protected function _isCustomerEdit()
    {
        return ('edit' == Mage::app()->getRequest()->getActionName());
    }

    public function needRelations()
    {
        if ($this->_hasRequired
            || $this->_hasValidation
            || 0 < Mage::helper('amcustomerattr')->getElementsRelation()->getSize()) {
            return true;
        }
        return false;
    }
}