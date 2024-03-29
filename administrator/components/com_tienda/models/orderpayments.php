<?php
/**
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaModelBase', 'models._base' );

class TiendaModelOrderPayments extends TiendaModelBase 
{
    protected function _buildQueryWhere(&$query)
    {
       	$filter             = $this->getState('filter');
       	$filter_orderid     = $this->getState('filter_orderid');
        $filter_id_from = $this->getState('filter_id_from');
        $filter_id_to   = $this->getState('filter_id_to');
        $filter_user    = $this->getState('filter_user');
        $filter_userid    = $this->getState('filter_userid');
        $filter_date_from   = $this->getState('filter_date_from');
        $filter_date_to     = $this->getState('filter_date_to');
        $filter_datetype    = $this->getState('filter_datetype');
        $filter_total_from = $this->getState('filter_total_from');
        $filter_total_to   = $this->getState('filter_total_to');
        $filter_type    = $this->getState('filter_type');
        $filter_transaction    = $this->getState('filter_transaction');
        
       	if ($filter) 
       	{
			$key	= $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter ) ) ).'%');
			$where = array();
			$where[] = 'LOWER(tbl.orderpayment_id) LIKE '.$key;
            $where[] = 'LOWER(ui.first_name) LIKE '.$key;
            $where[] = 'LOWER(ui.last_name) LIKE '.$key;
            $where[] = 'LOWER(u.email) LIKE '.$key;
            $where[] = 'LOWER(u.username) LIKE '.$key;
            $where[] = 'LOWER(u.name) LIKE '.$key;
			$query->where('('.implode(' OR ', $where).')');
       	}
       	
        if ($filter_orderid)
        {
            $query->where('tbl.order_id = '.$this->_db->Quote($filter_orderid));
        }
        
            if (strlen($filter_id_from))
        {
            if (strlen($filter_id_to))
            {
                $query->where('tbl.orderpayment_id >= '.(int) $filter_id_from);
            }
                else
            {
                $query->where('tbl.orderpayment_id = '.(int) $filter_id_from);
            }
        }
        if (strlen($filter_id_to))
        {
            $query->where('tbl.orderpayment_id <= '.(int) $filter_id_to);
        }
        
        if (strlen($filter_user))
        {
            $key    = $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter_user ) ) ).'%');

            $where = array();
            $where[] = 'LOWER(ui.first_name) LIKE '.$key;
            $where[] = 'LOWER(ui.last_name) LIKE '.$key;
            $where[] = 'LOWER(u.email) LIKE '.$key;
            $where[] = 'LOWER(u.username) LIKE '.$key;
            $where[] = 'LOWER(u.name) LIKE '.$key;
            $where[] = 'LOWER(u.id) LIKE '.$key;
            $query->where('('.implode(' OR ', $where).')');
        }
        
        if (strlen($filter_userid))
        {
            $query->where('u.id = '.$this->_db->Quote($filter_userid));
        }

        if (strlen($filter_type))
        {
            $query->where('tbl.orderpayment_type LIKE '.$this->_db->Quote( '%'.$filter_type.'%' ));
        }

        if (strlen($filter_transaction))
        {
            $query->where('tbl.transaction_id LIKE '.$this->_db->Quote( '%'.$filter_transaction.'%' ));
        }

        if (strlen($filter_date_from))
        {
            switch ($filter_datetype)
            {
                case "created":
                default:
                    $query->where("tbl.created_date >= '".$filter_date_from."'");
                  break;
            }
        }
        if (strlen($filter_date_to))
        {
            switch ($filter_datetype)
            {
                case "created":
                default:
                    $query->where("tbl.created_date <= '".$filter_date_to."'");
                  break;
            }
        }
        
        if (strlen($filter_total_from))
        {
            if (strlen($filter_total_to))
            {
                $query->where('tbl.orderpayment_amount >= '.(int) $filter_total_from);
            }
                else
            {
                $query->where('tbl.orderpayment_amount = '.(int) $filter_total_from);
            }
        }
        if (strlen($filter_total_to))
        {
            $query->where('tbl.orderpayment_amount <= '.(int) $filter_total_to);
        }
    }

    protected function _buildQueryJoins(&$query)
    {
        $query->join('LEFT', '#__tienda_orders AS o ON tbl.order_id = o.order_id');
        $query->join('LEFT', '#__users AS u ON u.id = o.user_id');
        $query->join('LEFT', '#__tienda_userinfo AS ui ON ui.user_id = o.user_id');
    }
    
    protected function _buildQueryFields(&$query)
    {
        $field = array();

        $field[] = " tbl.* ";
        $field[] = " u.name AS user_name ";
        $field[] = " u.username AS user_username "; 
        $field[] = " u.email ";
        $field[] = " ui.first_name as first_name";
        $field[] = " ui.last_name as last_name";
        $field[] = " ui.email as userinfo_email";
        $field[] = " o.user_id ";
        $query->select( $field );
    }

    public function getList()
    {
        Tienda::load( 'TiendaHelperBase', 'helpers._base' );
        $list = parent::getList();
        
        // If no item in the list, return an array()
        if( empty( $list ) ){
            return array();
        }
        
        foreach ($list as $item)
        {
            $item->link = 'index.php?option=com_tienda&view=orderpayments&task=edit&id='.$item->orderpayment_id;
        }
        
        return $list;
    }
}