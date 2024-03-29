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

Tienda::load( 'TiendaSelect', 'library.select' );
?>

<div id="productSearch">
    <form action="<?php echo JRoute::_( 'index.php', false); ?>" method="post" name="productSearch" onSubmit="if(this.elements['filter'].value == '<?php echo JText::_( 'SKU, Model # or Keyword' ); ?>') this.elements['filter'].value = '';">
        <?php echo JText::_('Search').': '; ?>
        <?php if ($category_filter != '0') : ?>
            <?php echo TiendaSelect::category('', 'filter_category', '', '', false, false, 'All Categories', '', '1'); ?>
        <?php else: ?>
            <input type="hidden" name="filter_category" value="1" />    
        <?php endif; ?>
        <input type="text" name="filter" value="<?php echo JText::_( $filter_text ); ?>" onclick="this.value='';"/> 
        <input type="submit" value="<?php echo JText::_( "Submit" ); ?>" />
        <input type="hidden" name="option" value="com_tienda" />
        <input type="hidden" name="view" value="products" />
        <input type="hidden" name="task" value="search" />
        <input type="hidden" name="search" value="1" />
        <input type="hidden" name="search_type" value="<?php echo (int) $params->get('filter_fields'); ?>" />
        <input type="hidden" name="Itemid" value="<?php echo $item_id; ?>" />
    </form>
</div>
