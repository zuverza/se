<?php
/**
 * @package	Tienda
 * @author 	Gerald Zalsos
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

// Check the registry to see if our Tienda class has been overridden
if ( !class_exists('Tienda') ) 
 			JLoader::register( "Tienda", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."defines.php" );

//include helper files
require_once( dirname(__FILE__).DS.'helper.php' );

// include lang files
$lang = JFactory::getLanguage();
$lang->load( 'com_tienda', JPATH_BASE );
$lang->load( 'com_tienda', JPATH_ADMINISTRATOR );

//get item to display
$helper = new modTiendaComparedProductsHelper();

//include template for display
require( JModuleHelper::getLayoutPath( 'mod_tienda_compared_products' ) );