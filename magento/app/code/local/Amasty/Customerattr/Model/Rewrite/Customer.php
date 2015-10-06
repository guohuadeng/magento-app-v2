<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Model_Rewrite_Customer extends Mage_Customer_Model_Customer
{
    protected $_customAttributes = array();
    protected $_fileAttributes = array();
    protected $_savedFileNames = array();
    const ACTIVATION_STATUS_PENDING     = '0';
    const ACTIVATION_STATUS_DEACTIVATED = '1';
    const ACTIVATION_STATUS_ACTIVATED   = '2';

    protected function _beforeDelete()
    {
        $collection = Mage::getModel('customer/attribute')->getCollection();
        $alias = Mage::helper('amcustomerattr')->getProperAlias($collection->getSelect()->getPart('from'), 'eav_attribute');
        $collection->addFieldToFilter($alias . 'is_user_defined', 1);
        $collection->addFieldToFilter($alias . 'entity_type_id', Mage::getModel('eav/entity')->setType('customer')->getTypeId());
        $collection->addFieldToFilter($alias . 'frontend_input', 'file');
        foreach ($collection as $attribute) {
            if ($value = $this->getData($attribute->getAttributeCode())) {
                Mage::helper('amcustomerattr')->deleteFile($value);
            }
        }
        
        parent::_beforeDelete();
    }
    
    protected function _afterSave()
    {
        if ($this->_fileAttributes) {
            $deleteFiles = Mage::app()->getRequest()->getPost('amcustomerattr_delete');
            foreach ($this->_fileAttributes as $id => $attributeCode) {
                if (isset($_FILES['amcustomerattr_' . $attributeCode]['error']) && UPLOAD_ERR_OK == $_FILES['amcustomerattr_' . $attributeCode]['error']) {
                    try {
                        $fileName = $_FILES['amcustomerattr_' . $attributeCode]['name'];
                        $uploader = new Varien_File_Uploader('amcustomerattr_' . $attributeCode);
                        $uploader->setAllowRenameFiles(false);
                        $uploader->setFilesDispersion(false);
                        $destinationFolder = Mage::helper('amcustomerattr')->getAttributeFileUrl($fileName);
                        $fileName = Mage::helper('amcustomerattr')->cleanFileName($fileName);
                        $uploader->save($destinationFolder . $fileName[1] . DS . $fileName[2] . DS, $fileName[3]);
                    } catch (Exception $error) {
                        $e = new Mage_Customer_Exception(Mage::helper('amcustomerattr')->__('An error occurred while saving the file: ' . $error->getMessage()));
                        if (method_exists($e, 'setMessage')) {
                            $e->setMessage(Mage::helper('amcustomerattr')->__('An error occurred while saving the file: ' . $error));
                        }
                        throw $e;
                    }
                    if ($this->_savedFileNames[$attributeCode]) {
                        Mage::helper('amcustomerattr')->deleteFile($this->_savedFileNames[$attributeCode]);
                    }
                }
                if ($deleteFiles && $deleteFiles[$attributeCode] && ($this->_savedFileNames[$attributeCode] === $deleteFiles[$attributeCode])) {
                    Mage::helper('amcustomerattr')->deleteFile($deleteFiles[$attributeCode]);
                }
                unset($_FILES['amcustomerattr_' . $attributeCode]);
            }
        }
        
        if(!$this->getOrigId() && !Mage::registry('amcustomerattr_customer_registry_dispatched')) {
            Mage::register('amcustomerattr_customer_registry_dispatched', true);
            Mage::dispatchEvent('amcustomerattr_customer_registry', array('model' => $this)); 
        }

        Mage::getSingleton('customer/session')->unsetData('amcustomerattr');

        /*
         * send notification upon new user registration to admin
         */
        $origData =  $this->getOrigData();
        if ($origData===null)
        { // new user registration will be
            $notified = Mage::registry('AdminNotifiedAboutNewUser');
            if (Mage::getStoreConfig('amcustomerattr/activation/notify_admin', $this->getStoreId()) && !$notified)
            {
                Mage::register('AdminNotifiedAboutNewUser','1');
                $translate = Mage::getSingleton('core/translate');
                $translate->setTranslateInline(false);

                $template = Mage::getStoreConfig('amcustomerattr/activation/email_template_admin', $this->getStoreId());
                $template = $template ? $template : 'amasty_amcustomerattr_activation_email_template_admin';

                $emails = Mage::getStoreConfig('amcustomerattr/activation/admin_email', $this->getStoreId());
                foreach (explode(',', $emails) as $email) {
                    $email = trim($email);
                    $tpl = Mage::getModel('core/email_template');
                    $tpl->setDesignConfig(array('area' => 'frontend', 'store' => $this->getStoreId()))
                        ->sendTransactional(
                            $template,
                            Mage::getStoreConfig('amcustomerattr/activation/admin_sender', $this->getStoreId()),
                            $email,
                            'Admin notification',
                            array(
                                'customer_name' => $this->getName(),
                                'customer_id'   => $this->getEntityId(),
                                'created_at'    => date('Y-m-d', $this->getCreatedAtTimestamp()),
                                'customer'      => $this,
                            ),
                            $this->getStoreId()
                        );
                    $tpl = null;
                }
                $translate->setTranslateInline(true);
            }
        }

        /*
         * send notification about activation to user
         */
        $payment_post = Mage::app()->getRequest()->getPost('payment'); //exclude checkout pages
        if (Mage::getStoreConfig('amcustomerattr/activation/notify_customer', $this->getStoreId())
            && !$payment_post
            && $this->getOrigData('am_is_activated') != $this->getData('am_is_activated')
            && self::ACTIVATION_STATUS_ACTIVATED == $this->getData('am_is_activated')) {
            $notified = Mage::registry('CustomerNotifiedAboutActivation');
            if (!$notified) {
                Mage::register('CustomerNotifiedAboutActivation','1');
                $translate = Mage::getSingleton('core/translate');
                $translate->setTranslateInline(false);
                $template = Mage::getStoreConfig('amcustomerattr/activation/email_template_customer', $this->getStoreId());
                $template = $template ? $template : 'amasty_amcustomerattr_activation_email_template_customer';

                $tpl = Mage::getModel('core/email_template');
                $tpl->setDesignConfig(array('area' => 'frontend', 'store' => $this->getStoreId()))
                    ->sendTransactional(
                        $template,
                        Mage::getStoreConfig('amcustomerattr/activation/customer_sender', $this->getStoreId()),
                        $this->getEmail(),
                        'Customer notification',
                        array(
                            'customer_name' => $this->getName(),
                            'customer_id'   => $this->getEntityId(),
                            'email'         => $this->getEmail(),
                            'created_at'    => $this->getCreatedAtTimestamp(),
                            'customer'      => $this,
                        ),
                        $this->getSoreId()
                    );
                $tpl = null;
                $translate->setTranslateInline(true);
            }
        } else if ($origData===null && Mage::getStoreConfig('amcustomerattr/activation/activation_required', $this->getStoreId())) {
            if (!Mage::getStoreConfig('amcustomerattr/activation/auto_activation', $this->getStoreId())
                || (Mage::getStoreConfig('amcustomerattr/activation/auto_activation', $this->getStoreId())
                    && Mage::getStoreConfig('amcustomerattr/activation/enabled_customer_groups', $this->getStoreId())
                    && self::ACTIVATION_STATUS_ACTIVATED != $this->getAmIsActivated())) {
                $this->setIsLoggedIn(true);
                Mage::getSingleton('customer/session')->logout();
                $customerSession = Mage::getSingleton('customer/session');
                $customerSession->unsetAll();
                $customerSession->getCookie()->delete($customerSession->getSessionName());
                if (!Mage::registry('CustomerNotificationAboutActivation')
                    && Mage::getStoreConfig('amcustomerattr/activation/registration_notice', $this->getStoreId())) {
                    Mage::register('CustomerNotificationAboutActivation', 1);
                    Mage::getSingleton('core/session')->addNotice(Mage::getStoreConfig('amcustomerattr/activation/registration_notice', $this->getStoreId()));
                }
            }
        }

        parent::_afterSave();
    }
    
    protected function _beforeSave()
    {
        parent::_beforeSave();
        $checkUnique = array();
        $nameGroupAttribute = '';
        /**
        * Will detect which attributes are dates and files, and check unique attributes
        */
        $collection = Mage::getModel('customer/attribute')->getCollection();
        $alias = Mage::helper('amcustomerattr')->getProperAlias($collection->getSelect()->getPart('from'), 'eav_attribute');
        $collection->addFieldToFilter($alias . 'is_user_defined', 1);
        $collection->addFieldToFilter($alias . 'entity_type_id', Mage::getModel('eav/entity')->setType('customer')->getTypeId());
        
        $castDate = array();
        $filesFields = array();
        $filesRestrictions = array();
        foreach ($collection as $attribute) {
            if ('selectgroup' == $attribute->getTypeInternal()) {
                $nameGroupAttribute = $attribute->getAttributeCode();
            }
            if ('datetime' == $attribute->getBackendType()) {
                $castDate[] = $attribute->getAttributeCode();
            }
            if ('file' == $attribute->getTypeInternal()) {
                $this->_fileAttributes[$attribute->getId()] = $attribute->getAttributeCode();
                $filesFields[] = $attribute->getAttributeCode();
                $filesRestrictions[$attribute->getAttributeCode()]['size'] = 1048576 * $attribute->getFileSize();
                $filesRestrictions[$attribute->getAttributeCode()]['type'] = $attribute->getFileTypes();
                $filesRestrictions[$attribute->getAttributeCode()]['dimentions'] = $attribute->getFileDimentions();
                $this->_savedFileNames[$attribute->getAttributeCode()] = $this->getData($attribute->getAttributeCode());
            }
            if ($attribute->getIsUnique()) {
                $translations = $attribute->getStoreLabels();
                if (isset($translations[Mage::app()->getStore()->getId()])) {
                    $attributeLabel = $translations[Mage::app()->getStore()->getId()];
                } else {
                    $attributeLabel = $attribute->getFrontend()->getLabel();
                }
                $checkUnique[$attribute->getAttributeCode()] = $attributeLabel;
            }
        }

        // Need to change `Customer Group Selector` attribute, if admin change group for customer in the Backend
        if ($nameGroupAttribute && ($backendPost = Mage::app()->getRequest()->getPost('account')) && isset($backendPost['group_id'])) {
            $entityType = Mage::getSingleton('eav/config')->getEntityType('customer');
            $groupAttribute = Mage::getModel('customer/attribute')->loadByCode($entityType, $nameGroupAttribute);
            $storeIds = explode(',', $groupAttribute->getData('store_ids'));
            if (in_array($this->getStoreId(), $storeIds) || in_array(0, $storeIds)) {
                $options = $groupAttribute->getSource()->getAllOptions(true, true);
                foreach ($options as $option) {
                    $temp = Mage::getModel('eav/entity_attribute_option')->load($option['value']);
                    if ($temp && $temp->getGroupId() && $temp->getGroupId() == $backendPost['group_id']) {
                        $this->setData($nameGroupAttribute, $option['value']);
                    }
                }
            }
        }
        
        /**
        * Adding customer attributes to self data array
        */
        $customerAttributes = Mage::app()->getRequest()->getPost('amcustomerattr');
        if (!$customerAttributes
			&& ('checkout' == Mage::app()->getRequest()->getModuleName()
			|| 'sgps' == Mage::app()->getRequest()->getModuleName()
			|| 'amscheckoutfront' == Mage::app()->getRequest()->getModuleName())) {
            $customerAttributes = Mage::getSingleton('checkout/session')->getAmcustomerattr();
        }
        if ($customerAttributes) {
            // set to session attributes except file attributes
            if ($filesFields) {
                $temp = $customerAttributes;
                foreach ($customerAttributes as $attributeCode => $attributeValue) {
                    if (in_array($attributeCode, $filesFields)) {
                        unset($temp[$attributeCode]);
                    }
                }
                Mage::getSingleton('customer/session')->setAmcustomerattr($temp);
            } else {
                Mage::getSingleton('customer/session')->setAmcustomerattr($customerAttributes);
            }
            
            $deleteFiles = Mage::app()->getRequest()->getPost('amcustomerattr_delete');
            $idGroupSelect = 0;
            foreach ($customerAttributes as $attributeCode => $attributeValue) {
                if ($attributeCode == $nameGroupAttribute) {
                    $idGroupSelect = $attributeValue;
                }
                if (in_array($attributeCode, $castDate)) {
                    if ($attributeValue) {
                        $temp = Mage::app()->getLocale()->date($attributeValue);
                        $attributeValue = Mage::getModel('core/date')->date('Y-m-d', $temp->getTimestamp());
                    }
                }
                if (in_array($attributeCode, $filesFields)) {
                    if (isset($_FILES['amcustomerattr_' . $attributeCode]['error']) && UPLOAD_ERR_OK == $_FILES['amcustomerattr_' . $attributeCode]['error']) { // check if uploaded new file
                        // correct filename
                        $temp = explode('.', $_FILES['amcustomerattr_' . $attributeCode]['name']);
                        $ext = strtolower(array_pop($temp));
                        $fileName = Mage::helper('amcustomerattr')->getCorrectFileName($temp[0]);
                        $f1 = Mage::helper('amcustomerattr')->getFolderName($fileName[0]);
                        $f2 = Mage::helper('amcustomerattr')->getFolderName($fileName[1]);
                        $fileDestination = Mage::getBaseDir('media') . DS . 'customer' . DS . $f1 . DS . $f2 . DS;
                        if (file_exists($fileDestination . $fileName . '.' . $ext)) { // check if exist file with the same name
                            $attributeValue = DS . $f1 . DS . $f2 . DS . uniqid(date('ihs')) . $fileName . '.' . $ext;
                        } else {
                            $attributeValue = DS . $f1 . DS . $f2 . DS . $fileName . '.' . $ext;
                        }
                        $_FILES['amcustomerattr_' . $attributeCode]['name'] = $attributeValue;
                    } elseif ($deleteFiles[$attributeCode] && ($this->_savedFileNames[$attributeCode] === $deleteFiles[$attributeCode])) { // check if file mark for delete
                        $attributeValue = '';
                    } else {
                        $attributeValue = $this->_savedFileNames[$attributeCode];
                    }
                }
                
                $this->setData($attributeCode, $attributeValue);
            }
            
            if ($idGroupSelect) {
                $option = Mage::getModel('eav/entity_attribute_option')->load($idGroupSelect);
                if ($option && $option->getGroupId()) {
                    $this->setGroupId($option->getGroupId());
                }
           } else if (Mage::helper('amcustomerattr/group')->isAllowed()) {
               if ($this->getData(Mage::helper('amcustomerattr/group')->getAttribute())) {
                   $this->setGroupId(Mage::helper('amcustomerattr/group')->getGroupId());
               }
           }
        }
        
        if ($checkUnique) {
            foreach ($checkUnique as $attributeCode => $attributeLabel) {
                //skip empty values
                if (!$this->getData($attributeCode)) {
                    continue;
                }
                $customerCollection = Mage::getResourceModel('customer/customer_collection');
                $customerCollection->addAttributeToFilter($attributeCode, array('eq' => $this->getData($attributeCode)));
                $mainAlias = ( false !== strpos($customerCollection->getSelect()->__toString(), 'AS `e') ) ? 'e' : 'main_table';
                $customerCollection->getSelect()->where($mainAlias . '.entity_id != ?', $this->getId());
                if ($customerCollection->getSize() > 0) {
                    $e = new Mage_Customer_Exception(Mage::helper('amcustomerattr')->__('Please specify different value for `%s` attribute. Customer with such value already exists.', $attributeLabel));
                    if (method_exists($e, 'setMessage')) {
                        $e->setMessage(Mage::helper('amcustomerattr')->__('Please specify different value for `%s` attribute. Customer with such value already exists.', $attributeLabel));
                    }
                    throw $e;
                }
            }
        }
        
        // check files
        $fileErrors = array();
        if ($filesFields) {
            foreach ($filesFields as $attributeCode) {
                if (isset($_FILES['amcustomerattr_' . $attributeCode]['error']) && UPLOAD_ERR_OK == $_FILES['amcustomerattr_' . $attributeCode]['error']) {
                    // check file size
                    if ($filesRestrictions[$attributeCode]['size'] && ($filesRestrictions[$attributeCode]['size'] < $_FILES['amcustomerattr_' . $attributeCode]['size'])) {
                        $fileErrors[] = Mage::helper('amcustomerattr')->__('File size restriction: %d bytes', $filesRestrictions[$attributeCode]['size']);
                    }
                    // check file ext
                    if ($filesRestrictions[$attributeCode]['type']) {
                        $temp = explode('.', $_FILES['amcustomerattr_' . $attributeCode]['name']);
                        $ext = strtolower(array_pop($temp));
                        if (!in_array($ext, explode(',', $filesRestrictions[$attributeCode]['type']))) {
                            $fileErrors[] = Mage::helper('amcustomerattr')->__('File ext restriction: %s', $filesRestrictions[$attributeCode]['type']);
                        }
                    }
                    // check type of file
                    if (substr_count($_FILES['amcustomerattr_' . $attributeCode]['type'], '/') > 1) { // check double file type
                        $fileErrors[] = Mage::helper('amcustomerattr')->__('Not supported type of file: %s', $_FILES['amcustomerattr_' . $attributeCode]['type']);
                    }
                    // check content (MIME) type of file
                    if (Mage::getStoreConfig('amcustomerattr/general/check_file_type') && !$this->checkContentType($ext, $_FILES['amcustomerattr_' . $attributeCode]['type'])) {
                        $fileErrors[] = Mage::helper('amcustomerattr')->__('Not supported content type of file: %s', $_FILES['amcustomerattr_' . $attributeCode]['type']);
                    }
                    // check dimentions for image
                    if ($filesRestrictions[$attributeCode]['dimentions'] && (false !== strpos($_FILES['amcustomerattr_' . $attributeCode]['type'], 'image/'))) {
                        $dimentions = explode('/', $filesRestrictions[$attributeCode]['dimentions']);
                        try {
                            $imageInfo = getimagesize($_FILES['amcustomerattr_' . $attributeCode]['tmp_name']);
                        } catch (Exception $error) {
                            $e = new Mage_Customer_Exception(Mage::helper('amcustomerattr')->__('System error: %s', $error));
                            if (method_exists($e, 'setMessage')) {
                                $e->setMessage(Mage::helper('amcustomerattr')->__('System error: %s', $error));
                            }
                            throw $e;
                        }
                        $imageInfo = getimagesize($_FILES['amcustomerattr_' . $attributeCode]['tmp_name']);
                        if (($imageInfo[0] > $dimentions[0]) || ($imageInfo[1] > $dimentions[1])) {
                            $fileErrors[] = Mage::helper('amcustomerattr')->__('Image size restriction: %s pixels (width/height)', $filesRestrictions[$attributeCode]['dimentions']);
                        }
                    }
                    // errors
                    if ($fileErrors) {
                        $e = new Mage_Customer_Exception(Mage::helper('amcustomerattr')->__('File error: %s', implode('; ', $fileErrors)));
                        if (method_exists($e, 'setMessage')) {
                            $e->setMessage(Mage::helper('amcustomerattr')->__('File error: %s', implode('; ', $fileErrors)));
                        }
                        throw $e;
                    }
                }
            }
        }

        // account activation checkbox save
        $account_post = Mage::app()->getRequest()->getPost('account');
        if ($account_post)
        { //set activation status
            $this->setData('am_is_activated',isset($account_post['am_is_activated']) ? $account_post['am_is_activated'] :self::ACTIVATION_STATUS_PENDING);
        } else if($this->getOrigData()===null && Mage::getStoreConfig('amcustomerattr/activation/auto_activation', $this->getStoreId()))
        { // activate new users automatically
            if (Mage::getStoreConfig('amcustomerattr/activation/enabled_customer_groups', $this->getStoreId())
                && $this->getGroupId()) { // activate by group
                $groups = explode(',', Mage::getStoreConfig('amcustomerattr/activation/customer_groups', $this->getStoreId()));
                if (in_array($this->getGroupId(), $groups)) {
                    $this->setData('am_is_activated', self::ACTIVATION_STATUS_ACTIVATED);
                }
            } else {
                $this->setData('am_is_activated', self::ACTIVATION_STATUS_ACTIVATED);
            }
        }

        return $this;
    }
     
    public function getAttributes()
    {
        $attributes = parent::getAttributes();
        
        $collection = Mage::getModel('customer/attribute')->getCollection();
        $alias = Mage::helper('amcustomerattr')->getProperAlias($collection->getSelect()->getPart('from'), 'eav_attribute');
        $collection->addFieldToFilter($alias . 'is_user_defined', 1);
        $collection->addFieldToFilter($alias . 'entity_type_id', Mage::getModel('eav/entity')->setType('customer')->getTypeId());
        
        $temp = array();
	    foreach ($attributes as $attribute) {
	        $temp[] = $attribute->getAttributeCode();
	    }
	
        foreach ($collection as $attribute)
        {
            if ('customer_activated' != $attribute->getAttributeCode() && 'unlock_customer' != $attribute->getAttributeCode())
            {
                // filter attributes by store on the edit customer page in the backend
                if ('customer' == Mage::app()->getRequest()->getControllerName() && 'edit' == Mage::app()->getRequest()->getActionName())
                {
                    $applicableStoreIds = explode(',', $attribute->getStoreIds());
                    // 0 means allowed on all store views
                    if (!in_array(0, $applicableStoreIds))
                    {
                        if (!in_array(Mage::registry('current_customer')->getStoreId(), $applicableStoreIds) && 0 != Mage::registry('current_customer')->getStoreId())
                        {
                            continue;
                        }
                    }
                }
                if (!in_array($attribute->getAttributeCode(), $temp)) {
                    $attributes[] = $attribute;
                }
            }
        }
        return $attributes;
    }
    
    public function loadByEmail($customerEmail)
    {
        if ('forgotpasswordpost' == Mage::app()->getRequest()->getActionName()) { // Forgot Password
            if (!Mage::getStoreConfig('amcustomerattr/forgot/disable_email') || !Mage::getStoreConfig('amcustomerattr/forgot/forgot_field')) {
                parent::loadByEmail($customerEmail);
                if ($this->getId()) {
                    // customer found by e-mail, no need to load by attribute
                    return $this;
                }
            }
            if (Mage::getStoreConfig('amcustomerattr/forgot/forgot_field')) {
                // will try to load by attribute
                $attribute = Mage::getModel('customer/attribute')->load(Mage::getStoreConfig('amcustomerattr/forgot/forgot_field'), 'attribute_code');
                if ($attribute->getId()) {
                    $this->_getResource()->loadByAttribute($this, $customerEmail, $attribute);
                }
            }
        } else { // Login
            $logged = false;
            if (!Mage::getStoreConfig('amcustomerattr/login/disable_email') || !Mage::getStoreConfig('amcustomerattr/login/login_field')) {
                parent::loadByEmail($customerEmail);
                if ($this->getId()) {
                    // customer found by e-mail, no need to load by attribute
                    $logged = true;
                }
            }
            if (Mage::getStoreConfig('amcustomerattr/login/login_field') && !$logged) {
                // will try to load by attribute
                $attribute = Mage::getModel('customer/attribute')->load(Mage::getStoreConfig('amcustomerattr/login/login_field'), 'attribute_code');
                if ($attribute->getId()) {
                    $this->_getResource()->loadByAttribute($this, $customerEmail, $attribute);
                }
            }

            /*
             * check if activated user logged in or not
             */
            $activated =  Mage::getModel('customer/customer')->load($this->getId())->getAmIsActivated();
            $activated = $activated === null ? 0:$activated;// just created user without any activation mark
            $activationRequired = Mage::getStoreConfig('amcustomerattr/activation/activation_required', $this->getStoreId());
            $loginForm   = Mage::app()->getRequest()->getPost('login');
            if (( (($activated==self::ACTIVATION_STATUS_PENDING && $activationRequired) || $activated==self::ACTIVATION_STATUS_DEACTIVATED))  && ($loginForm))
            {// user tries to log in (otherwise we're logged in already and we've changed user via admin panel or etc)
                Mage::getSingleton('customer/session')->addError(Mage::helper('amcustomerattr')->__('Account is not active yet.'));
                $this->setIsLoggedIn(true);
                Mage::getSingleton('core/session')->logout();
                Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('customer/account'));
                return $this;
            }

        }
        
        return $this;
    }
    
    public function custom($attributeCode)
    {
        if ('group_name' == $attributeCode)
        {
            $groupName = '';
            // possibility to get customer group name
            if ($this->getGroupId())
            {
                $group = Mage::getModel('customer/group')->load($this->getGroupId());
                $groupName = $group->getCode();
            }
            return $groupName;
        }
        
        if (!$this->_customAttributes)
        {
            $customAttributes    = array();
            $attributeCollection = Mage::getModel('customer/attribute')->getCollection();
            $alias = Mage::helper('amcustomerattr')->getProperAlias($attributeCollection->getSelect()->getPart('from'), 'eav_attribute');
            $attributeCollection->addFieldToFilter($alias . 'is_user_defined', 1);
            $attributeCollection->addFieldToFilter($alias . 'entity_type_id', Mage::getModel('eav/entity')->setType('customer')->getTypeId());
            foreach ($attributeCollection as $attribute)
            {
                if ($inputType = $attribute->getFrontend()->getInputType())
                {
                    switch ($inputType)
                    {
                        case 'date':
                            if ('0000-00-00' == $this->getData($attribute->getAttributeCode())) {
                                $customAttributes[$attribute->getAttributeCode()] = '';
                            } else {
                                // need to make something with date
                                $customAttributes[$attribute->getAttributeCode()] = $this->getData($attribute->getAttributeCode());
                            }
                            break;
                        case 'text':
                        case 'textarea':
                            $customAttributes[$attribute->getAttributeCode()] = nl2br($this->getData($attribute->getAttributeCode()));
                            break;
                        case 'select':
                        case 'boolean':
                            $value = $this->getData($attribute->getAttributeCode());
                            if (isset($value)) {
                                if ('boolean' == $inputType) {
                                    $hlp = Mage::helper('amcustomerattr');
                                    $customAttributes[$attribute->getAttributeCode()] = $this->getData($attribute->getAttributeCode()) ? $hlp->__('Yes') : $hlp->__('No');
                                } else {
                                    $customAttributes[$attribute->getAttributeCode()] = $this->getLabelForOption($attribute, $value);
                                }
                            } else {
                                $customAttributes[$attribute->getAttributeCode()] = '';
                            }
                            break;
                        case 'multiselect':
                            $columnData = '';
                            $values = explode(',', $this->getData($attribute->getAttributeCode()));
                            foreach ($values as $value) {
                                $columnData .= $this->getLabelForOption($attribute, $value) . ', ';
                            }
                            if ($columnData) {
                                $columnData = substr($columnData, 0, -2);
                            }
                            $customAttributes[$attribute->getAttributeCode()] = $columnData;
                            break;
						case 'file':
                            $value = $this->getData($attribute->getAttributeCode());
                            $customAttributes[$attribute->getAttributeCode()] = $value;
                            break;
                    }
                }
            }
            $this->_customAttributes = $customAttributes;
        }
        return (isset($this->_customAttributes[$attributeCode]) ? $this->_customAttributes[$attributeCode] : '');
    }
    
    public function getLabelForOption($attribute, $value) {
        $optionId = $attribute->getSource()->getOptionId($value);
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('eav/attribute_option_value');
        $select = $db->select()->from($table)->where('option_id = ?', $optionId);
        $labels = $db->fetchAll($select);
        $labelForOption = '';
        foreach ($labels as $label) {
            if (($this->getStoreId() == $label['store_id']) && ('' !== $label['value'])) {
                $labelForOption = $label['value'];
            } elseif ((0 == $label['store_id']) && ('' === $labelForOption)) {
                $labelForOption = $label['value'];
            }
        }
        return $labelForOption;
    }
    
    public function checkContentType($ext, $contentType) {
        $mime_types = array (
            "stl" => "application/SLA",
            "step" => "application/STEP",
            "stp" => "application/STEP",
            "dwg" => array("application/acad", "image/vnd.dwg", "image/x-dwg"),
            "ez" => "application/andrew-inset",
            "ccad" => "application/clariscad",
            "drw" => "application/drafting",
            "tsp" => "application/dsptype",
            "dxf" => array("application/dxf", "image/vnd.dwg", "image/x-dwg"),
            "xls" => array("application/excel", "application/vnd.ms-excel", "application/x-excel", "application/x-msexcel"),
            "unv" => "application/i-deas",
            "jar" => "application/java-archive",
            "hqx" => "application/mac-binhex40",
            "cpt" => array("application/mac-compactpro", "application/x-compactpro", "application/x-cpt"),
            "pot" => array("application/mspowerpoint", "application/vnd.ms-powerpoint"),
            "ppa" => "application/vnd.ms-powerpoint",
            "pps" => array("application/mspowerpoint", "application/vnd.ms-powerpoint"),
            "ppt" => array("application/vnd.ms-powerpoint", "application/mspowerpoint", "application/powerpoint", "application/x-mspowerpoint"),
            "ppz" => array("application/vnd.ms-powerpoint", "application/mspowerpoint"),
            "doc" => "application/msword",
            "bin" => array("application/octet-stream", "application/mac-binary", "application/macbinary", "application/x-binary", "application/x-macbinary"),
            "class" => array("application/octet-stream", "application/java", "application/java-byte-code", "application/x-java-class"),
            "dms" => "application/octet-stream",
            "exe" => array("application/octet-stream", "application/x-msdos-program"),
            "lha" => array("application/octet-stream", "application/lha", "application/x-lha"),
            "lzh" => array("application/octet-stream", "application/x-lzh"),
            "oda" => "application/oda",
            "ogg" => "application/ogg",
            "ogm" => "application/ogg",
            "pdf" => "application/pdf",
            "pgp" => "application/pgp",
            "ai" => "application/postscript",
            "eps" => "application/postscript",
            "ps" => "application/postscript",
            "prt" => "application/pro_eng",
            "rtf" => array("application/rtf", "text/rtf", "application/x-rtf", "text/richtext"),
            "set" => "application/set",
            "smi" => "application/smil",
            "smil" => "application/smil",
            "sol" => "application/solids",
            "vda" => "application/vda",
            "mif" => array("application/vnd.mif", "application/x-mif"),
            "xlc" => array("application/excel", "application/x-excel", "application/vnd.ms-excel"),
            "xll" => array("application/excel", "application/x-excel", "application/vnd.ms-excel"),
            "xlm" => array("application/excel", "application/x-excel", "application/vnd.ms-excel"),
            "xlw" => array("application/excel", "application/x-excel", "application/vnd.ms-excel", "application/x-msexcel"),
            "cod" => "application/vnd.rim.cod",
            "arj" => array("application/x-arj-compressed", "application/arj", "application/octet-stream"),
            "bcpio" => "application/x-bcpio",
            "vcd" => "application/x-cdlink",
            "vmd" => "application/vocaltec-media-desc",
            "pgn" => "application/x-chess-pgn",
            "cpio" => "application/x-cpio",
            "csh" => "application/x-csh",
            "deb" => "application/x-debian-package",
            "dcr" => "application/x-director",
            "dir" => "application/x-director",
            "dxr" => "application/x-director",
            "dvi" => "application/x-dvi",
            "pre" => "application/x-freelance",
            "spl" => "application/x-futuresplash",
            "gtar" => "application/x-gtar",
            "gz" => array("application/x-gunzip", "application/x-gzip", "application/x-compressed"),
            "hdf" => "application/x-hdf",
            "ipx" => "application/x-ipix",
            "ips" => "application/x-ipscript",
            "js" => array("application/x-javascript", "application/javascript", "application/ecmascript", "text/javascript", "text/ecmascript"),
            "skd" => "application/x-koan",
            "skm" => "application/x-koan",
            "skp" => "application/x-koan",
            "skt" => "application/x-koan",
            "latex" => "application/x-latex",
            "lsp" => array("application/x-lisp", "text/x-script.lisp"),
            "scm" => "application/x-lotusscreencam",
            "bat" => "application/x-msdos-program",
            "com" => array("application/x-msdos-program", "application/octet-stream", "text/plain"),
            "cdf" => "application/x-netcdf",
            "nc" => "application/x-netcdf",
            "pl" => array("application/x-perl", "text/plain", "text/x-script.perl"),
            "pm" => array("application/x-perl", "image/x-xpixmap", "text/x-script.perl-module"),
            "pm4" => "application/x-pagemaker",
            "pm5" => "application/x-pagemaker",
            "rar" => "application/x-rar-compressed",
            "sh" => array("application/x-sh", "application/x-bsh", "application/x-shar", "text/x-script.sh"),
            "shar" => "application/x-shar",
            "swf" => "application/x-shockwave-flash",
            "sit" => "application/x-stuffit",
            "sv4cpio" => "application/x-sv4cpio",
            "sv4crc" => "application/x-sv4crc",
            "tar" => array("application/x-tar", "application/x-tar-gz"),
            "tgz" => array("application/gnutar", "application/x-tar-gz", "application/x-compressed"),
            "tcl" => array("text/x-script.tcl", "application/x-tcl"),
            "tex" => "application/x-tex",
            "texi" => "application/x-texinfo",
            "texinfo" => "application/x-texinfo",
            "man" => "application/x-troff-man",
            "me" => "application/x-troff-me",
            "ms" => "application/x-troff-ms",
            "roff" => "application/x-troff",
            "t" => "application/x-troff",
            "tr" => "application/x-troff",
            "ustar" => "application/x-ustar",
            "src" => "application/x-wais-source",
            "zip" => array("application/x-zip-compressed", "application/zip", "multipart/x-zip", "application/x-compressed", "application/octet-stream"),
            "tsi" => "audio/TSP-audio",
            "au" => array("audio/basic", "audio/ulaw", "audio/x-au"),
            "snd" => "audio/basic",
            "kar" => "audio/midi",
            "mid" => array("audio/midi", "application/x-midi", "audio/x-mid", "audio/x-midi", "music/crescendo", "x-music/x-midi"),
            "midi" => array("audio/midi", "application/x-midi", "audio/x-mid", "audio/x-midi", "music/crescendo", "x-music/x-midi"),
            "mp2" => array("audio/mpeg", "video/mpeg", "audio/x-mpeg", "video/x-mpeg", "video/x-mpeq2a"),
            "mp3" => array("audio/mpeg", "audio/mpeg3", "audio/x-mpeg-3", "video/mpeg", "video/x-mpeg"),
            "mpga" => "audio/mpeg",
            "aif" => array("audio/x-aiff", "audio/aiff"),
            "aifc" => "audio/x-aiff",
            "aiff" => array("audio/x-aiff", "audio/aiff"),
            "m3u" => "audio/x-mpegurl",
            "wax" => "audio/x-ms-wax",
            "wma" => "audio/x-ms-wma",
            "rpm" => "audio/x-pn-realaudio-plugin",
            "ram" => "audio/x-pn-realaudio",
            "rm" => array("audio/x-pn-realaudio", "application/vnd.rn-realmedia"),
            "ra" => array("audio/x-realaudio", "audio/x-pn-realaudio", "audio/x-pn-realaudio-plugin"),
            "wav" => array("audio/x-wav", "audio/wav"),
            "pdb" => "chemical/x-pdb",
            "xyz" => array("chemical/x-pdb", "chemical/x-xyz"),
            "ras" => array("image/x-cmu-raster", "image/cmu-raster"),
            "gif" => "image/gif",
            "ief" => "image/ief",
            "jpe" => array("image/jpeg", "image/pjpeg"),
            "jpeg" => array("image/jpeg", "image/pjpeg"),
            "jpg" => array("image/jpeg", "image/pjpeg"),
            "png" => "image/png",
            "tif" => array("image/tiff", "image/x-tiff"),
            "tiff" => array("image/tiff", "image/x-tiff"),
            "pnm" => "image/x-portable-anymap",
            "pbm" => "image/x-portable-bitmap",
            "pgm" => "image/x-portable-graymap",
            "ppm" => "image/x-portable-pixmap",
            "rgb" => "image/x-rgb",
            "xbm" => "image/x-xbitmap",
            "xpm" => "image/x-xpixmap",
            "xwd" => "image/x-xwindowdump",
            "iges" => "model/iges",
            "igs" => "model/iges",
            "mesh" => "model/mesh",
            "msh" => "model/mesh",
            "silo" => "model/mesh",
            "vrml" => array("model/vrml", "x-world/x-vrml", "application/x-vrml"),
            "wrl" => "model/vrml",
            "css" => array("text/css", "application/x-pointplus"),
            "htm" => "text/html",
            "html" => "text/html",
            "asc" => "text/plain",
            "c" => array("text/plain", "text/x-c"),
            "cc" => "text/plain",
            "f90" => "text/plain",
            "f" => "text/plain",
            "h" => array("text/plain", "text/x-h"),
            "hh" => "text/plain",
            "m" => "text/plain",
            "txt" => "text/plain",
            "rtx" => array("application/rtf", "text/richtext"),
            "sgm" => "text/sgml",
            "sgml" => array("text/sgml", "text/x-sgml"),
            "tsv" => "text/tab-separated-values",
            "jad" => "text/vnd.sun.j2me.app-descriptor",
            "etx" => "text/x-setext",
            "xml" => array("application/xml", "text/xml"),
            "dl" => array("video/dl", "video/x-dl"),
            "fli" => array("video/fli", "video/x-fli"),
            "flv" => "video/flv",
            "gl" => "video/gl",
            "mpe" => "video/mpeg",
            "mpeg" => "video/mpeg",
            "mpg" => array("video/mpeg", "audio/mpeg"),
            "mov" => "video/quicktime",
            "qt" => "video/quicktime",
            "viv" => "video/vnd.vivo",
            "vivo" => "video/vnd.vivo",
            "asf" => "video/x-ms-asf",
            "asx" => array("application/x-mplayer2", "video/x-ms-asx", "video/x-ms-asf-plugin"),
            "wmv" => "video/x-ms-wmv",
            "wmx" => "video/x-ms-wmx",
            "wvx" => "video/x-ms-wvx",
            "avi" => array("video/x-msvideo", "application/x-troff-msvideo", "video/avi", "video/msvideo"),
            "movie" => "video/x-sgi-movie",
            "mime" => "www/mime",
            "ice" => "x-conference/x-cooltalk",
            "vrm" => "x-world/x-vrml",
            "atom" => "application/atom+xml",
            "bmp" => array("image/bmp", "image/x-windows-bmp"),
            "cgm" => "image/cgm",
            "dif" => "video/x-dv",
            "djv" => "image/vnd.djvu",
            "djvu" => "image/vnd.djvu",
            "dll" => "application/octet-stream",
            "dmg" => "application/octet-stream",
            "dtd" => "application/xml-dtd",
            "dv" => "video/x-dv",
            "gram" => "application/srgs",
            "grxml" => "application/srgs+xml",
            "ico" => "image/x-icon",
            "ics" => "text/calendar",
            "ifb" => "text/calendar",
            "jnlp" => "application/x-java-jnlp-file",
            "jp2" => "image/jp2",
            "m4a" => "audio/mp4a-latm",
            "m4b" => "audio/mp4a-latm",
            "m4p" => "audio/mp4a-latm",
            "m4u" => "video/vnd.mpegurl",
            "m4v" => "video/x-m4v",
            "mac" => "image/x-macpaint",
            "mathml" => "application/mathml+xml",
            "mp4" => "video/mp4",
            "mxu" => "video/vnd.mpegurl",
            "pct" => array("image/x-pict", "image/pict"),
            "pic" => "image/pict",
            "pict" => "image/pict",
            "pnt" => "image/x-macpaint",
            "pntg" => "image/x-macpaint",
            "qti" => "image/x-quicktime",
            "qtif" => "image/x-quicktime",
            "rdf" => "application/rdf+xml",
            "so" => "application/octet-stream",
            "svg" => "image/svg+xml",
            "vxml" => "application/voicexml+xml",
            "wbmp" => "image/vnd.wap.wbmp",
            "wbmxl" => "application/vnd.wap.wbxml",
            "wml" => "text/vnd.wap.wml",
            "wmlc" => "application/vnd.wap.wmlc",
            "wmls" => "text/vnd.wap.wmlscript",
            "wmlsc" => "application/vnd.wap.wmlscriptc",
            "xht" => "application/xhtml+xml",
            "xhtml" => "application/xhtml+xml",
            "xsl" => "application/xml",
            "xslt" => "application/xslt+xml",
            "xul" => "application/vnd.mozilla.xul+xml",
            "ani" => "application/x-navi-animation",
            "aos" => "application/x-nokia-9000-communicator-add-on-software",
            "aps" => "application/mime",
            "arc" => "application/octet-stream",
            "art" => "image/x-jg",
            "asm" => "text/x-asm",
            "asp" => "text/asp",
            "bm" => "image/bmp",
            "boo" => "application/book",
            "book" => "application/book",
            "c++" => "text/plain",
            "conf" => "text/plain",
            "cpp" => "text/x-c",
            "def" => "text/plain",
            "dot" => "application/msword",
            "gzip" => array("application/x-gzip", "multipart/x-gzip"),
            "hlp" => array("application/hlp", "application/x-helpfile", "application/x-winhelp"),
            "htc" => "text/x-component",
            "htmls" => "text/html",
            "htt" => "text/webviewhtml",
            "inf" => "application/inf",
            "jam" => "audio/x-jam",
            "jav" => array("text/plain", "text/x-java-source"),
            "java" => array("text/plain", "text/x-java-source"),
            "jcm" => "application/x-java-commerce",
            "jfif" => array("image/jpeg", "image/pjpeg"),
            "jfif-tbnl" => "image/jpeg",
            "jps" => "image/x-jps",
            "lhx" => "application/octet-stream",
            "list" => "text/plain",
            "lst" => "text/plain",
            "lzx" => array("application/lzx", "application/octet-stream", "application/x-lzx"),
            "mod" => array("audio/mod", "audio/x-mod"),
            "mpa" => array("audio/mpeg", "video/mpeg"),
            "pas" => "text/pascal",
            "pcl" => array("application/vnd.hp-pcl", "application/x-pcl"),
            "pcx" => "image/x-pcx",
            "psd" => "application/octet-stream",
            "pwz" => "application/vnd.ms-powerpoint",
            "py" => "text/x-script.phyton",
            "pyc" => "applicaiton/x-bytecode.python",
            "rv" => "video/vnd.rn-realvideo",
            "shtml" => array("text/html", "text/x-server-parsed-html"),
            "ssi" => "text/x-server-parsed-html",
            "text" => array("application/plain", "text/plain"),
            "uri" => "text/uri-list",
            "vsd" => "application/x-visio",
            "vst" => "application/x-visio",
            "vsw" => "application/x-visio",
            "wmf" => "windows/metafile",
            "xla" => array("application/excel", "application/x-excel", "application/x-msexcel"),
            "xlb" => array("application/excel", "application/x-excel", "application/vnd.ms-excel"),
            "xld" => array("application/excel", "application/x-excel"),
            "xlk" => array("application/excel", "application/x-excel"),
            "xlt" => array("application/excel", "application/x-excel"),
            "xlv" => array("application/excel", "application/x-excel"),
            "xm" => "audio/xm",
            "z" => array("application/x-compress", "application/x-compressed"),
        );
        foreach ($mime_types as $key => $value) {
            if ($key === $ext) {
                if (!is_array($value)) {
                    if ($value === $contentType) {
                        return true;
                    }
                } elseif (in_array($contentType, $value)) {
                    return true;
                }
                break;
            }
        }
        return false;
    }
}