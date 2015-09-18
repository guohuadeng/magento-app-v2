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

class Sunpop_News_Block_Adminhtml_News_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'clnews';
        $this->_controller = 'adminhtml_news';

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('clnews')->__('Save News Article'));
        $this->_updateButton('delete', 'label', Mage::helper('clnews')->__('Delete News Article'));

        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('clnews_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'clnews_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'clnews_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }

            function checkboxSwitch(){
                if (jQuery('#use_full_img').is(':checked')) {
                    jQuery('#image_short_content').parent().parent().css('display','none');
                } else {
                    jQuery('#image_short_content').parent().parent().css('display', 'table-row');
                    jQuery('#image_short_content').siblings('a').css('float', 'left');
                    jQuery('#image_short_content').siblings('a').css('margin-right', '4px');
                    jQuery('#image_short_content').parent().parent().css('width','155px');
                }
            }
        ";
    }

    public function getHeaderText()
    {
        if (Mage::registry('clnews_data') && Mage::registry('clnews_data')->getId()) {
            return Mage::helper('clnews')->__("Edit News Article '%s'",
                $this->htmlEscape(Mage::registry('clnews_data')->getTitle()));
        } else {
            return Mage::helper('clnews')->__('Add News Article');
        }
    }
}
