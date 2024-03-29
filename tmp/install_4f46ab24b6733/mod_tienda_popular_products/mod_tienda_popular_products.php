<?php
/**
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

// Check the registry to see if our Tienda class has been overridden
if ( !class_exists('Tienda') ) 
    JLoader::register( "Tienda", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."defines.php" );
    
require_once( dirname(__FILE__).DS.'helper.php' );

// include lang files
$element = strtolower( 'com_tienda' );
$lang =& JFactory::getLanguage();
$lang->load( $element, JPATH_BASE );
$lang->load( $element, JPATH_ADMINISTRATOR );

$display_null = $params->get( 'display_null', '1' );
$null_text = $params->get( 'null_text', 'No Products Returned' );

// Grab the products
$helper = new modTiendaPopularProductsHelper( $params ); 
$products = $helper->getProducts();
$num = count($products);

$mainframe =& JFactory::getApplication();
$document =& JFactory::getDocument();

require( JModuleHelper::getLayoutPath( 'mod_tienda_popular_products' ) );