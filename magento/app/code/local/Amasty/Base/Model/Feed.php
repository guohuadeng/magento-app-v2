<?php
/**
 * @copyright  Copyright (c) 2010 Amasty (http://www.amasty.com)
 */  
class Amasty_Base_Model_Feed extends Mage_AdminNotification_Model_Feed
{
    const XML_FREQUENCY_PATH    = 'ambase/feed/check_frequency';
    const XML_LAST_UPDATE_PATH  = 'ambase/feed/last_update';
    const XML_ITERESTS          = 'ambase/feed/interests';
    
    const URL_EXTENSIONS  = 'http://amasty.com/feed-extensions.xml';
    const URL_NEWS        = 'http://amasty.com/feed-news.xml';


    public static function check()
    {
        return Mage::getModel('ambase/feed')->checkUpdate();
    }

    public function checkUpdate()
    {
        if (($this->getFrequency() + $this->getLastUpdate()) > time()) {
            return $this;
        }
        
        $this->setLastUpdate();
        
        if (!extension_loaded('curl')) {
            return $this;
        }

        // load all new and relevant updates into inbox
        $feedData   = array();
        $feedXml = $this->getFeedData();
        $wasInstalled = gmdate('Y-m-d H:i:s', Mage::getStoreConfig('ambase/feed/installed'));
        
        if ($feedXml && $feedXml->channel && $feedXml->channel->item) {
            foreach ($feedXml->channel->item as $item) {
                $date = $this->getDate((string)$item->pubDate);

                // compare strings, but they are well-formmatted 
                if ($date < $wasInstalled){
                    continue;
                }
                if (!$this->isInteresting($item)){
                    continue;
                }
                    
                $feedData[] = array(
                    'severity'      => 3,
                    'date_added'    => $this->getDate($date),
                    'title'         => (string)$item->title,
                    'description'   => (string)$item->description,
                    'url'           => (string)$item->link,
                );
            }
            if ($feedData) {
                $inbox = Mage::getModel('adminnotification/inbox');
                
                if ($inbox)
                    $inbox->parse($feedData);   
            }
        }
        
        //load all available extensions in the cache
        $this->_feedUrl = self::URL_EXTENSIONS;
        $feedData   = array();
        $feedXml = $this->getFeedData();
        if ($feedXml && $feedXml->channel && $feedXml->channel->item) {
            foreach ($feedXml->channel->item as $item) {
                $feedData[(string)$item->code] = array(
                    'name'    => (string)$item->title,
                    'url'     => (string)$item->link,
                    'version' => (string)$item->version,
                );
            }
            if ($feedData) {
                Mage::app()->saveCache(serialize($feedData), 'ambase_extensions');
            }
        }
        
        return $this;
    }

    public function getFrequency()
    {
        return Mage::getStoreConfig(self::XML_FREQUENCY_PATH);
    }

    public function getLastUpdate()
    {
        return Mage::app()->loadCache('ambase_notifications_lastcheck');
    }
 
    public function setLastUpdate()
    {
        Mage::app()->saveCache(time(), 'ambase_notifications_lastcheck');
        return $this;
    }
    
    public function getFeedUrl()
    {
        if (is_null($this->_feedUrl)) {
            $this->_feedUrl = self::URL_NEWS;
        }
        $query = '?s=' . urlencode(Mage::getStoreConfig('web/unsecure/base_url')); 
        return $this->_feedUrl  . $query;
    }
    
    protected function getInterests()
    {
        return Mage::getStoreConfig(self::XML_ITERESTS);
    }

    protected function isInteresting($item)
    {
        $interests = @explode(',', $this->getInterests());
        $types     = @explode(':', (string)$item->type);
        $extenion  = (string)$item->extension;

        $selfUpgrades = array_search(Amasty_Base_Model_Source_Updates_Type::TYPE_INSTALLED_UPDATE, $types);

        foreach ($types as $type){
            if (array_search($type, $interests) !== false){
                return true;
            }

            if ($extenion && ($type == Amasty_Base_Model_Source_Updates_Type::TYPE_UPDATE_RELEASE) && $selfUpgrades){
                if ($this->isExtensionInstalled($extenion)){
                    return true;
                }
            }
        }

        return false;
    }

    protected function isExtensionInstalled($code)
    {
        $modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());
        foreach ($modules as $moduleName) {
            if ($moduleName == $code){
                return true;
            }
        }
        
        return false;
    }
    
}