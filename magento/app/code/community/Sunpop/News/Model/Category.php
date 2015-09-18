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

class Sunpop_News_Model_Category extends Mage_Core_Model_Abstract
{
    protected function _construct(){
        parent::_construct();
        $this->_init('clnews/category');
    }

    public function getCategoryByNewsId($id)
    {
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $db->select()
             ->from(array(Mage::getSingleton('core/resource')->getTableName('clnews_news_category')),
                    array('category_id'))
             ->where('news_id = ?', $id);
        $stmt = $db->query($select);
        $result = $stmt->fetchAll();
        return $result;
    }

}
