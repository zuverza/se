<?php
/**
 * @version    1.5
 * @package    Tienda
 * @author     Dioscouri Design
 * @link     http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

Tienda::load( 'TiendaTable', 'tables._base' );

class TiendaTableEavValues extends TiendaTable
{
	// If the table type was set
	private $active = false;
	
	// The type of the eav
	protected $type = '';
	
	// Allowed table types
	private $allowed_types = array();
	
	function TiendaTableEavValues( &$db )
	{
		
		$this->allowed_types = array('int', 'varchar', 'decimal', 'text', 'datetime');		
		
		// do NOT parent::__construct, do the two thing that we can do now
		$this->_tbl_key	= 'eavvalue_id';
		$this->_db		=& $db;
		
		// do NOT set table properties based on table's fields
		// delegate this to the setType() method
	}
	
	/**
	 * 
	 * Set the type of the table, to correctly use the related db table (eavvaluesvarchar, etc)
	 * @param string $type
	 */
	public function setType($type)
	{
		// Check the type
		$type = strtolower($type);
		if(!in_array($type, $this->allowed_types))
		{
			$type = 'varchar'; // default to varchar
		}
		
		$name 		= 'tienda';
		$eav_suffix = 'eavvalues';
		$this->type = $type;
		
		// Set the correct suffix
		$this->set( '_suffix', $eav_suffix.$type );
		$tbl_name =  "#__{$name}_{$eav_suffix}{$type}";
		$this->_tbl = $tbl_name;
		
		// Now set the properties!
		$this->setTableProperties();
		
		// Table Type defined: Activate the table
		$this->active = true;
	}
	
	public function getType()
	{
		return $this->type;
	}
	
	public function store( $updateNulls=false )
	{
		// Check the table activation status first
		if(!$this->active)
		{
			// Activate it with a default value
			$this->setType('');
		}
		
		if( $this->getType() == 'datetime' )
		{	
			if( isset( $this->eavvalue_value ) )
			{
				$null_date = JFactory::getDbo()->getNullDate();
				if( $this->eavvalue_value == $null_date || $this->eavvalue_value == '' )
				{
					$this->eavvalue_value = $null_date;	
				}
				else 
				{
					$offset = JFactory::getConfig()->getValue( 'config.offset' );
					$this->eavvalue_value = date( 'Y-m-d H:i:s', strtotime( TiendaHelperBase::getOffsetDate( $this->eavvalue_value, -$offset ) ) );
				}
			}
		}
		return parent::store( $updateNulls );
	}
	
	public function load( $oid=null, $reset=true)
	{
		// Check the table activation status first
		if(!$this->active)
		{
			// Activate it with a default value
			$this->setType('');
		}
		
		return parent::load( $oid, $reset );
	}
	
	public function save()
	{
		// Check the table activation status first
		if(!$this->active)
		{
			// Activate it with a default value
			$this->setType('');
		}
		
		return parent::save();
	}
	
	public function reset()
	{
		// Check the table activation status first
		if(!$this->active)
		{
			// Activate it with a default value
			$this->setType('');
		}
		
		return parent::reset();
	}
	
	public function check()
	{
		// Check the table activation status first
		if(!$this->active)
		{
			// Activate it with a default value
			$this->setType('');
		}
		
		$nullDate	= $this->_db->getNullDate();
		if (empty($this->modified_date) || $this->modified_date == $nullDate)
		{
			$date = JFactory::getDate();
			$this->modified_date = $date->toMysql();
		}
		if (empty($this->created_date) || $this->created_date == $nullDate)
		{
			$date = JFactory::getDate();
			$this->created_date = $date->toMysql();
		}
		
		return true;
	}
	
	public function delete( $oid = null )
	{
		// Check the table activation status first
		if(!$this->active)
		{
			// Activate it with a default value
			$this->setType('');
		}
		
		return parent::delete( $oid );
	}
	
	public function move( $change, $where = '' )
	{
		// Check the table activation status first
		if(!$this->active)
		{
			// Activate it with a default value
			$this->setType('');
		}
		
		return parent::move( $change, $where );
	}
	
	public function bind( $from, $ignore=array() )
	{
		// Check the table activation status first
		if(!$this->active)
		{
			// Activate it with a default value
			$this->setType('');
		}
		
		return parent::bind( $from, $ignore );
	}
	
	public function getAllowedTypes()
	{
		return $this->allowed_types;
	}
}
