<?php
/**
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

if ( !class_exists('Tienda') ) 
    JLoader::register( "Tienda", JPATH_ADMINISTRATOR.DS."components".DS."com_tienda".DS."defines.php" );

Tienda::load( "TiendaHelperBase", 'helpers._base' );

class TiendaHelperCurrency extends TiendaHelperBase 
{
    static $currencies = array();
    static $codes = array();
    
    /**
     * Format and convert a number according to currency rules
     * 
     * @param unknown_type $amount
     * @param unknown_type $currency
     * @return unknown_type
     */
    function _($amount, $currency='', $options='')
    {
        $currencies =& $this->currencies;
        $codes =& $this->codes;
        
        // default to whatever is in config
        $config = TiendaConfig::getInstance();
        $options = (array) $options;

        $default_currencyid = $config->get('default_currencyid', '1');
        $num_decimals = isset($options['num_decimals']) ? $options['num_decimals'] : $config->get('currency_num_decimals', '2');
        $thousands = isset($options['thousands']) ? $options['thousands'] : $config->get('currency_thousands', ',');
        $decimal = isset($options['decimal']) ? $options['decimal'] : $config->get('currency_decimal', '.');
        $pre = isset($options['pre']) ? $options['pre'] : $config->get('currency_symbol_pre', '$');
        $post = isset($options['post']) ? $options['post'] : $config->get('currency_symbol_post', '');

        // Now check the session variable to see if there is a currency setting there
        $session_currency = TiendaHelperBase::getSessionVariable( 'currency_id', 0 );
        if( $session_currency )
        {
            // Let the code below deal with currency loading
            $currency = $session_currency;
        }   
            
        // if currency is an object, use it's properties
        if (is_object($currency))
        {
            $table = $currency;
            $num_decimals = $table->currency_decimals;
            $thousands  = $table->thousands_separator;
            $decimal    = $table->decimal_separator;
            $pre        = $table->symbol_left;
            $post       = $table->symbol_right;
            if ($default_currencyid != $table->currency_id)
            {
                $convertTo = $table->currency_code;
            }
        }
        elseif (!empty($currency) && is_numeric($currency))
        {
            if (!isset($currencies[$currency]))
            {
                // if currency is an integer, load the object for its id
                JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
                $currencies[$currency] = JTable::getInstance('Currencies', 'TiendaTable');
                $currencies[$currency]->load( (int) $currency );               
            }
            $table = $currencies[$currency];
            
            if (!empty($table->currency_id))
            {
                $num_decimals = $table->currency_decimals;
                $thousands  = $table->thousands_separator;
                $decimal    = $table->decimal_separator;
                $pre        = $table->symbol_left;
                $post       = $table->symbol_right;
                
                if ($default_currencyid != $currency)
                {
                    $convertTo = $table->currency_code;
                }
            }
        }
        elseif (!empty($currency))
        {
            if (!isset($codes[$currency]))
            {
                // if currency is a string (currency_code) load the object for its code
                JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
                $codes[$currency] = JTable::getInstance('Currencies', 'TiendaTable');
                $keynames = array();
                $keynames['currency_code'] = (string) $currency;
                $codes[$currency]->load( $keynames );
            }
            $table = $codes[$currency];
            
            if (!empty($table->currency_id))
            {
                $num_decimals = $table->currency_decimals;
                $thousands  = $table->thousands_separator;
                $decimal    = $table->decimal_separator;
                $pre        = $table->symbol_left;
                $post       = $table->symbol_right;
                
                if ($default_currencyid != $table->currency_id)
                {
                    $convertTo = $table->currency_code;
                }
            }
        }

        // if the currency code we're using is diff from the store-wide currency, then we need to convert the amount
        if (!empty($convertTo))
        {
            if (!isset($currencies[$default_currencyid]))
            {
                // if currency is an integer, load the object for its id
                JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
                $currencies[$default_currencyid] = JTable::getInstance('Currencies', 'TiendaTable');
                $currencies[$default_currencyid]->load( (int) $default_currencyid );               
            }
            $table = $currencies[$default_currencyid];

            if (isset($this) && is_a( $this, 'TiendaHelperCurrency' )) 
            {
                $helper =& $this;
            } 
                else 
            {
                $helper =& TiendaHelperBase::getInstance( 'Currency' );
            }
            $amount = $helper->convert($table->currency_code, $convertTo, $amount);
        }
        
        $return = $pre.number_format($amount, $num_decimals, $decimal, $thousands).$post;
        
        return $return;
    }
    
    /**
     * Converts an amount from one currency to another
     * 
     * @param float $amount
     * @param str $currencyFrom
     * @param str $currencyTo
     * @return boolean
     */
    function convert( $currencyFrom, $currencyTo='USD', $amount='1', $refresh=false )
    {
        static $rates;
        
        if (!is_array($rates))
        {
            $rates = array();
        }
        
        if (empty($rates[$currencyFrom]) || !is_array($rates[$currencyFrom]))
        {
            $rates[$currencyFrom] = array();
        }
        
        if (empty($rates[$currencyFrom][$currencyTo]))
        {
            if (isset($this) && is_a( $this, 'TiendaHelperCurrency' )) 
            {
                $helper =& $this;
            } 
                else 
            {
                $helper =& TiendaHelperBase::getInstance( 'Currency' );
            }
            // get the exchange rate, and let the getexchange rate method handle refreshing the cache
            $rates[$currencyFrom][$currencyTo] = $helper->getExchangeRate( $currencyFrom, $currencyTo, $refresh );
        }
        $exchange_rate = $rates[$currencyFrom][$currencyTo];
        
        // convert the amount        
        $return = $amount * $exchange_rate;
        return $return;
    }
    
    /**
     * Gets the exchange rate 
     * 
     * @param float $amount
     * @param str $currencyFrom
     * @param str $currencyTo
     * @return boolean
     */
    function getExchangeRate( $currencyFrom, $currencyTo='USD', $refresh=false )
    {
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        
        $date = JFactory::getDate();
        $now = $date->toMySql();
        $database = JFactory::getDBO();
        $database->setQuery( "SELECT DATE_SUB( '$now', INTERVAL 1 HOUR )" );
        $expire_datetime = $database->loadResult();

        if ($currencyTo == 'USD')
        {
            if (empty($this->codes[$currencyFrom]))
            {
                // get from DB table
                $this->codes[$currencyFrom] = JTable::getInstance('Currencies', 'TiendaTable');
                $this->codes[$currencyFrom]->load( array('currency_code'=>$currencyFrom) );                
            }
            $tableFrom =& $this->codes[$currencyFrom];
            
            if (!empty($tableFrom->currency_id))
            {
            	// Auto Update Enabled?
            	if(TiendaConfig::getInstance()->get('currency_exchange_autoupdate', 1))
            	{
	                // refresh if it's too old or refresh forced
	                if ($tableFrom->updated_date < $expire_datetime || $refresh)
	                {
	                    if ($currencyFrom == "USD")
	                    {
	                        $tableFrom->exchange_rate = (float) 1.0;
	                    }
	                        else
	                    {
	                        $tableFrom->exchange_rate = $this->getExchangeRateYahoo( $currencyFrom, $currencyTo );    
	                    }
	                    $tableFrom->updated_date = $now;
	                    $tableFrom->save();
	                }
            	}
               	return (float) $tableFrom->exchange_rate * 1.0;
            	
            }
                else
            {
                // invalid currency, fail
                JError::raiseError('1', JText::_("Invalid Currency Type"));
                return;                
            }
        }
        
        // Auto Update Enabled?
        if(TiendaConfig::getInstance()->get('currency_exchange_autoupdate', 1))
        {
        	$exchange_rate = $this->getExchangeRateYahoo( $currencyFrom, $currencyTo );
        }
        else
        {
            if (empty($this->codes[$currencyFrom]))
            {
                // get from DB table
                $this->codes[$currencyFrom] = JTable::getInstance('Currencies', 'TiendaTable');
                $this->codes[$currencyFrom]->load( array('currency_code'=>$currencyFrom) );
            }
            $tableFrom =& $this->codes[$currencyFrom];

            if (empty($this->codes[$currencyTo]))
            {
                // get from DB table
                $this->codes[$currencyTo] = JTable::getInstance('Currencies', 'TiendaTable');
                $this->codes[$currencyTo]->load( array('currency_code'=>$currencyTo) );
            }
            $tableTo =& $this->codes[$currencyFrom];
            
            if(!empty($tableFrom->currency_id) && !empty($tableTo->currency_id))
            {
            	// Get the exchange rate manually
            	// All Values are USD based, so if (1$ = 1,3�) and (1$ = 1,6�), we have that (1� = 1,23�)
            	// so if we want to the exchange rate � => � is 1,23            	
            	$exchange_rate = $tableFrom->exchange_rate / $tableTo->exchange_rate;
            }
       		else
            {
                // invalid currency, fail
                JError::raiseError('1', JText::_("Invalid Currency Type"));
                return;                
            }
        }
             
        return (float) $exchange_rate * 1.0;
    }

   /**
     * Gets the exchange rate 
     * 
     * @param float $amount
     * @param str $currencyFrom
     * @param str $currencyTo
     * @return boolean
     */
    function getExchangeRateYahoo( $currencyFrom, $currencyTo='USD' )
    {
        static $has_run;
        
        // if refresh = true 
        // query yahoo for exchange rate
        if (!empty($has_run)) { sleep(1); }
        
        $url    = "http://quote.yahoo.com/d/quotes.csv?s={$currencyFrom}{$currencyTo}=X&f=l1&e=.csv";
        
        $handle = @fopen($url, 'r');
        
        if ($handle) {
            $result = fgets($handle, 4096);
            fclose($handle);
        }
        
        $rate = (float) $result * 1.0;
        
        $has_run = true;
        
        return $rate;
    }
    
    /**
     * Format a number according to currency rules
     * 
     * @param unknown_type $amount
     * @param unknown_type $currency
     * @return unknown_type
     */
    function format($amount, $currency='', $options='')
    {
        $currencies =& $this->currencies;
        $codes =& $this->codes;
        
        // default to whatever is in config
        $config = TiendaConfig::getInstance();
        $options = (array) $options;
            
        $num_decimals = isset($options['num_decimals']) ? $options['num_decimals'] : $config->get('currency_num_decimals', '2');
        $thousands = isset($options['thousands']) ? $options['thousands'] : $config->get('currency_thousands', ',');
        $decimal = isset($options['decimal']) ? $options['decimal'] : $config->get('currency_decimal', '.');
        $pre = isset($options['pre']) ? $options['pre'] : $config->get('currency_symbol_pre', '$');
        $post = isset($options['post']) ? $options['post'] : $config->get('currency_symbol_post', '');

        // Now check the session variable to see if there is a currency setting there
        $session_currency = TiendaHelperBase::getSessionVariable( 'currency_id', 0 );
        if( $session_currency )
        {
            // Let the code below deal with currency loading
            $currency = $session_currency;
        }   
            
        // if currency is an object, use it's properties
        if (is_object($currency))
        {
            $table = $currency;
            $num_decimals = $table->currency_decimals;
            $thousands  = $table->thousands_separator;
            $decimal    = $table->decimal_separator;
            $pre        = $table->symbol_left;
            $post       = $table->symbol_right;
        }
        elseif (!empty($currency) && is_numeric($currency))
        {
            if (!isset($currencies[$currency]))
            {
                // if currency is an integer, load the object for its id
                JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
                $currencies[$currency] = JTable::getInstance('Currencies', 'TiendaTable');
                $currencies[$currency]->load( (int) $currency );               
            }
            $table = $currencies[$currency];

            if (!empty($table->currency_id))
            {
                $num_decimals = $table->currency_decimals;
                $thousands  = $table->thousands_separator;
                $decimal    = $table->decimal_separator;
                $pre        = $table->symbol_left;
                $post       = $table->symbol_right;
            }
        }
        elseif (!empty($currency))
        {
            if (!isset($codes[$currency]))
            {
                // if currency is a string (currency_code) load the object for its code
                JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
                $codes[$currency] = JTable::getInstance('Currencies', 'TiendaTable');
                $keynames = array();
                $keynames['currency_code'] = (string) $currency;
                $codes[$currency]->load( $keynames );
            }
            $table = $codes[$currency];

            if (!empty($table->currency_id))
            {
                $num_decimals = $table->currency_decimals;
                $thousands  = $table->thousands_separator;
                $decimal    = $table->decimal_separator;
                $pre        = $table->symbol_left;
                $post       = $table->symbol_right;
            }
        }

        $return = $pre.number_format($amount, $num_decimals, $decimal, $thousands).$post;
        return $return;
    }
    
    /**
     * Loads a currency by its ID 
     * and stores it for later use by the application
     * 
     * @param unknown_type $id
     * @return unknown_type
     */
    function load( $id )
    {
        if (empty($this->currencies[$id]))
        {
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
            $this->currencies[$id] = JTable::getInstance('Currencies', 'TiendaTable');
            $this->currencies[$id]->load( $id );            
        }
        return $this->currencies[$id];
    }
}