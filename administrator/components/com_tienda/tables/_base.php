<?php
/**
 * @version	0.1
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

Tienda::load( 'TiendaQuery', 'library.query' );

class TiendaTable extends JTable
{
	/**
	 * constructor
	 */
	function __construct( $tbl_name, $tbl_key, &$db )
	{
		parent::__construct( $tbl_name, $tbl_key, $db );
		// set table properties based on table's fields
		$this->setTableProperties();
	}
	
	/**
	 * Lock the DB tables
	 * @return unknown_type
	 */
	function _lock()
	{
		$query = "LOCK TABLES {$this->_tbl} WRITE;";
		$this->_db->setQuery( $query );
		$this->_db->query();

		if ($this->_db->getErrorNum())
		{
			$this->setError( $this->_db->getErrorMsg() );
			return false;
		}
		$this->_locked = true;
		return true;
	}

	/**
	 * Unlock the DB tables locked in this session
	 * @return unknown_type
	 */
	function _unlock()
	{
		$query = "UNLOCK TABLES;";
		$this->_db->setQuery( $query );
		$this->_db->query();

		if ($this->_db->getErrorNum())
		{
			$this->setError( $this->_db->getErrorMsg() );
			return false;
		}
		$this->_locked = false;
		return true;
	}

	/**
	 * Get columns from db table
	 * @return unknown_type
	 */
	function getColumns()
	{
		static $fields;

		if (empty($fields))
		{
			$fields = $this->_db->getTableFields($this->getTableName());
		}

		$return = @$fields[$this->getTableName()];
		return $return;
	}
	
    /**
     * Set properties of object based on table fields
     *
     * @acces   public
     * @return  object
     */
    function setTableProperties()
    {
        static $fields;

        if (empty($fields))
        {
            $fields = $this->getColumns();
        }

        foreach (@$fields as $name=>$type)
        {
            $this->$name = null;
        }
    }
	
    /**
     * Gets the key names
     * 
     * returned $keynames array typically looks like:
     * $keynames['product_id']  = 'product_id';
     * $keynames['category_id'] = 'category_id';
     * 
     * @return array
     * @since 1.5
     */
    public function getKeyNames()
    {
        $keynames = $this->_tbl_keys;
        if (!is_array($keynames))
        {
            // set _tbl_keys using the primary keyname
            $keynames = array();
            $keyName = $this->getKeyName();
            $keynames[$keyName] = $keyName;
            $this->_tbl_keys = $keynames;
        }
        return $this->_tbl_keys;
    }
    
    /**
     * Sets the keynames
     * 
     * $keynames typically looks like:
     * $keynames = array();
     * $keynames['product_id']  = 'product_id';
     * $keynames['category_id'] = 'category_id';
     * 
     * @param $keynames array
     * @return unknown_type
     */
    public function setKeyNames( $keynames )
    {
        $this->_tbl_keys = $keynames;
        return $this->_tbl_keys;
    }

	/**
	 * Loads a row from the database and binds the fields to the object properties
	 *
	 * @access	public
	 * @param	mixed	Optional primary key.  If not specifed, the value of current key is used
	 * @return	boolean	True if successful
	 */
	function load( $oid=null, $reset=true )
	{
		if (!is_array($oid))
		{
			// load by primary key if not array
			$keyName = $this->getKeyName();
			$oid = array( $keyName => $oid );
		}
		
		if (empty($oid))
		{
			// if empty, use the value of the current key
			$keyName = $this->getKeyName();
			$oid = $this->$keyName;
			if (empty($oid))
			{
				// if still empty, fail
				$this->setError( JText::_( "Cannot load with empty key" ) );
                return false;
			}
		}

        // allow $oid to be an array of key=>values to use when loading
        $oid = (array) $oid;
		
        if (!empty($reset))
        {
            $this->reset();
        }

        $db = $this->getDBO();
        
        // initialize the query
        $query = new TiendaQuery();
        $query->select( '*' );
        $query->from( $this->getTableName() );
        
		foreach ($oid as $key=>$value)
		{
            // Check that $key is field in table
            if ( !in_array( $key, array_keys( $this->getProperties() ) ) )
            {
                $this->setError( get_class( $this ).' does not have the field '.$key );
                return false;
            }
            // add the key=>value pair to the query
            $value = $db->Quote( $db->getEscaped( trim( strtolower( $value ) ) ) );
            $query->where( $key.' = '.$value);
		}
		
		$db->setQuery( (string) $query );
		
	    if ( $result = $db->loadAssoc() )
        {
        	$result = $this->bind($result);
        	
        	if( $result )
        	{
        		$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger( 'onLoad'.$this->get('_suffix'), array( &$this ) );	
        	}
            
			return $result;
        }
        else
        {
            $this->setError( $db->getErrorMsg() );
            return false;
        }
	}

	/**
	 * Generic save function
	 *
	 * @access	public
	 * @returns TRUE if completely successful, FALSE if partially or not successful
	 */
	function save()
	{
	    $this->_isNew = false;
	    $key = $this->getKeyName();
	    if (empty($this->$key))
        {
            $this->_isNew = true;
        }
        
		if ( !$this->check() )
		{
			return false;
		}
		
		if ( !$this->store() )
		{
			return false;
		}
		
		if ( !$this->checkin() )
		{
			$this->setError( $this->_db->stderr() );
			return false;
		}
		
		if( TiendaConfig::getInstance()->get('enable_reorder_table', '1') == '1')
		{
			$this->reorder();
		}
		
		$this->setError('');
		
		// TODO Move ALL onAfterSave plugin events here as opposed to in the controllers, duh
        //$dispatcher = JDispatcher::getInstance();
        //$dispatcher->trigger( 'onAfterSave'.$this->get('_suffix'), array( $this ) );
		return true;
	}

	/**
	 * 
	 * @param unknown_type $updateNulls
	 * @return unknown_type
	 */
	function store( $updateNulls=false )
	{
		$dispatcher = JDispatcher::getInstance();
		$before = $dispatcher->trigger( 'onBeforeStore'.$this->get('_suffix'), array( &$this ) );
		if (in_array(false, $before, true))
		{
			return false;
		}

		if ( $return = parent::store( $updateNulls ))
		{
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger( 'onAfterStore'.$this->get('_suffix'), array( $this ) );
		}
		return $return;
	}

	/**
	 * 
	 * @param $oid
	 * @return unknown_type
	 */
	function delete( $oid=null )
	{
	    $dispatcher = JDispatcher::getInstance();
        $before = $dispatcher->trigger( 'onBeforeDelete'.$this->get('_suffix'), array( $this, $oid ) );
        if (in_array(false, $before, true))
        {
            return false;
        }
        
		if ( $return = parent::delete( $oid ))
		{
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger( 'onAfterDelete'.$this->get('_suffix'), array( $this, $oid ) );
		}
		return $return;
	}

	/**
	 * 
	 * @param unknown_type $change
	 * @param unknown_type $where
	 * @return unknown_type
	 */
	function move($change, $where='')
	{
		if ( !in_array( 'ordering', array_keys( $this->getProperties() ) ) )
		{
			$this->setError( get_class( $this ).' does not support ordering');
			return false;
		}

		settype($change, 'int');

		if ($change !== 0)
		{
			$old = $this->ordering;
			$new = $this->ordering + $change;
			$new = $new <= 0 ? 1 : $new;

			$query =  ' UPDATE '.$this->getTableName().' ';

			if ($change < 0) {
				$query .= 'SET ordering = ordering+1 WHERE '.$new.' <= ordering AND ordering < '.$old;
				$query .= ($where ? ' AND '.$where : '');
			} else {
				$query .= 'SET ordering = ordering-1 WHERE '.$old.' < ordering AND ordering <= '.$new;
				$query .= ($where ? ' AND '.$where : '');
			}

			$this->_db->setQuery( $query );
			if (!$this->_db->query())
			{
				$err = $this->_db->getErrorMsg();
				JError::raiseError( 500, $err );
				return false;
			}

			$this->ordering = $new;
			return $this->save();
		}

		return $this;
	}

	/**
	 * Uses the parameters from com_content to clean the HTML from a fieldname
	 *
	 * @param $fieldname (optional) default = description
	 * @return void
	 */
	function filterHTML( $fieldname='description' )
	{
		if ( !in_array( $fieldname, array_keys( $this->getProperties() ) ) )
		{
			$this->setError( get_class( $this ).' does not have a field named `'.$fieldname.'`' );
			return;
		}

		// Filter settings
		jimport( 'joomla.application.component.helper' );
		$config	= JComponentHelper::getParams( 'com_content' );
		$user	= &JFactory::getUser();
		$gid	= $user->get( 'gid' );

		$filterGroups	= $config->get( 'filter_groups' );

		// convert to array if one group selected
		if ( (!is_array($filterGroups) && (int) $filterGroups > 0) )
		{
			$filterGroups = array($filterGroups);
		}

		if (is_array($filterGroups) && in_array( $gid, $filterGroups ))
		{
			$filterType		= $config->get( 'filter_type' );
			$filterTags		= preg_split( '#[,\s]+#', trim( $config->get( 'filter_tags' ) ) );
			$filterAttrs	= preg_split( '#[,\s]+#', trim( $config->get( 'filter_attritbutes' ) ) );
			switch ($filterType)
			{
				case 'NH':
					$filter	= new JFilterInput();
					break;
				case 'WL':
					$filter	= new JFilterInput( $filterTags, $filterAttrs, 0, 0 );
					break;
				case 'BL':
				default:
					$filter	= new JFilterInput( $filterTags, $filterAttrs, 1, 1 );
					break;
			}
			$this->$fieldname	= $filter->clean( $this->$fieldname );
		}
			elseif (empty($filterGroups))
		{
			$filter = new JFilterInput(array(), array(), 1, 1);
			$this->$fieldname = $filter->clean( $this->$fieldname );
		}
	}
	
	/**
	 * Same as JFilterOutput::stringURLSafe, but allowing _ character
	 * 
	 * @param unknown_type $string
	 */
	public function stringDBSafe($string)
	{
		//remove any '-' from the string they will be used as concatonater
		$str = str_replace('-', ' ', $string);

		$lang =& JFactory::getLanguage();
		$str = $lang->transliterate($str);

		// remove any duplicate whitespace, and ensure all characters are alphanumeric
		$str = preg_replace(array('/\s+/','/[^A-Za-z0-9_\-]/'), array('-',''), $str);

		// lowercase and trim
		$str = trim(strtolower($str));
		return $str;
	}

	/**
     * Retrieve row field value
     *
     * @param  	string 	The user-specified column name.
     * @return 	string 	The corresponding column value.
     */
    public function __get($columnName)
    {
    	if ($columnName == 'id')
    	{
        	$columnName = $this->getKeyName();
        }
    	return $this->get($columnName);
    }

}