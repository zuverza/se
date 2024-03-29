<?php
/**
 * @version 1.5
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

Tienda::load( 'TiendaPluginBase', 'library.plugins._base' );
Tienda::load( 'TiendaModelBase', 'models._base' );

if ( !class_exists( 'TiendaShippingPlugin' ) )
{
	
	class TiendaShippingPlugin extends TiendaPluginBase
	{
		/**
		 * @var $_element  string  Should always correspond with the plugin's filename, 
		 *                         forcing it to be unique 
		 */
		var $_element = '';
		
		function __construct( &$subject, $config )
		{
			parent::__construct( $subject, $config );
			$this->loadLanguage( '', JPATH_ADMINISTRATOR );
			$this->loadLanguage( '', JPATH_SITE );
			$this->getShopAddress( );
			
			$this->_log_file = JPATH_SITE . DS . 'images' . DS . 'com_tienda' . DS . 'debug' . DS . $this->_element . DS .'.txt';
		}
		
		/************************************
		 * Note to 3pd: 
		 * 
		 * The methods between here
		 * and the next comment block are 
		 * yours to modify by overrriding them in your shipping plugin
		 * 
		 ************************************/
		
		/**
		 * Returns the Shipping Rates.
		 * @param $element the shipping element name
		 * @param $product the product row
		 * @return array
		 */
		public function onGetShippingRates( $element, $order )
		{
			if ( !$this->_isMe( $element ) )
			{
				return null;
			}
			
			$rate = array( );
			$rate['name'] = "";
			$rate['code'] = "";
			$rate['price'] = "";
			$rate['extra'] = "";
			$rate['total'] = "";
			$rate['tax'] = "";
			$rate['element'] = $this->_element;
			$rate['error'] = false;
			$rate['errorMsg'] = "";
			$rate['debug'] = "";
			
			$rates[] = $return;
			
			return $rates;
		}
		
		/**
		 * Here you will have to save the shipping rate information
		 * 
		 * @param $element the shipping element name
		 * @param $order the order object
		 * @return html
		 */
		public function onPostSaveShipping( $element, $order )
		{
			if ( !$this->_isMe( $element ) )
			{
				return null;
			}
		}
		
		/**
		 * Get a particular shipping rate
		 * @param unknown_type $rate_id
		 */
		public function getShippingRate( $rate_id )
		{
		}
		
		/** 
		 * Shows the shipping view
		 * 
		 * @param $row	the shipping data
		 * @return unknown_type
		 */
		public function onGetShippingView( $row )
		{
			if ( !$this->_isMe( $row ) )
			{
				return null;
			}
		}
		
		/**
		 * If you want to show something on the product admin page, 
		 * override this function
		 * 
		 * @param $product the product row
		 * @return html
		 */
		public function onGetProductView( $product )
		{
			// show something on the product admin page
		}
		
		/**
		 * If you have to deal with the product data after the save
		 * 
		 * @param $product the product row
		 * @return html
		 */
		public function onAfterSaveProducts( $product )
		{
			// Do Something here with the product data
		}
		
		/**
		 * If you want to show something on the category admin page
		 * 
		 * @param $category the product row
		 * @return html
		 */
		public function onGetCategoryView( $category )
		{
			// show something on the category admin page
		}
		
		/**
		 * If you have to deal with the category data after the save
		 * 
		 * @param $category the product row
		 * @return html
		 */
		public function onAfterSaveCategories( $category )
		{
			// Do Something here with the category data
		}
		
		/************************************
		 * Note to 3pd: 
		 * 
		 * DO NOT MODIFY ANYTHING AFTER THIS
		 * TEXT BLOCK UNLESS YOU KNOW WHAT YOU 
		 * ARE DOING!!!!!
		 * 
		 ************************************/
		
		/**
		 * Tells extension that this is a shipping plugin
		 * 
		 * @param $element  string      a valid shipping plugin element 
		 * @return boolean	true if it is this particular shipping plugin
		 */
		public function onGetShippingPlugins( $element )
		{
			$success = false;
			if ( $this->_isMe( $element ) )
			{
				$success = true;
			}
			return $success;
		}
		
	/**
     * Determines if this shipping option is valid for this order
     * 
     * @param $element
     * @param $order
     * @return unknown_type
     */
    function onGetShippingOptions($element, $order)
    {       
        // Check if this is the right plugin
        if (!$this->_isMe($element)) 
        {
            return null;
        }        
   
        $found = true;             
        $geozones = $this->params->get('geozones');        
        
        //return true if we have empty geozones
        if(!empty($geozones))
        {
        	$found = false;
          	
          	$geozones = explode(',', $geozones); 
          	$orderGeoZones = $order->getShippingGeoZones();
                 
          	//loop to see if we have at least one geozone assigned
          	foreach( $orderGeoZones as $orderGeoZone )
          	{
          		if(in_array($orderGeoZone->geozone_id, $geozones))
          		{
          			$found = true;
          			break;
          		}
          	}
        }
        
        // if this shipping methods should be available for this order, return true
        // if not, return false.
        // by default, all enabled shipping methods are valid, so return true here,
        // but plugins may override this         
        return $found;
    }
		
		
		/**
		 * Gets the reports namespace for state variables
		 * @return string
		 */
		protected function _getNamespace( )
		{
			$app = JFactory::getApplication( );
			$ns = $app->getName( ) . '::' . 'com.tienda.shipping.' . $this->get( '_element' );
		}
		
		/**
		 * Get the task for the shipping plugin controller
		 */
		public function getShippingTask( )
		{
			$task = JRequest::getVar( 'shippingTask', '' );
			return $task;
		}
		
		/**
		 * Get the id of the current shipping plugin
		 */
		public function getShippingId( )
		{
			$sid = JRequest::getVar( 'sid', '' );
			return $sid;
		}
		
		/**
		 * Get a variable from the JRequest object
		 * @param unknown_type $name
		 */
		public function getShippingVar( $name )
		{
			$var = JRequest::getVar( $name, '' );
			return $var;
		}
		
		/**
		 * Prepares the 'view' tmpl layout
		 * when viewing a report
		 *  
		 * @return unknown_type
		 */
		function _renderView( $view = 'view', $vars = null )
		{
			if ( $vars == null ) $vars = new JObject( );
			$html = $this->_getLayout( $view, $vars );
			
			return $html;
		}
		
		/**
		 * Prepares variables for the report form
		 * 
		 * @return unknown_type
		 */
		function _renderForm( )
		{
			$vars = new JObject( );
			$html = $this->_getLayout( 'form', $vars );
			
			return $html;
		}
		
		/**
		 * Gets the appropriate values from the request
		 * 
		 * @return unknown_type
		 */
		function _getState( )
		{
			$state = new JObject( );
			
			foreach ( $state->getProperties( ) as $key => $value )
			{
				$new_value = JRequest::getVar( $key );
				$value_exists = array_key_exists( $key, JRequest::get( 'post' ) );
				if ( $value_exists && !empty( $key ) )
				{
					$state->$key = $new_value;
				}
			}
			return $state;
		}
		
		/**
		 * Gets the store's address
		 * @return unknown_type
		 */
		function getShopAddress( )
		{
			if ( empty( $this->shopAddress ) )
			{
				$this->shopAddress = new JObject( );
				
				$config = TiendaConfig::getInstance( );
				$this->shopAddress->address_1 = $config->get( 'shop_address_1' );
				$this->shopAddress->address_2 = $config->get( 'shop_address_2' );
				$this->shopAddress->city = $config->get( 'shop_city' );
				$this->shopAddress->country = $config->get( 'shop_country' );
				
				$this->includeTiendaTables( );
				$table = JTable::getInstance( 'Countries', 'TiendaTable' );
				$table->load( $this->shopAddress->country );
				$this->shopAddress->country_isocode_2 = $table->country_isocode_2;
				
				$this->shopAddress->zone = $config->get( 'shop_zone' );
				
				$table = JTable::getInstance( 'Zones', 'TiendaTable' );
				$table->load( $this->shopAddress->zone );
				$this->shopAddress->zone_code = $table->code;
				
				$this->shopAddress->zip = $config->get( 'shop_zip' );
			}
			return $this->shopAddress;
		}
		
		/**
		 * Adds zone and country codes to teh address object if not present
		 * @param $address
		 * @return unknown_type
		 */
		function checkAddress( $address )
		{
			$this->includeTiendaTables( );
			
			if ( empty( $address->zone_code ) )
			{
				if ( !empty( $address->zone_id ) )
				{
					$table = JTable::getInstance( 'Zones', 'TiendaTable' );
					$table->load( $address->zone_id );
					$address->zone_code = $table->code;
				}
			}
			
			if ( empty( $address->country_code ) || empty( $address->country_name ) || empty( $address->country_isocode_2 )
					|| empty( $address->country_isocode_3 ) )
			{
				if ( !empty( $address->country_id ) )
				{
					$table = JTable::getInstance( 'Countries', 'TiendaTable' );
					$table->load( $address->country_id );
					$address->country_name = $table->country_name;
					$address->country_isocode_3 = $table->country_isocode_3;
					$address->country_isocode_2 = $table->country_isocode_2;
					$address->country_code = $table->country_isocode_2;
				}
			}
			
			return $address;
		}
		
	}
	
}
