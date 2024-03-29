<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$this->state; ?>
<?php $form = @$this->form; ?>
<?php $items = @$this->items; ?>
<?php $row = @$this->row; ?>

<h1 style="margin-left: 2%; margin-top: 2%;"><?php echo JText::_( "Set Issues for" ); ?>: <?php echo $row->product_name; ?></h1>

<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">

	<?php echo TiendaGrid::pagetooltip( JRequest::getVar('view') ); ?>
	
<div class="note" style="width: 96%; margin-left: auto; margin-right: auto; margin-bottom: 20px;">
    <div style="float: left; font-size: 1.3em; font-weight: bold; height: 30px;"><?php echo JText::_('Add a New Issue'); ?></div>
    <div style="float: right;">
        <button onclick="document.getElementById('task').value='createissue'; document.adminForm.submit();"><?php echo JText::_('Create Issue'); ?></button>
    </div>
    <div class="reset"></div>
	<table class="adminlist">
    	<thead>
        	<tr>
        		<th><?php echo JText::_( "Volume Num" ); ?></th>
        		<th><?php echo JText::_( "Issue Num" ); ?></th>
        		<th><?php echo JText::_( "Publishing Date" ); ?></th>
        	</tr>
        	</thead>
        	<tbody>
        	<tr>
        		<td style="text-align: center;">
        			<input id="volume_num" name="volume_num" value="" size="5" />
           	</td>
        		<td style="text-align: center;">
        			<input id="issue_num" name="issue_num" value="" size="5" />
        		</td>
        		<td style="text-align: center;">
        			<?php echo JHTML::calendar( "", "publishing_date", "publishing_date", '%Y-%m-%d %H:%M:%S' ); ?> 
        		</td>
        	</tr>
    	</tbody>
	</table>
</div>

<div class="note_green" style="width: 96%; margin-left: auto; margin-right: auto;">
    <div style="float: left; font-size: 1.3em; font-weight: bold; height: 30px;"><?php echo JText::_('Issue List'); ?></div>
    <div style="float: right;">
        <button onclick="document.adminForm.toggle.checked=true; checkAll(<?php echo count( @$items ); ?>); document.getElementById('task').value='saveissues'; document.adminForm.submit();"><?php echo JText::_('Save All Changes'); ?></button>
    </div>
    <div class="reset"></div>
	<table class="adminlist" style="clear: both;">
		<thead>
      <tr>
        <th style="width: 20px;">
         	<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( @$items ); ?>);" />
        </th>
        <th style="text-align: center;">
					<?php echo TiendaGrid::sort( 'Volume Num', "tbl.volume_num", @$state->direction, @$state->order ); ?>
        </th>
        <th style="text-align: center;">
					<?php echo TiendaGrid::sort( 'Issue Num', "tbl.issue_num", @$state->direction, @$state->order ); ?>
        </th>
        <th style="text-align: center;">
					<?php echo TiendaGrid::sort( 'Publishing Date', "tbl.publishing_date", @$state->direction, @$state->order ); ?>
        </th>
      </tr>
		</thead>
    <tbody>
		<?php $i=0; $k=0; ?>
        <?php foreach (@$items as $item) : ?>
            <tr class='row<?php echo $k; ?>'>
				<td style="text-align: center;">
					<?php echo TiendaGrid::checkedout( $item, $i, 'product_issue_id' ); ?>
				</td>
				<td style="text-align: center;">
					<input type="text" name="volumes_num[<?php echo $item->product_issue_id; ?>]" value="<?php echo $item->volume_num; ?>" />
				</td>
				<td style="text-align: center;">
					<input type="text" name="issues_num[<?php echo $item->product_issue_id; ?>]" value="<?php echo $item->issue_num; ?>" />
				</td>
				<td style="text-align: center;">
          <?php echo JHTML::calendar( $item->publishing_date, "publishing_dates[{$item->product_issue_id}]", "publishing_dates[{$item->product_issue_id}]", '%Y-%m-%d %H:%M:%S' ); ?>
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
					<?php echo @$this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
	</table>

	<input type="hidden" name="order_change" value="0" />
	<input type="hidden" name="id" value="<?php echo $row->product_id; ?>" />
	<input type="hidden" name="task" id="task" value="setissues" />
	<input type="hidden" name="boxchecked" value="" />
	<input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
	<input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />
	
	<?php echo $this->form['validate']; ?>
</div>
</form>