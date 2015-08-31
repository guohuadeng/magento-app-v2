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
 * @package     Sunpop_Moneris
 * @copyright   Copyright (c) 2015 Ivan Deng. (http://www.sunpop.cn)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Store Locator Adminhtml Controller
 *
 * @author      Qun WU <info@Sunpopwebsolutions.com>
 */
class Sunpop_Storelocator_AdminhtmlController extends Mage_Adminhtml_Controller_Action
{
 
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('storelocator/stores')
            ->_addBreadcrumb(Mage::helper('storelocator')->__('Store Manager'), Mage::helper('storelocator')->__('Store Manager'));
        return $this;
    }   
   
    public function indexAction() {
        $this->_initAction();       
        $this->_addContent($this->getLayout()->createBlock('storelocator/adminhtml_storelocator'));
        $this->renderLayout();
    }
 
    public function editAction()
    {
        $storelocatorId     = $this->getRequest()->getParam('id');
        $storelocatorModel  = Mage::getModel('storelocator/storelocator')->load($storelocatorId)->setDefault();
 
        if ($storelocatorModel->getId() || $storelocatorId == 0) {
 
            Mage::register('storelocator_data', $storelocatorModel);
 
            $this->loadLayout();
            $this->_setActiveMenu('storelocator/stores');
            
            if ($storelocatorModel->getId()) {
                $breadcrumbTitle = Mage::helper('storelocator')->__('Edit Store');
                $breadcrumbLabel = $breadcrumbTitle;
            }
            else {
                $breadcrumbTitle = Mage::helper('storelocator')->__('New Store');
                $breadcrumbLabel = Mage::helper('storelocator')->__('Create Store');
            }
            
            $this->_title($storelocatorModel->getId() ? $this->__('Edit Store') : $this->__('New Store'));
            
            $this->_addBreadcrumb($breadcrumbLabel, $breadcrumbTitle);

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
       
              
            if ($editBlock = $this->getLayout()->getBlock('storelocator_edit')) {
                $editBlock->setEditMode($storelocatorModel->getId() > 0);
            }
               
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('storelocator')->__('Store does not exist'));
            $this->_redirect('*/*/');
        }
    }
   
    public function newAction()
    {
        $this->_forward('edit');
    }
   
    public function saveAction()
    {
        if ( $this->getRequest()->getPost() ) {
            try {
                $postData = $this->getRequest()->getPost();
                $storelocatorModel = Mage::getModel('storelocator/storelocator');
               
                $storelocatorModel->setId($this->getRequest()->getParam('storelocator_id'))->setData($postData)->save();
    
               
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('storelocator')->__('Item was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setStorelocatorData(false);
 
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setStorelocatorData($this->getRequest()->getPost());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }
   
    public function deleteAction()
    {
        if( $this->getRequest()->getParam('id') > 0 ) {
            try {
                $storelocatorModel = Mage::getModel('storelocator/storelocator');
               
                $storelocatorModel->setId($this->getRequest()->getParam('id'))
                    ->delete();
                   
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('storelocator')->__('Store was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }
    
    public function massDeleteAction()
    {
        $storelocatorIds = $this->getRequest()->getParam('storelocator');
        if (!is_array($storelocatorIds)) {
            $this->_getSession()->addError($this->__('Please select $storelocator(s).'));
        } else {
            if (!empty($storelocatorIds)) {
                try {
                    foreach ($storelocatorIds as $storelocatorId) {
                        $storelocator = Mage::getSingleton('storelocator/storelocator')->load($storelocatorId);
                        Mage::dispatchEvent('storelocator_controller_storelocator_delete', array('storelocator' => $storelocator));
                        $storelocator->delete();
                    }
                    $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) have been deleted.', count($storelocatorIds))
                    );
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }
        $this->_redirect('*/*/index');
    }
    
    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'storelocator.csv';
        $grid       = $this->getLayout()->createBlock('storelocator/adminhtml_storelocator_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    } 
    
    /**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction()
    {
        $fileName   = 'storelocator.xml';
        $grid       = $this->getLayout()->createBlock('storelocator/adminhtml_storelocator_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }    
    
    /**
     * Product grid for AJAX request.
     * Sort and filter result for example.
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
               $this->getLayout()->createBlock('storelocator/adminhtml_storelocator_grid')->toHtml()
        );
    }
}