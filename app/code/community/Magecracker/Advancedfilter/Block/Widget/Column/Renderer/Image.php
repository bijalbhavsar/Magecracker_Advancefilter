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
 * Adminhtml grid item abstract renderer for Product Image
 *
 * @category   Magecracker
 * @package    Magecracker_Advancedfilter
 * @author     Bijal Bhavsar
 */

class Magecracker_Advancedfilter_Block_Widget_Column_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	 public function render(Varien_Object $row) {
    	if (empty($row['image'])) return 'No Image';  
    	return '<img src="'. Mage::helper('catalog/image')->init($row, 'image', $row['image'])->resize(75)->__toString(). '" />';
    }

       
}
	