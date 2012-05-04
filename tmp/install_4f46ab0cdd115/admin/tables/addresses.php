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

class TiendaTableAddresses extends TiendaTable
{
	/**
	 *
	 *
	 * @param $db
	 * @return unknown_type
	 */
	function TiendaTableAddresses ( &$db )
	{

		$tbl_key 	= 'address_id';
		$tbl_suffix = 'addresses';
		$this->set( '_suffix', $tbl_suffix );
		$name 		= 'tienda';

		parent::__construct( "#__{$name}_{$tbl_suffix}", $tbl_key, $db );
	}

	/**
	 * First stores the record
	 * Then checks if it should be the default
	 *
	 * @see tienda/admin/tables/TiendaTable#store($updateNulls)
	 */
	function store( $updateNulls=false )
	{
		if ( $return = parent::store( $updateNulls ))
		{
			if ($this->is_default_shipping == '1' || $this->is_default_billing == '1')
			{
				// update the defaults
				$query = new TiendaQuery();
				$query->update( "#__tienda_addresses" );
				$query->where( "`user_id` = '{$this->user_id}'" );
				$query->where( "`address_id` != '{$this->address_id}'" );
				if ($this->is_default_shipping == '1')
				{
					$query->set( "`is_default_shipping` = '0'" );
				}
				if ($this->is_default_billing == '1')
				{
					$query->set( "`is_default_billing` = '0'" );
				}
				$this->_db->setQuery( (string) $query );
				if (!$this->_db->query())
				{
					$this->setError( $this->_db->getErrorMsg() );
					return false;
				}
			}
		}
		return $return;
	}

	/**
	 * Checks the entry to maintain DB integrity
	 * @return unknown_type
	 */
	function check()
	{
		$config = TiendaConfig::getInstance();

		if(!$this->addresstype_id)
		{
			$this->addresstype_id = '1';
		}
		$address_type = $this->addresstype_id;

		if (empty($this->user_id))
		{
			$this->user_id = JFactory::getUser()->id;
			if (empty($this->user_id))
			{
				$this->setError( JText::_("User Required") );
				return false;
			}
		}
		if (empty($this->address_name) && ( ($config->get('validate_field_title', '3') == '3' || $config->get('validate_field_title', '3') == $address_type )) )
		{
			$this->setError( JText::_("Please include an Address Title".$address_type) );
			return false;
		}
		if (empty($this->first_name) && ( ($config->get('validate_field_name', '3') == '3' || $config->get('validate_field_name', '3') == $address_type )) )
		{
			$this->setError( JText::_("First Name Required") );
			return false;
		}
		if (empty($this->middle_name) && ( ($config->get('validate_field_middle', '3') == '3' || $config->get('validate_field_middle', '3') == $address_type )) )
		{
			$this->setError( JText::_("Middle Name Required") );
			return false;
		}
		if (empty($this->last_name) && ( ($config->get('validate_field_last', '3') == '3' || $config->get('validate_field_last', '3') == $address_type )) )
		{
			$this->setError( JText::_("Last Name Required") );
			return false;
		}
		if (empty($this->address_1) && ( ($config->get('validate_field_address1', '3') == '3' || $config->get('validate_field_address1', '3') == $address_type )) )
		{
			$this->setError( JText::_("At Least One Address Line is Required") );
			return false;
		}
		if (empty($this->address_2) && ( ($config->get('validate_field_address2', '3') == '3' || $config->get('validate_field_address2', '3') == $address_type )) )
		{
			$this->setError( JText::_("Second Address Line is Required") );
			return false;
		}
		if (empty($this->company) && ( ($config->get('validate_field_company', '3') == '3' || $config->get('validate_field_company', '3') == $address_type )) )
		{
			$this->setError( JText::_("Company Required") );
			return false;
		}

		if (empty($this->tax_number) && ( ($config->get('validate_field_tax_number', '3') == '3' || $config->get('validate_field_tax_number', '3') == $address_type )) )
		{
			$this->setError( JText::_("Company Tax Number Required") );
			return false;
		}

		if (empty($this->city) && ( ($config->get('validate_field_city', '3') == '3' || $config->get('validate_field_city', '3') == $address_type )) )
		{
			$this->setError( JText::_("City Required") );
			return false;
		}
		if (empty($this->postal_code) && ( ($config->get('validate_field_zip', '3') == '3' || $config->get('validate_field_zip', '3') == $address_type )) )
		{
			$this->setError( JText::_("Postal Code Required") );
			return false;
		}
		
		if( empty( $this->country_id ) )
		{
			if ( ($config->get('validate_field_country', '3') == '3' || $config->get('validate_field_country', '3') == $address_type ) )
			{
				$this->setError( JText::_("Country Required") );
				return false;
			}
			else
			{
				$this->country_id = 9999;
			}
		}

		$countryA = explode(',', trim($config->get('ignored_countries', '83,188,190')));
		if ( empty( $this->zone_id ) && !in_array( $this->country_id, $countryA ) )
		{
			if( ( ( $config->get('validate_field_zone', '3') == '3' || $config->get('validate_field_zone', '3') == $address_type ) ) )
			{
				$this->setError( JText::_("Zone Required") );
				return false;				
			}
			else
			{
				$this->zone_id = 9999;					
			}
		}
		
		if (empty($this->phone_1) && ( ( $config->get('validate_field_phone', '3') == '3' ) || ( $config->get('validate_field_phone', '3') == $address_type ) ) )
		{
			$this->setError( JText::_("Phone Required") );
			return false;
		}
		return true;
	}
}
