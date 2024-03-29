<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

Tienda::load( 'TiendaTable', 'tables._base' );

class TiendaTableProductIssues extends TiendaTable
{
	function TiendaTableProductIssues ( &$db )
	{

		$tbl_key 	= 'product_issue_id';
		$tbl_suffix = 'productissues';
		$this->set( '_suffix', $tbl_suffix );
		$name 		= 'tienda';

		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );
	}

	/**
	 * Checks row for data integrity.
	 * Assumes working dates have been converted to local time for display,
	 * so will always convert working dates to GMT
	 *
	 * @return unknown_type
	 */
	function check()
	{
		if (empty($this->product_id))
		{
			$this->setError( JText::_( "Product Association Required" ) );
			return false;
		}

		$offset = JFactory::getConfig()->getValue( 'config.offset' );
		if( isset( $this->publishing_date ) )
		{
			$this->publishing_date = date( 'Y-m-d H:i:s', strtotime( TiendaHelperBase::getOffsetDate( $this->publishing_date, -$offset ) ) );
		}


		$nullDate = $this->_db->getNullDate();
		Tienda::load( 'TiendaHelperBase', 'helpers._base' );

		if (empty($this->created_date) || $this->created_date == $nullDate)
		{
			$date = JFactory::getDate();
			$this->created_date = $date->toMysql();
		}

		$date = JFactory::getDate();
		$this->modified_date = $date->toMysql();
		$act = strtotime( Date( 'Y-m-d', strtotime( $this->publishing_date ) ) );
					
		$db = $this->_db;
		if( empty( $this->product_issue_id ) ) // add at the end
		{
			$q = 'SELECT `publishing_date` FROM `#__tienda_productissues` WHERE `product_id`='.$this->product_id.' ORDER BY `publishing_date` DESC LIMIT 1';
			$db->setQuery( $q );
			$next = $db->loadResult();
			if( $next === null )
				return true;
			$next = strtotime( $next );
			if( $act <= $next )
			{
				$this->setError( JText::_( "Publishing date is not preserving issue order" ).' - '.$this->publishing_date );
				return false;
			}
		}
		else
		{
			$q = 'SELECT `publishing_date` FROM `#__tienda_productissues` WHERE `product_issue_id`='.$this->product_issue_id;
			$db->setQuery( $q );
			$original = $db->loadResult();
			if( $act == strtotime( Date( 'Y-m-d', strtotime( $original ) ) ) )
				return true;

			$q = 'SELECT `publishing_date` FROM `#__tienda_productissues` WHERE `product_id`='.$this->product_id.' AND `publishing_date` < \''.$original.'\' ORDER BY `publishing_date` DESC LIMIT 1';
			$db->setQuery( $q );
			$prev = $db->loadResult();
			$q = 'SELECT `publishing_date` FROM `#__tienda_productissues` WHERE `product_id`='.$this->product_id.' AND `publishing_date` > \''.$original.'\' ORDER BY `publishing_date` ASC LIMIT 1';
			$db->setQuery( $q );
			$next = $db->loadResult();
			
			if( $prev === null )
				$prev = 0;
			else
				$prev = strtotime( $prev );
			if( $next )
				$next = strtotime( $next );
	
			if( ( $prev >= $act ) || ( $next && $next <= $act ) )
			{
				$this->setError( JText::_( "Publishing date is not preserving issue order" ).' - '.$this->publishing_date );
				return false;
			}
		}
		return true;
	}
}
