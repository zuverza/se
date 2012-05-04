<?php
defined('_JEXEC') or die('Restricted access');
JHTML::_('stylesheet', 'menu.css', 'media/com_tienda/css/');
JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
JHTML::_('script', 'joomla.javascript.js', 'includes/js/');
Tienda::load( 'TiendaGrid', 'library.grid' );
Tienda::load( 'TiendaHelperOrder', 'helpers.order' );
$state = @$this->state;
$order = @$this->order;
$items = @$this->orderitems;
$coupons = @$this->coupons;
$display_credits = TiendaConfig::getInstance()->get( 'display_credits', '0' );
?>
<div class="cartitems">
	<div class="adminlist">
		<div id="cartitems_header" class="floatbox">
			<span class="left50"><?php echo JText::_( "Product" ); ?></span>
			<span class="left20 center"><?php echo JText::_( "Quantity" ); ?></span>
			<span class="left30 right"><?php echo JText::_( "Total" ); ?></span>
		</div>
            <?php $i=0; $k=0; ?> 
            <?php foreach ($items as $item) : ?>
                <div class="row<?php echo $k; ?> floatbox cart_item_list">
                    <div class="left50">
                    	<div class="inner">
	                        <a href="<?php echo JRoute::_("index.php?option=com_tienda&controller=products&view=products&task=view&id=".$item->product_id); ?>">
	                            <?php echo $item->orderitem_name; ?>
	                        </a>
	                        <br/>
	                        
	                        <?php if (!empty($item->orderitem_attribute_names)) : ?>
	                            <?php echo $item->orderitem_attribute_names; ?>
	                            <br/>
	                        <?php endif; ?>
	                        
	                        <?php if (!empty($item->orderitem_sku)) : ?>
	                            <b><?php echo JText::_( "SKU" ); ?>:</b>
	                            <?php echo $item->orderitem_sku; ?>
	                            <br/>
	                        <?php endif; ?>
	
	                        <?php if ($item->orderitem_recurs) : ?>
	                            <?php $recurring_subtotal = $item->recurring_price; ?>
	                            <?php echo JText::_( "RECURRING PRICE" ); ?>: <?php echo TiendaHelperBase::currency($item->recurring_price); ?>
	                            (<?php echo $item->recurring_payments . " " . JText::_( "PAYMENTS" ); ?>, <?php echo $item->recurring_period_interval." ". JText::_( "$item->recurring_period_unit PERIOD UNIT" )." ".JText::_( "PERIODS" ); ?>) 
											            <?php if( $item->subscription_prorated ) : ?>
	                                <br/>
			                                <?php echo JText::_( "Initial Period Price" ); ?>: <?php echo TiendaHelperBase::currency($item->recurring_trial_price); ?>
			                                (<?php echo "1 " . JText::_( "PAYMENT" ); ?>, <?php echo $item->recurring_trial_period_interval." ". JText::_( "$item->recurring_trial_period_unit PERIOD UNIT" )." ".JText::_( "PERIOD" ); ?>)
											            <?php else : ?>
				                            <?php if ($item->recurring_trial) : ?>
			                                <br/>
			                                <?php echo JText::_( "TRIAL PERIOD PRICE" ); ?>: <?php echo TiendaHelperBase::currency($item->recurring_trial_price); ?>
			                                (<?php echo "1 " . JText::_( "PAYMENT" ); ?>, <?php echo $item->recurring_trial_period_interval." ". JText::_( "$item->recurring_trial_period_unit PERIOD UNIT" )." ".JText::_( "PERIOD" ); ?>)
											            <?php endif;?>
	                            <?php endif; ?>    
	                        <?php else : ?>
	                            <?php echo JText::_( "Price" ); ?>:
	                            <?php echo TiendaHelperBase::currency($item->price); ?>                         
	                        <?php endif; ?> 
	                        
						    <?php if (!empty($this->onDisplayOrderItem) && (!empty($this->onDisplayOrderItem[$i]))) : ?>
						        <div class='onDisplayOrderItem_wrapper_<?php echo $i?>'>
						        <?php echo $this->onDisplayOrderItem[$i]; ?>
						        </div>
						    <?php endif; ?>  
	
	                        <?php if( in_array($item->product_id, $coupons) ){ ?>
	                        	<span style="float: right;"><?php echo JText::_('Coupon Discount Applied!'); ?></span>
	                        <?php } ?>
                    	</div>                      
                    </div>
                    <div class="left20 center">
                        <?php echo $item->orderitem_quantity;?>  
                    </div>
                    <div class="left30 right">
                    	<div class="inner">
                    		<?php echo TiendaHelperBase::currency($item->orderitem_final_price); ?>
                    	</div>
                    </div>
                </div>
              	<div class="marginbot"></div>
            <?php ++$i; $k = (1 - $k); ?>
            <?php endforeach; ?>
            <div class="marginbot"></div>
                <div class="floatbox">
                    <span class="left50 header">
                    	<span class="inner">
                    		<?php echo JText::_( "Subtotal" ); ?>
                    	</span>
                    </span>
                    <span class="right">
                    	<span class="inner">
                    		<?php echo TiendaHelperBase::currency($order->order_subtotal); ?>
                    	</span>
                    </span>
                </div>
                
                <?php if (!empty($order->_coupons['order_price'])) : ?>
                <div class="floatbox">
                    <span class="left50 header">
                    	<span class="inner">
                    		<?php echo JText::_( "Discount" ); ?>
                    	</span>
                    </span>
                    <span class="left50 right">
                    	<span class="inner">
                    		<?php echo TiendaHelperBase::currency($order->order_discount); ?>
                    	</span>
                    </span>
                </div>
                <?php endif; ?>
        </div>
        <div class="floatbox">
        	<span class="header">
        		<span class="inner">
        			<?php echo JText::_( "Tax and Shipping Totals" ); ?>
        		</span>
            <br/>
            </span>
	            <?php 
	            	$display_shipping_tax = TiendaConfig::getInstance()->get('display_shipping_tax', '1');
	                $display_taxclass_lineitems = TiendaConfig::getInstance()->get('display_taxclass_lineitems', '0');
	                    	
		            	if ($display_taxclass_lineitems)
		                {
		                	$taxes = $order->getTaxClasses();
		                	$i = 0;
		                	$c = count( $taxes );
		                	foreach ( $taxes as $taxclass)
		                    {
		                    	$tax_desc = $taxclass->tax_rate_description ? $taxclass->tax_rate_description : 'Tax';
		                        	if ($order->getTaxClassAmount( $taxclass->tax_class_id ))
		                        	{
		                        		?>
										            <span class="left62">
										            	<span class="inner"><?php echo JText::_( $tax_desc ).":"; ?></span>
										            </span>
									              <span class="left38 right">
									                <span class="inner"><?php echo TiendaHelperBase::currency($order->getTaxClassAmount( $taxclass->tax_class_id ), $order->currency); ?></span>
									             	</span>
  	                           <?php
		                        	}
		                      $i++;
		                    }
		                }
		                else
		                	{
			                	if( $order->order_tax )
			                    {
			                    	?>
										            <span class="left62">
										            	<span class="inner">
										            	<?php
							                    	if (!empty($this->show_tax)) { echo JText::_("Product Tax Included").":"; }
							                    	elseif (!empty($this->using_default_geozone)) { echo JText::_("Product Tax Estimate").":"; } 
							                    	else { echo JText::_("Product Tax").":"; }    
										            	?>
										            	</span>
										            </span>
									              <span class="left38 right">
									                <span class="inner"><?php echo TiendaHelperBase::currency($order->order_tax) ?></span>
									             	</span>
									            <?php
			                    }
			                }
	                    ?>
                </span>
            </span>                  
            <span class="left62">
            	<span class="inner">
	            <?php 
								if (!empty($this->showShipping))
									echo JText::_("Shipping and Handling").":";
							?>
	            </span>
            </span>
               
             <span class="left38 right">
                <span class="inner">
                    <?php 
                     if (!empty($this->showShipping))
                       echo TiendaHelperBase::currency($order->order_shipping);
                    ?>
                </span>
            </span>                  
            <span class="left62">
            	<span class="inner">
            	<?php 
            		if( !empty($this->showShipping) && $display_shipping_tax && $order->order_shipping_tax )
									echo JText::_("Shipping Tax").":";
            		?>
              </span>
            </span>                  
             <span class="left38 right">
               <span class="inner">
            	<?php 
            		if( !empty($this->showShipping) && $display_shipping_tax && $order->order_shipping_tax )
         					echo TiendaHelperBase::currency( (float) $order->order_shipping_tax);
            		?>
	             </span>
            </span>                  

        </div>
        
        <?php if( $display_credits ): ?>
        <div class="marginbot"></div>
        <div class="floatbox">
        	<span class="left50 header">
        		<span class="inner">
        			 <?php echo JText::_( "Store Credit" ); ?>
        		</span>
            </span>
            <span class="left50 right">
            	<span class="inner">
            		- <?php echo TiendaHelperBase::currency($order->order_credit); ?>
            	</span>
            </span>
        </div>        
        <?php endif; ?>
        
        <div class="marginbot"></div>
        <div class="floatbox">
        	<span class="left50 header">
        		<span class="inner">
        			<?php echo JText::_( "Total" ); ?>
        		</span>
            </span>
            <span class="left50 right">
            	<span class="inner">
            		<?php echo TiendaHelperBase::currency($order->order_total); ?>
            	</span>
            </span>
        </div>
</div>
