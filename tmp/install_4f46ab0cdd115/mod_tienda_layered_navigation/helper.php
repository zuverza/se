<?php
/**
 * @version    1.5
 * @package    Tienda
 * @author     Dioscouri Design
 * @link     http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.application.component.model' );

class modTiendaLayeredNavigationFiltersHelper extends JObject
{
	private $_db 					= null;
	private $_params 				= null;
	private $_multi_mode 			= true;		
	private $_catfound 				= false;	
	private $_link 					= 'index.php?option=com_tienda&view=products';
	private $_itemid				= null;
	private $_trackcatcount 		= 0;
	private $_products				= array();
	private $_pids					= array();
	private $_view					= '';
	private $_filter_category		= '';
	private $_filter_manufacturer_set	= '';
	private $_filter_manufacturer	= '';
	private $_filter_price_from		= '';
	private $_filter_price_to		= '';	
	private $_filter_attribute_set	= '';	
	private $_filter_option_set  	= '';
	private $_filter_rating			= '';
	private $_options				= array();	
	public $brands					= null;
	public $category_current		= null;
	
    /**
     * Sets the modules params as a property of the object
     * @param object $params     
     *  
     */
    function __construct( $params )    
    {    	
        $this->_params 		= $params;          
        $this->_db 			= JFactory::getDBO();
        $this->_multi_mode 	= $params->get('multi_mode', 1); 
    	$this->_itemid 		= JRequest::getInt('Itemid');    	
    	$this->_view 		= JRequest::getVar('view');    	
    	$this->_products = $this->getProducts(); 	
    }      
    
    /**
     * Method to get condition to know if we have available either
     * categories, manufacturers, price ranges, attributes
     * @return boolean
     */
    function getCondition()
    {
    	return $this->_catfound || count($this->_products) ? true : false;  	
    }    
    
    /**
     * 
     * Enter description here ...
     * @return unknown_type
     */
    function getTrackCatCount()
    {
    	return $this->_trackcatcount;
    }
        
    /**      
     * Method to get the categories based on the current view
     * @return array
     */
    function getCategories()
    {    	
    	$items = array();    	    	
    	//filter category found so we display child categories and products inside
    	if(!empty($this->_filter_category) && $this->_params->get('filter_category'))
    	{   	
    		//get categories with parent_id = filter_category and category_id = filter_category
    		Tienda::load( 'TiendaQuery', 'library.query' );
			$query = new TiendaQuery();
			$query->select( 'tbl.*' );					
			$query->where('tbl.parent_id = '.(int) $this->_filter_category.' OR tbl.category_id = '.(int) $this->_filter_category);
			$query->where('tbl.category_enabled = \'1\'');			
			$query->from('#__tienda_categories AS tbl'); 
			$this->_db->setQuery((string) $query);
			$items = $this->_db->loadObjectList();

			if (!empty($items))
		    {
		    	$this->_catfound = true;
		    	$catids = array();
		    	$total = 0;
		    	foreach ($items as $item)
		    	{		    	
		    		//get current category object
		    		if($item->category_id == $this->_filter_category)
		    		{		 
		    			//set the current category   			
		    			$this->category_current = $item;
		    		}
		    		else
		    		{
		    			$pmodel = JModel::getInstance('Products', 'TiendaModel');
		                $pmodel->setState('filter_category', $item->category_id);
		                //make sure that it is enabled
		                $pmodel->setState('filter_enabled', '1');
		                //make sure the product is available
		                $pmodel->setState('filter_quantity_from', '1');	 
		                //add filters from user session   
		                $pmodel->setState('filter_attribute_set', $this->_filter_attribute_set);   
		                $pmodel->setState('filter_price_from', $this->_filter_price_from); 
		                $pmodel->setState('filter_price_to', $this->_filter_price_to);
		                $pmodel->setState('filter_rating', $this->_filter_rating);	                
		                
		                if($this->_multi_mode)
		                {
		                	 $pmodel->setState('filter_manufacturer_set', $this->_filter_manufacturer_set); 
		                }
		                else
		                {
		                	 $pmodel->setState('filter_manufacturer', $this->_filter_manufacturer); 	
		                }
		                
			            $item->product_total = $pmodel->getTotal();	   	    	       
			    	   	$item->link = $this->_link.'&filter_category='.$item->category_id.'&Itemid='.$this->_itemid;	    		
			    		
			    		$total = $total + $item->product_total;
		    		}			    	  
		    	}  
		    	$this->_trackcatcount = $total;		    	
		    }
    	}

    	return $items;
    }
    
    /**
     * Method to get the ratings filter
     * return array    
     */
    function getRatings()
    {
    	$ratings = array();  
    	
    	if( $this->_view != 'products' || empty($this->_products) || !$this->_params->get('filter_rating') || !TiendaConfig::getInstance( )->get( 'product_review_enable' )) return $ratings;
    		    
	    $ratingFirst 	= 0;
	    $ratingSecond	= 0;
	    $ratingThird 	= 0;
	    $ratingFourth	= 0;
	    //loop the products to get the total of products for each rating
	    foreach($this->_products as $product)
	    {
	    	if($product->product_rating >= 1)
	    	{
	    		$ratingFirst++;
	    		
	    		if($product->product_rating >= 2)
	    		{
	    			$ratingSecond++;
	    			if($product->product_rating >= 3)
	    			{
	    				 $ratingThird++;
		    			if($product->product_rating >= 4)
		    			{
		    				 $ratingFourth++;
		    			}
	    			}
	    		}	    		
	    	}
	    }	    
    	$link = $this->_link.'&filter_category='.$this->_filter_category;	    	
    	
	    Tienda::load( 'TiendaHelperProduct', 'helpers.product' );
		$ratingFourthObj = new stdClass();
		$ratingFourthObj->rating_name = TiendaHelperProduct::getRatingImage( 4 );
		$ratingFourthObj->link = $link.'&filter_rating=4';	
		$ratingFourthObj->total = $ratingSecond;
		$ratings[] = $ratingFourthObj;	
		
		$ratingThirdObj = new stdClass();
		$ratingThirdObj->rating_name = TiendaHelperProduct::getRatingImage( 3 );
		$ratingThirdObj->link = $link.'&filter_rating=3';	
		$ratingThirdObj->total = $ratingSecond;
		$ratings[] = $ratingThirdObj;

		$ratingSecondObj = new stdClass();
		$ratingSecondObj->rating_name = TiendaHelperProduct::getRatingImage( 2 );
		$ratingSecondObj->link = $link.'&filter_rating=2';	
		$ratingSecondObj->total = $ratingSecond;
		$ratings[] = $ratingSecondObj;
		
		$ratingFirstObj = new stdClass();
		$ratingFirstObj->rating_name = TiendaHelperProduct::getRatingImage( 1 );
		$ratingFirstObj->link = $link.'&filter_rating=1';	
		$ratingFirstObj->total = $ratingFirst;
		$ratings[] = $ratingFirstObj;
	  
    	return $ratings;
    }    
    
    /**      
     * Method to get the manufacturers based on the current view
     * @return array
     */
    function getManufacturers()
    {    	
    	$brandA = array();
    	   	
    	if( $this->_view != 'products' || empty($this->_products) || !$this->_params->get('filter_manufacturer') ) return $brandA;
	    
    	$setA = explode(',', $this->_filter_manufacturer_set);
    	$pids = array();
	    foreach($this->_products as $item)
	    {    		
	    	$pids[] = $item->product_id;
	    	if(!empty($item->manufacturer_id))
	    	{
	    		$brandA[$item->manufacturer_id] = $item->manufacturer_name;
	    	}     		
	    }    
	    	$this->_pids = $pids;
    	
    	asort($brandA);
		$this->brands = $brandA;	
	    	
    	$brands = array();   
    	
    	//we need to return an empty array if in single mode we dont want to show the current brand filter to brand listing
    	if(!empty($this->_filter_manufacturer) && !$this->_multi_mode)
    	{
    		return $brands;
    	}   
    	
		if(!empty($brandA))
		{			
			foreach($brandA as $key=>$value)
			{				
				$link = $this->_link.'&filter_category='.$this->_filter_category;	
				
				if($this->_multi_mode)				
				{	
					if(in_array($key, $setA))
					{
						continue;
					}
					$link .= '&filter_manufacturer_set=';
					$link .= empty($this->_filter_manufacturer_set) ? $key : $this->_filter_manufacturer_set.','.$key;			
				}
				else 
				{
					$link .= '&filter_manufacturer='.$key;
				}
				
				$link .= '&Itemid='.$this->_itemid;
				
				$brandObj = new stdClass();
				$brandObj->manufacturer_name = $value;
				$brandObj->link = $link;	
				
				$total = 0;
				foreach($this->_products as $item)
		    	{    		
		    		if($item->manufacturer_id == $key && $item->manufacturer_name == $value) 
		    		{ 
		    			$total++;
		    		}
		    	}
				if(!$total) continue;	
				
				$brandObj->total = $total;
				$brands[] = $brandObj;			
			}
		}
		
	    return $brands;    	
    }
        
    /**      
     * Method to get the prices based on the current view
     * @return array
     */
    function getPriceRanges()
    {
    	$ranges = array();    	
    	 
    	if( $this->_view != 'products' || empty($this->_products) || !$this->_params->get('filter_price') || $this->_filter_price_from ||  $this->_filter_price_to) return $ranges;
    	
    	$link = $this->_link.'&filter_category='.$this->_filter_category;
    	$items = $this->_products;
    	
    	//get the highest price
    	$priceHigh = abs( floor($items['0']->price) );
    	
    	//automatically create price ranges
    	if( $this->_params->get('auto_price_range') )
    	{    		
    		$glueZero = '';    		       	
  			for( $i = 1; $i < strlen($priceHigh); $i++ )
        	{
        		$glueZero .= '0'; 
        	}
        	
        	$priceHigh = $this->roundToNearest($priceHigh, '1'.$glueZero, '1');
        	
        	//get if we are in 1, 10, 100, 1000, 10000,... 
    		$places =strlen($priceHigh);
        	    		
    		//only 1 product
    		if(count($items) < 2)
    		{    			
    			$rangeObj = new stdClass();	
				$rangeObj->price_from = 0;
				$rangeObj->price_to = $priceHigh;
    			$rangeObj->total = 1;		
				$rangeObj->link = $link.'&filter_price_from='.$rangeObj->price_from.'&filter_price_to='.$rangeObj->price_to.'&Itemid='.$this->_itemid;			
				$ranges[] = $rangeObj;    
				return 	$ranges;		
    		}
    		
    		//get the range
    		$range = "1{$glueZero}";  		       		
    	
			for($i = 0; $i <= (substr($priceHigh, 0, 1) - 1); $i++)
			{
				$rangeObj = new stdClass();
				$rangeObj->price_from = $i == 0 ? 0 : $range * $i;
				$rangeObj->price_to = $i == 0 ? (int) $range : ((int) $range * $i) + (int) $range;
				
				   $total_product = 0;
			    foreach($items as $item)
			    {			    	
			    	if(($item->price >= $rangeObj->price_from) && ($item->price <= $rangeObj->price_to))
			    	{
			    		$total_product++;
			    	}	
			    }		  
			      
				if(!$total_product) continue;	
			    
				$rangeObj->total = $total_product;		
				$rangeObj->link = $link.'&filter_price_from='.$rangeObj->price_from.'&filter_price_to='.$rangeObj->price_to.'&Itemid='.$this->_itemid;			
				$ranges[] = $rangeObj;		
			}  
    		
    	}
    	else
    	{    		
    		$price_range_set = $this->_params->get('price_range_set', '0:100');   		
    		
    		$setXplodes = explode('|', $price_range_set);
    		$catA = array();
    		foreach($setXplodes as $setXplode)
    		{
    			$catSet = explode(':', $setXplode);
    			if(count($catSet) == '2')
    			{
    				$catA[$catSet[0]] = $catSet[1];
    			}    			
    		}
    		
    		$increment =  array_key_exists($this->_filter_category, $catA) ? $catA[$this->_filter_category] : $catA[0]; 
			$priceHigh = $this->roundToNearest($priceHigh, $increment, '1');
			$priceLow = abs( floor($items[count($items)-1]->price) );

			$priceLow = $priceLow > $increment ? $this->roundToNearest($priceLow, $increment, '2') : 0;
					
    		//only 1 product
    		if(count($items) < 2)
    		{    			
    			$rangeObj = new stdClass();	
				$rangeObj->price_from = $priceHigh-$increment;
				$rangeObj->price_to = $priceHigh;
    			$rangeObj->total = 1;		
				$rangeObj->link = $link.'&filter_price_from='.$rangeObj->price_from.'&filter_price_to='.$rangeObj->price_to.'&Itemid='.$this->_itemid;			
				$ranges[] = $rangeObj;    
				return 	$ranges;		
    		}
    		
    		for($i = ($priceLow / $increment); $i <= ($priceHigh / $increment); $i++)
			{
				$rangeObj = new stdClass();
				$rangeObj->price_from = $i == ($priceLow / $increment) ? $priceLow : $increment * $i;
				$rangeObj->price_to = $i == ($priceLow / $increment) ? (int) $priceLow + (int) $increment : ((int) $increment * $i) + (int) $increment;
								
			    $total_product = 0;
			    foreach($items as $item)
			    {			    	
			    	if(($item->price >= $rangeObj->price_from) && ($item->price <= $rangeObj->price_to))
			    	{
			    		$total_product++;
			    	}	
			    }
			  			    
			    //if we have 0 product, we dont need to add the object so we just continue
			    if(!$total_product) continue;		    		
	           
				$rangeObj->total = $total_product;		
				$rangeObj->link = $link.'&filter_price_from='.$rangeObj->price_from.'&filter_price_to='.$rangeObj->price_to.'&Itemid='.$this->_itemid;			
				$ranges[] = $rangeObj;		
			}
    	}
          
    	return $ranges;
    }   
    
    /**
     * Method to round a number to nearest 10, 100, 1000 ...
     * @param int - $number
     * @param int - nearest
     * @param int $round - 0 => round to nearest, 1 => always round up, 2 => always round down
     * @return int
     */
    private function roundToNearest($number,$nearest=100, $round = '0')
    {
    	$number = round($number);
  
      	if($nearest>$number || $nearest <= 0)
      	{
      		return $number;
      	}
             
      	$mod = ($number%$nearest);  
      	
      	switch($round)
      	{
      		case '2':
      			$return = $number-$mod;
      			break;
      		case '1':
      			$return = $number+($nearest-$mod);
      			break;
      		case '0':
      		default:
      			$return = $mod<($nearest/2) ? $number+($nearest-$mod) : $number-$mod;
      			break;
      	}
          
      	return $return;
    }

    /**
     * Method to get the attributes with options of the current products     
     * @return array
     */
    function getAttributes()
    {
    	$finalAttributes = array();			
    	if($this->_view != 'products' || empty($this->_products) || !$this->_params->get('filter_attributes')) return $finalAttributes;
    	
    	Tienda::load( 'TiendaHelperProduct', 'helpers.product' ); 	
		
    	//check if we have pids
    	//else get the pids from $this->_products
    	if(empty($this->_pids))
    	{
	    	$pids = array();
	    	foreach($this->_products as $item)
	    	{
	    		$pids[] = $item->product_id;
	    	}
	    	$this->_pids = $pids;
    	}    
    	
    	//retun if we dont have pids
    	if(empty($this->_pids)) return $finalAttributes;
    			
    	//check if we TiendaQuery class exist
    	if(!class_exists('TiendaQuery'))
    	{
    		Tienda::load( 'TiendaQuery', 'library.query' );
    	}

		//get the attributes of the current products
	    $query = new TiendaQuery();
		$query->select( 'tbl.product_id' );	
		$query->select( 'tbl.productattribute_name' );	
		$query->select( 'tbl.productattribute_id' );	
		$query->from('#__tienda_productattributes AS tbl');  		
			
		//explode first because mysql needs the attribute ids inside a quote
		$excluded_attributes = explode( ',', $this->_params->get('excluded_attributes'));			
		$query->where( "tbl.productattribute_id NOT IN ('" . implode("', '", $excluded_attributes) . "')" );	
		$query->where( "tbl.product_id IN ('" . implode("', '", $this->_pids) . "')" );
		$this->_db->setQuery( (string) $query );
		$attributes = $this->_db->loadObjectList(); 

		//return if no available attributes
		if(empty($attributes)) return $finalAttributes;
			
		$newAttributes = array();
	    //loop to get the available options of the attribute
    	foreach($attributes as $attribute)
		{
			$options = TiendaHelperProduct::getAttributeOptionsObjects($attribute->productattribute_id);
	
			foreach($options as $option)
			{
				$option->product_id = $attribute->product_id;
				$option->attributename = $attribute->productattribute_name;
				$this->_options[$option->productattributeoption_id] = $option;
			}
						
			if(isset($newAttributes[$attribute->productattribute_name]))
			{
				$newAttributes[$attribute->productattribute_name] = array_merge($newAttributes[$attribute->productattribute_name], $options);
			}
			else
			{						
				$newAttributes[$attribute->productattribute_name] = $options;
			}			
		}
		$link = $this->_link.'&filter_category='.$this->_filter_category;

		if(empty($this->_filter_attribute_set))
		{
			$session = JFactory::getSession();		
			$cleanO = array();
			$cleanO[$this->_filter_category]= $this->_options;
			$session->set('options', $cleanO, 'tienda_layered_nav');
		}						
		
		$options_ids = !empty($this->_filter_option_set) ? explode(',', $this->_filter_option_set) :  array();		
		
		$finalAttributes = array();
		foreach($newAttributes as $key=>$options) 
		{
			foreach($options as $option) 
			{
				$addoptionset = '';				
				if(!in_array($option->productattributeoption_id, $options_ids))
				{				
					if(isset($finalAttributes[$key][$option->productattributeoption_name]))				
					{
						$addoptionset = ','.$option->productattributeoption_id;
						$finalAttributes[$key][$option->productattributeoption_name]->products[] = $option->product_id;
						$finalAttributes[$key][$option->productattributeoption_name]->attributes[] = $option->productattribute_id;
					}
					else
					{
						$finalAttributes[$key][$option->productattributeoption_name] = new stdClass();		
						$newoption_set = count($options_ids) ? $this->_filter_option_set.','.$option->productattributeoption_id : $option->productattributeoption_id;
						$finalAttributes[$key][$option->productattributeoption_name]->products = array($option->product_id);
						$finalAttributes[$key][$option->productattributeoption_name]->attributes = array($option->productattribute_id);
					}
					$finalAttributes[$key][$option->productattributeoption_name]->link = $link.'&filter_option_set='.$newoption_set.$addoptionset;
				}
			}
		}	

		return $finalAttributes;
    }  
    
    /**
     * 
     * Enter description here ...
     * @return unknown_type
     */
 	private function getProducts()
    {    	    	
    	$items = array();
    	
    	$option = JRequest::getVar('option');    	
    	if($option != 'com_tienda' && $this->_view != 'products')
    	{
    		return $items;
    	}
    	
    	$app = JFactory::getApplication(); 
    	$ns = $app->getName().'::'.'com.tienda.model.products';
    	$this->_filter_category = $app->getUserStateFromRequest($ns.'.category', 'filter_category', '0', 'int');    	    	
        $this->_filter_attribute_set = $app->getUserStateFromRequest($ns.'.attribute_set', 'filter_attribute_set', '', '');    
		$this->_filter_option_set = $app->getUserStateFromRequest($ns.'.option_set', 'filter_option_set', '', '');        
        $this->_filter_price_from = $app->getUserStateFromRequest($ns.'.price_from', 'filter_price_from', '0', 'int');
        $this->_filter_price_to = $app->getUserStateFromRequest($ns.'.price_to', 'filter_price_to', '', '');    
        $this->_filter_rating = $app->getUserStateFromRequest($ns.'.rating', 'filter_rating', '0', 'int');     
         	
    	$model = JModel::getInstance( 'Products', 'TiendaModel' );   
        $model->setState('filter_category', $this->_filter_category);  
        
    	if($this->_multi_mode)
    	{    		
    		$this->_filter_manufacturer_set = $app->getUserStateFromRequest($ns.'.manufacturer_set', 'filter_manufacturer_set', '', '');
    		$model->setState('filter_manufacturer_set',  $this->_filter_manufacturer_set);       
    	}
    	else 
    	{    		       
    		$this->_filter_manufacturer = $app->getUserStateFromRequest($ns.'.manufacturer', 'filter_manufacturer', '', 'int');
    		$model->setState('filter_manufacturer',  $this->_filter_manufacturer);       
    	}        
       
       	$model->setState('filter_attribute_set', $this->_filter_attribute_set);      
     	$model->setState('filter_price_from', $this->_filter_price_from);           
        $model->setState('filter_price_to', $this->_filter_price_to);     
        $model->setState('filter_rating', $this->_filter_rating);	
        $model->setState('filter_enabled', '1');
	    $model->setState('filter_quantity_from', '1');	
	    $model->setState( 'order', 'price' );
        $model->setState( 'direction', 'DESC' ); 
        $items = $model->getList();  
          
        return $items;
    }
    
    function getFilters()
    {
		$filters = array();
		
    	if(!empty($this->_filter_category) && !empty($this->category_current))
		{
			$catObj = new stdClass();
			$catObj->label = JText::_('Category');
			$catObj->value = $this->category_current->category_name;
			$catObj->link = $this->_link.'&filter_category=';		
			$filters[] = $catObj;
		}		
		
		if(!empty($this->_filter_price_from) || !empty($this->_filter_price_to))
		{
			$priceObj = new stdClass();
			$priceObj->label = JText::_('Price');
			$priceObj->value = TiendaHelperBase::currency($this->_filter_price_from).' - '.TiendaHelperBase::currency($this->_filter_price_to);
			$priceObj->link = $this->_link.'&filter_category='.$this->_filter_category.'&filter_price_from=0&filter_price_to=';		
			$filters[] = $priceObj;
		}
	
    	if(!empty($this->_filter_attribute_set))
		{			
			$options = explode(',', $this->_filter_option_set);	
			$session = JFactory::getSession();	
			$saveOptions = $session->get('options', array(), 'tienda_layered_nav');
	
			$trackOpts = array();
			
			$link = '';
			$newOPT = array();
			$listPAO= array();
			$listPA = array();
			foreach($saveOptions[$this->_filter_category] as $saveOption)
			{
				if(in_array($saveOption->productattributeoption_id, $options))
				{
					$listPAO[] = $saveOption->productattributeoption_id;
					$listPA[] = $saveOption->productattribute_id;
					$newOPT[$saveOption->productattributeoption_name]->istopa[] = $saveOption->productattribute_id;
					$newOPT[$saveOption->productattributeoption_name]->istopao[] = $saveOption->productattributeoption_id;
				}			
			}
	
			foreach($options as $option)
			{
				if(empty($this->_options[$option])) continue;
				
				$combination = $this->_options[$option]->attributename.'::'.$this->_options[$option]->productattributeoption_name;
				if(!in_array($combination, $trackOpts))
				{
					$trackOpts[] = $combination;
					$attriObj = new stdClass();
					$attriObj->label = $this->_options[$option]->attributename;
					$attriObj->value = $this->_options[$option]->productattributeoption_name;
					
					//create option set
					$option_set = array_diff($listPAO, $newOPT[$attriObj->value]->istopao);
					//create attribute set
					$attribute_set = array_diff($listPA, $newOPT[$attriObj->value]->istopa);
					
					$attriObj->link	 = $this->_link.'&filter_category='.$this->_filter_category.'&filter_attribute_set='.implode(',',$attribute_set).'&filter_option_set='.implode(',',$option_set);							
					
					$filters[] = $attriObj;
								
				}
			}	
		}
		
    	if($this->_filter_rating && $this->_params->get('filter_rating'))
		{			
			$ratingObj = new stdClass();
			$ratingObj->label = JText::_('Rating');
			$ratingObj->value = TiendaHelperProduct::getRatingImage( (float) $this->_filter_rating ).' '.JText::_('& Up');
			$ratingObj->link = $this->_link.'&filter_category='.$this->_filter_category.'&filter_rating=0';		
			$filters[] = $ratingObj;
		}
		
		if($this->_multi_mode)
		{
			if(!empty($this->_filter_manufacturer_set))
			{
				$brandSet = explode(',', $this->_filter_manufacturer_set);
				
				foreach($brandSet as $brand)
				{
					$brandObj = new stdClass();
					$brandObj->label = JText::_('Manufacturer');
					$brandObj->value = $this->brands[$brand];
					$brandObj->link = $this->_link.'&filter_category='.$this->_filter_category.'&filter_manufacturer_set='.implode(',',array_diff($brandSet, array($brand)));		
					$filters[] = $brandObj;					
				}
			}
		}
		else 
		{
			if(!empty($this->_filter_manufacturer))
			{
				$brandObj = new stdClass();
				$brandObj->label = JText::_('Manufacturer');
				$brandObj->value = $this->brands[$this->_filter_manufacturer];
				$brandObj->link = $this->_link.'&filter_category='.$this->_filter_category.'&filter_manufacturer=';		
				$filters[] = $brandObj;
			}
		}   	
		
    	return $filters;
    }   
}
?>   
