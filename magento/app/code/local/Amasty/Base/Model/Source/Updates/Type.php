<?php
/**
 * @copyright  Copyright (c) 2010 Amasty (http://www.amasty.com)
 */  
class Amasty_Base_Model_Source_Updates_Type extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    const TYPE_PROMO            = 'PROMO';
    const TYPE_NEW_RELEASE      = 'NEW_RELEASE';
    const TYPE_UPDATE_RELEASE   = 'UPDATE_RELEASE';
    const TYPE_INFO             = 'INFO';
    const TYPE_INSTALLED_UPDATE = 'INSTALLED_UPDATE';


    public function toOptionArray()
    {
        $hlp = Mage::helper('ambase');
        return array(
            array('value' => self::TYPE_INSTALLED_UPDATE, 'label' => $hlp->__('My extensions updates')),
            array('value' => self::TYPE_UPDATE_RELEASE,   'label' => $hlp->__('All extensions updates')),
            array('value' => self::TYPE_NEW_RELEASE,      'label' => $hlp->__('New Releases')),
            array('value' => self::TYPE_PROMO,            'label' => $hlp->__('Promotions/Discounts')),
            array('value' => self::TYPE_INFO,             'label' => $hlp->__('Other information'))
        );
    }

    /**
     * Retrive all attribute options
     *
     * @return array
     */
    public function getAllOptions()
    {
        return $this->toOptionArray();
    }


    /**
     * Returns label for value
     * @param string $value
     * @return string
     */
    public function getLabel($value)
    {
        $options = $this->toOptionArray();
        foreach($options as $v){
            if($v['value'] == $value){
                return $v['label'];
            }
        }
        return '';
    }

    /**
     * Returns array ready for use by grid
     * @return array
     */
    public function getGridOptions()
    {
        $items = $this->getAllOptions();
        $out = array();
        foreach($items as $item){
            $out[$item['value']] = $item['label'];
        }
        return $out;
    }
}
