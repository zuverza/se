<?php
/**
 * @version		$Id: query.php 12628 2009-08-13 13:20:46Z erdsiger $
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Query Element Class.
 *
 * @package		Joomla.Framework
 * @subpackage	Database
 * @since		1.6
 */
class TiendaQueryElement extends JObject
{
	/** @var string The name of the element */
	protected $_name = null;
	
	/** @var array An array of elements */
	public $_elements = null;
	
	/** @var string Glue piece */
	protected $_glue = null;

	/**
	 * Constructor.
	 * 
	 * @param	string	The name of the element.
	 * @param	mixed	String or array.
	 * @param	string	The glue for elements.
	 */
	public function __construct($name, $elements, $glue=',')
	{
		$this->_elements	= array();
		$this->_name		= $name;		
		$this->_glue		= $glue;
		
		$this->append($elements);
	}
	
	public function __toString()
	{
		return PHP_EOL.$this->_name.' '.implode($this->_glue, $this->_elements);
	}
	
	/**
	 * Appends element parts to the internal list.
	 * 
	 * @param	mixed	String or array.
	 */
	public function append($elements)
	{
		if (is_array($elements)) {
			$this->_elements = array_unique(array_merge($this->_elements, $elements));
		} else {
			$this->_elements = array_unique(array_merge($this->_elements, array($elements)));
		}
	}
}

/**
 * Query Building Class.
 *
 * @package		Joomla.Framework
 * @subpackage	Database
 * @since		1.6
 */
class TiendaQuery extends JObject
{
	/** @var string The query type */
	public $_type = '';
	
	/** @var object The select element */
	public $_select = null;

  /** @var object The delete element */
  public $_delete = null;
	
  /** @var object The update element */
  public $_update = null;
    
  /** @var object The insert element */
  public $_insert = null;
        
	/** @var object The from element */
	public $_from = null;
	
	/** @var object The join element */
	public $_join = null;

  /** @var object The set element */
  public $_set = null;
	
	/** @var object The where element */
	public $_where = null;
	
	/** @var object The group element */
	public $_group = null;
	
	/** @var object The having element */
	public $_having = null;
	
	/** @var object The order element */
	public  $_order = null;

	/**
	 * @param	mixed	A string or an array of field names
	 */
	public function select($columns)
	{
		$this->_type = 'select';
		if (is_null($this->_select)) {
			$this->_select = new TiendaQueryElement('SELECT', $columns);
		} else {
			$this->_select->append($columns);
		}

		return $this;
	}
	
    /**
     * @param   mixed   A string or an array of field names
     */
    public function delete()
    {
        $this->_type = 'delete';
        $this->_delete = new TiendaQueryElement('DELETE', array(), '');
        return $this;
    }
    
    /**
     * @param   mixed   A string or array of table names
     */
    public function insert($tables)
    {
        $this->_type = 'insert';
        $this->_insert = new TiendaQueryElement('INSERT INTO', $tables);
        return $this;
    }
    
    /**
     * @param   mixed   A string or array of table names
     */    
    public function update($tables)
    {
        $this->_type = 'update';
        $this->_update = new TiendaQueryElement('UPDATE', $tables);
        return $this;
    }
    
	/**
	 * @param	mixed	A string or array of table names
	 */
	public function from($tables)
	{
		if (is_null($this->_from)) {
			$this->_from = new TiendaQueryElement('FROM', $tables);
		} else {
			$this->_from->append($tables);
		}

		return $this;
	}

	/**
	 * @param	string
	 * @param	string
	 */
	public function join($type, $conditions)
	{
		if (is_null($this->_join)) {
			$this->_join = array();
		}
		$this->_join[] = new TiendaQueryElement(strtoupper($type) . ' JOIN', $conditions);

		return $this;
	}

	/**
	 * @param	string
	 */
	public function &innerJoin($conditions)
	{
		$this->join('INNER', $conditions);

		return $this;
	}

	/**
	 * @param	string
	 */
	public function &outerJoin($conditions)
	{
		$this->join('OUTER', $conditions);

		return $this;
	}

	/**
	 * @param	string
	 */
	public function &leftJoin($conditions)
	{
		$this->join('LEFT', $conditions);

		return $this;
	}

	/**
	 * @param	string
	 */
	public function &rightJoin($conditions)
	{
		$this->join('RIGHT', $conditions);

		return $this;
	}
	
    /**
     * @param   mixed   A string or array of conditions
     * @param   string
     */
    public function set($conditions, $glue=',')
    {
        if (is_null($this->_set)) {
            $glue = strtoupper($glue);
            $this->_set = new TiendaQueryElement('SET', $conditions, "\n\t$glue ");
        } else {
            $this->_set->append($conditions);
        }

        return $this;
    }

	/**
	 * @param	mixed	A string or array of where conditions
	 * @param	string
	 */
	public function where($conditions, $glue='AND')
	{
		if (is_null($this->_where)) {
			$glue = strtoupper($glue);
			$this->_where = new TiendaQueryElement('WHERE', $conditions, "\n\t$glue ");
		} else {
			$this->_where->append($conditions);
		}

		return $this;
	}

	/**
	 * @param	mixed	A string or array of ordering columns
	 */
	public function group($columns)
	{
		if (is_null($this->_group)) {
			$this->_group = new TiendaQueryElement('GROUP BY', $columns);
		} else {
			$this->_group->append($columns);
		}

		return $this;
	}

	/**
	 * @param	mixed	A string or array of columns
     * @param   string
	 */
	public function having($conditions, $glue='AND')
	{
		if (is_null($this->_having)) {
			$glue = strtoupper($glue);
			$this->_having = new TiendaQueryElement('HAVING', $conditions, "\n\t$glue ");
		} else {
			$this->_having->append($conditions);
		}

		return $this;
	}

	/**
	 * @param	mixed	A string or array of ordering columns
	 */
	public function order($columns)
	{
		if (is_null($this->_order)) {
			$this->_order = new TiendaQueryElement('ORDER BY', $columns);
		} else {
			$this->_order->append($columns);
		}

		return $this;
	}

	/**
	 * @return	string	The completed query
	 */
	public function __toString()
	{
		$query = '';

		switch ($this->_type)
		{
			case 'select':
				$query .= (string) $this->_select;
				$query .= (string) $this->_from;
				if ($this->_join) {
					// special case for joins
					foreach ($this->_join as $join) {
						$query .= (string) $join;
					}
				}
				if ($this->_where) {
					$query .= (string) $this->_where;
				}
				if ($this->_group) {
					$query .= (string) $this->_group;
				}
				if ($this->_having) {
					$query .= (string) $this->_having;
				}
				if ($this->_order) {
					$query .= (string) $this->_order;
				}
				break;
            case 'delete':
                $query .= (string) $this->_delete;
                $query .= (string) $this->_from;
                if ($this->_where) {
                    $query .= (string) $this->_where;
                }
                break;
            case 'update':
                $query .= (string) $this->_update;
                $query .= (string) $this->_set;
                if ($this->_where) {
                    $query .= (string) $this->_where;
                }
                break;
            case 'insert':
                $query .= (string) $this->_insert;
                $query .= (string) $this->_set;
                if ($this->_where) {
                    $query .= (string) $this->_where;
                }
                break;
		}

		return $query;
	}
}