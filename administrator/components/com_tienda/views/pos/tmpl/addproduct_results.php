<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $items = @$this->items; ?>
<?php $state = @$this->state; ?>

<div class="table">
    <div class="row">
        <div class="cell step_body">
            <h2><?php echo JText::_( "SEARCH_RESULTS" ); ?></h2>
            
            <table class="adminlist">
                <thead>
                    <tr>
                        <th style="width: 50px;">
                            <?php echo TiendaGrid::sort( 'ID', "tbl.product_id", @$state->direction, @$state->order ); ?>
                        </th>                
                        <th style="text-align: left;">
                            <?php echo TiendaGrid::sort( 'Name', "tbl.product_name", @$state->direction, @$state->order ); ?>
                        </th>
                        <th style="width: 150px;">

                        </th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <td colspan="20">
                            <?php echo @$this->pagination->getListFooter(); ?>
                        </td>
                    </tr>
                </tfoot>
                <tbody>
                <?php $i=0; $k=0; ?>
                <?php foreach (@$items as $item) : ?>
                    <tr class='row<?php echo $k; ?>'>
                        <td style="text-align: center;">
                            <?php echo $item->product_id; ?>
                        </td>   
                        <td style="text-align: left;">
                            <?php echo $item->product_name; ?>
                        </td>
                        <td style="text-align: center;">
                            <a href="index.php?option=com_tienda&view=pos&task=viewproduct&id=<?php echo $item->product_id; ?>&tmpl=component">
                                <?php echo JText::_( "ADD_PRODUCT" ) ?>
                            </a>
                        </td>
                    </tr>
                    <?php $i=$i+1; $k = (1 - $k); ?>
                    <?php endforeach; ?>
                    
                    <?php if (!count(@$items)) : ?>
                    <tr>
                        <td colspan="10" align="center">
                            <?php echo JText::_('NO ITEMS FOUND'); ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <input type="hidden" name="filter_order" value="<?php echo @$this->state->order; ?>" />
            <input type="hidden" name="filter_direction" value="<?php echo @$this->state->direction; ?>" />
            
        </div>
    </div>
</div>