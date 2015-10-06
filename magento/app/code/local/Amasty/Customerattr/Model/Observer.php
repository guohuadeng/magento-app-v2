<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
* @package Amasty_Customerattr
*/ 
class Amasty_Customerattr_Model_Observer
{
    /**
     * Add columns (if `Show on Orders Grid` set to `Yes`) to the Orders Grid.
     * @param Varien_Event_Observer $observer
     */    
    public function modifyOrderGrid($observer)
    {
        $layout = Mage::getSingleton('core/layout');
        if (!$layout)
            return;
        
        $permissibleActions = array('index', 'grid');
        if ( false === strpos(Mage::app()->getRequest()->getControllerName(), 'sales_order') || 
             !in_array(Mage::app()->getRequest()->getActionName(), $permissibleActions) )
            return;
        
        $attributesCollection = Mage::getModel('customer/attribute')->getCollection();
        $alias = Mage::helper('amcustomerattr')->getProperAlias($attributesCollection->getSelect()->getPart('from'), 'eav_attribute');
        $attributesCollection->getSelect()
            ->where($alias . 'is_user_defined = ?', 1)
            ->where($alias . 'attribute_code != ?', 'customer_activated');
            
        $alias = Mage::helper('amcustomerattr')->getProperAlias($attributesCollection->getSelect()->getPart('from'), 'customer_eav_attribute');
        $attributesCollection->getSelect()
            ->where($alias . 'used_in_order_grid = ?', 1)
            ->order($alias . 'sorting_order');
        
        $grid = $layout->getBlock('sales_order.grid'); // Mage_Adminhtml_Block_Sales_Order_Grid
        if ( ($attributesCollection->getSize() > 0) && ($grid) ) {
            $after = 'grand_total';
            foreach ($attributesCollection as $attribute) {
                $column = array();
                switch ($attribute->getFrontendInput())
                {
                    case 'date':
                        $column = array(
                            'header'       => $attribute->getFrontendLabel(),
                            'index'        => $attribute->getAttributeCode(),
                            'filter_index' => '_table_'.$attribute->getAttributeCode().'.value',
                            'type'         => 'date',
                            'align'        => 'center',
                            'gmtoffset'    => true
                        );
                        break;
                    case 'select':
                    case 'selectimg':
                        $options = array();
                        foreach ($attribute->getSource()->getAllOptions(false, true) as $option)
                        {
                            $options[$option['value']] = $option['label'];
                        }
                        $column = array(
                            'header'       => $attribute->getFrontendLabel(),
                            'index'        => $attribute->getAttributeCode(),
                            'filter_index' => '_table_'.$attribute->getAttributeCode().'.value',
                            'align'        => 'center',
                            'type'         => 'options',
                            'options'      => $options,
                        );
                        break;
                    case 'multiselect':
                    case 'multiselectimg':
                        $options = array();
                        foreach ($attribute->getSource()->getAllOptions(false, true) as $option)
                        {
                            $options[$option['value']] = $option['label'];
                        }
                        $column = array(
                            'header'       => $attribute->getFrontendLabel(),
                            'index'        => $attribute->getAttributeCode(),
                            'filter_index' => '_table_'.$attribute->getAttributeCode().'.value',
                            'align'        => 'center',
                            'type'         => 'options',
                            'options'      => $options,
                            'renderer'     => 'amcustomerattr/adminhtml_renderer_multiselect',
                            'filter'       => 'amcustomerattr/adminhtml_filter_multiselect',
                        );
                        break;
                    case 'boolean':
                        $options = array(0 => 'No', 1 => 'Yes');
                        $column = array(
                            'header'       => $attribute->getFrontendLabel(),
                            'index'        => $attribute->getAttributeCode(),
                            'filter_index' => '_table_'.$attribute->getAttributeCode().'.value',
                            'align'        => 'center',
                            'type'         => 'options',
                            'options'      => $options,
                            'renderer'     => 'amcustomerattr/adminhtml_renderer_boolean',
                        );
                        break;
                    default:
                        $column = array(
                            'header'       => $attribute->getFrontendLabel(),
                            'index'        => $attribute->getAttributeCode(),
                            'filter_index' => '_table_'.$attribute->getAttributeCode().'.value',
                            'align'        => 'center',
                            'sortable'     => true,
                        );
                        break;
                }
                if ('file' == $attribute->getTypeInternal()) {
                    $column['renderer'] = 'amcustomerattr/adminhtml_renderer_file';
                }
                $grid->addColumnAfter($attribute->getAttributeCode(), $column, $after); // Mage_Adminhtml_Block_Widget_Grid
                $after = $attribute->getAttributeCode();
            }
        }
    }
	
	protected function _isJoined($from, $check)
    {
        $found = false;
        foreach ($from as $alias => $data) {
            if ($check === $alias) {
                $found = true;
            }
        }
        return $found;
    }
    
    /**
     * Join columns to the Orders Collection.
     * @param Varien_Event_Observer $observer
     */
    public function modifyOrderCollection($observer)
    {
        $permissibleActions = array('index', 'grid');
        if ( false === strpos(Mage::app()->getRequest()->getControllerName(), 'sales_order') ||
            !in_array(Mage::app()->getRequest()->getActionName(), $permissibleActions) )
            return;
        
        $collection = $observer->getOrderGridCollection();
        $tableNameCustomerEntity = Mage::getSingleton('core/resource')->getTableName('customer_entity');
        $attributesCollection = Mage::getModel('customer/attribute')->getCollection();
        
        $alias = Mage::helper('amcustomerattr')->getProperAlias($attributesCollection->getSelect()->getPart('from'), 'eav_attribute');
        $attributesCollection->getSelect()
            ->where($alias . 'is_user_defined = ?', 1)
            ->where($alias . 'attribute_code != ?', 'customer_activated');
            
        $alias = Mage::helper('amcustomerattr')->getProperAlias($attributesCollection->getSelect()->getPart('from'), 'customer_eav_attribute');
        $attributesCollection->getSelect()
            ->where($alias . 'used_in_order_grid = ?', 1);
        
        if ($attributesCollection->getSize() > 0) {
            foreach ($attributesCollection as $attribute) {
				if ($this->_isJoined($collection->getSelect()->getPart('from'), '_table_'.$attribute->getAttributeCode()))
					continue;
                $collection->getSelect()
                    ->joinLeft(array('_table_'.$attribute->getAttributeCode() => $tableNameCustomerEntity.'_'.$attribute->getBackendType()),
                               '_table_'.$attribute->getAttributeCode().'.entity_id = main_table.customer_id ' .
                               ' AND _table_'.$attribute->getAttributeCode().'.attribute_id = '.$attribute->getAttributeId(),
                               array($attribute->getAttributeCode() => '_table_'.$attribute->getAttributeCode().'.value')
                               );
            }
        }
    }
    
    /**
     * Handler for event `controller_action_layout_render_before_adminhtml_customer_index`.
     * @param Varien_Event_Observer $observer
     */
    public function forIndexCustomerGrid($observer)
    {
        $layout = Mage::getSingleton('core/layout');
        if (!$layout)
            return;
        
        $permissibleActions = array('index', 'grid');
        if ( false === strpos(Mage::app()->getRequest()->getControllerName(), 'customer') || 
             !in_array(Mage::app()->getRequest()->getActionName(), $permissibleActions) )
            return;

        $grid = $layout->getBlock('customer.grid');
        $grid = $this->_modifyCustomerGrid($grid);
    }
    
    /**
     * Handler for event `core_layout_block_create_after`.
     * @param Varien_Event_Observer $observer
     */
    public function forSearchCustomerGrid($observer)
    {
        if ('index' === Mage::app()->getRequest()->getActionName())
            return;
        
        $grid = $observer->getBlock();
        if ($grid instanceof Mage_Adminhtml_Block_Customer_Grid) {
            $grid = $this->_modifyCustomerGrid($grid);
        }
    }
    
    /**
     * Add columns (if `Show on Manage Customers Grid` set to `Yes`) to the Manage Customers Grid.
     * @param Varien_Event_Observer $observer
     */
    protected function _modifyCustomerGrid($grid)
    {
        $attributesCollection = Mage::getModel('customer/attribute')->getCollection();

        $alias = Mage::helper('amcustomerattr')->getProperAlias($attributesCollection->getSelect()->getPart('from'), 'eav_attribute');
        $attributesCollection->getSelect()
            ->where($alias . 'is_user_defined = ?', 1)
            ->where($alias . 'attribute_code != ?', 'customer_activated');
            
        $alias = Mage::helper('amcustomerattr')->getProperAlias($attributesCollection->getSelect()->getPart('from'), 'customer_eav_attribute');
        $attributesCollection->getSelect()
            ->where($alias . 'is_filterable_in_search = ?', 1) // `is_filterable_in_search` used to setting `Show on Manage Customers Grid`
            ->order($alias . 'sorting_order');
        
        if ( ($attributesCollection->getSize() > 0) && ($grid) ) {
            if (!Mage::app()->isSingleStoreMode()) {
                $after = 'website_id';
            } else {
                $after = 'customer_since';
            }
            foreach ($attributesCollection as $attribute) {
                $column = array();
                switch ($attribute->getFrontendInput())
                {
                    case 'date':
                        $column = array(
                            'header'       => $attribute->getFrontendLabel(),
                            'index'        => $attribute->getAttributeCode(),
                            'filter_index' => $attribute->getAttributeCode(),
                            'type'         => 'date',
                            'align'        => 'center',
                            'gmtoffset'    => true
                        );
                        break;
                    case 'select':
                    case 'selectimg':
                        $options = array();
                        foreach ($attribute->getSource()->getAllOptions(false, true) as $option)
                        {
                            if (isset($option['value']) && $option['value']){
                                if (is_array($option['value']))
                                {
                                    foreach($option['value'] as $opt){
                                        $options[$opt['value']] =$opt['label'];
                                    }
                                } else {
                                    $options[$option['value']] = $option['label'];
                                }
                            }
                        }
                        $column = array(
                            'header'       => $attribute->getFrontendLabel(),
                            'index'        => $attribute->getAttributeCode(),
                            'filter_index' => $attribute->getAttributeCode(),
                            'align'        => 'center',
                            'type'         => 'options',
                            'options'      => $options,
                        );
                        break;
                    case 'multiselect':
                    case 'multiselectimg':
                        $options = array();
                        foreach ($attribute->getSource()->getAllOptions(false, true) as $option)
                        {
                            $options[$option['value']] = $option['label'];
                        }
                        $column = array(
                            'header'       => $attribute->getFrontendLabel(),
                            'index'        => $attribute->getAttributeCode(),
                            'filter_index' => $attribute->getAttributeCode(),
                            'align'        => 'center',
                            'type'         => 'options',
                            'options'      => $options,
                            'renderer'     => 'amcustomerattr/adminhtml_renderer_multiselect',
                            'filter'       => 'amcustomerattr/adminhtml_filter_multiselect',
                        );
                        break;
                    case 'boolean':
                        $options = array(0 => 'No', 1 => 'Yes');
                        $column = array(
                            'header'       => $attribute->getFrontendLabel(),
                            'index'        => $attribute->getAttributeCode(),
                            'filter_index' => $attribute->getAttributeCode(),
                            'align'        => 'center',
                            'type'         => 'options',
                            'options'      => $options,
                            'renderer'     => 'amcustomerattr/adminhtml_renderer_boolean',
                        );
                        break;
                    default:
                        $column = array(
                            'header'       => $attribute->getFrontendLabel(),
                            'index'        => $attribute->getAttributeCode(),
                            'filter_index' => $attribute->getAttributeCode(),
                            'align'        => 'center',
                            'sortable'     => true,
                        );
                        break;
                }
                if ('file' == $attribute->getTypeInternal()) {
                    $column['renderer'] = 'amcustomerattr/adminhtml_renderer_file';
                }
                $grid->addColumnAfter($attribute->getAttributeCode(), $column, $after); // Mage_Adminhtml_Block_Widget_Grid
                $after = $attribute->getAttributeCode();
            }
        }

        // add column for `Admin Activation` feature
		$after = (!Mage::app()->isSingleStoreMode()) ? 'website_id' : 'customer_since';
		$options = array(0 => 'Pending', 1 => 'No', 2=>'Yes');
		$column = array(
			'header'       => 'Activated',
			'index'        => 'am_is_activated',
			'filter_index' => 'am_is_activated',
			'align'        => 'center',
			'type'         => 'options',
			'options'      => $options,
			'renderer'     => 'amcustomerattr/adminhtml_renderer_activationStatus',
		);
		$grid->addColumnAfter('am_is_activated', $column, $after);

        return $grid;
    }
    
    /**
     * Join columns to the Customers Collection.
     * @param Varien_Event_Observer $observer
     */
    public function modifyCustomerCollection($observer)
    {
        $collection = $observer->getCollection();
        if ($collection instanceof Mage_Customer_Model_Entity_Customer_Collection || $collection instanceof Mage_Customer_Model_Resource_Customer_Collection) {
            $attributesCollection = Mage::getModel('customer/attribute')->getCollection();

            $alias = Mage::helper('amcustomerattr')->getProperAlias($attributesCollection->getSelect()->getPart('from'), 'eav_attribute');
            $attributesCollection->getSelect()
                ->where($alias . 'is_user_defined = ?', 1)
                ->where($alias . 'attribute_code != ?', 'customer_activated');
                
            $alias = Mage::helper('amcustomerattr')->getProperAlias($attributesCollection->getSelect()->getPart('from'), 'customer_eav_attribute');
            $attributesCollection->getSelect()
                ->where($alias . 'is_filterable_in_search = ?', 1);
            
            if ($attributesCollection->getSize() > 0) {
                foreach ($attributesCollection as $attribute) {
                    $collection->addAttributeToSelect($attribute->getAttributeCode());
                }
            }

            // add `activated` attribute to data selection
            $attributesCollectionFull = Mage::getModel('customer/attribute')->getCollection();
            foreach($attributesCollectionFull as $attribute){
                $attrCode = $attribute->getAttributeCode();
                if ($attrCode == 'am_is_activated'){
                    $collection->addAttributeToSelect($attrCode);
                    break;
                }
            }
        }
    }
    
    public function handleBlockOutput($observer) 
    {
        /* @var $block Mage_Core_Block_Abstract */
        $block = $observer->getBlock();
        
        $transport = $observer->getTransport();
        $html = $transport->getHtml();

        $salesOrderViewTabInfoClass = Mage::getConfig()->getBlockClassName('adminhtml/sales_order_view_tab_info');
        if ($salesOrderViewTabInfoClass == get_class($block)) { // Order View Page
            if ($customerId = $block->getOrder()->getCustomerId()) {
                $tempPos = strpos($html, '<!--Account Information-->');
                if (false !== $tempPos) {
                    $pos = strpos($html, '</table>', $tempPos);
                    $storeId = $block->getOrder()->getStoreId();
                    if ($accountData = Mage::helper('amcustomerattr')->getCustomerAccountData($customerId, $storeId)) {
                        $insert = '';
                        foreach ($accountData as $data) {
                            $insert .= '
                                <tr>
                                    <td class="label"><label>' . $data['label'] . '</label></td>
                                    <td class="value"><strong>' . $data['value'] . '</strong></td>
                                </tr>';
                        }
                        $html = substr_replace($html, $insert, $pos-1, 0);
                    }
                }
            }
        }

        if (Mage::getStoreConfig('amcustomerattr/login/login_field')) { // Login
            $attributesHash = Mage::helper('amcustomerattr')->getAttributesHash();
            if (isset($attributesHash[Mage::getStoreConfig('amcustomerattr/login/login_field')])) { // check if isset attribute
                $loginClasses = array();
                $loginClasses[] = Mage::getConfig()->getBlockClassName('checkout/onepage_login');
                $loginClasses[] = Mage::getConfig()->getBlockClassName('customer/form_login');
                if (in_array(get_class($block), $loginClasses)) { // check block
                    if (Mage::getStoreConfig('amcustomerattr/login/disable_email')) {
                        $replaceWith = $attributesHash[Mage::getStoreConfig('amcustomerattr/login/login_field')];
                    } else {
                        $replaceWith = Mage::helper('amcustomerattr')->__('Email Address') . '/' . $attributesHash[Mage::getStoreConfig('amcustomerattr/login/login_field')];
                    }
                    $html = str_replace(Mage::helper('amcustomerattr')->__('Email Address'), $replaceWith, $html);
                    $html = str_replace('validate-email', '', $html);
                    $html = str_replace('type="email"', 'type="text"', $html);
                }
            }
        }
        
        if (Mage::getStoreConfig('amcustomerattr/forgot/forgot_field')) { // Forgot Password Page
            $attributesHash = Mage::helper('amcustomerattr')->getAttributesHash();
            if (isset($attributesHash[Mage::getStoreConfig('amcustomerattr/forgot/forgot_field')])) { // check if isset attribute
                $forgotpasswordClass = Mage::getConfig()->getBlockClassName('customer/account_forgotpassword');
                if ($forgotpasswordClass == get_class($block)) { // check block
                    // replace url for form action
                    $html = str_replace('customer/account/forgotpasswordpost', 'amcustomerattr/attachment/forgotpasswordpost', $html);
                    
                    // remove JS validation
                    $html = str_replace('validate-email', '', $html);
                    
                    // replace field title
                    if ($insert = Mage::getStoreConfig('amcustomerattr/forgot/field_title', Mage::app()->getStore()->getId())) {
                        $pos = strpos($html, '</em>');
                        $tempPos = strpos($html, '</label>');
                        $length = $tempPos - $pos - 5;
                        $html = substr_replace($html, $insert, $pos+5, $length);
                    }
                    
                    // replace text on the page
                    if ($insert = Mage::getStoreConfig('amcustomerattr/forgot/text', Mage::app()->getStore()->getId())) {
                        $pos = strpos($html, '<p>');
                        $tempPos = strpos($html, '</p>');
                        $length = $tempPos - $pos - 3;
                        $html = substr_replace($html, $insert, $pos+3, $length);
                    }
                }
            }
        }

        $formEditClass = Mage::getConfig()->getBlockClassName('adminhtml/customer_edit_tab_account');
        if ($formEditClass == get_class($block)) { // Customer Edit Page (Adminhtml)
            $customerId = Mage::app()->getRequest()->getParam('id');
            $customer = Mage::getModel('customer/customer')->load($customerId);
            $is_activated = $customer->getAmIsActivated();
            $pos = strpos($html, 'id="_accountbase_fieldset"');
            $pos = strpos($html,'</tr>',$pos);
            $insert = '<tr>
                                <td class="label"><label for="am_is_activated">Account activated</label></td>
                                <td class="value">
                                    <select id="am_is_activated" name="account[am_is_activated]" class=" select">
                                        <option value="0" '.($is_activated==0?'selected="selected" ':'').'>Pending</option>
                                        <option value="1" '.($is_activated==1?'selected="selected" ':'').'>No</option>
                                        <option value="2" '.($is_activated==2?'selected="selected" ':'').'>Yes</option>
                                    </select>
                                </td>
                            </tr>';
            $html = substr_replace($html, $insert, $pos+5, 0);
        }

        $flag = false;
        $formRegisterClass = Mage::getConfig()->getBlockClassName('customer/form_register');
        $formEditClass = Mage::getConfig()->getBlockClassName('customer/form_edit');
        $onepageBillingClass = Mage::getConfig()->getBlockClassName('checkout/onepage_billing');
        if ($formRegisterClass == get_class($block)) {
            $flag = true;
            $column = 'on_registration';
        }
        if ($formEditClass == get_class($block)) {
            $flag = true;
            $column = '';
        }
        if ($onepageBillingClass == get_class($block)) {
            $flag = true;
            $column = 'used_in_product_listing';
        }

        if (Mage::getStoreConfig('amcustomerattr/general/front_auto_output')) { // check if can to try auto output
            if ($formRegisterClass == get_class($block)) { // Registration Page
                $flag = true;
                if (Mage::helper('amcustomerattr')->getFileAttributes('on_registration')->getSize() > 0) {
                    $html = str_replace('id="form-validate"', ' id="form-validate" enctype="multipart/form-data" ', $html);
                }
                if (false === strpos($html, 'amcustomerattr')) {
                    $pos = strpos($html, '<div class="buttons-set');
                    $insert = Mage::helper('amcustomerattr')->fields();
                    $html = substr_replace($html, $insert, $pos-1, 0);
                }
            }

            if ($formEditClass == get_class($block)) { // Account Edit Page
                $flag = true;
                if (Mage::helper('amcustomerattr')->getFileAttributes()->getSize() > 0) { // need for upload
                    $html = str_replace('id="form-validate"', ' id="form-validate" enctype="multipart/form-data" ', $html);
                }
                if (false === strpos($html, 'amcustomerattr')) {
                    $pos = strpos($html, '<div class="buttons-set">');
                    $insert = Mage::helper('amcustomerattr')->fields();
                    $html = substr_replace($html, $insert, $pos-1, 0);
                }
            }
            
            if ($onepageBillingClass == get_class($block) &&
                'express' !== Mage::app()->getRequest()->getControllerName()) { // PayPal Express (attributes do not need)
                $flag = true;
                if (false === strpos($html, 'amcustomerattr')) {
					if ($block->isCustomerLoggedIn()) {
						$pos = strpos($html, '<div class="buttons-set"') - 1;
					} else {
						$pos = strpos($html, '<li class="fields" id="register-customer-password">') + 51;
					}
                    $insert = Mage::helper('amcustomerattr')->fields();
                    $html = substr_replace($html, $insert, $pos, 0);
                }
            }

        }

        if ($flag
            && false !== strpos($html, '<!-- Customer Attributes Relations -->')) {
            $insert = Mage::app()->getLayout()->createBlock('amcustomerattr/customer_fields_relations')->setParts($column)->toHtml();
            $pos = strripos($html, '<!-- Customer Attributes Relations -->') + 38;
            $html = substr_replace($html, $insert, $pos, 0);
        }
        $transport->setHtml($html);
    }
    
    public function onCoreLayoutBlockCreateAfter($observer)
    {
        $block = $observer->getBlock();
        // Order Grid
        $permissibleActions = array('exportCsv', 'exportExcel');
        if (($block instanceof Mage_Adminhtml_Block_Sales_Order_Grid || $block instanceof EM_DeleteOrder_Block_Adminhtml_Sales_Order_Grid) &&
            (in_array(Mage::app()->getRequest()->getActionName(), $permissibleActions))) {
            // Customer Attributes
            $attributesCollection = Mage::getModel('customer/attribute')->getCollection();
            $alias = Mage::helper('amcustomerattr')->getProperAlias($attributesCollection->getSelect()->getPart('from'), 'eav_attribute');
            $attributesCollection->getSelect()
                ->where($alias . 'is_user_defined = ?', 1)
                ->where($alias . 'attribute_code != ?', 'customer_activated');
                
            $alias = Mage::helper('amcustomerattr')->getProperAlias($attributesCollection->getSelect()->getPart('from'), 'customer_eav_attribute');
            $attributesCollection->getSelect()
                ->where($alias . 'used_in_order_grid = ?', 1)
                ->order($alias . 'sorting_order');
            
            if ( ($attributesCollection->getSize() > 0) && ($block) ) {
                $after = 'grand_total';
                foreach ($attributesCollection as $attribute) {
                    $column = array();
                    switch ($attribute->getFrontendInput())
                    {
                        case 'date':
                            $column = array(
                                'header'       => $attribute->getFrontendLabel(),
                                'index'        => $attribute->getAttributeCode(),
                                'filter_index' => '_table_'.$attribute->getAttributeCode().'.value',
                                'type'         => 'date',
                                'align'        => 'center',
                                'gmtoffset'    => true
                            );
                            break;
                        case 'select':
                        case 'selectimg':
                            $options = array();
                            foreach ($attribute->getSource()->getAllOptions(false, true) as $option)
                            {
                                $options[$option['value']] = $option['label'];
                            }
                            $column = array(
                                'header'       => $attribute->getFrontendLabel(),
                                'index'        => $attribute->getAttributeCode(),
                                'filter_index' => '_table_'.$attribute->getAttributeCode().'.value',
                                'align'        => 'center',
                                'type'         => 'options',
                                'options'      => $options,
                            );
                            break;
                        case 'multiselect':
                        case 'multiselectimg':
                            $options = array();
                            foreach ($attribute->getSource()->getAllOptions(false, true) as $option)
                            {
                                $options[$option['value']] = $option['label'];
                            }
                            $column = array(
                                'header'       => $attribute->getFrontendLabel(),
                                'index'        => $attribute->getAttributeCode(),
                                'filter_index' => '_table_'.$attribute->getAttributeCode().'.value',
                                'align'        => 'center',
                                'type'         => 'options',
                                'options'      => $options,
                                'renderer'     => 'amcustomerattr/adminhtml_renderer_multiselect',
                                'filter'       => 'amcustomerattr/adminhtml_filter_multiselect',
                            );
                            break;
                        case 'boolean':
                            $options = array(0 => 'No', 1 => 'Yes');
                            $column = array(
                                'header'       => $attribute->getFrontendLabel(),
                                'index'        => $attribute->getAttributeCode(),
                                'filter_index' => '_table_'.$attribute->getAttributeCode().'.value',
                                'align'        => 'center',
                                'type'         => 'options',
                                'options'      => $options,
                                'renderer'     => 'amcustomerattr/adminhtml_renderer_boolean',
                            );
                            break;
                        default:
                            $column = array(
                                'header'       => $attribute->getFrontendLabel(),
                                'index'        => $attribute->getAttributeCode(),
                                'filter_index' => '_table_'.$attribute->getAttributeCode().'.value',
                                'align'        => 'center',
                                'sortable'     => true,
                            );
                            break;
                    }
                    if ('file' == $attribute->getTypeInternal()) {
                        $column['renderer'] = 'amcustomerattr/adminhtml_renderer_file';
                    }
                    $block->addColumnAfter($attribute->getAttributeCode(), $column, $after); // Mage_Adminhtml_Block_Widget_Grid
                    $after = $attribute->getAttributeCode();
                }
            }
        }
    }

    public function handleBlockOutputBefore($observer){
        $block = $observer->getBlock();
        $massactionClass  = Mage::getConfig()->getBlockClassName('adminhtml/widget_grid_massaction');
        $customerGridClass = Mage::getConfig()->getBlockClassName('adminhtml/customer_grid');
        $parentClass = get_class($block->getParentBlock());
        if ($massactionClass == get_class($block) && $parentClass==$customerGridClass){
            $block->addItem('deactivate',
                array(
                    'label'   => Mage::helper('amcustomerattr')->__('Deactivate'),
                    'url'     => Mage::helper("adminhtml")->getUrl('amcustomerattr/adminhtml_activation/massDeactivate'),
                    'confirm' => Mage::helper('amcustomerattr')->__('Are you sure?')));
            $block->addItem('activate',
                array(
                    'label'   => Mage::helper('amcustomerattr')->__('Activate'),
                    'url'     => Mage::helper("adminhtml")->getUrl('amcustomerattr/adminhtml_activation/massActivate'),
                    'confirm' => Mage::helper('amcustomerattr')->__('Are you sure?')));
        }

    }


}
