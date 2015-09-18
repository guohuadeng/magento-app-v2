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

class Sunpop_News_Block_Adminhtml_News_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('news_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('clnews')->__('Main Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('info', array(
            'label'     => Mage::helper('clnews')->__('Main Information'),
            'content'   => $this->getLayout()->createBlock('clnews/adminhtml_news_edit_tab_info')->initForm()->toHtml(),
        ));

        $this->addTab('additional', array(
            'label'     => Mage::helper('clnews')->__('Additional Options'),
            'content'   => $this->getLayout()
                ->createBlock('clnews/adminhtml_news_edit_tab_additional')->initForm()->toHtml(),
        ));

        return parent::_beforeToHtml();
    }
}
