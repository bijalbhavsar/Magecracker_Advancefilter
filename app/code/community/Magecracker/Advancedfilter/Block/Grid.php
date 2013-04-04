<?php
/**
 * created : 04/04/12
 * 
 * @category Magecracker
 * @package Magecracker_Advancedfilter
 * @author Bijal Bhavsar
 * @copyright Magecracker - 2012 - http://magecracker.wordpress.com
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Override of admin product grid
 * Used to Advance filter product grid in admin
 * @package Magecracker_Advancedfilter
 */
class Magecracker_Advancedfilter_Block_Grid extends Mage_Adminhtml_Block_Catalog_Product_Grid 
{ 
		
	const CONFIG_ENABLED = 'attribute_filter_section/settings_group/use_attribute_filter';
	const CONFIG_ATTRIBUTE_CODE = 'attribute_filter_section/settings_group/filter_attribute';
	const CONFIG_CATEGORY_FILTER = 'attribute_filter_section/settings_group/use_category_filter';
	const CONFIG_PRODUCT_IMAGE = 'attribute_filter_section/settings_group/use_product_thumbnail';

	
	 public function __construct()
    {
        parent::__construct();
        $this->setId('productGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setTemplate('advancedfilter/product/grid.phtml');
        $this->setUseAjax(true);
        $this->setVarNameFilter('product_filter');

    }

    
    protected function _prepareCollection()
    {
    	if(Mage::getStoreConfig(self::CONFIG_ENABLED)) {
    		$store = $this->_getStore();
    		$collection = Mage::getModel('catalog/product')->getCollection()
    		->addAttributeToSelect('sku')
    		->addAttributeToSelect('name')
    		->addAttributeToSelect('image')
    		->addAttributeToSelect('attribute_set_id')
    		->addAttributeToSelect('type_id')
    		->joinField('qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');

    		if ($store->getId()) { 
    			$adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
    			$collection->addStoreFilter($store);
    			$collection->joinAttribute('name', 'catalog_product/name', 'entity_id', null, 'inner', $adminStore);
    			$collection->joinAttribute('custom_name', 'catalog_product/name', 'entity_id', null, 'inner', $store->getId());
    			$collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner', $store->getId());
    			$collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner', $store->getId());
    			$collection->joinAttribute('price', 'catalog_product/price', 'entity_id', null, 'left', $store->getId());
    		}
    		else {
    			$collection->addAttributeToSelect('price');
    			$collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
    			$collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
    		}
    		
    		

    		
    		
    		$attributeCode = Mage::getStoreConfig(self::CONFIG_ATTRIBUTE_CODE);
    		if(strstr($attributeCode,',')) 
    		{
    			$arrAttributecode = explode(',', $attributeCode);
    			foreach ($arrAttributecode as $attrib)
    			{
    				$collection->joinAttribute($attrib, 'catalog_product/'.$attrib, 'entity_id', null, 'left');
    				$collection->addAttributeToSelect($attrib);
    			}
    		}
    		else {
    			$collection->joinAttribute($attributeCode, 'catalog_product/'.$attributeCode, 'entity_id', null, 'left');
    			$collection->addAttributeToSelect($attributeCode);
    		}
    		$isCategoryFilter = Mage::getStoreConfig(self::CONFIG_CATEGORY_FILTER);
    		if($isCategoryFilter):
    			$collection->addAttributeToSelect('category_ids');//added for categories
    		endif;
    		$this->setCollection($collection);

    		Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    		$this->getCollection()->addWebsiteNamesToResult();
    		//this extension specific end
    	} else {
    		parent::_prepareCollection();
    	}
    	return $this;

    }
    
    
    
    protected function _prepareColumns()
    {  	
    	if(Mage::getStoreConfig(self::CONFIG_ENABLED)) {
    		$this->addColumn('entity_id',
    		array(
                'header'=> Mage::helper('catalog')->__('ID'),
                'width' => '50px',
                'type'  => 'number',
                'index' => 'entity_id',  
                'id' => 'entity_id',  
    		));
    		$this->addColumn('name',
    		array(
                'header'=> Mage::helper('catalog')->__('Name'),
                'index' => 'name',
                'id' => 'entity_id', 
    		));

    		$store = $this->_getStore();
    		if ($store->getId()) {
    			$this->addColumn('custom_name',
    			array(
                    'header'=> Mage::helper('catalog')->__('Name in %s', $store->getName()),
                    'index' => 'custom_name',
    			));
    		}

    		$this->addColumn('type',
    		array(
                'header'=> Mage::helper('catalog')->__('Type'),
                'width' => '60px',
                'index' => 'type_id',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
    		));

    		// this extension specific

    		 
			$isCategoryFilter = Mage::getStoreConfig(self::CONFIG_CATEGORY_FILTER);		 //To Get category filter config value	
    		if($isCategoryFilter):
				$categories = Mage::getResourceModel('catalog/category_collection')
									->addFieldToFilter(array(array('attribute'=>'level','nin'=>array('0','1'))));
				$category_sets = array();
					foreach($categories as $category){
						$id = $category->getEntityId(); 
						$cat_mod = Mage::getModel('catalog/category')->load($id);
						$category_sets[$id] = $cat_mod->getName();
					}
					
				$this->addColumn('category_ids', 
		 
					array(
					
						'header'=> Mage::helper('catalog')->__('Categories'),
						
						'index' => 'category_ids',
						
						'width' => '100px',
						
						'type' => 'options',
						
						'options' => $category_sets,  
					
					));  
			endif;
			
			$isProductThumbImage = Mage::getStoreConfig(self::CONFIG_PRODUCT_IMAGE); //To Get product image config value
			if($isProductThumbImage): 
			
				 $this->addColumn('image',
		            array(
				                'header'=> Mage::helper('catalog')->__('Image'),
				                'width' => '75px',
				                'index' => 'image',
				                'filter'    => false,
				                'type'		=> 'image',
				                'escape'    => true,
				                'sortable'  => false, 
				                'renderer'  => 'Magecracker_Advancedfilter_Block_Widget_Column_Renderer_Image' 
		         ));
			endif; 
			
    		$attributeCodeConfig = Mage::getStoreConfig(self::CONFIG_ATTRIBUTE_CODE); //To Get attribute config value
    		
    		if(strstr($attributeCodeConfig,','))
    		{
    			$arrAttributecode = array();
    			$arrAttributecode = explode(',', $attributeCodeConfig);
    			foreach ($arrAttributecode as $attrib)
    			{
    				$attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', $attrib);
		
		    		$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
		    		$attributeData = $attribute->getData();
		    		$frontEndLabel = $attributeData['frontend_label'];
		
		    		$attributeOptions = $attribute->getSource()->getAllOptions();
		    		$b = new Mage_Catalog_Model_Resource_Eav_Attribute();
		    		$attributeOptions2 = array();
		    		foreach ($attributeOptions as $value) {
		    			if(!empty($value['value'])) {
		    				$attributeOptions2[$value['value']] = $value['label'];
		    			}
		    				
		    		}
		    		
		
		    		if(count($attributeOptions2) > 0) {
		    			$options = array(
		                		'header'=> Mage::helper('catalog')->__($frontEndLabel),
		                		'width' => '80px',
		                		'index' => $attrib,
		                		'type'  => 'options',
		                		'options' => $attributeOptions2,
		
		    			);
		    			if($attributeData['frontend_input'] == 'multiselect')
		    			{   
		    				$options['filter_condition_callback'] = array($this, '_filterCondition');
		    			}
		    			$this->addColumn($attrib,$options);
		    		} else {
		    			$this->addColumn($attrib,
		    				array(
		                		'header'=> Mage::helper('catalog')->__($frontEndLabel),
		                		'width' => '80px',
		                		'index' => $attrib,
		
		    			));
		    		}
    			
    			}
    		}
    		else {
    			
    				$attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', $attributeCodeConfig);
		
		    		$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
		    		$attributeData = $attribute->getData();
		    		$frontEndLabel = $attributeData['frontend_label'];
		
		    		$attributeOptions = $attribute->getSource()->getAllOptions();
		    		$b = new Mage_Catalog_Model_Resource_Eav_Attribute();
		    		$attributeOptions2 = array();
		    		foreach ($attributeOptions as $value) {
		    			if(!empty($value['value'])) {
		    				$attributeOptions2[$value['value']] = $value['label'];
		    			}
		    				
		    		}
		    		
		
		    		if(count($attributeOptions2) > 0) {
		    			$this->addColumn($attributeCodeConfig,
		    				array(
		                		'header'=> Mage::helper('catalog')->__($frontEndLabel),
		                		'width' => '80px',
		                		'index' => $attributeCodeConfig,
		                		'type'  => 'options',
		                		'options' => $attributeOptions2,
		
		    			));
		    		} else {
		    			$this->addColumn($attributeCodeConfig,
		    				array(
		                		'header'=> Mage::helper('catalog')->__($frontEndLabel),
		                		'width' => '80px',
		                		'index' => $attributeCodeConfig,
		
		    			));
		    		}
    			
    		}

    	

    		// this extension specific end


    		$sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
    		->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
    		->load()
    		->toOptionHash();


    		$this->addColumn('set_name',
    		array(
                'header'=> Mage::helper('catalog')->__('Attrib. Set Name'),
                'width' => '100px',
                'index' => 'attribute_set_id',
                'type'  => 'options',
                'options' => $sets,
    		));
    		$this->addColumn('sku',
    		array(
                'header'=> Mage::helper('catalog')->__('SKU'),
                'width' => '80px',
                'index' => 'sku',
    		));

    		$store = $this->_getStore();
    		$this->addColumn('price',
    		array(
                'header'=> Mage::helper('catalog')->__('Price'),
                'type'  => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'price',
    		));

    		$this->addColumn('qty',
	    		array(
	                'header'=> Mage::helper('catalog')->__('Qty'),
	                'width' => '100px',
	                'type'  => 'number',
	                'index' => 'qty',
	    		));

    		$this->addColumn('visibility',
    		array(
                'header'=> Mage::helper('catalog')->__('Visibility'),
                'width' => '70px',
                'index' => 'visibility',
                'type'  => 'options',
                'options' => Mage::getModel('catalog/product_visibility')->getOptionArray(),
    		));

    		$this->addColumn('status',
    		array(
                'header'=> Mage::helper('catalog')->__('Status'),
                'width' => '70px',
                'index' => 'status',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
    		));

    		if (!Mage::app()->isSingleStoreMode()) {
    			$this->addColumn('websites',
    			array(
                    'header'=> Mage::helper('catalog')->__('Websites'),
                    'width' => '100px',
                    'sortable'  => false,
                    'index'     => 'websites',
                    'type'      => 'options',
                    'options'   => Mage::getModel('core/website')->getCollection()->toOptionHash(),
    			));
    		}

    		$this->addColumn('action',
    		array(
                'header'    => Mage::helper('catalog')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
    		array(
                        'caption' => Mage::helper('catalog')->__('Edit'),
                        'url'     => array(
                            'base'=>'*/*/edit',
                            'params'=>array('store'=>$this->getRequest()->getParam('store'))
    		),
                        'field'   => 'id'
                        )
                        ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                        ));

        $this->addRssList('rss/catalog/notifystock', Mage::helper('catalog')->__('Notify Low Stock RSS'));
        Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
    	} else {
    		parent::_prepareColumns();
    	}
        
        return $this;
    }
    
    /**
     * *
     *  function will filter multiselect attribute value
     * @param Object $collection
     * @param Object $column
     */
    protected function _filterCondition($collection, $column )
	{ 
	    if (!$value = $column->getFilter()->getValue()) {
	        return;
	    } 
	    $this->getCollection()->addAttributeToFilter($column->getId(), array('finset' => $value)); 
	}
}