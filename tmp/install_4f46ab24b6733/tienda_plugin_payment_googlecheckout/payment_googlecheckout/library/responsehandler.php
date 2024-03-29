<?php 
    /** this file is a new entry point, so define _JEXEC */
    define( '_JEXEC', 1 );	

	define('TPATH_BASE', dirname(__FILE__) );
	define( 'DS', DIRECTORY_SEPARATOR );
	$pathXplode = explode(DS."plugins", TPATH_BASE);
   	
	require_once( $pathXplode[0] .DS.'index.php' );

	jimport( 'joomla.plugin.plugin');
    $plugin =& JPluginHelper::getPlugin('tienda', 'payment_googlecheckout');
   	$params = new JParameter($plugin->params);
   	
   	chdir("..");
  	require_once(dirname(__FILE__).'/googleresponse.php');
  	require_once(dirname(__FILE__).'/googlemerchantcalculations.php');
  	require_once(dirname(__FILE__).'/googleresult.php');
  	require_once(dirname(__FILE__).'/googlerequest.php');  	
  	
  	$path = JPATH_ROOT . '/cache';
  	define('RESPONSE_HANDLER_ERROR_LOG_FILE', $path.'/googleerror.log');
  	define('RESPONSE_HANDLER_LOG_FILE', $path.'/googlemessage.log');
  		
  	$server_type 	= $params->get('sandbox') ? "sandbox" : "production";  // change this to go live
	$merchant_id 	= $params->get('sandbox') ? $params->get('sandbox_merchant_id') : $params->get('merchant_id');  // Your Merchant ID
  	$merchant_key 	= $params->get('sandbox') ? $params->get('sandbox_merchant_key') : $params->get('merchant_key');  // Your Merchant Key  		
  
  	$currency = $params->get('currency');  // set to GBP if in the UK
	
	$Gresponse = new GoogleResponse($merchant_id, $merchant_key);

  	$Grequest = new GoogleRequest($merchant_id, $merchant_key, $server_type, $currency);

  	

	//$response->SetLogFiles($path . '/google_error.log', $path . '/google_message.log', L_ALL);
  	
  	//Setup the log file
 	$Gresponse->SetLogFiles(RESPONSE_HANDLER_ERROR_LOG_FILE, RESPONSE_HANDLER_LOG_FILE, L_ALL);

  	// Retrieve the XML sent in the HTTP POST request to the ResponseHandler
  	$xml_response = isset($HTTP_RAW_POST_DATA)? $HTTP_RAW_POST_DATA:file_get_contents("php://input");
  
  	if (get_magic_quotes_gpc())
  	{
    	$xml_response = stripslashes($xml_response);
  	}
 		
  	list($root, $data) = $Gresponse->GetParsedXML($xml_response);
  		
  	$Gresponse->SetMerchantAuthentication($merchant_id, $merchant_key);

  	$status = $Gresponse->HttpAuthentication();
 		
  	if(! $status) 
  	{
    	die('authentication failed');
  	}
  	
  	$orderData = _getOrder($data);
  	
   /**
	*  Commands to send the various order processing APIs
   	* Send charge order : $Grequest->SendChargeOrder($data[$root]['google-order-number']['VALUE'], <amount>);
   	* Send process order : $Grequest->SendProcessOrder($data[$root]['google-order-number']['VALUE']);
   	* Send deliver order: $Grequest->SendDeliverOrder($data[$root]['google-order-number']['VALUE'], <carrier>, <tracking-number>,
   	*    <send_mail>);
   	* Send archive order: $Grequest->SendArchiveOrder($data[$root]['google-order-number']['VALUE']);
   	*
   	*/
  	
	switch ($root) {
    	case "request-received":     	
      		break;    	
    	case "error":     	
      		break;    	
    	case "diagnosis":     	
      		break;
    	case "checkout-redirect":     	
      		break;    	
    	case "merchant-calculation-callback":     	
      		
    		// Create the results and send it
     		$merchant_calc = new GoogleMerchantCalculations($currency);

      		// Loop through the list of address ids from the callback
			$addresses = get_arr_result($data[$root]['calculate']['addresses']['anonymous-address']);
      		
      		foreach($addresses as $curr_address) 
      		{
		        $curr_id = $curr_address['id'];
		        $country = $curr_address['country-code']['VALUE'];
		        $city = $curr_address['city']['VALUE'];
		        $region = $curr_address['region']['VALUE'];
		        $postal_code = $curr_address['postal-code']['VALUE'];

		        // Loop through each shipping method if merchant-calculated shipping
		        // support is to be provided
		        if(isset($data[$root]['calculate']['shipping'])) 
		        {
		          $shipping = get_arr_result($data[$root]['calculate']['shipping']['method']);
		          foreach($shipping as $curr_ship) 
		          {
		            $name = $curr_ship['name'];
		            //Compute the price for this shipping method and address id
		            $price = $orderData->order_shipping; // Modify this to get the actual price
		            $shippable = "true"; // Modify this as required
		            $merchant_result = new GoogleResult($curr_id);
		            $merchant_result->SetShippingDetails($name, $price, $shippable);
		
		            if($data[$root]['calculate']['tax']['VALUE'] == "true") 
		            {
		              //Compute tax for this address id and shipping type
		              $amount = $orderData->order_shipping_tax; // Modify this to the actual tax value
		              $merchant_result->SetTaxDetails($amount);
		            }
					
		            //TODO: Implement the coupon merchant calculation
		            if(isset($data[$root]['calculate']['merchant-code-strings']['merchant-code-string'])) 
		            {
		            	$codes = get_arr_result($data[$root]['calculate']['merchant-code-strings']
		                  ['merchant-code-string']);
		              	foreach($codes as $curr_code) 
		              	{
		                	//Update this data as required to set whether the coupon is valid, the code and the amount
		                	$coupons = new GoogleCoupons("true", $curr_code['code'], 5, "test2");
		                	$merchant_result->AddCoupons($coupons);
		              	}
		            }
		            
		            $merchant_calc->AddResult($merchant_result);
		          }
		        } 
		        else 
		        {
		          $merchant_result = new GoogleResult($curr_id);
		          if($data[$root]['calculate']['tax']['VALUE'] == "true") 
		          {
		            //Compute tax for this address id and shipping type
		            $amount = $orderData->order_tax; // Modify this to the actual tax value
		            $merchant_result->SetTaxDetails($amount);
		          }
		          $codes = get_arr_result($data[$root]['calculate']['merchant-code-strings']
		              ['merchant-code-string']);
		          foreach($codes as $curr_code) 
		          {
		            //Update this data as required to set whether the coupon is valid, the code and the amount
		            $coupons = new GoogleCoupons("true", $curr_code['code'], 5, "test2");
		            $merchant_result->AddCoupons($coupons);
		          }
		          $merchant_calc->AddResult($merchant_result);
		        }
		      }
			$Gresponse->ProcessMerchantCalculations($merchant_calc);
      		break;
  
    	case "new-order-notification": 
      		$Gresponse->SendAck();
      		break;
   
    	case "order-state-change-notification":     	
	      	$Gresponse->SendAck();
	      	$new_financial_state = $data[$root]['new-financial-order-state']['VALUE'];
	      	$new_fulfillment_order = $data[$root]['new-fulfillment-order-state']['VALUE'];

      		switch($new_financial_state) 
      		{
		        case 'REVIEWING':
		          break;		       
		        case 'CHARGEABLE':
		          //$Grequest->SendProcessOrder($data[$root]['google-order-number']['VALUE']);
		          //$Grequest->SendChargeOrder($data[$root]['google-order-number']['VALUE'],'');
		          break;
		        
		        case 'CHARGING':
		          break;		        
		        case 'CHARGED': 
		          break;		        
		        case 'PAYMENT_DECLINED':
		          break;		        
		        case 'CANCELLED': 
		          break;		        
		        case 'CANCELLED_BY_GOOGLE': 
		          //$Grequest->SendBuyerMessage($data[$root]['google-order-number']['VALUE'],
		          //    "Sorry, your order is cancelled by Google", true);
		          break;		        
		        default:
		          break;
     		}

			switch($new_fulfillment_order) 
			{
		    	case 'NEW': 
		          break;		        
		        case 'PROCESSING': 
		          break;		        
		        case 'DELIVERED': 
		          break;		        
		        case 'WILL_NOT_DELIVER': 
		          break;		     
		        default:
		          break;
		    }
      		break;    	
    	case "charge-amount-notification":       
      		$Gresponse->SendAck();
      		break;    
    	case "chargeback-amount-notification":
      		$Gresponse->SendAck();
     		 break;  
    	case "refund-amount-notification": 
      		$Gresponse->SendAck();
      		break;    
   	 	case "risk-information-notification": 
      		$Gresponse->SendAck();
      		break;    
    	default:
      		$Gresponse->SendBadRequestStatus("Invalid or not supported Message");
      		break;
  	}
  	
   /**
    * 
	* In case the XML API contains multiple open tags
   	* with the same value, then invoke this function and
   	* perform a foreach on the resultant array.
   	* This takes care of cases when there is only one unique tag or multiple tags.
   	* Examples of this are "anonymous-address", "merchant-code-string" from the merchant-calculations-callback API
   	* 
   	*/
	function get_arr_result($child_node) 
	{
	    $result = array();
	    if(isset($child_node)) 
	    {
	      if(is_associative_array($child_node)) 
	      {
	        $result[] = $child_node;
	      }
	      else 
	      {
	        foreach($child_node as $curr_node)
	        {
	          $result[] = $curr_node;
	        }
	      }
	    }
	    return $result;
  	}

   /**
	* 
   	* Returns true if a given variable represents an associative array
   	* 
   	*/ 
	function is_associative_array( $var ) 
	{
    	return is_array( $var ) && !is_numeric( implode( '', array_keys( $var ) ) );
  	}
  	
  	function _getOrder($data)
  	{
  		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		$orderpayment = JTable::getInstance('OrderPayments', 'TiendaTable');
		$oredrPaymentId=$data['shopping-cart']['merchant-private-data']['orderPaymentId']['VALUE'];
		$orderpayment->load($oredrPaymentId);
		
		$order = JTable::getInstance('Orders', 'TiendaTable');
		$order->load( $orderpayment->order_id );
  		return $order;
  	}
?>