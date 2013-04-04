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
 * All attributes option array
 *
 * @category   Magecracker
 * @package    Magecracker_Advancedfilter
 * @author     Bijal Bhavsar 
 */
class Magecracker_Advancedfilter_Model_Attributes
{

    public function toOptionArray()
    {
    	$ignoreAttributes = array('sku', 'name', 'attribute_set_id', 'type_id', 'qty', 'price', 'status', 'visibility');
    	
    	$collection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter();
        
        $result = array();   
    	foreach ($collection as $model) {
    		if(in_array($model->getAttributeCode(), $ignoreAttributes)) {
    			continue;
    		}
    		$productCollection = Mage::getModel('catalog/product')->getCollection();
    		$productCollection->addAttributeToSelect(array($model->getAttributeCode()));
    		$productCollection->addAttributeToFilter($model->getAttributeCode(), array('gt' => 0));
    		
    		if(count($productCollection->getData()) > 0) {
    			$result[] = array('value' => $model->getAttributeCode(), 'label'=>$model->getFrontendLabel());
    		}

        }

       return $result;

    }
}
