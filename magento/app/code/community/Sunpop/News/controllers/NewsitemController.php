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

class Sunpop_News_NewsitemController extends Mage_Core_Controller_Front_Action
{
    public function viewAction()
    {
        $session = Mage::getSingleton('core/session');
        $categoryKey = $this->getRequest()->getParam('category');
        if ($categoryKey) {
            $collection = Mage::getModel('clnews/category')->getCollection()
                ->addFieldToFilter('url_key', $categoryKey);
                $category = $collection->getFirstItem();
        } else {
            $category = null;
        }

        $news_key = $this->getRequest()->getParam('key');
        $news_id = (int)$this->getRequest()->getParam('id');
        if (preg_match('/[A-Za-z0-9\-\_]+/', $news_key)) {
            $collection = Mage::getModel('clnews/news')->getCollection()
                ->addFieldToFilter('url_key', $news_key)
                ->addEnableFilter(1)
                ->addFieldToFilter('publicate_from_time', array('or' => array(
                    0 => array('date' => true, 'to' => date('Y-m-d H:i:s')),
                    1 => array('is' => new Zend_Db_Expr('null'))),
                    ), 'left')
                ->addFieldToFilter('publicate_to_time', array('or' => array(
                    0 => array('date' => true, 'from' => date('Y-m-d H:i:s')),
                    1 => array('is' => new Zend_Db_Expr('null'))),
                    ), 'left')
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->load();
                $newsitem = $collection->getFirstItem();
            if ($newsitem->getData() && $newsitem->getData('status') == 1) {
                if ($category!=null) {
                    $newsitem->setCategory($category);
                }
                Mage::register('newsitem', $newsitem);
            } else {
                $this->_forward('NoRoute');
                return;
            }
        }

        if (!preg_match('/[A-Za-z0-9\-\_]+/', $news_key) && $news_id && !Mage::registry('clnews') ) {
            $newsitem = Mage::getModel('clnews/news')->load($news_id);
            if ($newsitem!=null) {
                if ($category!=null) {
                    $newsitem->setCategory($category);
                }
                Mage::register('newsitem', $newsitem);
            } else {
                $this->_redirect(Mage::helper('clnews')->getRoute());
            }
        }

        $mode = $this->getRequest()->getParam('mode');
        if ($mode=='print') {
            $this->_forward('print');
            return;
        }

        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('clnews/comment');
            $model->setData($data);
            if (!$newsitem->getCommentsEnabled()) {
                $session->addError(Mage::helper('clnews')->__('Comments are not enabled.'));
                $this->_forward('NoRoute');
                return;
            }
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $model->setUser(Mage::helper('clnews')->getUserName());
                $model->setEmail(Mage::helper('clnews')->getUserEmail());
            }

            try {
                $model->setCreatedTime(now());
                $model->setComment(htmlspecialchars($model->getComment(), ENT_QUOTES));
                if ((int)Mage::getStoreConfig('clnews/comments/need_confirmation')) {
                    $model->setCommentStatus(Sunpop_News_Helper_Data::UNAPPROVED_STATUS);
                    $session->addSuccess(Mage::helper('clnews')->__('Your comment has been successfully sent. It will be displayed after approval by our admin'));
                } else {
                    $model->setCommentStatus(Sunpop_News_Helper_Data::APPROVED_STATUS);
                    $session->addSuccess(Mage::helper('clnews')->__('Thank you for adding a comment.'));
                }
                $model->save();

                $commentId = $model->getCommentId();
            } catch (Exception $e) {
                $this->_forward('NoRoute');
            }

            if ((int)Mage::getStoreConfig('clnews/comments/need_confirmation') && Mage::getStoreConfig('clnews/comments/recipient_email') != null && $model->getStatus() == Sunpop_News_Helper_Data::UNAPPROVED_STATUS && isset($commentId)) {
                $translate = Mage::getSingleton('core/translate');
                /* @var $translate Mage_Core_Model_Translate */
                $translate->setTranslateInline(false);
                try {
                    $emailData = new Varien_Object();
                    $data["url"] = Mage::getUrl('clnews/adminhtml_comment/edit/id/' . $commentId);
                    $emailData->setData($data);
                    $mailTemplate = Mage::getModel('core/email_template');
                    $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                        ->sendTransactional(Mage::getStoreConfig('clnews/comments/email_template'),
                            Mage::getStoreConfig('clnews/comments/sender_email_identity'),
                            Mage::getStoreConfig('clnews/comments/recipient_email'),
                            null,
                            array('data' => $emailData));
                    $translate->setTranslateInline(true);
                } catch (Exception $e) {
                    $translate->setTranslateInline(true);
                }
            }
            $this->_redirectReferer();
            return;
        }
        $newsitem = Mage::registry('newsitem');
        if (!$newsitem) {
            $this->_forward('NoRoute');
        }
        $this->loadLayout();
        $this->_initLayoutMessages('core/session');
        $this->renderLayout();
    }

    public function printAction()
    {
        $head = $this->getLayout()->getBlock('head');
        $news_id = (int)$this->getRequest()->getParam('article');
        if ($news_id && !Mage::registry('clnews')) {
            $newsitem = Mage::getModel('clnews/news')->load($news_id);
            if ($newsitem!=null) {
                Mage::register('newsitem', $newsitem);
            } else {
                $this->_redirect(Mage::helper('clnews')->getRoute());
            }
        }
        $block = $this->getLayout()->createBlock('clnews/newsitem')->setTemplate('clnews/news_print.phtml');
        echo $block->toHtml();
    }

    public function ajaxAction()
    {
        $data = array();
        $pages = array();
        $currentPage = $this->getRequest()->getParam('page');
        if (!$currentPage) {
            $currentPage=1;
        }

        $itemsPerPage = (int)Mage::getStoreConfig('clnews/comments/commentsperpage');
        if ($itemsPerPage > 0) {
            $itemsOnPage = $itemsPerPage;
        } else {
            $itemsOnPage = 10;
        }
        $collection = Mage::getModel('clnews/comment')->getCollection()
            ->addNewsFilter($this->getRequest()->getParam('id'))
            ->addApproveFilter(Sunpop_News_Helper_Data::APPROVED_STATUS)
            ->setOrder('created_time ', 'asc');
        $pagesCount = ceil($collection->getSize()/$itemsOnPage);
        for ($i=1; $i<=$pagesCount;$i++) {
            $pages[] = $i;
        }

        $collection->setPageSize($itemsOnPage);
        $collection->setCurPage($currentPage);
        if ($currentPage == 1) {
            $backPage = 'undefined';
            $fovardPage = $currentPage + 1;
        } elseif ($currentPage == $pagesCount) {
            $backPage = $currentPage -1;
            $fovardPage = 'undefined';
        } else {
            $backPage = $currentPage - 1;
            $fovardPage = $currentPage + 1;
        }
        $k = 0;
        foreach ($collection->getData() as $val) {
            $data['collection'][$k]['comment_id'] = $val['comment_id'];
            $data['collection'][$k]['news_id'] = $val['news_id'];
            $data['collection'][$k]['comment'] = $val['comment'];
            $data['collection'][$k]['status'] = $val['comment_status'];
            $data['collection'][$k]['created_time'] = $val['created_time'];
            $data['collection'][$k]['user'] = $val['user'];
            $data['collection'][$k]['email'] = $val['email'];
            $k++;
        }
        if ($backPage != 'undefined') {
            $data['dat']['back_url'] = 'onclick="AjaxSend(' . $backPage . ',' . $this->getRequest()->getParam('id') . ', true);"';
        }
        if ($fovardPage != 'undefined') {
            $data['dat']['fovard_url'] =  'onclick="AjaxSend(' . $fovardPage . ',' . $this->getRequest()->getParam('id') . ', true);"';
        }
        $data['dat']['cnt'] = count($collection->getData());
        $data = json_encode($data);
        echo $data;
    }
}
