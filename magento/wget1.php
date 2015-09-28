<?php

require_once('app/Mage.php');
Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));
$id = 2;

$product = Mage::getModel('catalog/product')->load($id);

$data = array ();
$attributes = $product->getAttributes();
        $data[base] = array(
                        'id' => $id,
                        'name' => $product -> getName (),
                        'sku'  => $product -> getSku ()
                    );
foreach ( $attributes as $attribute ) {
            $value= $attribute->getFrontend()->getValue($product);
			$code = $attribute->getAttributeCode();          		
            if ($attribute->getIsVisibleOnFront() && !in_array($attribute->getAttributeCode(), $excludeAttr)) {
                if (!$product->hasData($attribute->getAttributeCode())) {
                    $value = Mage::helper('catalog')->__('N/A');
                } elseif ((string)$value == '') {
                    $value = Mage::helper('catalog')->__('No');
                } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                    $value = Mage::app()->getStore()->convertPrice($value, true);
                }
            }
                if (is_string($value) && strlen($value)) {
                    $data[$code] = array(
                        'label' => $attribute->getStoreLabel(),
                        'value' => $value,
                        'code'  => $code
                    );
                }
	}	

	echo json_encode ( $data );

?>