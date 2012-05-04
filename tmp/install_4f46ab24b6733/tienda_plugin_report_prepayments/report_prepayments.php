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

Tienda::load( 'TiendaReportPlugin', 'library.plugins.report' );

class plgTiendaReport_prepayments extends TiendaReportPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
    var $_element    = 'report_prepayments';

    /**
     * @var $default_model  string  Default model used by report
     */
    var $default_model    = 'orders';

	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function plgTiendaReport_prepayments(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
	}

    /**
     * Override parent::_getData() to set the direction of the product quantity
     *
     * @return objectlist
     */
    function _getData()
    {
        $state = $this->_getState();
        $model = $this->_getModel();

		$model->setState( 'order', 'order_id' );
        $model->setState( 'direction', 'ASC' );
        $model->setState( 'filter_orderstate', '15' );    
        $data = $model->getList();

        return $data;
    }
    
    /**
     * Override parent::_getState() to do the filtering
     *
     * @return object
     */
    function _getState()
    {
    	$app = JFactory::getApplication();
        $model = $this->_getModel( 'orders' );
        $ns = $this->_getNamespace();

        $state = array();
        
        $state['filter_userid'] = $app->getUserStateFromRequest($ns.'userid', 'filter_userid');
        $state['filter_id_from'] = $app->getUserStateFromRequest($ns.'filter_id_from','filter_id_from');
		$state['filter_id_to'] = $app->getUserStateFromRequest($ns.'filter_id_to','filter_id_to');
		$state['filter_date_from'] = $app->getUserStateFromRequest($ns.'filter_date_from','filter_date_from');
		$state['filter_date_to'] = $app->getUserStateFromRequest($ns.'filter_date_to','filter_date_to');
		$state['filter_total_from'] = $app->getUserStateFromRequest($ns.'filter_total_from','filter_total_from');
		$state['filter_total_to'] = $app->getUserStateFromRequest($ns.'filter_total_to','filter_total_to');
		$state['filter_datetype']=$app->getUserStateFromRequest($ns.'filter_datetype','filter_datetype');

        $state = $this->_handleRangePresets( $state );

        foreach (@$state as $key=>$value)
        {
            $model->setState( $key, $value );
        }

        return $state;

    }
}
