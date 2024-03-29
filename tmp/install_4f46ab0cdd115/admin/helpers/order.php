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

Tienda::load( 'TiendaHelperBase', 'helpers._base' );
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class TiendaHelperOrder extends TiendaHelperBase
{
    /**
     * This is a wrapper method for after an orderpayment has been received 
     * that performs acts such as: 
     * enabling file downloads, removing items from cart,
     * updating product quantities, etc
     * 
     * @param $order_id
     * @return unknown_type
     */
    function setOrderPaymentReceived( $order_id )
    {
        $errors = array();
        $error = false;
        
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $order = JTable::getInstance('Orders', 'TiendaTable');
        $order->load( $order_id );
        
        if (empty($order->order_id))
        {
            // TODO we must make sure this class is always instantiated
            $this->setError( JText::_( "Invalid Order ID" ) );
            return false;
        }
        
        // optionally email the user
        $row = JTable::getInstance('OrderHistory', 'TiendaTable');
        $row->order_id = $order_id;
        $row->order_state_id = $order->order_state_id;
        $row->notify_customer = TiendaConfig::getInstance()->get( 'autonotify_onSetOrderPaymentReceived', '0');
        $row->comments = JText::_( "Payment Received" );
        if (!$row->save())
        {
            $errors[] = $row->getError();
            $error = true;
        }
        
        // Fire an onAfterSetOrderPaymentReceived event
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger( 'onAfterSetOrderPaymentReceived', array( $order_id ) );
            
        // Do orderTasks
        TiendaHelperOrder::doCompletedOrderTasks( $order_id );
                
        if ($error)
        {
            $this->setError( implode( '<br/>', $errors ) );
            return false;
        }
        return true;
    }
    
    /*
     * This would cancel an order
     * and undo everything done by setOrderPaymentReceived()
     *
     * @param $order_id
     * @return unknown_type
     */
    function cancelOrder( $order_id )
    {
        return true;    
    }
    
    /**
     * After a checkout has been completed
     * and a payment has been received (instant)
     * run this method to enable product downloads
     * 
     * @param $order_id
     * @return unknown_type
     */
    function enableProductDownloads( $order_id )
    {
    	$error = false;
        $errorMsg = "";
        
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $productsModel = JModel::getInstance( 'Products', 'TiendaModel' );
      
        $model = JModel::getInstance( 'Orders', 'TiendaModel' );
        $model->setId( $order_id );
        $order = $model->getItem();
        if ($order->orderitems)
        {
            foreach ($order->orderitems as $orderitem)
            {
            	// if this orderItem product has productfiles that are enabled and only available when product is purchased
                $model = JModel::getInstance( 'ProductFiles', 'TiendaModel' );
                $model->setState( 'filter_product', $orderitem->product_id );
                $model->setState( 'filter_enabled', 1 );
                //$model->setState( 'filter_purchaserequired', 1 ); //we still show the downloable file in the My Downloads area if the user completed the checkout
                if (!$items = $model->getList())
                {
                    continue;
                }
                
                // then create a productdownloads table object
                foreach ($items as $item)
                {
                    $productDownload = JTable::getInstance('ProductDownloads', 'TiendaTable');
                    $productDownload->product_id = $orderitem->product_id;
                    $productDownload->productfile_id = $item->productfile_id;
                    // Download Maximum Number is respective of the quantity purchased
                    $productDownload->productdownload_max = ($item->max_download) * ($orderitem->orderitem_quantity);
                    $productDownload->order_id = $order->order_id;
                    $productDownload->user_id = $order->user_id;
                    if (!$productDownload->save())
                    {
                        // track error
                        $error = true;
                        $errorMsg .= $productDownload->getError();
                        // TODO What to do with this error 
                    }
                }
            }
        }
    }
    
	/**
	 * After a checkout has been completed
	 * and a payment has been received (instant) or scheduled (offline)
	 * run this method to update product quantities for the order
	 * 
	 * @param $order_id
	 * @return unknown_type
	 */
	function updateProductQuantities( $order_id, $delta='-' )
	{
		JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
		JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
		$productsModel = JModel::getInstance( 'Products', 'TiendaModel' );
        $model = JModel::getInstance( 'Orders', 'TiendaModel' );
        $model->setId( $order_id );
        $order = $model->getItem();
        if (!empty($order->orderitems) && empty($order->quantities_updated))
        {
        	foreach ($order->orderitems as $orderitem)
        	{
            // update quantities
            // TODO Update quantity based on vendor_id
            $product = JTable::getInstance('ProductQuantities', 'TiendaTable');
            $product->load( array('product_id'=>$orderitem->product_id, 'vendor_id'=>'0', 'product_attributes'=>$orderitem->orderitem_attributes), true, false);

            $productsTable = JTable::getInstance( 'Products', 'TiendaTable' );
            $productsTable->load($orderitem->product_id);
                   
            
            // Check if it has inventory enabled
            if (!$productsTable->product_check_inventory  || empty($product->product_id))
            {
            	// do not update quantities
            	continue;
            }
            
            switch ($delta)
            {
            	case "+":
            		$new_quantity = $product->quantity + $orderitem->orderitem_quantity;
            		break;
            	case "-":
            	default:
                    $new_quantity = $product->quantity - $orderitem->orderitem_quantity;		
            		break;
            }
            
            // no product made infinite accidentally
            if ($new_quantity < 0)
            {
            	$new_quantity = 0;
            }
            $product->quantity = $new_quantity;
	 			    $product->save();

						// send mail to notify low quantity
						$config = TiendaConfig::getInstance();
						$low_stock_notify_enabled = $config->get('low_stock_notify', '0');
						$low_stock_notify_value   = $config->get('low_stock_notify_value', '0');
						
						if ( ( $low_stock_notify_enabled ) && ( $new_quantity <= ( ( int ) $low_stock_notify_value ) ) )
						{
							Tienda::load( "TiendaHelperBase", 'helpers._base' );
							$helper = TiendaHelperBase::getInstance( 'Email' );
							$helper->sendEmailLowQuanty( $product->productquantity_id );
						}
        	}
        	
        	$row = $model->getTable();
        	$row->load(array('order_id'=>$order->order_id));
        	$row->quantities_updated = 1;
        	$row->store();
        }
        
	}
	
    /**
     * Finds the prev & next items in a list of orders 
     *  
     * @param $id   product id
     * @return array( 'prev', 'next' )
     */
    function getSurrounding( $id )
    {
        $return = array();
        
        $prev = intval( JRequest::getVar( "prev" ) );
        $next = intval( JRequest::getVar( "next" ) );
        if ($prev || $next) 
        {
            $return["prev"] = $prev;
            $return["next"] = $next;
            return $return;
        }
        
        $app = JFactory::getApplication();
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'Orders', 'TiendaModel' );
        $ns = $app->getName().'::'.'com.tienda.model.'.$model->getTable()->get('_suffix');
        $state = array();
        
        $config = TiendaConfig::getInstance();
        
        $state['limit']     = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $state['limitstart'] = $app->getUserStateFromRequest($ns.'limitstart', 'limitstart', 0, 'int');
        $state['filter']    = $app->getUserStateFromRequest($ns.'.filter', 'filter', '', 'string');               
        $state['order']     = $app->getUserStateFromRequest($ns.'.filter_order', 'filter_order', 'tbl.created_date', 'cmd');
        $state['direction'] = $app->getUserStateFromRequest($ns.'.filter_direction', 'filter_direction', 'DESC', 'word');
        $state['filter_orderstate']     = $app->getUserStateFromRequest($ns.'orderstate', 'filter_orderstate', '', '');
        $state['filter_user']         = $app->getUserStateFromRequest($ns.'user', 'filter_user', '', '');
        $state['filter_userid']         = $app->getUserStateFromRequest($ns.'userid', 'filter_userid', '', '');
        $state['filter_id_from']    = $app->getUserStateFromRequest($ns.'id_from', 'filter_id_from', '', '');
        $state['filter_id_to']      = $app->getUserStateFromRequest($ns.'id_to', 'filter_id_to', '', '');
        $state['filter_date_from'] = $app->getUserStateFromRequest($ns.'date_from', 'filter_date_from', '', '');
        $state['filter_date_to'] = $app->getUserStateFromRequest($ns.'date_to', 'filter_date_to', '', '');
        $state['filter_datetype']   = $app->getUserStateFromRequest($ns.'datetype', 'filter_datetype', '', '');
        $state['filter_total_from']    = $app->getUserStateFromRequest($ns.'id_from', 'filter_total_from', '', '');
        $state['filter_total_to']      = $app->getUserStateFromRequest($ns.'id_to', 'filter_total_to', '', '');
        
        foreach (@$state as $key=>$value)
        {
            $model->setState( $key, $value );   
        }
        $rowset = $model->getList();
            
        $found = false;
        $prev_id = '';
        $next_id = '';

        for ($i=0; $i < count($rowset) && empty($found); $i++) 
        {
            $row = $rowset[$i];     
            if ($row->order_id == $id) 
            { 
                $found = true; 
                $prev_num = $i - 1;
                $next_num = $i + 1;
                if (isset($rowset[$prev_num]->order_id)) { $prev_id = $rowset[$prev_num]->order_id; }
                if (isset($rowset[$next_num]->order_id)) { $next_id = $rowset[$next_num]->order_id; }
    
            }
        }
        
        $return["prev"] = $prev_id;
        $return["next"] = $next_id; 
        return $return;
    }
    
    /**
	 * Returns a JParameter Formatted string representing the currency
	 * 
	 * @param $currency_id currency_id
	 * @return $string JParameter formatted string 
	 */
    
    function currencyToParameters($currency_id){
    	
    	if(!is_numeric($currency_id))
    		return false;
    	
    	JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
    	$model = &JModel::getInstance('Currencies', 'TiendaModel' );
    	$table = $model->getTable(); 
		
    	// Load the currency
    	if(!$table->load($currency_id))
    		return false;
    		    	
    	// Convert this into a JParameter formatted string
    	// a bit rough, but works smoothly and is extensible (works even if you add another parameter to the curremcy table
    	$currency_parameters = $table;
    	unset($table);
    	unset($currency_parameters->currency_id);
    	unset($currency_parameters->created_date);
    	unset($currency_parameters->modified_date);
    	unset($currency_parameters->currency_enabled);
    	
    	$param = new JParameter('');
    	$param->bind($currency_parameters);
    	
    	return $param->toString();
    }
    
    /**
     * This method for after an orderpayment has been received when the admin click on the 
     * that performs acts such as: 
     * enabling file downloads
     * 
     * @param $order_id
     * @return unknown_type
     */
    function doCompletedOrderTasks( $order_id )
    {
        $errors = array();
        $error = false;

        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        $order = JTable::getInstance('Orders', 'TiendaTable');
        $order->load( $order_id );
         
        if (empty($order->order_id))
        {
            	// TODO we must make sure this class is always instantiated
            $this->setError( JText::_( "Invalid Order ID" ) );
            return false;
        }
        
        // Fire an doCompletedOrderTasks event
        $dispatcher = JDispatcher::getInstance();
        $dispatcher->trigger( 'doCompletedOrderTasks', array( $order_id ) );
        
        // 0. Enable One-Time Purchase Subscriptions
        TiendaHelperOrder::enableNonRecurringSubscriptions( $order_id );            
        
        // 1. Update quantities
        TiendaHelperOrder::updateProductQuantities( $order_id, '-' );
        
        // 2. remove items from cart
        Tienda::load( 'TiendaHelperCarts', 'helpers.carts' );
        TiendaHelperCarts::removeOrderItems( $order_id );
        
        // 3. add productfiles to product downloads
        TiendaHelperOrder::enableProductDownloads( $order_id );
        
        // 4. do SQL queries
        $helper = TiendaHelperBase::getInstance( 'Sql' );
        $helper->processOrder( $order_id );
        
        // register commission if amigos is installed
        $helper = TiendaHelperBase::getInstance( 'Amigos' );
        $helper->createCommission( $order_id );
        
        // change ticket limits if billets is installed
        $helper = TiendaHelperBase::getInstance( 'Billets' );
        $helper->processOrder( $order_id );
        
        // add to JUGA Groups if JUGA installed
        $helper = TiendaHelperBase::getInstance( 'Juga' );
        $helper->processOrder( $order_id );
        
        // change core ACL if set
        $helper = TiendaHelperBase::getInstance( 'User' );
        $helper->processOrder( $order_id );

        // do Ambra Subscriptions Integration processes
        $helper = TiendaHelperBase::getInstance( 'Ambrasubs' );
        $helper->processOrder( $order_id );
        
        if ($error)
        {
            $this->setError( implode( '<br/>', $errors ) );
            return false;
        }
            else
        {
            $order->completed_tasks = '1';
            $order->store();
            return true;    
        }
    }
    
    /**
     * Gets an order, formatted for email
     * 
     * return html
     */
    function getOrderHtmlForEmail( $order_id )
    {
        $app = JFactory::getApplication();
        JPluginHelper::importPlugin( 'tienda' );

        JLoader::register( "TiendaViewOrders", JPATH_SITE."/components/com_tienda/views/orders/view.html.php" );
        
        // tells JView to load the front-end view, and enable template overrides
        $config = array();
        $config['base_path'] = JPATH_SITE."/components/com_tienda";  
        if ($app->isAdmin())
        {
            // finds the default Site template
            $db = JFactory::getDBO();
            $query = "SELECT template FROM #__templates_menu WHERE `client_id` = '0' AND `menuid` = '0';";
            $db->setQuery( $query );
            $template = $db->loadResult();
            
            jimport('joomla.filesystem.file');
            if (JFile::exists(JPATH_SITE.'/templates/'.$template.'/html/com_tienda/orders/email.php'))
            {
                // (have to do this because we load the same view from the admin-side Orders view, and conflicts arise)            
                $config['template_path'] = JPATH_SITE.'/templates/'.$template.'/html/com_tienda/orders';                
            }
        }
        $view = new TiendaViewOrders( $config );
        
        $model = Tienda::getClass("TiendaModelOrders", "models.orders");
        $model->setId( $order_id );
        $order =& $model->getItem();
        
        $view->set( '_controller', 'orders' );
        $view->set( '_view', 'orders' );
        $view->set( '_doTask', true);
        $view->set( 'hidemenu', false);
        $view->setModel( $model, true );
        $view->assign( 'order', $order );
        $view->setLayout( 'email' );
        
        // Perform the requested task
        ob_start();
        $view->display();
        $output = ob_get_contents();
        ob_end_clean();
        
        return $output;
    }
    
    /**
     * After a checkout has been completed
     * and a payment has been received (instant)
     * run this method to enable 
     * any non-recurring subscriptions that were created when the order was saved
     * 
     * @param $order_id
     * @return unknown_type
     */
    function enableNonRecurringSubscriptions( $order_id )
    {
        $error = false;
        $errorMsg = "";
        
        JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'Orders', 'TiendaModel' );
        $model->setId( $order_id );
        $model_issues = null;
        $order = $model->getItem();
        if ($order->orderitems)
        {
            foreach ($order->orderitems as $orderitem)
            {
                // if this orderItem created a subscription, enable it
                if (!empty($orderitem->orderitem_subscription))
                {
                    // these are only for one-time payments that create subscriptions
                    // recurring payment subscriptions are handled differently - by the payment plugins
                    $subscription = JTable::getInstance('Subscriptions', 'TiendaTable');
                    $subscription->load( array( 'orderitem_id'=>$orderitem->orderitem_id ) );
                    if (!empty($subscription->subscription_id))
                    {
                        $subscription->subscription_enabled = '1';
                        Tienda::load( 'TiendaHelperProduct', 'helpers.product' );
                        $product = TiendaHelperProduct::load( $subscription->product_id, true, false );
                        
                        if( $product->subscription_period_unit == 'I' ) // subscription by issue => calculate ID of the end issue (create the rest of them if they dont exist)
                        {
                        	$model_issues = JModel::getInstance( 'ProductIssues', 'TiendaModel' );
													$subscription->subscription_issue_end_id = $model_issues->getEndIssueId( $subscription->product_id, $product->subscription_period_interval );
                        }
                        if (!$subscription->save())
                        {
                            // track error
                            $error = true;
                            $errorMsg .= $subscription->getError();
                            // TODO What to do with this error 
                        }
						else
						{
					        $dispatcher = JDispatcher::getInstance();
					        $dispatcher->trigger( 'onAfterEnableSubscription', array( $subscription ) );
						}
                    }
                }
            }
        }
    }
    
    function onDisplayOrderItems($orderitems)
    {
        //trigger the onDisplayOrderItem for each orderitem
        $dispatcher =& JDispatcher::getInstance();

        $onDisplayOrderItem = array();
        $index = 0;
        foreach( $orderitems as $orderitem)
        {        	
	        ob_start();
	        $dispatcher->trigger( 'onDisplayOrderItem', array( $index, $orderitem ) );
	        $orderItemContents = ob_get_contents();		        
	        ob_end_clean();
	        if (!empty($orderItemContents))
	        {
	        	$onDisplayOrderItem[$index] = $orderItemContents;
	        }
	        $index++;
        }
        
        return $onDisplayOrderItem;
    }
		
		/*
		 * Method to display order number or order ID (in case there is no order number)
		 * 
		 * @param $order TiendaTableOrder object
		 * 
		 * @return string Order number (or order ID in case of order number is not present)
		 */
		function displayOrderNumber( $order )
		{
			return empty( $order->order_number ) ? $order->order_id : $order->order_number;
		}
}