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

class Sunpop_News_Adminhtml_NewsController extends Mage_Adminhtml_Controller_Action
{
    public function preDispatch() {
        parent::preDispatch();
    }

    /**
     * Init actions
     *
     */
    protected function _initAction()
    {
        // load layout, set active menu
        $this->loadLayout()
            ->_setActiveMenu('clnews/items');
        return $this;
    }

    public function indexAction() {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('clnews/adminhtml_news'))
            ->renderLayout();
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('clnews/news')->load($id);

        if ($id) {
            $model->load($id);
            if (! $model->getId()) {
                Mage::getSingleton('adminhtml/session')
                    ->addError(Mage::helper('clnews')->__('News article does not exist'));
                $this->_redirect('*/*/');
                return;
            }
        }

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);

        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('clnews_data', $model);

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('clnews/adminhtml_news_edit'))
            ->_addLeft($this->getLayout()->createBlock('clnews/adminhtml_news_edit_tabs'))
            ->renderLayout();
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {
            $_newsItem = Mage::getModel('clnews/news')->getCollection()
                ->addFieldToFilter('url_key', $data['url_key'])
                ->setPageSize(1)
                ->getFirstItem();
            /*
            $arr = array();
            $arr = $newsCollection->getData();
            if (isset($arr[0]) && $this->getRequest()->getParam('id') == $arr[0]['news_id'] && $data['url_key'] == $arr[0]['url_key']) {
                $sameUrl = null;
            } else {
                $sameUrl = $newsCollection->getData();
            }
            if ($sameUrl != null) {*/
            if ($_newsItem->getId() &&
            ($this->getRequest()->getParam('id') == $_newsItem->getId()) &&
            ($data['url_key'] == $_newsItem->getUrlKey()) &&
            (count(array_diff($_newsItem->getStoreId(), $data['stores'])) == 0) &&
            (count(array_diff($data['stores'], $_newsItem->getStoreId())) == 0 )
            ) {
                $sameUrl = false;
            } else {
                $sameUrl = false;
                $_newsItemCollection = Mage::getModel('clnews/news')->getCollection()
                ->addFieldToFilter('news_id', array('neq' => $this->getRequest()->getParam('id')))
                ->addFieldToFilter('url_key', $data['url_key']);
                foreach($_newsItemCollection as $_newsItem) {
                    if ((count(array_intersect($_newsItem->getStoreId(), $data['stores'])) != 0) ||
                    in_array(0, $data['stores']) ||
                    in_array(0, $_newsItem->getStoreId())
                    ) {
                        $sameUrl = true;
                    }
                }
            }
            if ($sameUrl) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('clnews')->__('News Item with such url_key already exists'));
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            } else {

                if (isset($data['is_delete'])) {
                    $isDeleteFile = true;
                } else {
                    $isDeleteFile = false;
                }

                if (isset($_FILES['document_save']['name']) && ($_FILES['document_save']['name'] != '')
                    && ($_FILES['document_save']['size'] != 0) ) {
                    try {
                        $uploader = new Varien_File_Uploader('document_save');
                        $uploader->setAllowRenameFiles(false);

                        // Set the file upload mode
                        // false -> get the file directly in the specified folder
                        // true -> get the file in folders like /media/a/b/
                        $uploader->setFilesDispersion(false);

                        $path = Mage::getBaseDir('media') . DS . 'clnews' . DS;

                        //saved the name in DB
                        $prefix = time().rand();
                        $fileName = $prefix.'.'.pathinfo($_FILES['document_save']['name'], PATHINFO_EXTENSION);
                        $uploader->save($path, $fileName);
                        $filepath = 'clnews' . DS .$fileName;
                        $data['full_path_document'] = $path . $fileName;
                        $data['document'] = $fileName;
                        $data['document'] = str_replace('\\', '/', $data['document']);
                    } catch (Exception $e) {
                        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                        $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                    }
                } elseif ($isDeleteFile === true) {
                    unlink($data['full_path_document']);
                    $data['document']='';
                } else {
                    /// to insert a code for deleting image
                    /// ....
                    if (isset($data['document'])) {
                        $data['document']=$data['document'];
                    }
                }

                if (isset($_FILES['image_short_content']['name']) && ($_FILES['image_short_content']['name'] != '')
                    && ($_FILES['image_short_content']['size'] != 0) ) {
                    try {
                        $uploader = new Varien_File_Uploader('image_short_content');
                        $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                        $uploader->setAllowRenameFiles(false);

                        // Set the file upload mode
                        // false -> get the file directly in the specified folder
                        // true -> get the file in folders like /media/a/b/
                        $uploader->setFilesDispersion(false);

                        $path = Mage::getBaseDir('media') . DS . 'clnews' . DS;

                        //saved the name in DB
                        $prefix = time().rand();
                        $fileName = $prefix.'.'.pathinfo($_FILES['image_short_content']['name'], PATHINFO_EXTENSION);
                        $uploader->save($path, $fileName);
                        $filepath = 'clnews' . DS .$fileName;
                        /*
                        if (!getimagesize($filepath)) {
                            Mage::throwException($this->__('Disallowed file type.'));
                        }*/
                        $data['image_short_content'] = $filepath;
                        $data['image_short_content'] = str_replace('\\', '/', $data['image_short_content']);
                    } catch (Exception $e) {
                        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                        $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                        return;
                    }
                } elseif (isset($data['image_short_content']['delete'])) {
                    $path = Mage::getBaseDir('media') . DS;
                    $result = unlink($path . $data['image_short_content']['value']);
                    if ($data['short_height_resize'] && $data['short_width_resize']) {
                        $resizePath = Mage::getBaseDir('media') . DS . 'clnews' . DS . $data['short_width_resize'] . 'x' . $data['short_height_resize'] . DS;
                    }
                    $result = unlink($resizePath . str_replace('clnews/', '', $data['image_short_content']['value']));
                    $data['image_short_content'] = '';
                } else {
                    if (isset($data['image_short_content']['value'])) {
                        $data['image_short_content'] = $data['image_short_content']['value'];
                    }
                }

                if (isset($_FILES['image_full_content']['name']) && ($_FILES['image_full_content']['name'] != '')
                    && ($_FILES['image_full_content']['size'] != 0) ) {
                    try {
                        $uploader = new Varien_File_Uploader('image_full_content');
                        $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                        $uploader->setAllowRenameFiles(false);

                        // Set the file upload mode
                        // false -> get the file directly in the specified folder
                        // true -> get the file in folders like /media/a/b/
                        $uploader->setFilesDispersion(false);

                        $path = Mage::getBaseDir('media') . DS . 'clnews' . DS;

                        //saved the name in DB
                        $prefix = time().rand();
                        $fileName = $prefix.'.'.pathinfo($_FILES['image_full_content']['name'], PATHINFO_EXTENSION);
                        $uploader->save($path, $fileName);
                        $filepath = 'clnews' . DS .$fileName;
                        $data['image_full_content'] = $filepath;
                        $data['image_full_content'] = str_replace('\\', '/', $data['image_full_content']);
                    } catch (Exception $e) {
                        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                        $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                        return;
                    }
                } elseif (isset($data['image_full_content']['delete'])) {
                    $path = Mage::getBaseDir('media') . DS;
                    $result = unlink($path . $data['image_full_content']['value']);
                    if ($data['full_height_resize'] && $data['full_width_resize']) {
                        $resizePath = Mage::getBaseDir('media') . DS . 'clnews' . DS . $data['full_width_resize'] . 'x' . $data['full_height_resize'] . DS;
                    }
                    $result = unlink($resizePath . str_replace('clnews/', '', $data['image_full_content']['value']));
                    $data['image_full_content'] = '';
                } else {
                    if (isset($data['image_full_content']['value'])) {
                        $data['image_full_content'] = $data['image_full_content']['value'];
                    }
                }

                if (isset($data['use_full_img'])) {
                    if (isset($data['image_full_content'])) {
                        $data['image_short_content'] = $data['image_full_content'];
                    }
                }

                $model = Mage::getModel('clnews/news');
                $hoursFrom = $this->getRequest()->getParam('publicate_from_hours');
                $minutesFrom = $this->getRequest()->getParam('publicate_from_minutes');
                $hoursTo = $this->getRequest()->getParam('publicate_to_hours');
                $minutesTo = $this->getRequest()->getParam('publicate_to_minutes');
                $data['publicate_from_hours'] = $hoursFrom;
                $data['publicate_from_minutes'] = $minutesFrom;
                $data['publicate_to_hours'] = $hoursTo;
                $data['publicate_to_minutes'] = $minutesTo;
                $data['link'] = $this->getRequest()->getParam('link');
                $data['tags'] = $this->getRequest()->getParam('tags');

                // prepare dates
                if ($this->getRequest()->getParam('news_time')!='') {
                    $dateFormatIso = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                    if (!Zend_Date::isDate($this->getRequest()->getParam('news_time') . ' ' . date("H:i:s"), $dateFormatIso)) {
                        throw new Exception($this->__(('News date field is required')));
                    }
                    $date = new Zend_Date($this->getRequest()->getParam('news_time') . ' ' . date("H:i:s"), $dateFormatIso);
                    $dateInfo = $date->toArray();
                    $data['news_time'] = preg_replace('/([0-9]{4})\-(.*)/', $dateInfo['year'].'-$2', $date->toString('YYYY-MM-dd HH:mm:ss'));
                } else {
                    $data['news_time'] = new Zend_Db_Expr('null');
                }

                if ($this->getRequest()->getParam('publicate_from_time')!='') {
                    if (!Zend_Date::isDate($this->getRequest()->getParam('publicate_from_time'). ' ' . $hoursFrom . ':' . $minutesFrom . ':00', $dateFormatIso)) {
                        throw new Exception($this->__(('News date field is required')));
                    }
                    $date = new Zend_Date($this->getRequest()->getParam('publicate_from_time'). ' ' . $hoursFrom . ':' . $minutesFrom . ':00', $dateFormatIso);
                    $dateInfo = $date->toArray();
                    $data['publicate_from_time'] = preg_replace('/([0-9]{4})\-(.*)/', $dateInfo['year'].'-$2', $date->toString('YYYY-MM-dd HH:mm:ss'));
                } else {
                    $data['publicate_from_time'] = new Zend_Db_Expr('null');
                }

                if ($this->getRequest()->getParam('publicate_to_time')!='') {
                    if (!Zend_Date::isDate($this->getRequest()->getParam('publicate_to_time'). ' ' . $hoursTo . ':' . $minutesTo . ':00', $dateFormatIso)) {
                        throw new Exception($this->__(('News date field is required')));
                    }
                    $date = new Zend_Date($this->getRequest()->getParam('publicate_to_time'). ' ' . $hoursTo . ':' . $minutesTo . ':00', $dateFormatIso);
                    $dateInfo = $date->toArray();
                    $data['publicate_to_time'] = preg_replace('/([0-9]{4})\-(.*)/', $dateInfo['year'].'-$2', $date->toString('YYYY-MM-dd HH:mm:ss'));
                } else {
                    $data['publicate_to_time'] = new Zend_Db_Expr('null');
                }
                $model->setData($data)
                    ->setId($this->getRequest()->getParam('id'));

                try {
                    if ($this->getRequest()->getParam('news_time') == NULL) {
                        $model->setNewsTime(now());
                        $model->setCreatedTime(now());
                    } else {
                        if (isset($arr[0]) && (!$newsItemId = $arr[0]['news_id'])) {
                            $model->setCreatedTime(now());
                        }
                    }

                    $model->setUpdateTime(now());

                    if ($this->getRequest()->getParam('author') == NULL) {
                        $model->setUpdateAuthor(NULL);
                        /*$model->setAuthor(Mage::getSingleton('admin/session')->getUser()->getFirstname() .
                            " " . Mage::getSingleton('admin/session')->getUser()->getLastname())
                            ->setUpdateAuthor(Mage::getSingleton('admin/session')->getUser()->getFirstname() .
                            " " . Mage::getSingleton('admin/session')->getUser()->getLastname());*/
                    } else {
                        $model->setUpdateAuthor(Mage::getSingleton('admin/session')->getUser()->getFirstname() .
                            " " . Mage::getSingleton('admin/session')->getUser()->getLastname());
                    }
                    $model->save();

                    Mage::getSingleton('adminhtml/session')
                        ->addSuccess(Mage::helper('clnews')->__('News article has been successfully saved'));
                    Mage::getSingleton('adminhtml/session')->setFormData(false);

                    if ($this->getRequest()->getParam('back')) {
                        $this->_redirect('*/*/edit', array('id' => $model->getId()));
                        return;
                    }
                    $this->_redirect('*/*/');
                    return;
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    Mage::getSingleton('adminhtml/session')->setFormData($data);
                    $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                    return;
                }

                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('clnews')->__('No items to save'));
                $this->_redirect('*/*/');
            }
        }
    }

    public function deleteAction() {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('clnews/news');
                $model->load($id);
                $model->delete();

                Mage::getSingleton('adminhtml/session')
                    ->addSuccess(Mage::helper('adminhtml')->__('Item has been successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        $newsIds = $this->getRequest()->getParam('clnews');
        if (!is_array($newsIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                $model = Mage::getModel('clnews/news');
                foreach ($newsIds as $newsId) {
                    $model->reset()
                        ->load($newsId)
                        ->delete();
                }
                Mage::getSingleton('adminhtml/session')
                    ->addSuccess(Mage::helper('adminhtml')
                    ->__('%d record(s) have been successfully deleted', count($newsIds)));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }


    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'news.csv';
        $grid       = $this->getLayout()
            ->createBlock('clnews/adminhtml_news_grid')
            ->addColumn('news_id', array(
            'header'    => Mage::helper('clnews')->__('ID'),
            'align'     =>'right',
            'width'     => '50',
            'index'     => 'news_id',
        ))->addColumn('title', array(
            'header'    => Mage::helper('clnews')->__('Title'),
            'align'     =>'left',
            'index'     => 'title',
        ))->addColumn('url_key', array(
            'header'    => Mage::helper('clnews')->__('URL Key'),
            'align'     => 'left',
            'index'     => 'url_key',
        ))->addColumn('author', array(
            'header'    => Mage::helper('clnews')->__('Author'),
            'index'     => 'author',
        ))->addColumn('short_content', array(
            'header'    => Mage::helper('clnews')->__('Short Content'),
            'type'     =>'text',
            'index'     => 'short_content',
        ))->addColumn('image_short_content', array(
            'header'    => Mage::helper('clnews')->__('Short Content Image'),
            'type'     =>'text',
            'display' => 'none',
            'index'     => 'image_short_content',
        ))->addColumn('full_content', array(
            'header'    => Mage::helper('clnews')->__('Full Content'),
            'type'     =>'text',
            'index'     => 'full_content',
        ))->addColumn('image_full_content', array(
            'header'    => Mage::helper('clnews')->__('Full Content Image'),
            'type'     =>'text',
            'index'     => 'image_full_content',
        ))->addColumn('document', array(
            'header'    => Mage::helper('clnews')->__('Document'),
            'type'     =>'text',
            'index'     => 'document',
        ))->addColumn('meta_keywords', array(
            'header'    => Mage::helper('clnews')->__('Meta Keywords'),
            'type'     =>'text',
            'index'     => 'meta_keywords',
        ))->addColumn('meta_description', array(
            'header'    => Mage::helper('clnews')->__('Meta Description'),
            'type'     =>'text',
            'index'     => 'meta_description',
        ))->addColumn('tags', array(
            'header'    => Mage::helper('clnews')->__('Tags'),
            'type'     =>'text',
            'index'     => 'tags',
        ));

        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    public function massStatusAction()
    {
        $newsIds = $this->getRequest()->getParam('clnews');
        if (!is_array($newsIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($newsIds as $newsId) {
                    $model = Mage::getSingleton('clnews/news')
                        ->setId($newsId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->save();
                }
                $this->_getSession()
                    ->addSuccess($this->__('%d record(s) have been successfully updated', count($newsIds)));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
}
