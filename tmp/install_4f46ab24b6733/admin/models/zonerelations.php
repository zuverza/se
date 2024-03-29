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

class TiendaModelZonerelations extends TiendaModelBase 
{
	protected function _buildQueryWhere(&$query)
	{
		$filter               = $this->getState('filter');
		$filter_geozoneid     = $this->getState('filter_geozoneid');
		$filter_zone          = $this->getState('filter_zone');
		$filter_geozonetype   = $this->getState('filter_geozonetype');
		$filter_countryid     = $this->getState('filter_countryid');
		
		if ($filter) 
		{
			$key	= $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter ) ) ).'%');

			$where = array();
			$where[] = 'LOWER(tbl.zonerelation_id) LIKE '.$key;
			$where[] = 'LOWER(tbl.zone_id) LIKE '.$key;
			$where[] = 'LOWER(tbl.geozone_id) LIKE '.$key;
			
			$query->where('('.implode(' OR ', $where).')');
		}
		if (strlen($filter_geozoneid))
		{
			$query->where('tbl.geozone_id = '.$this->_db->Quote($filter_geozoneid));
		}
	    if (strlen($filter_geozonetype))
        {
            $query->where('gz.geozonetype_id = '.$this->_db->Quote($filter_geozonetype));
        }
	    if (strlen($filter_zone))
        {
            $query->where('tbl.zone_id = '.$this->_db->Quote($filter_zone));
        }
	    if (strlen($filter_countryid))
        {
            $query->where('z.country_id = '.$this->_db->Quote($filter_countryid));
        }
	}
    
	protected function _buildQueryJoins(&$query)
	{
		$query->join('LEFT', '#__tienda_geozones AS gz ON gz.geozone_id = tbl.geozone_id');
		$query->join('LEFT', '#__tienda_zones AS z ON z.zone_id = tbl.zone_id');
		$query->join('LEFT', '#__tienda_countries AS c ON z.country_id = c.country_id');
	}
	
	protected function _buildQueryFields(&$query)
	{
		$field = array();
		$field[] = " z.zone_name";
		$field[] = " z.code AS zone_code ";
		$field[] = " z.country_id";
		$field[] = " c.country_name";
		$field[] = " gz.geozone_name";
		
		$query->select( $this->getState( 'select', 'tbl.*' ) );		
		$query->select( $field );
	}
	
	public function getList()
	{
		$list = parent::getList(); 
		
		// If no item in the list, return an array()
        if( empty( $list ) ){
        	return array();
        }
        
        $filter_zip    		  = $this->getState('filter_zip');
		
		foreach($list as $key => $item)
		{
			// Check the zip range
			if(strlen($filter_zip))
			{
				$in_range = false;
				$ranges = explode(";", $item->zip_range);
				if (empty($item->zip_range))
				{
				    // no zip range defined, so assume the geozone covers all of them
				    $in_range = true;
				}
				
				foreach($ranges as $range)
				{
					if(strlen($range))
					{
						$temp = explode("-", $range);
						$start = $temp[0];
						$end = $temp[1];
	
						// check that it is in range
						if($filter_zip <= $end && $filter_zip >= $start)
						{
							$in_range = true;
						}
    						elseif ($filter_zip == $start)
						{
						    $in_range = true;
						}
						
					}
				}
				
				// in not in the ranges, unset it
				if(!$in_range)
				{
					unset($list[$key]);
				}
			}
			
            $item->link = "index.php?option=com_tienda&controller=zonerelations&view=zonerelations&tmpl=component&task=edit&geozoneid=$item->geozone_id&id=$item->zonerelation_id";
		}
		return $list;
	}
}
