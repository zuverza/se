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

class TiendaTableCountries extends TiendaTable 
{
	function TiendaTableCountries ( &$db ) 
	{
		
		$tbl_key 	= 'country_id';
		$tbl_suffix = 'countries';
		$this->set( '_suffix', $tbl_suffix );
		$name 		= 'tienda';
		
		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );	
	}
	
	function check()
	{		
		return true;
	}
	
    function reorder()
    {
        $k = $this->_tbl_key;

        $query = new TiendaQuery();
        $query->select( $this->_tbl_key );
        $query->select( 'ordering' );
        $query->from( $this->_tbl );
        $query->order( 'ordering ASC' );
        $query->order( 'country_name ASC' );

        $this->_db->setQuery( (string) $query );
        if (!($orders = $this->_db->loadObjectList()))
        {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }
        
        // correct all the ordering numbers
        for ($i=0, $n=count( $orders ); $i < $n; $i++)
        {
            if ($orders[$i]->ordering >= 0)
            {
                if ($orders[$i]->ordering != $i+1)
                {
                    $orders[$i]->ordering = $i+1;
                    
                    $query = new TiendaQuery();
                    $query->update( $this->_tbl );
                    $query->set( 'ordering = '. (int) $orders[$i]->ordering );
                    $query->where( $k .' = '. $this->_db->Quote($orders[$i]->$k) );

                    $this->_db->setQuery( (string) $query );
                    $this->_db->query();
                }
            }
        }
        return true;
    }
}
