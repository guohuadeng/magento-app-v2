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

class Sunpop_News_Block_Adminhtml_News_Edit_Tab_Additional extends Mage_Adminhtml_Block_Widget_Form
{
    public function initForm()
    {
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('news_time_data',
            array('legend'=>Mage::helper('clnews')->__('News Time Settings'), 'style' => 'width: 520px;'));

        $fieldset->addField('news_time', 'date', array(
            'name' => 'news_time',
            'label' => Mage::helper('clnews')->__('News Time'),
            'title' => Mage::helper('clnews')->__('News Time'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'after_element_html' =>
                '<span class="hint" style="white-space:nowrap;"><p class="note">'.
                    Mage::helper('clnews')->__('Next to the Article will be stated current time').'</p></span>'
        ));

        $fieldset->addField('publicate_from_time', 'date', array(
            'name' => 'publicate_from_time',
            'label' => Mage::helper('clnews')->__('Publish From:'),
            'title' => Mage::helper('clnews')->__('Publish From:'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
        ));

        $values = $this->getTimeValues(0, 23);
        $fieldset->addField('publicate_from_hours', 'select', array(
            'label'     => Mage::helper('clnews')->__('Hours'),
            'name'      => 'publicate_from_hours',
            'style'     => 'width: 50px!important;',
            'values'    => $values
        ));

        $values = $this->getTimeValues(0, 59);
        $fieldset->addField('publicate_from_minutes', 'select', array(
            'label'     => Mage::helper('clnews')->__('Minutes'),
            'name'      => 'publicate_from_minutes',
            'style'     => 'width: 50px!important;',
            'values'    => $values
        ));

        $fieldset->addField('publicate_to_time', 'date', array(
            'name' => 'publicate_to_time',
            'label' => Mage::helper('clnews')->__('Publish Until:'),
            'title' => Mage::helper('clnews')->__('Publish Until:'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
        ));

        $values = $this->getTimeValues(0, 23);
        $fieldset->addField('publicate_to_hours', 'select', array(
            'label'     => Mage::helper('clnews')->__('Hours'),
            'name'      => 'publicate_to_hours',
            'style'     => 'width: 50px!important;',
            'values'    => $values
        ));

        $values = $this->getTimeValues(0, 59);
        $fieldset->addField('publicate_to_minutes', 'select', array(
            'label'     => Mage::helper('clnews')->__('Minutes'),
            'name'      => 'publicate_to_minutes',
            'style'     => 'width: 50px!important',
            'values'    => $values
        ));

        $fieldset = $form->addFieldset('news_meta_data', array('legend'=>Mage::helper('clnews')->__('Meta Data')));

        $fieldset->addField('meta_keywords', 'editor', array(
            'name' => 'meta_keywords',
            'label' => Mage::helper('clnews')->__('Keywords'),
            'title' => Mage::helper('clnews')->__('Meta Keywords'),
            'style' => 'width: 520px;',
        ));

        $fieldset->addField('meta_description', 'editor', array(
            'name' => 'meta_description',
            'label' => Mage::helper('clnews')->__('Description'),
            'title' => Mage::helper('clnews')->__('Meta Description'),
            'style' => 'width: 520px;',
        ));

        $fieldset = $form->addFieldset('news_options_data',
            array('legend'=>Mage::helper('clnews')->__('Advanced Post Options')));

        $fieldset->addField('author', 'text', array(
            'label'     => Mage::helper('clnews')->__('Author name'),
            'name'      => 'author',
            'style' => 'width: 520px;',
            'after_element_html' => '<span class="hint"><p class="note">'.$this->__('Leave blank to disable').'</p></span>',
        ));

        if ( Mage::getSingleton('adminhtml/session')->getNewsData() ) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getNewsData());
            Mage::getSingleton('adminhtml/session')->setNewsData(null);
        } elseif ( Mage::registry('clnews_data') ) {
            $form->setValues(Mage::registry('clnews_data')->getData());
        }
        $this->setForm($form);
        return $this;
    }

    public function getTimeValues($start, $end)
    {
        $values = array();
        for($i=$start; $i<=$end; $i++) {
            $values[] = array('label' => (strlen($i)>1)?$i:('0'.$i), 'value' => $i);
        }
        return $values;
    }
}
