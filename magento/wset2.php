<?php
//安装2，生成属性
require_once('app/Mage.php');
Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));
$installer = new Mage_Sales_Model_Mysql4_Setup; //Mage_Eav_Model_Entity_Setup,Mage_Catalog_Model_Resource_Setup,

//型号
$a_xinghao  = array(
        'type'              => 'varchar',//varchar,int,decimal,text,datetime
        'backend'           => '',
        'frontend'          => '',
        'label'             => '型号',
        'input'             => 'text', //text,textarea,date,boolean,multiselect,select,price,media_image,weee
        'class'             => '',
    	'source'            => '',
        'default'           => '',
	    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	    'visible'           => 1,
	    'required'          => 0,
	    'user_defined'      => 1,
	    'searchable'        => 1,
	    'visible_in_advanced_search' => 1,
	    'filterable'        => 0,
	    'comparable'        => 0,
	    'visible_on_front'  => 1,
        'used_in_product_listing' => 1,
	    'unique'            => 0,		
        'apply_to'          => '',
        'is_configurable'   => 0		
);
//形状
$a_xingzhuang  = array(
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => '形状',
        'input'             => 'select', //text,textarea,date,boolean,multiselect,select,price,media_image,weee
        'class'             => '',
    	'source'            => 'eav/entity_attribute_source_table',
        'default'           => '',
	    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	    'visible'           => 1,
	    'required'          => 0,
	    'user_defined'      => 1,
	    'searchable'        => 1,
	    'visible_in_advanced_search' => 1,
	    'filterable'        => 0,
	    'comparable'        => 0,
	    'visible_on_front'  => 1,
        'used_in_product_listing' => 1,
	    'unique'            => 0,		
        'apply_to'          => '',
        'is_configurable'   => 0,
	    'option' => array(
	        'value' => array( 
	            'o1'   => array( '长方形',''),
	            'o2'   => array( '正方形',''),
	            'o3' => array( '圆形',''),
	            'o4'   => array( '三角形',''),
	        )
	    ),		
);
//规格
$a_guige  = array(
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => '规格(长x宽)',
        'input'             => 'select', //text,textarea,date,boolean,multiselect,select,price,media_image,weee
        'class'             => '',
    	'source'            => 'eav/entity_attribute_source_table',
        'default'           => '',
	    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	    'visible'           => 1,
	    'required'          => 0,
	    'user_defined'      => 1,
	    'searchable'        => 1,
	    'visible_in_advanced_search' => 1,
	    'filterable'        => 0,
	    'comparable'        => 0,
	    'visible_on_front'  => 1,
        'used_in_product_listing' => 1,
	    'unique'            => 0,		
        'apply_to'          => '',
        'is_configurable'   => 0
);
//长
$a_chang  = array(
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => '长(mm)',
        'input'             => 'text', //text,textarea,date,boolean,multiselect,select,price,media_image,weee
        'class'             => '',
    	'source'            => '',
        'default'           => '',
	    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	    'visible'           => 1,
	    'required'          => 0,
	    'user_defined'      => 1,
	    'searchable'        => 1,
	    'visible_in_advanced_search' => 1,
	    'filterable'        => 0,
	    'comparable'        => 0,
	    'visible_on_front'  => 1,
        'used_in_product_listing' => 1,
	    'unique'            => 0,		
        'apply_to'          => '',
        'is_configurable'   => 0
);
//宽
$a_kuan  = array(
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => '宽(mm)',
        'input'             => 'text', //text,textarea,date,boolean,multiselect,select,price,media_image,weee
        'class'             => '',
    	'source'            => '',
        'default'           => '',
	    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	    'visible'           => 1,
	    'required'          => 0,
	    'user_defined'      => 1,
	    'searchable'        => 1,
	    'visible_in_advanced_search' => 1,
	    'filterable'        => 0,
	    'comparable'        => 0,
	    'visible_on_front'  => 1,
        'used_in_product_listing' => 1,
	    'unique'            => 0,		
        'apply_to'          => '',
        'is_configurable'   => 0
);
//有否日期
$a_riqi  = array(
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => '有无日期',
        'input'             => 'select', //text,textarea,date,boolean,multiselect,select,price,media_image,weee
        'class'             => '',
    	'source'            => 'eav/entity_attribute_source_boolean',
        'default'           => '',
	    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	    'visible'           => 1,
	    'required'          => 0,
	    'user_defined'      => 1,
	    'searchable'        => 1,
	    'visible_in_advanced_search' => 1,
	    'filterable'        => 0,
	    'comparable'        => 0,
	    'visible_on_front'  => 1,
        'used_in_product_listing' => 1,
	    'unique'            => 0,		
        'apply_to'          => '',
        'is_configurable'   => 0
);
//类型（日期or数字)
$a_leixing  = array(
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => '类型',
        'input'             => 'select', //text,textarea,date,boolean,multiselect,select,price,media_image,weee
        'class'             => '',
    	'source'            => 'eav/entity_attribute_source_table',
        'default'           => '',
	    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	    'visible'           => 1,
	    'required'          => 0,
	    'user_defined'      => 1,
	    'searchable'        => 1,
	    'visible_in_advanced_search' => 1,
	    'filterable'        => 0,
	    'comparable'        => 0,
	    'visible_on_front'  => 1,
        'used_in_product_listing' => 1,
	    'unique'            => 0,		
        'apply_to'          => '',
        'is_configurable'   => 0,
	    'option' => array(
	        'value' => array( 
	            'o1'   => array( '日期印',''),
	            'o2'   => array( '数字印',''),
	        )
	    ),
);
//字高
$a_zigao  = array(
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => '字高(mm)',
        'input'             => 'select', //text,textarea,date,boolean,multiselect,select,price,media_image,weee
        'class'             => '',
    	'source'            => 'eav/entity_attribute_source_table',
        'default'           => '',
	    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	    'visible'           => 1,
	    'required'          => 0,
	    'user_defined'      => 1,
	    'searchable'        => 1,
	    'visible_in_advanced_search' => 1,
	    'filterable'        => 0,
	    'comparable'        => 0,
	    'visible_on_front'  => 1,
        'used_in_product_listing' => 1,
	    'unique'            => 0,		
        'apply_to'          => '',
        'is_configurable'   => 0,
	    'option' => array(
	        'value' => array( 
	            'o1'   => array( '10',''),
	            'o2'   => array( '12',''),
	        )
	    ),
);
//格式
$a_geshi  = array(
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => '语言格式',
        'input'             => 'select', //text,textarea,date,boolean,multiselect,select,price,media_image,weee
        'class'             => '',
    	'source'            => 'eav/entity_attribute_source_table',
        'default'           => '',
	    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	    'visible'           => 1,
	    'required'          => 0,
	    'user_defined'      => 1,
	    'searchable'        => 1,
	    'visible_in_advanced_search' => 1,
	    'filterable'        => 0,
	    'comparable'        => 0,
	    'visible_on_front'  => 1,
        'used_in_product_listing' => 1,
	    'unique'            => 0,		
        'apply_to'          => '',
        'is_configurable'   => 0,
	    'option' => array(
	        'value' => array( 
	            'o1'   => array( '中文',''),
	            'o2'   => array( '英文',''),
	        )
	    ),
);
//位数
$a_weishu  = array(
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => '位数',
        'input'             => 'select', //text,textarea,date,boolean,multiselect,select,price,media_image,weee
        'class'             => '',
    	'source'            => 'eav/entity_attribute_source_table',
        'default'           => '',
	    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	    'visible'           => 1,
	    'required'          => 0,
	    'user_defined'      => 1,
	    'searchable'        => 1,
	    'visible_in_advanced_search' => 1,
	    'filterable'        => 0,
	    'comparable'        => 0,
	    'visible_on_front'  => 1,
        'used_in_product_listing' => 1,
	    'unique'            => 0,		
        'apply_to'          => '',
        'is_configurable'   => 0,
	    'option' => array(
	        'value' => array( 
	            'o1'   => array( '10',''),
	            'o2'   => array( '12',''),
	        )
	    ),
);
//用途
$a_yongtu  = array(
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => '用途',
        'input'             => 'select', //text,textarea,date,boolean,multiselect,select,price,media_image,weee
        'class'             => '',
    	'source'            => 'eav/entity_attribute_source_table',
        'default'           => '',
	    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	    'visible'           => 1,
	    'required'          => 0,
	    'user_defined'      => 1,
	    'searchable'        => 1,
	    'visible_in_advanced_search' => 1,
	    'filterable'        => 0,
	    'comparable'        => 0,
	    'visible_on_front'  => 1,
        'used_in_product_listing' => 1,
	    'unique'            => 0,		
        'apply_to'          => '',
        'is_configurable'   => 0,
	    'option' => array(
	        'value' => array( 
	            'o1'   => array( '10',''),
	            'o2'   => array( '12',''),
	        )
	    ),
);
//包装
$a_baozhuang  = array(
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => '包装',
        'input'             => 'select', //text,textarea,date,boolean,multiselect,select,price,media_image,weee
        'class'             => '',
    	'source'            => 'eav/entity_attribute_source_table',
        'default'           => '',
	    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	    'visible'           => 1,
	    'required'          => 0,
	    'user_defined'      => 1,
	    'searchable'        => 1,
	    'visible_in_advanced_search' => 1,
	    'filterable'        => 0,
	    'comparable'        => 0,
	    'visible_on_front'  => 1,
        'used_in_product_listing' => 1,
	    'unique'            => 0,		
        'apply_to'          => '',
        'is_configurable'   => 0,
	    'option' => array(
	        'value' => array( 
	            'o1'   => array( '15ML',''),
	            'o2'   => array( '28ML',''),
	            'o3'   => array( '55ML',''),
	            'o4'   => array( '500ML',''),
	            'o5'   => array( '1000ML',''),
	        )
	    ),
);
//颜色
$a_yanse  = array(
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => '颜色',
        'input'             => 'select', //text,textarea,date,boolean,multiselect,select,price,media_image,weee
        'class'             => '',
    	'source'            => 'eav/entity_attribute_source_table',
        'default'           => '',
	    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	    'visible'           => 1,
	    'required'          => 0,
	    'user_defined'      => 1,
	    'searchable'        => 1,
	    'visible_in_advanced_search' => 1,
	    'filterable'        => 0,
	    'comparable'        => 0,
	    'visible_on_front'  => 1,
        'used_in_product_listing' => 1,
	    'unique'            => 0,		
        'apply_to'          => '',
        'is_configurable'   => 0,
	    'option' => array(
	        'value' => array( 
	            'o1'   => array( '红',''),
	            'o2'   => array( '绿',''),
	            'o3'   => array( '蓝',''),
	            'o4'   => array( '紫',''),
	            'o5'   => array( '黑',''),
	            'o6'   => array( '其它',''),
	        )
	    ),
);
//材质
$a_caizhi  = array(
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => '材质',
        'input'             => 'select', //text,textarea,date,boolean,multiselect,select,price,media_image,weee
        'class'             => '',
    	'source'            => 'eav/entity_attribute_source_table',
        'default'           => '',
	    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	    'visible'           => 1,
	    'required'          => 0,
	    'user_defined'      => 1,
	    'searchable'        => 1,
	    'visible_in_advanced_search' => 1,
	    'filterable'        => 0,
	    'comparable'        => 0,
	    'visible_on_front'  => 1,
        'used_in_product_listing' => 1,
	    'unique'            => 0,		
        'apply_to'          => '',
        'is_configurable'   => 0,
	    'option' => array(
	        'value' => array( 
	            'o1'   => array( '钢',''),
	            'o2'   => array( '木',''),
	            'o3'   => array( '胶',''),
	        )
	    ),
);
//先清理可能存在的属性
$installer->removeAttribute('catalog_product', 'a_xinghao');	
$installer->removeAttribute('catalog_product', 'a_xingzhuang');	
$installer->removeAttribute('catalog_product', 'a_guige');	
$installer->removeAttribute('catalog_product', 'a_chang');		 
$installer->removeAttribute('catalog_product', 'a_kuan');		 
$installer->removeAttribute('catalog_product', 'a_riqi');		 
$installer->removeAttribute('catalog_product', 'a_leixing');		 
$installer->removeAttribute('catalog_product', 'a_zigao');		 
$installer->removeAttribute('catalog_product', 'a_geshi');		 
$installer->removeAttribute('catalog_product', 'a_weishu');		 
$installer->removeAttribute('catalog_product', 'a_yongtu');		 
$installer->removeAttribute('catalog_product', 'a_baozhuang');		 
$installer->removeAttribute('catalog_product', 'a_yanse');		 	 
$installer->removeAttribute('catalog_product', 'a_caizhi');	
//end 清理属性
//生成属性
/*
$installer->addAttribute('catalog_product', 'a_xinghao',$a_xinghao);
$installer->addAttribute('catalog_product', 'a_xingzhuang',$a_xingzhuang);
$installer->addAttribute('catalog_product', 'a_guige',$a_guige);
$installer->addAttribute('catalog_product', 'a_chang',$a_chang);
$installer->addAttribute('catalog_product', 'a_kuan',$a_kuan);
$installer->addAttribute('catalog_product', 'a_riqi',$a_riqi);
$installer->addAttribute('catalog_product', 'a_leixing',$a_leixing);
$installer->addAttribute('catalog_product', 'a_zigao',$a_zigao);
$installer->addAttribute('catalog_product', 'a_geshi',$a_geshi);
$installer->addAttribute('catalog_product', 'a_weishu',$a_weishu);
$installer->addAttribute('catalog_product', 'a_yongtu',$a_yongtu);
$installer->addAttribute('catalog_product', 'a_baozhuang',$a_baozhuang);
$installer->addAttribute('catalog_product', 'a_yanse',$a_yanse);
$installer->addAttribute('catalog_product', 'a_caizhi',$a_caizhi);
*/
//end生成属性
/*

$attributeId = $installer->getAttributeId($entity, $attributeCode);

// add it to all attribute sets' default group
foreach ($installer->getAllAttributeSetIds($entity) as $setId) {
    $installer->addAttributeToSet(
        $entity, 
        $setId, 
        $installer->getDefaultAttributeGroupId($entity, $setId), 
        $attributeId
    );
}

*/	 	 
$installer->endSetup();
echo __file__;
echo '2增加产品属性成功';
?>