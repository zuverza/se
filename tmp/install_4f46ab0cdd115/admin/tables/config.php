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

class TiendaTableConfig extends TiendaTable 
{

	function TiendaTableConfig( &$db ) 
	{
		$tbl_key 	= 'config_id';
		$tbl_suffix = 'config';
		$this->set( '_suffix', $tbl_suffix );
		$name 		= "tienda";
		
		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );	
	}
	
	function store( $updateNulls = true) 
	{
		$k = 'config_id';
 
        if (intval( $this->$k) > 0 )
        {
            $ret = $this->_db->updateObject( $this->_tbl, $this, $this->_tbl_key );
        }
        else
        {
            $ret = $this->_db->insertObject( $this->_tbl, $this, $this->_tbl_key );
        }
        if( !$ret )
        {
            $this->setError(get_class( $this ).'::store failed - '.$this->_db->getErrorMsg());
            return false;
        }
        else
        {
            return true;
        }
	}
    
}