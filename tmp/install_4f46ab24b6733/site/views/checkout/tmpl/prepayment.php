<?php 
	defined('_JEXEC') or die('Restricted access'); 
	JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/'); 
 	JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); 
 	JHTML::_('script', 'tienda_checkout.js', 'media/com_tienda/js/'); 
	$order = @$this->order;
	$billing_info = @$this->billing_info;
	$shipping_info = @$this->shipping_info;
	$plugin_html = @$this->plugin_html;
?>

<div class='componentheading'>
    <span><?php echo JText::_( "Review Checkout Selections and Submit Payment" ); ?></span>
</div>

	<!-- Progress Bar -->
	<?php echo $this->progress; ?>

    <!--    ORDER SUMMARY   -->
    <h3><?php echo JText::_("Order Summary") ?></h3>
    <div id="onCheckoutCart_wrapper" style="position: relative;">
	<?php
	echo @$this->orderSummary;
	?>
	</div>
    <?php if (!empty($this->onBeforeDisplayPrePayment)) : ?>
        <div id='onBeforeDisplayPrePayment_wrapper'>
        <?php echo $this->onBeforeDisplayPrePayment; ?>
        </div>
    <?php endif; ?>
	
	<?php if (!empty($this->showBilling)) { ?>
        <div id="payment_info" class="address">
            <h3><?php echo JText::_("Billing Information"); ?></h3>
            <strong><?php echo JText::_("Billing Address"); ?></strong>:<br/> 
            <?php
            if( strlen( $billing_info['company'] ) )
            	echo $billing_info['company']."<br/>";
            
            echo $billing_info['first_name']." ". $billing_info['last_name']."<br/>";
            echo $billing_info['address_1'].", ";
            echo $billing_info['address_2'] ? $billing_info['address_2'] .", " : "";
            echo $billing_info['city'] .", ";
            echo $billing_info['zone_name'] ." ";
            echo $billing_info['postal_code'] ." ";
            echo $billing_info['country_name'];
            if( strlen( $billing_info['tax_number'] ) )
            	echo "<br/>".$billing_info['tax_number'];
            ?>
        </div>
    <?php } ?>
    
    <?php if (!empty($this->showShipping)) { ?>
        <div id="shipping_info" class="address">
            <h3><?php echo JText::_("Shipping Information"); ?></h3>
            <strong><?php echo JText::_("Shipping Method"); ?></strong>: <?php echo JText::_( $this->shipping_method_name ); ?><br/>
            <strong><?php echo JText::_("Shipping Address"); ?></strong>:<br/> 
            <?php
            if( strlen( $shipping_info['company'] ) )
            	echo $shipping_info['company']."<br/>";
            
            echo $shipping_info['first_name']." ". $shipping_info['last_name']."<br/>";
            echo $shipping_info['address_1'].", ";
            echo $shipping_info['address_2'] ? $shipping_info['address_2'] .", " : "";
            echo $shipping_info['city'] .", ";
            echo $shipping_info['zone_name'] ." ";
            echo $shipping_info['postal_code'] ." ";
            echo $shipping_info['country_name'];
            if( strlen( $shipping_info['tax_number'] ) )
            	echo "<br/>".$shipping_info['tax_number'];
            ?>
        </div>
        
        <div class="reset"></div>
        
    	<?php 
    	if(!empty($this->order->customer_note))
    	{
    		?>
    		<div id="shipping_comments">
    			<h3><?php echo JText::_("Shipping Notes"); ?></h3>
    			<?php echo $this->order->customer_note; ?>
    		</div>
    	    <?php 
    	} 
    	?>
    <?php } ?>
    
    <div class="reset"></div>
    
    <?php if (!empty($this->onAfterDisplayPrePayment)) : ?>
        <div id='onAfterDisplayPrePayment_wrapper'>
        <?php echo $this->onAfterDisplayPrePayment; ?>
        </div>
    <?php endif; ?>

    <div class="reset"></div>
        
    <!--    PAYMENT METHOD   -->
    <h3><?php echo JText::_("Payment Method"); ?></h3>

	<?php echo $plugin_html; ?>
    