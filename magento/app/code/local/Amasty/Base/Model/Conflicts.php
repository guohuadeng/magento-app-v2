<?php
class Amasty_Base_Model_Conflicts{
    protected $_codeDir     = '';
    protected $_etcDir      = '';
    protected $_amastyDir    = '';
    protected $_designDir   = '';
    protected $_cacheDir    = '';

    protected $_localXml    = null;
    protected $_filePaths   = null;

    protected $_mysqlConnect = null;

    protected $_magentoVersion = null;

    protected $_messages    = null;
    
    protected $_out = array();

    public function __construct() {
        $this->_etcDir      = BP . DS . 'app' . DS . 'etc';
        $this->_codeDir     = BP . DS . 'app' . DS . 'code';
        $this->_amastyDir    = $this->_codeDir . DS . 'local' . DS . 'Amasty';
        $this->_designDir   = BP . DS . 'app' . DS . 'design';
        $this->_cacheDir    = BP . DS . 'var' . DS . 'cache';

        $this->_filePaths = array();

        $this->_messages = array();

        $this->mysqlConnect();
    }

    public function __destruct() {
        $this->mysqlDisconnect();
    }
    
    public function getHtml(){
        return implode("", $this->_out);
    }
    
    public static function run($includeBody = FALSE)
    {
        try {
            $testObj = Mage::getModel("ambase/conflicts");
            if ($includeBody)
                $testObj->outHtmlBegin();

//            $testObj->checkPostForm();

            $testObj->outMessages();

            $testObj->checkRewriteConflicts();
            $testObj->checkRewrites();
            //$testObj->checkFilePermissions();
            $testObj->outMagentoVersion();
//            $testObj->checkModulesVersion();
            $testObj->checkDisableLocalModules();
            $testObj->checkDisableModulesOutput();
//            $testObj->getCacheConfig();
            $testObj->checkCrontabConfig();
            
            if ($includeBody)
                $testObj->outHtmlEnd();
            
            return $testObj->getHtml();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    protected function checkPostForm() {
        if( isset($_POST['submit']) ) {
            $actionType = $_POST['action'];

            switch($actionType) {
                case 'flush_cache' : $this->flushMagentoCache(); break;
            }
        }
    }

    protected function flushMagentoCache() {
        $this->emptyDir($this->_cacheDir);
        $this->_messages[] = 'Magento cache successfully flushed';
    }


    protected function checkRewriteConflicts() {
        $conflicts = $this->_getPossibleConflictsList();

        if(FALSE !== $conflicts) {
            $this->outConflicts($conflicts);
        }
    }
    
    protected function checkRewrites(){
        $rewrites = $this->_getRewritesList();

        if(FALSE !== $rewrites) {
            $this->outRewrites($rewrites);
        }
    }
    
    
    protected function _getRewritesList(){
        $moduleFiles = glob($this->_etcDir . DS . 'modules' . DS . '*.xml');

        if (!$moduleFiles) {
            return false;
        }
        
        // load file contents
        $unsortedConfig = new Varien_Simplexml_Config();
        $unsortedConfig->loadString('<config/>');
        $fileConfig = new Varien_Simplexml_Config();

        foreach($moduleFiles as $filePath) {
            $fileConfig->loadFile($filePath);
            $unsortedConfig->extend($fileConfig);
        }

        // create sorted config [only active modules]
        $sortedConfig = new Varien_Simplexml_Config();
        $sortedConfig->loadString('<config><modules/></config>');

        foreach ($unsortedConfig->getNode('modules')->children() as $moduleName => $moduleNode) {
            if('true' === (string)$moduleNode->active) {
                $sortedConfig->getNode('modules')->appendChild($moduleNode);
            }
        }

        $fileConfig = new Varien_Simplexml_Config();

        $_finalResult = array();

        foreach($sortedConfig->getNode('modules')->children() as $moduleName => $moduleNode) {
            $codePool = (string)$moduleNode->codePool;
            $configPath = $this->_codeDir . DS . $codePool . DS . uc_words($moduleName, DS) . DS . 'etc' . DS . 'config.xml';

            $fileConfig->loadFile($configPath);

            $rewriteBlocks = array('blocks', 'models', 'helpers');

            foreach($rewriteBlocks as $param) {
                if(!isset($_finalResult[$param])) {
                    $_finalResult[$param] = array();
                }

                if($rewrites = $fileConfig->getXpath('global/' . $param . '/*/rewrite')) {
                    foreach ($rewrites as $rewrite) {
                        $parentElement = $rewrite->xpath('../..');
                        foreach($parentElement[0] as $moduleKey => $moduleItems) {
                            $moduleItemsArray['rewrite'] = array();
                            foreach ($moduleItems->rewrite as $rewriteLine)
                            {
                                foreach ($rewriteLine as $key => $value)
                                {
                                    $moduleItemsArray['rewrite'][$key] = (string)$value;
                                }
                            }
                            if($moduleItems->rewrite) {
                                $_finalResult[$param] = array_merge_recursive($_finalResult[$param], array($moduleKey => $moduleItemsArray));
                            }
                        }
                    }
                }
            }
        }
        
        return $_finalResult;
    }

    /**
     * Retrive possible conflicts list
     *
     * @return array
     */
    protected  function _getPossibleConflictsList()
    {
        $_finalResult = $this->_getRewritesList();
        
        foreach(array_keys($_finalResult) as $groupType) {

            foreach(array_keys($_finalResult[$groupType]) as $key) {
                // remove some repeating elements after merging all parents 
                foreach($_finalResult[$groupType][$key]['rewrite'] as $key1 => $value) {
                    if(is_array($value)) {
                        $_finalResult[$groupType][$key]['rewrite'][$key1] = array_unique($value);
                    }

                    // if rewrites count < 2 - no conflicts - remove
                    if( 
                        (gettype($_finalResult[$groupType][$key]['rewrite'][$key1]) == 'array' && count($_finalResult[$groupType][$key]['rewrite'][$key1]) < 2) 
                        ||
                        gettype($_finalResult[$groupType][$key]['rewrite'][$key1]) == 'string'
                    ) {
                        unset($_finalResult[$groupType][$key]['rewrite'][$key1]);
                    }
                } 
                
                // clear empty elements
                if(count($_finalResult[$groupType][$key]['rewrite']) < 1) {
                    unset($_finalResult[$groupType][$key]);
                }
            }
            
            // clear empty elements
            if(count($_finalResult[$groupType]) < 1) {
                unset($_finalResult[$groupType]);
            }

        }
        return $_finalResult;
    }

    protected function checkFilePermissions() {
        $this->checkPermissions($this->_etcDir . DS . 'modules', 'Amasty_');
        $this->checkPermissions($this->_amastyDir);
        $this->checkPermissions($this->_designDir . DS . 'frontend' . DS . 'default' . DS . 'default' . DS . 'template', 'am');
        $this->checkPermissions($this->_designDir . DS . 'frontend' . DS . 'default' . DS . 'default' . DS . 'layout', 'am');
        $this->checkPermissions($this->_designDir . DS . 'adminhtml' . DS . 'default' . DS . 'default' . DS . 'template', 'am');
        $this->checkPermissions($this->_designDir . DS . 'adminhtml' . DS . 'default' . DS . 'default' . DS . 'layout', 'am');
        
        $this->outFilePermissions();
    }


    protected function checkPermissions($path, $wildCard = null, $processRecursive = true) {
        // check parent dir's permissions
        $this->checkParentsPermissions($path);
        $this->checkChildrenPermissions($path, $wildCard, $processRecursive);
    }

    protected function checkParentsPermissions($path) {
        while(1) {
            if(file_exists($path)) {
                if($path == BP) {
                    break;
                }
                if(!$this->isWriteable($path)) {
                    $this->_filePaths[$path] = substr(sprintf('%o', fileperms($path)), -4);
                }
                $path = dirname($path);
            }
            else {
                break;
            }
        }
    }

    protected function checkChildrenPermissions($path, $wildCard = null, $processRecursive = true) {
        // Check if the path exists
        if(!file_exists($path)) {
            return false;
        }
        // See whether this is a file
        if(is_file($path)) {
            // Chmod the file with our given filepermissions
            if(($wildCard && strpos($path, $wildCard) !== FALSE) || is_null($wildCard)) {
                if(!$this->isWriteable($path)) {
                    $this->_filePaths[$path] = substr(sprintf('%o', fileperms($path)), -4);
                }
            }
        } 
        // If this is a directory...
        elseif(is_dir($path)) {
                // Then get an array of the contents
                $foldersAndFiles = scandir($path);
                // Remove "." and ".." from the list
                $entries = array_slice($foldersAndFiles, 2);
                // Parse every result...
                foreach($entries as $entry) {
                    if($processRecursive) {
                        if(($wildCard && strpos($path."/".$entry, $wildCard) !== FALSE) || is_null($wildCard)) {
                            // And call this function again recursively, with the same permissions
                            $this->checkChildrenPermissions($path."/".$entry, $wildCard);
                        }
                    }
                }
                // When we are done with the contents of the directory, we chmod the directory itself
                if(!$this->isWriteable($path)) {
                    $this->_filePaths[$path] = substr(sprintf('%o', fileperms($path)), -4);
                }
        }
        // Everything seemed to work out well, return TRUE
        return true;
    }

    protected function findGlobalConfigInCache() {
    }

    protected function checkModulesVersion() {
        $moduleFiles = glob($this->_etcDir . DS . 'modules' . DS . '*.xml');
        
        $moduleNames = array();

        foreach ($moduleFiles as $moduleFile)
        {
            if( FALSE !== strpos($moduleFile, 'Amasty_')) {
                $aModuleFile = explode(DS, $moduleFile);
                $moduleName = substr(array_pop($aModuleFile), 0, -4);

                $moduleConfig = new Varien_Simplexml_Config();
                $moduleConfig->loadFile($moduleFile);
                
                if((string)$moduleConfig->getNode('modules/' . $moduleName . '/active') == 'false') {
                    continue;
                }

                $moduleNames[$moduleName] = array();

                $adminhtmlPath = $this->_codeDir . DS . 'local' . DS . uc_words($moduleName, DS) . DS . 'etc' . DS . 'adminhtml.xml';
                $configPath = $this->_codeDir . DS . 'local' . DS . uc_words($moduleName, DS) . DS . 'etc' . DS . 'config.xml';
                
                if(file_exists($configPath)) {
                    $fileConfig = new Varien_Simplexml_Config();
                    $fileConfig->loadFile($configPath);

                    $version = (string)$fileConfig->getNode('modules/' . $moduleName . '/version');
                    $platform = (string)$fileConfig->getNode('modules/' . $moduleName . '/platform');

                    $moduleNames[$moduleName][] = $version;

                    if($platform) {
                        $moduleNames[$moduleName][] = $platform;
                    }
                    else {
                        if(file_exists($adminhtmlPath)) {
                            $moduleNames[$moduleName][] = '1.4';
                        }
                        else {
                            $moduleNames[$moduleName][] = '1.3?';
                        }
                    }
                }
                else {
                    $moduleNames[$moduleName][] = 'config.xml not found';
                }
            }
        }

        $this->outModulesVersion($moduleNames);
    }

    protected function getLocalXmlConfig() {
        if(is_null($this->_localXml)) {
            $this->_localXml = new Varien_Simplexml_Config();
            $this->_localXml->loadFile($this->_etcDir . DS . 'local.xml');
        }
        return $this->_localXml;
    }
    
    protected function checkDisableLocalModules() {
        $isDisabled = (string)$this->getLocalXmlConfig()->getNode('global/disable_local_modules');

        $this->outDisableLocalModules($isDisabled);
    }


    protected function checkDisableModulesOutput() {
        //$isDisabled = (string)$this->getLocalXmlConfig()->getNode('global/disable_local_modules');
        $query = "SELECT * FROM ~tablePrefix~core_config_data WHERE path LIKE '%advanced/modules_disable_output%' AND value = 1";
        $query = str_replace('~tablePrefix~', $this->getMysqlTablePrefix(), $query);
        $result = mysql_query($query, $this->mysqlConnect()) or die (mysql_error());

        $data = array();
        if($result && mysql_num_rows($result)) {
            while($row = mysql_fetch_assoc($result)) {
                $t = explode('/', $row['path']);
                $moduleName = $t[2];
                if(stristr($moduleName, 'Amasty')) {
                    $data[] = $moduleName;
                }
            }
        }
        
        $this->outDisableModulesOutput($data);
    }

    protected function mysqlConnect() {
        if(!$this->_mysqlConnect) {
            $dbParams = $this->getLocalXmlConfig()->getNode('global/resources/default_setup/connection');
        	 if (false !== strpos($dbParams->host, '/')){
            	     $dbParams->host = ':' . $dbParams->host;
        	 }
            $this->_mysqlConnect = mysql_connect(
                (string)$dbParams->host, 
                (string)$dbParams->username, 
                (string)$dbParams->password) or die(mysql_error());
            mysql_select_db((string)$dbParams->dbname);
        }
        return $this->_mysqlConnect;
    }

    protected function mysqlDisconnect() {
        if($this->_mysqlConnect) {
            mysql_close($this->_mysqlConnect);
        }
    }

    protected function mysqlPrepareQuery($query) {
        return str_replace('~tablePrefix~', $this->getMysqlTablePrefix(), $query);
    }

    protected function getMysqlTablePrefix() {
        return (string)$this->getLocalXmlConfig()->getNode('global/resources/db/table_prefix');
    }

    protected function getMagentoVersion() {
        if(is_null($this->_magentoVersion)) {
            $this->_magentoVersion = '1.3';

            if( FALSE !== strpos(file_get_contents(BP . DS . 'app' . DS . 'Mage.php'), 'getVersionInfo()') ) {
                $this->_magentoVersion = '1.4+';
            }
        }

        return $this->_magentoVersion;
    }

    protected function getCacheConfig() {
        $cacheData = array();

        // for Magento 1.3 cache config stores in use_cache.ser file
        if($this->getMagentoVersion() == '1.3') {
            $cacheData = unserialize(file_get_contents($this->_etcDir . DS . 'use_cache.ser'));
        }
        // for Magento 1.4 cache config stores in db
        else {
            $query = "SELECT * FROM ~tablePrefix~core_cache_option";
            $query = str_replace('~tablePrefix~', $this->getMysqlTablePrefix(), $query);
            $result = mysql_query($query, $this->mysqlConnect()) or die (mysql_error());

            if($result && mysql_num_rows($result)) {
                while($row = mysql_fetch_assoc($result)) {
                    $cacheData[$row['code']] = $row['value'];
                }
            }
        }

        $this->outCacheConfig($cacheData);
    }

    protected function checkCrontabConfig() {
        $returnValue = array();

        if(function_exists('exec')) {
            exec('crontab -l', $returnValue);
            if(!count($returnValue)) {
                $returnValue[] = 'No cron jobs found';
            }
        }
        else {
            $returnValue[] = 'EXEC is disabled';
        }
        $this->outCrontabConfig($returnValue);
    }


    /**
    * Output functions
    */
    protected function outConflicts(&$conflicts) {
        $this->_out[] = '<h4>Modules conflicts</h4>';
        if(count($conflicts)) {
            $this->outHasConflicts($conflicts);
        }
        else {
            $this->outNoConflicts();
        }
//        $this->_out[] = '<hr/>';
    }
    
    protected function outRewrites(&$conflicts) {
       $this->_out[] = '<h4>Modules rewrites</h4>';
        if(count($conflicts)) {
            $this->outHasRewrites($conflicts);
        }
        else {
            $this->outNoRewrites();
        }
//        $this->_out[] = '<hr/>'; 
    }

    protected function outNoRewrites() {
        $this->_out[] = 'No Module Rewrites Found';
    }
    
    protected function outNoConflicts() {
        $this->_out[] = 'No Module Conflicts Found';
    }

    protected function outHasRewrites(&$conflicts) {
        $this->_out[] = '<b>Found Rewrites</b>';
        $this->_out[] = '<table width="60%" border="0" cellpadding="0" cellspacing="0">';
        $this->_out[] = '<tr><th width="50%" style="text-align:right; padding-right: 10px;">Magento Class</th><th width="50%" style="text-align:left; padding-left: 10px;">Rewrite Classes</th></tr>';
        $this->outConflictsRows($conflicts);
        $this->_out[] = '</table>';
    }
    
    protected function outHasConflicts(&$conflicts) {
        $this->_out[] = '<b>Found Conflicts</b>';
        $this->_out[] = '<table width="60%" border="0" cellpadding="0" cellspacing="0">';
        $this->_out[] = '<tr><th width="50%" style="text-align:right; padding-right: 10px;">Magento Class</th><th width="50%" style="text-align:left; padding-left: 10px;">Rewrite Classes</th></tr>';
        $this->outConflictsRows($conflicts);
        $this->_out[] = '</table>';
    }
    
    protected function outConflictsRows(&$conflicts){
        $rowDefaultStyle = 'padding: 5px 0px; border: 0px; border-top: 1px solid #BDBDBD; border-style: dotted;';
        foreach($conflicts as $groupType => $modules) {
            $this->_out[] = '<tr ><td colspan="2"><center><b>' . ucwords($groupType) . '</b></center></td></tr>';
            foreach($modules as $moduleName => $moduleRewrites) {
                foreach($moduleRewrites['rewrite'] as $moduleClass => $rewriteClasses) {
                    $this->_out[] = '<tr>';
                    $this->_out[] = '<td style="'.$rowDefaultStyle.' text-align:right; padding-right: 10px; " >' . uc_words($moduleName . '_' . $moduleClass) . '</td>';
                    if (is_array($rewriteClasses))
                        $this->_out[] = '<td style="'.$rowDefaultStyle.'">' . implode('<br/>', array_unique($rewriteClasses)) . '</td>';
                    else
                        $this->_out[] = '<td style="'.$rowDefaultStyle.'">' . $rewriteClasses . '</td>';
                    $this->_out[] = '</tr>';
                }
            }
        }
    }



    protected function outModulesVersion(&$modules) {
        $this->_out[] = '<h4>Modules versions [active modules only]</h4>';
        if(count($modules)) {
            $this->outHasAmastyModules($modules);
        }
        else {
            $this->outNoAmastyModules();
        }
//        $this->_out[] = '<hr/>';
    }

    protected function outNoAmastyModules() {
        $this->_out[] = 'No Amasty Modules Found';
    }

    protected function outHasAmastyModules(&$modules) {
        $this->_out[] = '<table border="1" cellpadding="5" width="800">';
        //$this->_out[] = '<caption style="color:Red;padding-bottom:5px;">If Magento version displays as <i>"1.3 <b>OR</b> 1.4"</i>, it means that <b>adminhtml.xml</b> not found in module etc dir</caption>';
        $this->_out[] = '<tr><th>Module Name</th><th>Magento Version</th></tr>';
        foreach($modules as $moduleName => $moduleVersions) {
            $this->_out[] = '<tr>';
            $this->_out[] = '<td>' . $moduleName . '</td>';
            $this->_out[] = '<td>' . implode(' <b>for</b> ', $moduleVersions) . '</td>';
            $this->_out[] = '</tr>';
       }
        $this->_out[] = '</table>';
    }




    protected function outFilePermissions() {
        $this->_out[] = '<h4>File Permissions</h4>';
        if(count($this->_filePaths)) {
            $this->outHasFilePermissionsErrors();
        }
        else {
            $this->outNoFilePermissionsErrors();
        }
//        $this->_out[] = '<hr/>';
    }

    protected function outNoFilePermissionsErrors() {
        $this->_out[] = 'All files has right permissions';
    }

    protected function outHasFilePermissionsErrors() {
        $this->_out[] = '<b>These files should have write permissions:</b>';
        $this->_out[] = '<ul>';
        foreach($this->_filePaths as $filePath => $filePerms) {
            $this->_out[] = '<li>' . $filePath . '</li>';
        }
        $this->_out[] = '</ul>';
    }

    protected function outDisableLocalModules($isDisabled) {
        $this->_out[] = '<b>Disable Local Modules</b> ';
        $this->_out[] = $isDisabled;
        $this->_out[] = '<br/>';
    }

    protected function outDisableModulesOutput($data) {
        $this->_out[] = '<b>Disable Modules Output</b> ';
        if(count($data)) {
            $this->outHasDisableModulesOutput($data);
        }
        else {
            $this->outNoDisableModulesOutput();
        }
        $this->_out[] = '<br/>';
    }

    protected function outHasDisableModulesOutput($data) {
        $this->_out[] = '<b>These modules output disabled:</b>';
        $this->_out[] = '<ul>';
        foreach($data as $moduleName) {
            $this->_out[] = '<li>' . $moduleName . '</li>';
        }
        $this->_out[] = '</ul>';
    }

    protected function outNoDisableModulesOutput() {
        $this->_out[] = 'All Amasty modules output enabled';
    }

    protected function outCacheConfig($data) {
        $this->_out[] = '<h4>Cache Configuration</h4>';
        $this->outHasCacheConfig($data);
//        $this->_out[] = '<hr/>';
    }

    protected function outHasCacheConfig($data) {
        $this->_out[] = '<ul>';
        foreach($data as $cacheTag => $cacheStatus) {
            $this->_out[] = '<li><b>' . $cacheTag . ' : </b>' . ($cacheStatus ? 'enabled' : 'disabled') . '</li>';
        }
        $this->_out[] = '</ul>';

        // show Clear Cache Button
        $this->_out[] = '<form method="POST" onsubmit="return confirm(\'Are you sure?\')">';
        $this->_out[] = '<input type="hidden" name="action" value="flush_cache" />';
        $this->_out[] = '<input type="submit" name="submit" value="Flush Magento Cache" />';
        $this->_out[] = '</form>';
    }

    protected function outMagentoVersion() {
        $this->_out[] = '<b>Magento version</b> ';
        $this->_out[] = $this->getMagentoVersion();
        $this->_out[] = '<br/>';
    }

    protected function outCrontabConfig(&$data) {
        $this->_out[] = '<b>Crontab Configuration</b>';
        
        $this->_out[] = '<ul>';
        foreach($data as $cronCommand) {
            $this->_out[] = '<li>' . $cronCommand . '</li>';
        }
        $this->_out[] = '</ul>';
        $this->_out[] = '<br/>';
//        $this->_out[] = '<hr/>';
    }






    protected function outHtmlBegin() {
        $this->_out[] = '<html><head>';
        $this->_out[] = '<title>Amasty Test Script</title>';
        $this->_out[] = '<style>html,body{font-family:Arial;}</style>';
        $this->_out[] = '</head><body>';
    }

    protected function outHtmlEnd() {
        $this->_out[] = '</body><html>';
    }

    protected function outMessages() {
        if(count($this->_messages)) {
            $this->_out[] = '<ul style="border: 1px solid Black; padding-top: 10px; padding-bottom: 10px; background:#00CCCC">';
            foreach($this->_messages as $mKey => $mText) {
                $this->_out[] = '<li><b>' . $mText . '</b></li>';
                unset($this->_messages[$mKey]);
            }
            $this->_out[] = '</ul>';
        }
    }





    /**
    * Check if file or directory is writeable
    */
    protected function isWriteable($sPath, $bCheckParentDirIfNotExists = true) {
        clearstatcache();
        if (file_exists($sPath) and is_file($sPath))
        {
            return $this->isFileWritable($sPath);
        }
        if (file_exists($sPath) and is_dir($sPath))
        {
            return $this->isDirWritable($sPath);
        }
        if (!file_exists($sPath))
        {
            if (!$bCheckParentDirIfNotExists)
            {
                return false;
            }
            $sDirname = dirname($sPath);
            while (strlen($sDirname) > 0 AND !file_exists($sDirname))
            {
                $sDirname = dirname($sDirname);
            }
            return $this->isDirWritable($sDirname);
        }
        return false;
    }
    
    protected function isFileWritable($sPath) {
        if (!$sPath)
        {
            return false;
        }
        if (stristr(PHP_OS, "win"))
        {
            // trying to append
            $fp = @fopen($sPath, 'a+');
            if (!$fp)
            {
                return false;
            }
            fclose($fp);
            return true;
        } else 
        {
            return is_writable($sPath);
        }
    }
    
    protected function isDirWritable($sPath) {
        if (!$sPath)
        {
            return false;
        }
        if ('/' != $sPath[strlen($sPath)-1])
        {
            $sPath .= DIRECTORY_SEPARATOR;
        }
        if (stristr(PHP_OS, "win"))
        {
            /**
             * Trying to create a new file
             */
            $sFilename = uniqid(time());
            $fp = @fopen($sPath . $sFilename, 'w');
            if (!$fp) 
            {
                return false;
            }
            if (!@fwrite($fp, 'test'))
            {
                return false;
            }
            fclose($fp);
            /**
             * clean up after ourselves
             */
            unlink($sPath . $sFilename);
            return true;
        } else 
        {
            return is_writable($sPath);
        }
    } 


    protected function emptyDir($dirname = null)
    {
        if(!is_null($dirname)) {
            if (is_dir($dirname)) {
                if ($handle = @opendir($dirname)) {
                    while (($file = readdir($handle)) !== false) {
                        if ($file != "." && $file != "..") {
                            $fullpath = $dirname . '/' . $file;
                            if (is_dir($fullpath)) {
                                $this->emptyDir($fullpath);
                                @rmdir($fullpath);
                            }
                            else {
                                @unlink($fullpath);
                            }
                        }
                    }
                    closedir($handle);
                }
            }
        }
    }
}
?>