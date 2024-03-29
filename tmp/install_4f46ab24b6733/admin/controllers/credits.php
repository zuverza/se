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
defined( '_JEXEC' ) or die( 'Restricted access' );

class TiendaControllerCredits extends TiendaController 
{
	/**
	 * constructor
	 */
	function __construct() 
	{
		parent::__construct();
		$this->set('suffix', 'credits');
	}
	
	/**
	 * Sets the model's state
	 * 
	 * @return array()
	 */
    function _setModelState()
    {
    	$state = parent::_setModelState();   	
		$app = JFactory::getApplication();
		$model = $this->getModel( $this->get('suffix') );
    	$ns = $this->getNamespace();

        $state['order']     = $app->getUserStateFromRequest($ns.'.filter_order', 'filter_order', 'tbl.created_date', 'cmd');
        $state['direction'] = $app->getUserStateFromRequest($ns.'.filter_direction', 'filter_direction', 'DESC', 'word');
      	$state['filter_type'] 	= $app->getUserStateFromRequest($ns.'filter_type', 'filter_type', '', '');
      	$state['filter_enabled'] 	= $app->getUserStateFromRequest($ns.'filter_enabled', 'filter_enabled', '', '');
      	$state['filter_user'] 	      = $app->getUserStateFromRequest($ns.'user', 'filter_user', '', '');
      	$state['filter_userid']          = $app->getUserStateFromRequest($ns.'userid', 'filter_userid', '', '');
    	$state['filter_id_from'] 	= $app->getUserStateFromRequest($ns.'id_from', 'filter_id_from', '', '');
    	$state['filter_id_to'] 		= $app->getUserStateFromRequest($ns.'id_to', 'filter_id_to', '', '');
        $state['filter_amount_from']    = $app->getUserStateFromRequest($ns.'filter_amount_from', 'filter_amount_from', '', '');
        $state['filter_amount_to']      = $app->getUserStateFromRequest($ns.'filter_amount_to', 'filter_amount_to', '', '');
    	        
    	foreach (@$state as $key=>$value)
		{
			$model->setState( $key, $value );	
		}
  		return $state;
    }

}
?>