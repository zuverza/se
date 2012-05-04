<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$this->state; ?>
<?php $form = @$this->form; ?>
<?php $items = @$this->items; ?>
<?php Tienda::load( 'TiendaHelperSubscription', 'helpers.subscription' ); ?>
<?php $display_subnum = TiendaConfig::getInstance()->get( 'display_subnum', 0 ); ?>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">

	<?php echo TiendaGrid::pagetooltip( JRequest::getVar('view') ); ?>
	
    <table>
        <tr>
            <td align="left" width="100%">
                <?php $link = "index.php?option=com_users&task=add"; ?>
                <?php $button = "<input type='button' class='button' value='".JText::_("Create New User")."' />"; ?>
                <?php echo TiendaUrl::popup( $link, $button, array('update' => true) ); ?>
            </td>
            <td nowrap="nowrap">
                <input name="filter" value="<?php echo @$state->filter; ?>" />
                <button onclick="this.form.submit();"><?php echo JText::_('Search'); ?></button>
                <button onclick="tiendaFormReset(this.form);"><?php echo JText::_('Reset'); ?></button>
            </td>
        </tr>
    </table>

	<table class="adminlist" style="clear: both;">
		<thead>
            <tr>
                <th style="width: 5px;">
                	<?php echo JText::_("Num"); ?>
                </th>
                <th style="width: 50px;">
                	<?php echo TiendaGrid::sort( 'ID', "tbl.id", @$state->direction, @$state->order ); ?>
                </th>                
                <?php if( $display_subnum ): ?>
                <th style="width: 70px;">
                    <?php echo TiendaGrid::sort( 'Sub Num', "ui.sub_number", @$state->direction, @$state->order ); ?>
                </th>
                <?php endif; ?>
                <th style="text-align: left;">
                	<?php echo TiendaGrid::sort( 'Name', "tbl.name", @$state->direction, @$state->order ); ?>
                </th>
                <th style="text-align: center;">
                	<?php echo TiendaGrid::sort( 'Username', "tbl.username", @$state->direction, @$state->order ); ?>
                </th>
				<th style="text-align: center;">
					<?php echo TiendaGrid::sort( 'Email', 'tbl.email', @$state->direction, @$state->order); ?>
				</th>
                <th>
                    <?php echo TiendaGrid::sort( 'Group', 'group.group_name', @$state->direction, @$state->order); ?>
                </th>
				<th>
				</th>
				<th>
				</th>
            </tr>
            <tr class="filterline">
                <th colspan="2">
                    <?php $attribs = array('class' => 'inputbox', 'size' => '1', 'onchange' => 'document.adminForm.submit();'); ?>
                    <div class="range">
                        <div class="rangeline">
                            <span class="label"><?php echo JText::_("From"); ?>:</span> <input id="filter_id_from" name="filter_id_from" value="<?php echo @$state->filter_id_from; ?>" size="5" class="input" />
                        </div>
                        <div class="rangeline">
                            <span class="label"><?php echo JText::_("To"); ?>:</span> <input id="filter_id_to" name="filter_id_to" value="<?php echo @$state->filter_id_to; ?>" size="5" class="input" />
                        </div>
                    </div>
                </th>
                <?php if( $display_subnum ): ?>
                <th>
                    <input id="filter_subnum" name="filter_subnum" value="<?php echo @$state->filter_subnum; ?>" size="10"/>
                </th>
                <?php endif; ?>
                <th style="text-align: left;">
                    <input id="filter_name" name="filter_name" value="<?php echo @$state->filter_name; ?>" size="25"/>
                </th>
                <th>
                    <input id="filter_username" name="filter_username" value="<?php echo @$state->filter_username; ?>" size="25"/>
                </th>
                <th>
                    <input id="filter_email" name="filter_email" value="<?php echo @$state->filter_email; ?>" size="25"/>
                </th>
                <th>
                    <?php echo TiendaSelect::groups(@$state->filter_group, 'filter_group', $attribs, 'filter_group', true ); ?>
                </th>
                <th>
                </th>
                <th>
                </th>
            </tr>
            <tr>
                <th colspan="20" style="font-weight: normal;">
                    <div style="float: right; padding: 5px;"><?php echo @$this->pagination->getResultsCounter(); ?></div>
                    <div style="float: left;"><?php echo @$this->pagination->getListFooter(); ?></div>
                </th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="20">
                    <div style="float: right; padding: 5px;"><?php echo @$this->pagination->getResultsCounter(); ?></div>
                    <?php echo @$this->pagination->getPagesLinks(); ?>
                </td>
            </tr>
        </tfoot>
        <tbody>
		<?php $i=0; $k=0; ?>
        <?php foreach (@$items as $item) : ?>
            <tr class='row<?php echo $k; ?>'>
				<td align="center">
					<?php echo $i + 1; ?>
				</td>
				<td style="text-align: center;">
					<a href="<?php echo $item->link; ?>">
						<?php echo $item->id; ?>
					</a>
				</td>	
        <?php if( $display_subnum ): ?>
        <td style="text-align: center;">
        	<?php echo TiendaHelperSubscription::displaySubNum( $item->sub_number ); ?>
        </td>
        <?php endif; ?>
				<td style="text-align: left;">
					<a href="<?php echo $item->link; ?>">
						<?php echo $item->name; ?>
					</a>
				</td>	
				<td style="text-align: center;">
					<?php echo $item->username; ?>
				</td>
				<td style="text-align: center;">
					<?php echo $item->email; ?>
				</td>
                <td style="text-align: center;">
                    <?php echo $item->group_name; ?>
                </td>
				<td style="text-align: center;">
					[
					<a href="<?php echo $item->link; ?>">
						<?php echo JText::_( "View Dashboard" ); ?>
					</a>
					]
				</td>
                <td style="text-align: center;">
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
	</table>

	<input type="hidden" name="order_change" value="0" />
	<input type="hidden" name="id" value="" />
	<input type="hidden" name="task" id="task" value="" />
	<input type="hidden" name="boxchecked" value="" />
	<input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
	<input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />
	
	<?php echo $this->form['validate']; ?>
</form>