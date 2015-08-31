<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is under the Magento root directory in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category    Sunpop
 * @package     Sunpop_Storelocator
 * @copyright   Copyright (c) 2015 Ivan Deng. (http://www.sunpop.cn)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Store Locator Controller
 *
 * @author      Qun WU <info@Sunpopwebsolutions.com>
 */
class Sunpop_Storelocator_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function searchAction()
    {
        // Get parameters from URL
        $center_lat = $this->getRequest()->getParam('lat');
        $center_lng = $this->getRequest()->getParam('lng');
        $radius = $this->getRequest()->getParam('radius');

        // Start XML file, create parent node
        $dom = new DOMDocument("1.0");
        $node = $dom->createElement("markers");
        $parnode = $dom->appendChild($node);
        
        $collection = Mage::getModel('storelocator/storelocator')->getCollection();
        
        if (isset($radius)) {
            $distance = sprintf( "3959 * 0.621 * acos( cos( radians(%s) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(%s) ) + sin( radians(%s) ) * sin( radians( lat ) ) )", 
                $center_lat, $center_lng, $center_lat);        
            $collection->addExpressionFieldToSelect('distance', $distance, array('lat'=>'lat', 'lng'=>'lng'));
            $collection->getSelect()->having('distance < ' . $radius)->order('distance','ASC');
        }

        $stores=$collection->load();

        header("Content-type: text/xml");

        // Iterate through the rows, adding XML nodes for each
        foreach ($stores as $store) {
            $node = $dom->createElement("marker");
            $newnode = $parnode->appendChild($node);
            $newnode->setAttribute("id", $store['storelocator_id']);
            $newnode->setAttribute("name", $store['store_name']);
            $newnode->setAttribute("address", $store['address']);
            $newnode->setAttribute("city", $store['city']);
            $newnode->setAttribute("lat", $store['lat']);
            $newnode->setAttribute("lng", $store['lng']);
            $newnode->setAttribute("distance", $store['distance']);
        }

        echo $dom->saveXML();
    }
    
    public function detailAction()
    {
        $storelocatorId     = $this->getRequest()->getParam('id');
        $storelocatorModel  = Mage::getModel('storelocator/storelocator')->load($storelocatorId);
 
        if ($storelocatorModel->getId()) {
 
            Mage::register('storelocator_data', $storelocatorModel);
 
            $this->loadLayout();
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('storelocator')->__('Store does not exist'));
            $this->_redirect('*/*/');
        }
    }
    
    public function cityAction()
    {
    	//http://domainname／storelocator/index/city/lat/111/lng/222/radius/1111
    	// Get parameters from URL
    	$center_lat = $this->getRequest()->getParam('lat');
    	$center_lng = $this->getRequest()->getParam('lng');
    	$radius = $this->getRequest()->getParam('radius') ? $this->getRequest()->getParam('radius') : 200;

    	// Start XML file, create parent node
    
    	$collection = Mage::getModel('storelocator/storelocator')->getCollection();
    	
    	if (isset($radius)) {
    		$distance = sprintf( "3959 * 0.621 * acos( cos( radians(%s) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(%s) ) + sin( radians(%s) ) * sin( radians( lat ) ) )",
    				$center_lat, $center_lng, $center_lat);
    		$collection->addExpressionFieldToSelect('distance', $distance, array('lat'=>'lat', 'lng'=>'lng'));
    		$collection->getSelect()->having('distance < ' . $radius)->order('distance','ASC');
    	}
    
    	$stores=$collection->load();
    	echo Mage::helper('core')->jsonEncode($collection->getData());
    
    }
}
