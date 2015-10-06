<?php
/**
 * @copyright   Copyright (c) 2010 Amasty
 */ 
class Amasty_Base_Block_Extensions extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected $_dummyElement;
    protected $_fieldRenderer;
    protected $_values;

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $this->_getHeaderHtml($element);
        $modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());
        sort($modules);

        foreach ($modules as $moduleName) {
            if (strstr($moduleName, 'Amasty_') === false) {
                if(strstr($moduleName, 'Belitsoft_') === false){
                    if(strstr($moduleName, 'Mageplace_') === false){
                        continue;
                    }
                }
            }

            if ($moduleName == 'Amasty_Base'){
                continue;
            }

            $html.= $this->_getFieldHtml($element, $moduleName);
        }
        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    protected function _getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = Mage::getBlockSingleton('adminhtml/system_config_form_field');
        }
        return $this->_fieldRenderer;
    }

    protected function _getFieldHtml($fieldset, $moduleCode)
    {
        $currentVer = Mage::getConfig()->getModuleConfig($moduleCode)->version;
        if (!$currentVer)
            return '';

        $moduleName = substr($moduleCode, strpos($moduleCode, '_') + 1); // in case we have no data in the RSS

        $allExtensions = unserialize(Mage::app()->loadCache('ambase_extensions'));
            
        $status = '<a  target="_blank"><img src="'.$this->getSkinUrl('images/ambase/ok.gif').'" title="'.$this->__("Installed").'"/></a>';

        if ($allExtensions && isset($allExtensions[$moduleCode])){
            $ext = $allExtensions[$moduleCode];

            $url     = $ext['url'];
            $name    = $ext['name'];
            $lastVer = $ext['version'];

            $moduleName = '<a href="'.$url.'" target="_blank" title="'.$name.'">'.$name."</a>";

            if ($this->_convertVersion($currentVer) < $this->_convertVersion($lastVer)){
                $status = '<a href="'.$url.'" target="_blank"><img src="'.$this->getSkinUrl('images/ambase/update.gif').'" alt="'.$this->__("Update available").'" title="'.$this->__("Update available").'"/></a>';
            }
        }
        
        //TODO check if module output disabled in future

        $moduleName = $status . ' ' . $moduleName;

        $field = $fieldset->addField($moduleCode, 'label', array(
            'name'  => 'dummy',
            'label' => $moduleName,
            'value' => $currentVer,
        ))->setRenderer($this->_getFieldRenderer());

        return $field->toHtml();
    }
    
    protected function _convertVersion($v)
    {
        $digits = @explode(".", $v);
        $version = 0;
        if (is_array($digits)){
            foreach ($digits as $k=>$v){
                $version += ($v * pow(10, max(0, (3-$k))));
            }

        }
        return $version;
    }
}