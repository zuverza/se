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

// TODO Make all Tienda plugins extend this _base file, to reduce code redundancy

/** Import library dependencies */
jimport( 'joomla.plugin.plugin' );
jimport( 'joomla.utilities.string' );

class TiendaPluginBase extends JPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
	var $_element = '';
	
	var $_log_file = '';
	
	/**
	 * Override this to avoid overwriting of other constants
	 * (we have custom language file for that)
	 * @see JPlugin::loadLanguage()
	 */
	function loadLanguage( $extension = '', $basePath = JPATH_BASE, $overwrite = false )
	{
		if ( empty( $extension ) )
		{
			$extension = 'plg_' . $this->_type . '_' . $this->_name;
		}
		
		$language = &JFactory::getLanguage( );
		$lang = $language->_lang;
		
		$path = JLanguage::getLanguagePath( $basePath, $lang );
		
		if ( !strlen( $extension ) )
		{
			$extension = 'joomla';
		}
		
		$filename = ( $extension == 'joomla' ) ? $lang : $lang . '.' . $extension;
		$filename = $path . DS . $filename . '.ini';
		
		$result = false;
		if ( isset( $language->_paths[$extension][$filename] ) )
		{
			// Strings for this file have already been loaded
			$result = true;
		}
		else
		{
			// Load the language file
			$result = $language->_load( $filename, $extension, $overwrite );
			
			// Check if there was a problem with loading the file
			if ( $result === false )
			{
				// No strings, which probably means that the language file does not exist
				$path = JLanguage::getLanguagePath( $basePath, $language->_default );
				$filename = ( $extension == 'joomla' ) ? $language->_default : $language->_default . '.' . $extension;
				$filename = $path . DS . $filename . '.ini';
				
				$result = $language->_load( $filename, $extension, $overwrite );
			}
			
		}
		
		return $result;
		
	}
	
	/**
	 * Checks to make sure that this plugin is the one being triggered by the extension
	 *
	 * @access public
	 * @return mixed Parameter value
	 * @since 1.5
	 */
	function _isMe( $row )
	{
		$element = $this->_element;
		$success = false;
		if ( is_object( $row ) && !empty( $row->element ) && $row->element == $element )
		{
			$success = true;
		}
		
		if ( is_string( $row ) && $row == $element )
		{
			$success = true;
		}
		
		return $success;
	}
	
	/**
	 * Prepares variables for the form
	 * 
	 * @return string   HTML to display
	 */
	function _renderForm( )
	{
		$vars = new JObject( );
		$html = $this->_getLayout( 'form', $vars );
		return $html;
	}
	
	/**
	 * Prepares the 'view' tmpl layout
	 * 
	 * @param array
	 * @return string   HTML to display
	 */
	function _renderView( $options = '' )
	{
		$vars = new JObject( );
		$html = $this->_getLayout( 'view', $vars );
		return $html;
	}
	
	/**
	 * Wraps the given text in the HTML
	 *
	 * @param string $text
	 * @return string
	 * @access protected
	 */
	function _renderMessage( $message = '' )
	{
		$vars = new JObject( );
		$vars->message = $message;
		$html = $this->_getLayout( 'message', $vars );
		return $html;
	}
	
	/**
	 * Gets the parsed layout file
	 * 
	 * @param string $layout The name of  the layout file
	 * @param object $vars Variables to assign to
	 * @param string $plugin The name of the plugin
	 * @param string $group The plugin's group
	 * @return string
	 * @access protected
	 */
	function _getLayout( $layout, $vars = false, $plugin = '', $group = 'tienda' )
	{
		if ( empty( $plugin ) )
		{
			$plugin = $this->_element;
		}
		
		ob_start( );
		$layout = $this->_getLayoutPath( $plugin, $group, $layout );
		include ( $layout );
		$html = ob_get_contents( );
		ob_end_clean( );
		
		return $html;
	}
	
	/**
	 * Get the path to a layout file
	 *
	 * @param   string  $plugin The name of the plugin file
	 * @param   string  $group The plugin's group
	 * @param   string  $layout The name of the plugin layout file
	 * @return  string  The path to the plugin layout file
	 * @access protected
	 */
	function _getLayoutPath( $plugin, $group, $layout = 'default' )
	{
		$app = JFactory::getApplication( );
		
		// get the template and default paths for the layout
		$templatePath = JPATH_SITE . DS . 'templates' . DS . $app->getTemplate( ) . DS . 'html' . DS . 'plugins' . DS . $group . DS . $plugin . DS
				. $layout . '.php';
		$defaultPath = JPATH_SITE . DS . 'plugins' . DS . $group . DS . $plugin . DS . 'tmpl' . DS . $layout . '.php';
		
		// if the site template has a layout override, use it
		jimport( 'joomla.filesystem.file' );
		if ( JFile::exists( $templatePath ) )
		{
			return $templatePath;
		}
		else
		{
			return $defaultPath;
		}
	}
	
	/**
	 * This displays the content article
	 * specified in the plugin's params
	 * 
	 * @return unknown_type
	 */
	function _displayArticle( )
	{
		$html = '';
		
		$articleid = $this->params->get( 'articleid' );
		if ( $articleid )
		{
			Tienda::load( 'TiendaArticle', 'library.article' );
			$html = TiendaArticle::display( $articleid );
		}
		
		return $html;
	}
	
	/**
	 * Checks for a form token in the request
	 * Using a suffix enables multi-step forms
	 * 
	 * @param string $suffix
	 * @return boolean
	 */
	function _checkToken( $suffix = '', $method = 'post' )
	{
		$token = JUtility::getToken( );
		$token .= "." . strtolower( $suffix );
		if ( JRequest::getVar( $token, '', $method, 'alnum' ) )
		{
			return true;
		}
		return false;
	}
	
	/**
	 * Generates an HTML form token and affixes a suffix to the token
	 * enabling the form to be identified as a step in a process 
	 *  
	 * @param string $suffix
	 * @return string HTML
	 */
	function _getToken( $suffix = '' )
	{
		$token = JUtility::getToken( );
		$token .= "." . strtolower( $suffix );
		$html = '<input type="hidden" name="' . $token . '" value="1" />';
		$html .= '<input type="hidden" name="tokenSuffix" value="' . $suffix . '" />';
		return $html;
	}
	
	/**
	 * Gets the suffix affixed to the form's token
	 * which helps identify which step this is
	 * in a multi-step process 
	 *  
	 * @return string
	 */
	function _getTokenSuffix( $method = 'post' )
	{
		$suffix = JRequest::getVar( 'tokenSuffix', '', $method );
		if ( !$this->_checkToken( $suffix, $method ) )
		{
			// what to do if there isn't this suffix's token in the request?
			// anything?
		}
		return $suffix;
	}
	
	/**
	 * Gets the row from the __plugins DB table that corresponds to this plugin
	 *  
	 * @return object
	 */
	function _getMe( )
	{
		if ( empty( $this->_row ) )
		{
			JTable::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'tables' );
			$table = JTable::getInstance( 'Shipping', 'TiendaTable' );
			$table->load( array(
						'element' => $this->_element, 'folder' => 'tienda'
					) );
			$this->_row = $table;
		}
		return $this->_row;
	}
	
	/**
	 * Make the standard Tienda Tables avaiable in the plugin
	 */
	protected function includeTiendaTables( )
	{
		// Include Tienda Tables Classes
		JTable::addIncludePath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_tienda' . DS . 'tables' );
	}
	
	/**
	 * Include a particular Tienda Model
	 * @param $name the name of the mode (ex: products)
	 */
	protected function includeTiendaModel( $name )
	{
		if ( strtolower( $name ) != 'base' ) Tienda::load( 'TiendaModel' . ucfirst( strtolower( $name ) ), 'models.' . strtolower( $name ) );
		else Tienda::load( 'TiendaModelBase', 'models._base' );
	}
	
	/**
	 * Include a particular Custom Model
	 * @param $name the name of the model
	 * @param $plugin the name of the plugin in which the model is stored
	 * @param $group the group of the plugin
	 */
	protected function includeCustomModel( $name, $plugin = '', $group = 'tienda' )
	{
		if ( empty( $plugin ) )
		{
			$plugin = $this->_element;
		}
		
		if ( !class_exists( 'TiendaModel' . $name ) ) JLoader::import( 'plugins.' . $group . '.' . $plugin . '.models.' . strtolower( $name ), JPATH_SITE );
	}
	
	/**
	 * add a user-defined table to list of available tables (including the Tienda tables
	 * @param $plugin the name of the plugin in which the table is stored
	 * @param $group the group of the plugin
	 */
	protected function includeCustomTables( $plugin = '', $group = 'tienda' )
	{
		
		if ( empty( $plugin ) )
		{
			$plugin = $this->_element;
		}
		
		$this->includeTiendaTables( );
		$customPath = JPATH_SITE . DS . 'plugins' . DS . $group . DS . $plugin . DS . 'tables';
		JTable::addIncludePath( $customPath );
	}
	
	/**
	 * Include a particular Custom View
	 * @param $name the name of the view
	 * @param $plugin the name of the plugin in which the view is stored
	 * @param $group the group of the plugin
	 */
	protected function includeCustomView( $name, $plugin = '', $group = 'tienda' )
	{
		if ( empty( $plugin ) )
		{
			$plugin = $this->_element;
		}
		
		if ( !class_exists( 'TiendaView' . $name ) ) JLoader::import( 'plugins.' . $group . '.' . $plugin . '.views.' . strtolower( $name ), JPATH_SITE );
	}
	
	protected function writeToLog( $text )
	{
		static $first = true;
		jimport( 'joomla.filesystem.file' );
		
		$dump = '';
		if ( JFile::exists( $this->_log_file ) )
		{
			$dump = JFile::read( $this->_log_file );
		}
		if ( $first )
		{
			$dump = "\n\n" . Date( 'd.n.Y - H:i.s ', time( ) ) . $text;
			$first = false;
		}
		else
		{
			// Dump at the head of the file
			$dump = "\n\n" . Date( 'd.n.Y - H:i.s ', time( ) ) . $text . $dump;
		}
		JFile::write( $this->_log_file, $dump );
	}
	
	public function clearLog( )
	{
		if ( JFile::exists( $this->_log_file ) )
		{
			JFile::write( $this->_log_file, '' );
		}
	}
	
}
