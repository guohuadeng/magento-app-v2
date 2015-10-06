<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2008-2012 Amasty (http://www.amasty.com)
* @package Amasty_Customerattr
*/
$installer = $this;

$installer->startSetup();

$installer->run("
    UPDATE `{$this->getTable('eav_attribute')}` SET `backend_type`='varchar' WHERE `frontend_input`='file' AND `backend_type`='static';
");

$installer->endSetup();