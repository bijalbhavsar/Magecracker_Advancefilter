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
 * Adminhtml grid item abstract renderer
 *
 * @category   Magecracker
 * @package    Magecracker_Advancedfilter
 * @author     Bijal Bhavsar
 */

class Magecracker_Advancedfilter_Block_Widget_Column_Renderer_Options extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Options
{
	
	/**
     * Render a grid cell as options
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $options = $this->getColumn()->getOptions();
        
        $showMissingOptionValues = (bool)$this->getColumn()->getShowMissingOptionValues();
        if (!empty($options) && is_array($options)) {
            $value = $row->getData($this->getColumn()->getIndex());
            ///START added for category filter
	        $attr = $this->getColumn()->getIndex();
			if($attr == 'category_ids'){
				$value = $row->getCategoryIds($row);
			}else{
				$attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', $attr);
		
		    	$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
		    	if($attribute->getData('frontend_input') == 'multiselect'){
		    		 
		    		$value =  $row->getData($attr);
		    		if(strstr($value,',')) {
		    			$value = explode(',',$value);
		    		}
		    		 
		    	} else {
		    	
					$value = $row->getData($attr);
		    	}
			}
	        ///END added for category filter 
            
            if (is_array($value)) {
                $res = array();
                foreach ($value as $item) {
                    if (isset($options[$item])) {
                        $res[] = $this->escapeHtml($options[$item]);
                    }
                    elseif ($showMissingOptionValues) {
                        $res[] = $this->escapeHtml($item);
                    }
                }
                return implode(', ', $res);
            } elseif (isset($options[$value])) {
                return $this->escapeHtml($options[$value]);
            } elseif (in_array($value, $options)) {
                return $this->escapeHtml($value);
            }
        }
    }
}