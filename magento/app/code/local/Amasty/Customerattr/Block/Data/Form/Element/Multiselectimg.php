<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Customerattr
*/
class Amasty_Customerattr_Block_Data_Form_Element_Multiselectimg extends Varien_Data_Form_Element_Abstract
{
    public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        $this->setType('select');
        $this->setExtType('multiple');
        $this->setSize(10);
    }

    public function getName()
    {
        $name = parent::getName();
        if (strpos($name, '[]') === false) {
            $name.= '[]';
        }
        return $name;
    }

    public function getElementHtml()
    {
        $this->addClass('select multiselect');
        $html = '';

        $value = $this->getValue();
        if (!is_array($value)) {
            $value = explode(',', $value);
        }

        if ($values = $this->getValues()) {
            foreach ($values as $option) {
                if ($option['value'])
                {
                    $html.= $this->_optionToHtml($option, $value);
                }
            }
        }
        $html .= '<input type="hidden" class="" name="amcustomerattr[' . $this->getData('html_id') . '][]" id="' . $this->getData('html_id') . '___0" value="">';
        $html .= '<div style="clear: both;"></div>';

        $html.= $this->getAfterElementHtml();
        return $html;
    }

    public function getHtmlAttributes()
    {
        return array('title', 'class', 'style', 'onclick', 'onchange', 'disabled', 'size', 'tabindex');
    }

    public function getDefaultHtml()
    {
        $result = ( $this->getNoSpan() === true ) ? '' : '<span class="field-row" id="' . $this->getData('html_id') . '">'."\n";
        $result.= $this->getLabelHtml();
        $result.= $this->getElementHtml();


        if($this->getSelectAll() && $this->getDeselectAll()) {
            $result.= '<a href="#" onclick="return ' . $this->getJsObjectName() . '.selectAll()">' . $this->getSelectAll() . '</a> <span class="separator">&nbsp;|&nbsp;</span>';
            $result.= '<a href="#" onclick="return ' . $this->getJsObjectName() . '.deselectAll()">' . $this->getDeselectAll() . '</a>';
        }

        $result.= ( $this->getNoSpan() === true ) ? '' : '</span>'."\n";


        $result.= '<script type="text/javascript">' . "\n";
        $result.= '   var ' . $this->getJsObjectName() . ' = {' . "\n";
        $result.= '     selectAll: function() { ' . "\n";
        $result.= '         var sel = $("' . $this->getHtmlId() . '");' . "\n";
        $result.= '         for(var i = 0; i < sel.options.length; i ++) { ' . "\n";
        $result.= '             sel.options[i].selected = true; ' . "\n";
        $result.= '         } ' . "\n";
        $result.= '         return false; ' . "\n";
        $result.= '     },' . "\n";
        $result.= '     deselectAll: function() {' . "\n";
        $result.= '         var sel = $("' . $this->getHtmlId() . '");' . "\n";
        $result.= '         for(var i = 0; i < sel.options.length; i ++) { ' . "\n";
        $result.= '             sel.options[i].selected = false; ' . "\n";
        $result.= '         } ' . "\n";
        $result.= '         return false; ' . "\n";
        $result.= '     }' . "\n";
        $result.= '  }' . "\n";
        $result.= "\n</script>";

        return $result;
    }

    public function getJsObjectName() {
         return $this->getHtmlId() . 'ElementControl';
    }

    protected function _optionToHtml($option, $selected)
    {
        $html  = '<div class="amorderattr_img_checkbox" style="float: left; clear: none; padding-right: 4px;">';
        if (Mage::helper('amcustomerattr')->getAttributeImageUrl($option['value']))
        {
            $html .= '<img src="' . Mage::helper('amcustomerattr')->getAttributeImageUrl($option['value']) . '" style="clear: right;" />';
        }
        $cssClass = '';
        if (false !== strpos($this->getData('class'), 'required-entry') && !$this->getCheckboxValidationUsed())
        {
            $this->setCheckboxValidationUsed(true);
            $cssClass = 'validate-checkboxgroup-required';
        }
        $html .= '<div><input type="checkbox" class="' . $cssClass . '" name="' . parent::getName() . '[]" id="' . $this->getData('html_id') . '___' . $this->_escape($option['value']) . '" value="' . $this->_escape($option['value']) . '"';
        if (in_array((string)$option['value'], $selected)) {
            $html.= ' checked="checked"';
        }
        $html .= ' />&nbsp;';
        $html .= $this->_escape($option['label']);
        $html .= '</div></div>';
        
        return $html;
        
        /*
        $html = '<option value="'.$this->_escape($option['value']).'"';
        $html.= isset($option['title']) ? 'title="'.$this->_escape($option['title']).'"' : '';
        $html.= isset($option['style']) ? 'style="'.$option['style'].'"' : '';
        if (in_array((string)$option['value'], $selected)) {
            $html.= ' selected="selected"';
        }
        $html.= '>'.$this->_escape($option['label']). '</option>'."\n";
        return $html;*/
    }
}