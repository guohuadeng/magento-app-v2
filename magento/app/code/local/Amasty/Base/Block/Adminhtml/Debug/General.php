<?php
/**
 * @copyright   Copyright (c) 2010 Amasty (http://www.amasty.com)
 */ 
class Amasty_Base_Block_Adminhtml_Debug_General extends Amasty_Base_Block_Adminhtml_Debug_Base
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amasty/ambase/debug/general.phtml');        
    }
    
    function getDisableModulesOutput() {
        $config = array();
        
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        
        $tableName = $resource->getTableName('core/config_data');
        
        $query = "SELECT * FROM " . $tableName . " WHERE path LIKE '%advanced/modules_disable_output%' AND value = 1";
 
        $data = $readConnection->fetchAll($query);
        
        foreach($data as $item){
            $config[] = array(
                "name" => str_replace("advanced/modules_disable_output/", "", $item["path"])
            );
        }
        
        return $config;
    }
    
    function isCompilationEnabled() {
        $ret = FALSE;
        
        $configFile = BP . DS . 'includes' . DS . 'config.php';
        if (file_exists($configFile)){
            $config = file_get_contents($configFile);
            $ret = strpos($config, "#define('COMPILER_INCLUDE_PATH', dirname(__FILE__).DIRECTORY_SEPARATOR.'src')") === FALSE;
        }
        
        return $ret;
    }
    
    function getCrontabConfig() {
        $returnValue = null;

        if(function_exists('exec')) {
            exec('crontab -l', $returnValue);
            if(!count($returnValue)) {
                $returnValue = null;
            }
        }
        
        return $returnValue;
    }
    
}