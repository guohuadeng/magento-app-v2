<?php    
    class Amasty_Base_Model_Conflict{
        protected $_log = array();
        protected $_beforeCode = '/* added automatically by conflict fixing tool */ ';
        
        protected function _log($m){
            $this->_log[] = $m;
            Mage::log($m, null, 'amasty_conflict_checker.log');
        }
        
        function log(){
            return $this->_log;
        }
        
        protected function _getRewriteConfig($object, $module, $rewrite){
            $ret = array(
                "classes" => null,
                "codePool" => null
            );
            $conflictsList = Mage::helper("ambase")->getPossibleConflictsList();
            if (isset($conflictsList[$object]) && 
                    isset($conflictsList[$object][$module]) &&
                    isset($conflictsList[$object][$module]["rewrite"][$rewrite])){
                
                $rewriteClasses = $conflictsList[$object][$module]["rewrite"][$rewrite];
                $codePool = $conflictsList[$object][$module]["codePool"][$rewrite];
                
                krsort($rewriteClasses);
                
                $ret["classes"] = $rewriteClasses;
                $ret["codePool"] = $codePool;
                
            
            } else {
                throw new Exception("Conflict not found");
            }
            
            return $ret;
        }

        protected function _checkPermissions($config){
            foreach($config["classes"] as $rewriteIndex => $rewriteClass){
                $classPath = self::getClassPath($config['codePool'][$rewriteIndex], $rewriteClass);
                
                if (!is_writable($classPath)){
                    Mage::throwException('Please add write permissions on the file ' . $classPath);
                }
            }
        }

        public function fix($object, $module, $rewrite){
            
            $config = $this->_getRewriteConfig($object, $module, $rewrite);
            
            $this->_checkPermissions($config);
            
            $extendsClasses = $config["classes"];
            
            foreach($config["classes"] as $rewriteIndex => $rewriteClass){
                unset($extendsClasses[$rewriteIndex]);

                if (count($extendsClasses) > 0)
                    $this->_applyFix($rewriteClass, $extendsClasses, $rewriteIndex, $config["codePool"]);
            }
        }
        
        public function rollback($object, $module, $rewrite){
            $config = $this->_getRewriteConfig($object, $module, $rewrite);
            
            $this->_checkPermissions($config);
            
            $extendsClasses = $config["classes"];
            
            foreach($config["classes"] as $rewriteIndex => $rewriteClass){
                unset($extendsClasses[$rewriteIndex]);
                
                if (count($extendsClasses) > 0) {
                    $classPath = self::getClassPath($config['codePool'][$rewriteIndex], $rewriteClass);
                    $backupPath = $this->_getBackupFileName($classPath);
                    $backupFixedPath = $this->_getBackupFixedFileName($classPath);
                    
                    if (file_exists($backupPath)){
                        rename($classPath, $backupFixedPath);
                        $this->_log($classPath . " renamed to " . $backupFixedPath);
                        rename($backupPath, $classPath);
                        $this->_log($backupPath . " renamed to " . $classPath);
                    } else {
                        if (file_exists($backupFixedPath)){
                            $this->_log("Rollback already completed: " . $classPath);
                        } else {
                            throw new Exception("Backup not found:" . $backupPath);
                        }
                    }
                }
            }
        }
        
        static function getClassPath($pool, $class){
            return BP . DS . 'app' . DS . 'code' . DS . $pool . DS . uc_words($class, DS) . '.php';
        }
        
        protected function _applyFix($class, $extends, $rewriteIndex, $codePool){
            $classPath = self::getClassPath($codePool[$rewriteIndex], $class);
            
            if (file_exists($classPath)){
                $parentClasses = Mage::helper("ambase")->getParentClasses($class);
                if (count($parentClasses) > 0){

                    $this->_createPureClass($class, $parentClasses, $classPath, $extends);
                    $this->_overwriteExistingClass($class, $parentClasses, $classPath);
                } else {
                    throw new Exception("Parent class not found:" . $class);
                }
            } else {
                throw new Exception("Wrong class path:" . $classPath);
            }
        }
        
        protected function _createPureClass($class, $parentClasses, $classPath, $extends){
            $tplClass = "class :pure_class extends :extend_class {}";
            $tpl = "if (Mage::getConfig()->getNode('modules/:extend_module/active')) {
                " . $tplClass . "
            }";
            
            
            $classDir = str_replace(".php", "", $classPath);

            $purePath = $classDir . DS . "Amasty". DS . "Pure.php";

            if (!file_exists($classDir)){
                mkdir($classDir);
                $this->_log("Dir created: " . $classDir);
            }

            if (!file_exists($classDir . DS . "Amasty")){
                mkdir($classDir . DS . "Amasty");
                $this->_log("Dir created: " . $classDir . DS . "Amasty");
            }
            
            $pureClassData = array();

            foreach($extends as $extendClass){
                $moduleName = implode("_", array_slice(explode("_", $extendClass), 0, 2));
                $params = array(
                    ":class" => $class,
                    ":extend_module" => $moduleName,
                    ":extend_class" => $extendClass,
                    ":pure_class" => self::getPureClassName($class),


                );
                $pureClassData [] = strtr($tpl, $params);
            }

            $pureClassCode = implode(" else ", $pureClassData);

            $pureClassCode .= " else { " . strtr($tplClass, array(
                ":pure_class" => self::getPureClassName($class),
                ":extend_class" => $parentClasses[0]
            )) . " }";

            file_put_contents($purePath, "<?php " . $this->_beforeCode . $pureClassCode . " ?>");

            $this->_log("File created: " . $purePath);
            
        }
        
        protected function _overwriteExistingClass($class, $parentClasses, $classPath){
            $classCode = file_get_contents($classPath);
            
            $classCodeNew = strtr($classCode, array(
                $parentClasses[0] => self::getPureClassName($class)
            ));
            
            if ($classCode != $classCodeNew){
                rename($classPath, $this->_getBackupFileName($classPath));
                $this->_log($classPath . " renamed to " . $this->_getBackupFileName($classPath));
                file_put_contents($classPath, $classCodeNew);
                $this->_log("File created: " . $classPath);
            }
        }
        
        static function getPureClassName($class){
            return $class . '_Amasty_Pure';
        }
        
        protected function _getBackupFileName($classPath){
            return $classPath . '_ambackup';
        }
        
        protected function _getBackupFixedFileName($classPath){
            return $classPath . '_fixed_ambackup';
        }
    }
?>