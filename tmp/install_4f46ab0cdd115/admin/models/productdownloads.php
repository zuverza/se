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
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaModelBase', 'models._base' );

class TiendaModelProductDownloads extends TiendaModelBase
{
    protected function _buildQueryWhere( &$query )
    {
        $filter	     		 = $this->getState( 'filter' );
        $filter_user	     = $this->getState( 'filter_user' );
        $filter_product_id   = $this->getState( 'filter_product_id' );
        $filter_product_name = $this->getState( 'filter_product_name' );
        $filter_enabled  	 = $this->getState( 'filter_enabled' );
        $filter_productfile  = $this->getState( 'filter_productfile' );
        $filter_downloadable = $this->getState( 'filter_downloadable' );
      
		if ( $filter )
		{
			$key	= $this->_db->Quote( '%'.$this->_db->getEscaped( trim( strtolower( $filter ) ) ).'%' );
			$where = array();
			$where[] = 'LOWER(tbl.user_id) LIKE '.$key;
			$where[] = 'LOWER(tbl.order_id) LIKE '.$key;
			$where[] = 'LOWER(tbl.product_id) LIKE '.$key;
			$where[] = 'LOWER(tbl.productfile_id) LIKE '.$key;
			$where[] = 'LOWER(tbl.productdownload_id) LIKE '.$key;
			$where[] = 'LOWER(tbl_files.productfile_name) LIKE '.$key;
			$query->where( '('.implode( ' OR ', $where ).')' );
		} 
			
        if ( @$filter_user )
        {
            $query->where( 'tbl.user_id = '.(int) $filter_user );
        }
        
        if ( @$filter_product_id )
        {
            $query->where( 'tbl.product_id = '.(int) $filter_product_id );
        }
        
        if ( @$filter_productfile )
        {
            $query->where( 'tbl.productfile_id = '.( int ) $filter_productfile );
        }
        
        if ( strlen( $filter_product_name ) )
        {
        	$key	= $this->_db->Quote( '%'.$this->_db->getEscaped( trim( strtolower( $filter_product_name ) ) ).'%' );
            $query->where( 'tbl_files.productfile_name LIKE '.$key );
        }
        
        if ( strlen( $filter_downloadable ) )
        {
            if( $filter_downloadable )
            {
                $query->where( 'tbl.productdownload_max <> 0' );
            }
                else
            {
                $query->where( 'tbl.productdownload_max = 0' );
            }
        }
        
        if ( strlen( $filter_enabled ) )
        {
            $query->where( 'tbl_files.productfile_enabled = '.( int )$filter_enabled );
        }
        
        $query->where('tbl_products.product_enabled = 1');
        
        //TODO filter based on start-end date (not in use right now)
    }

    protected function _buildQueryJoins( &$query )
    {
        $query->join('LEFT', '#__tienda_productfiles AS tbl_files ON tbl.productfile_id = tbl_files.productfile_id');   
        $query->join('INNER', '#__tienda_products AS tbl_products ON tbl.product_id = tbl_products.product_id');   
    }
    
    protected function _buildQueryFields( &$query )
    {
        $fields = array();
        $fields[] = $this->getState( 'select', 'tbl.productdownload_id' );
        $fields[] = " tbl.productfile_id ";
        $fields[] = " tbl.productdownload_max ";
        $fields[] = " tbl.product_id ";
        $fields[] = " tbl_files.productfile_name as filename ";
        $fields[] = " tbl_products.product_name as product_name ";
        
        $query->select( $fields );
    }
}
