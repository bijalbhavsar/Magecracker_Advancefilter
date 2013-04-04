<?php
/**
 * Add attribute to filter
 *
 * @category   Magecracker
 * @package    Magecracker_Advancedfilter
 * @author     Bijal Bhavsar 
 */
class Magecracker_Advancedfilter_Model_Resource_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{
	
	 /**
     * Add attribute to filter
     *
     * @param Mage_Eav_Model_Entity_Attribute_Abstract|string $attribute
     * @param array $condition
     * @param string $joinType
     * @return Magecracker_Advancedfilter_Model_Resource_Product_Collection
     */
	  
	 public function addAttributeToFilter($attribute, $condition = null, $joinType = 'inner')
    {
    	if(is_string($attribute) && $attribute == 'category_ids'){
			
			if(isset($condition['eq'])){
			
					$this->getSelect()->join(
					
					array('category' => $this->getTable('catalog/category_product')),
					
					'category.product_id=e.entity_id AND category.category_id='.$condition['eq'],
					
					array()
					
					);
					
			}
        } else {
        	return parent::addAttributeToFilter($attribute, $condition, $joinType);
        }
    }
    
    
     /**
     * Add attribute to sort order
     *
     * @param string $attribute
     * @param string $dir
     * @return Magecracker_Advancedfilter_Model_Resource_Product_Collection
     */
    public function addAttributeToSort($attribute, $dir = self::SORT_ORDER_ASC)
    {
    	  ////Added to sort by category added by bijal
		if ($attribute == 'category_ids') {
			$this->getSelect()->order('e.entity_id ' . $dir);
			return $this;
		}
		return parent::addAttributeToSort($attribute, $dir); 
    }
}