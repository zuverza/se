<?php
defined('_JEXEC') or die('Restricted access');
$item = @$vars->item;
$form = @$vars->form;
$values = @$vars->values;
$formName = 'adminForm_'.$item->product_id; 
JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/');
JHTML::_('script', 'tienda.js', 'media/com_tienda/js/');
Tienda::load( 'TiendaUrl', 'library.url' );
$working_image = TiendaConfig::getInstance()->get( 'dispay_working_image_product', 1);
$return = base64_encode( JUri::getInstance()->toString() );
?>

<div id="product_buy_<?php echo $item->product_id; ?>" class="product_buy">
    <div id="validationmessage_<?php echo $item->product_id; ?>"></div>
    
    <form action="<?php echo JRoute::_( 'index.php?option=com_tienda&controller=products&view=products&id='.$vars->product_id ); ?>" method="post" class="adminform" name="<?php echo $formName; ?>" enctype="multipart/form-data" >

	<?php if(@$vars->params['show_price'] == '1'): ?>
    <!--base price-->
    <span id="product_price_<?php echo $item->product_id; ?>" class="product_price">
    	<?php  echo TiendaHelperProduct::dispayPriceWithTax($item->price, $vars->tax, $vars->show_tax); ?>
       	<!-- For UE States, we should let the admin choose to show (+19% vat) and (link to the shipping rates) -->       
    	<br />
    	<?php if(TiendaConfig::getInstance()->get( 'display_prices_with_shipping') && !empty($item->product_ships)):?>
    	<?php echo TiendaUrl::popup( JRoute::_($vars->shipping_cost_link.'&tmpl=component'), JText::_( "LINK_TO_SHIPPING_COST" ) ); ?>
    	<?php endif;?> 
    </span>
    <?php endif; ?>
    
    <!--attribute options-->
    <div id='product_attributeoptions_<?php echo $item->product_id; ?>' class="product_attributeoptions">
    <?php
    $attributes = TiendaHelperProduct::getAttributes( $item->product_id );
    foreach ($attributes as $attribute)
    {
        ?>
        <div class="pao" id='productattributeoption_<?php echo $attribute->productattribute_id; ?>'>
        <?php
        echo "<span>".$attribute->productattribute_name." : </span>";
        
        $key = 'attribute_'.$attribute->productattribute_id;
        $selected = (!empty($values[$key])) ? $values[$key] : ''; 
        
        Tienda::load('TiendaSelect', 'library.select');
        $attribs = array('class' => 'inputbox', 'size' => '1','onchange'=>"tiendaUpdateAddToCart( 'product','product_buy_".$item->product_id."', document.".$formName.", ".$working_image.", '".JText::_( 'Updating Attributes' )."' );");
        echo TiendaSelect::productattributeoptions( $attribute->productattribute_id, $selected, $key, $attribs  );
    
        ?>
        </div>
        <?php
    }
    ?>
    
    <?php if (!empty($vars->onDisplayProductAttributeOptions)) : ?>
        <div class='onDisplayProductAttributeOptions_wrapper'>
        <?php echo $vars->onDisplayProductAttributeOptions; ?>
        </div>
    <?php endif; ?>
    
    </div>
    
    <?php if (!empty($vars->params['quantity_restriction'])) : ?>
    <div id='product_quantity_input_<?php echo $item->product_id; ?>' class="product_quantity_input">
        <input type="hidden" name="product_qty" value="<?php echo $vars->params['quantity_restriction']; ?>" size="5" />
    </div>
    <?php else : ?>
    <div id='product_quantity_input_<?php echo $item->product_id; ?>' class="product_quantity_input">
        <span class="title"><?php echo JText::_( "Quantity" ); ?>:</span>
        <input type="text" name="product_qty" value="1" size="5" />
    </div>
    <?php endif; ?>
    
    <!-- Add to cart button ---> 
    <div id='add_to_cart_<?php echo $item->product_id; ?>' class="add_to_cart" style="display: block;"> 
        <input type="hidden" name="product_id" value="<?php echo $item->product_id; ?>" />
        
        <?php 
        	// Custom Redirect URL
        	$uri = JURI::getInstance();
        	$url = $uri->toString( array('scheme', 'host', 'port', 'path', 'query', 'fragment'));
        ?>
        
        <input type="hidden" name="product_url" value="<?php echo $url; ?>" />
        <input type="hidden" name="filter_category" value="<?php echo @$vars->filter_category; ?>" />
        <input type="hidden" id="task" name="task" value="" />
        <input type="hidden" name="return" value="<?php echo $return; ?>" />
        <?php echo JHTML::_( 'form.token' ); ?>
        
        <?php $onclick = "tiendaFormValidation( '".JRoute::_( @$vars->validation )."', 'validationmessage_".$item->product_id."', 'addtocart', document.".$formName.", true, '".JText::_( 'Validating' )."' );"; ?>
        <?php //$onclick = "tiendaFormValidation( '".JRoute::_( @$vars->validation )."', 'validationmessage_".$item->product_id."', 'addtocart', document.".$formName." );"; ?>
        
        <?php 
        if (empty($item->product_check_inventory) || (!empty($item->product_check_inventory) && empty($vars->invalidQuantity)) ) :
            switch (TiendaConfig::getInstance()->get('cartbutton', 'image')) 
            {
                case "button":
                    ?>
                    <input onclick="<?php echo $onclick; ?>" value="<?php echo JText::_('Add to Cart'); ?>" type="button" class="button" />
                    <?php
                    break;
                case "image":
                default:
                	// Search for localized version of the image
                	Tienda::load('TiendaHelperImage', 'helpers.image');
                	$image = TiendaHelperImage::getLocalizedName("addcart.png", Tienda::getPath('images'));
                    ?> 
                    <img class='addcart' src='<?php echo Tienda::getUrl('images').$image; ?>' alt='<?php echo JText::_('Add to Cart'); ?>' onclick="<?php echo $onclick; ?>" />
                    <?php
                    break;
            }
        endif; 
        ?>
    </div>
    
    <?php if (!empty($item->product_recurs)) : ?> 
        <div id='product_recurs_<?php echo $item->product_id; ?>' class="product_recurs"> 
            <span class="title"><?php echo JText::_("THIS PRODUCTS CHARGES RECUR"); ?></span>
            <div id="product_recurs_prices_<?php echo $item->product_id; ?>" class="product_recurs_prices">
            <?php echo JText::_( "RECURRING PRICE" ); ?>: <?php echo TiendaHelperBase::currency($item->recurring_price); ?>
            (<?php echo $item->recurring_payments . " " . JText::_( "PAYMENTS" ); ?>, <?php echo $item->recurring_period_interval." ". JText::_( "$item->recurring_period_unit PERIOD UNIT" )." ".JText::_( "PERIODS" ); ?>) 
            <?php if ($item->recurring_trial) : ?>
                <br/>
                <?php echo JText::_( "TRIAL PERIOD PRICE" ); ?>: <?php echo TiendaHelperBase::currency($item->recurring_trial_price); ?>
                (<?php echo "1 " . JText::_( "PAYMENT" ); ?>, <?php echo $item->recurring_trial_period_interval." ". JText::_( "$item->recurring_trial_period_unit PERIOD UNIT" )." ".JText::_( "PERIOD" ); ?>)
            <?php endif; ?> 
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($item->product_check_inventory)) : ?>
        <?php if (TiendaConfig::getInstance()->get('display_product_quantity', '1')) : ?> 
        <div id='available_stock_<?php echo $item->product_id; ?>' class="available_stock"> 
          <?php echo JText::_("AVAILABLE_STOCK"); ?> <label id="stock_<?php echo $item->product_id; ?>"><?php echo (int) $vars->availableQuantity->quantity; ?></label> 
        </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <?php if (!empty($item->product_check_inventory) && !empty($vars->invalidQuantity) ) : ?>
        <!-- Not avilable in stock  --->  
        <div id='out_of_stock_<?php echo $item->product_id; ?>'> 
          <?php echo JText::_("OUT_OF_STOCK"); ?> 
        </div>
    <?php endif; ?>
    
    </form>
</div>
