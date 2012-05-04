<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$this->state; ?>
<?php $form = @$this->form; ?>
<?php $items = @$this->items; ?>
<?php Tienda::load( 'TiendaHelperBase', 'helpers._base' ); ?>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">

	<table class="adminlist" style="clear: both;">
		<thead>
            <tr>
                <th style="width: 20px;">
                	<?php echo JText::_("Id"); ?>
                </th>
                <th style="text-align: left;">
                	<?php echo JText::_("Order"); ?>
                </th>
                <th>
                	<?php echo JText::_("Order Status"); ?>
                </th>
				 <th>
                	<?php echo JText::_("Do Completed Order Tasks"); ?>?
                </th>
                <th>
                	<?php echo JText::_("Notify Customer"); ?>
                </th>
                 <th>
                	<?php echo JText::_("Comments"); ?>                	
                </th>
            </tr>
		</thead>
        <tbody>
		<?php $i=0; $k=0; ?>
        <?php foreach (@$items as $item) : ?>
            <tr class='row<?php echo $k; ?>'>
				<td style="text-align: center;">
					<a href="<?php echo $item->link; ?>" target="_blank">
						<?php echo $item->order_id; ?>
					</a>
					<input type="hidden" name="cid[]" value="<?php echo $item->order_id; ?>"/>
				</td>
				<td style="text-align: left;">
				    <div>
					<a href="<?php echo $item->link; ?>" target="_blank">
						<?php echo JHTML::_('date', $item->created_date, TiendaConfig::getInstance()->get('date_format')); ?>
					</a>
					</div>
                    <?php echo $item->user_name .' [ '.$item->user_id.' ]'; ?>
                    &nbsp;&nbsp;&bull;&nbsp;&nbsp;<?php echo $item->email .' [ '.$item->user_username.' ]'; ?>
                    <br/>
                    <b><?php echo JText::_( "Ship to" ); ?></b>:
                    <?php 
                    if (empty($item->shipping_address_1)) 
                    {
                       echo JText::_( "Undefined Shipping Address" ); 
                    }
                       else
                    {
                        echo $item->shipping_address_1.", ";
                        echo $item->shipping_address_2 ? $item->shipping_address_2.", " : "";
                        echo $item->shipping_city.", ";
                        echo $item->shipping_zone_name." ";
                        echo $item->shipping_postal_code." ";
                        echo $item->shipping_country_name;
                    }
                    ?>
                    <?php 
                    if (!empty($item->order_number))
                    {
                        echo "<br/><b>".JText::_( "Order Number" )."</b>: ".$item->order_number;
                    }
                    ?>
				</td>
								
				<td style="text-align: center;">
			    <?php echo TiendaSelect::orderstate( $item->order_state_id, 'new_orderstate_id[]' ); ?>
    	    	</td>
    	    	
    	    	<td style="text-align: center;">
			    <?php if (empty($item->completed_tasks)) { 
        	      echo '<input id="completed_tasks" name="completed_tasks['.$item->order_id.']" type="checkbox" />' ; 
        	     } else {
        	     echo '<input id="completed_tasks" name="completed_tasks['.$item->order_id.']" type="checkbox" checked="checked" disabled="disabled" />' ; 
        	     }?>
    	    	</td>
    	    	
    	    	<td style="text-align: center;">
			     <?php echo '<input id="new_orderstate_notify" name="new_orderstate_notify['.$item->order_id.']" type="checkbox" />' ; ?>
    	    	</td>
    	    	
    	    <td style="text-align: center;">
			     <textarea id="new_orderstate_comments" style="width: 90%;" rows="5" name="new_orderstate_comments[<?php echo $item->order_id; ?>]" ></textarea>
			     
			</td>				
			</tr>
			<?php $i=$i+1; $k = (1 - $k); ?>
			<?php endforeach; ?>
			
			<?php if (!count(@$items)) : ?>
			<tr>
				<td colspan="10" align="center">
					<?php echo JText::_('No items found'); ?>
				</td>
			</tr>
			<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="20">
					&nbsp;
				</td>
			</tr>
		</tfoot>
	</table>

	<input type="hidden" name="task" id="task" value="" />
	
	<?php echo $this->form['validate']; ?>
</form>