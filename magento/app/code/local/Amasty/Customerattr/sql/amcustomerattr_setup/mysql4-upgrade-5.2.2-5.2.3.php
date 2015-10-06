<?php
/**
* @author Amasty Team
* @copyright Copyright (c) Amasty (http://www.amasty.com)
* @package Amasty_Customerattr
*/


$installer = $this;

$installer->startSetup();

$entity = $this->getEntityTypeId('customer');

/* If the attribute exists */
if(!$this->attributeExists($entity, 'am_is_activated'))
{
    /* delete it */
    $this->removeAttribute($entity, 'am_is_activated');
}

/* create the new attribute */
$this->addAttribute($entity, 'am_is_activated', array(
    'type' => 'text',				/* input type */
    'label' => 'Activated',	        /* Label for the user to read */
    'input' => 'text',				/* input type */
    'visible' => TRUE,				/* users can see it */
    'required' => FALSE,			/* is it required, self-explanatory */
    'default_value' => '0',	        /* default value */
    'adminhtml_only' => '1'			/* use in admin html only */
));

$installer->endSetup();