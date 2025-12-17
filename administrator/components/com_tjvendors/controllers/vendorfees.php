<?php
/**
 * @package     TJVendors
 * @subpackage  com_tjvendors
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;

/**
 * Vendors list controller class.
 *
 * @since  1.6
 */
class TjvendorsControllerVendorFees extends AdminController
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    Optional. Model name
	 * @param   string  $prefix  Optional. Class prefix
	 * @param   array   $config  Optional. Configuration array for model
	 *
	 * @return  object	The Model
	 *
	 * @since    1.6
	 */
	public function getModel($name = 'vendorfee', $prefix = 'TjvendorsModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	/**
	 * Method for back to previous page
	 *
	 * @return  boolean
	 */
	public function back()
	{
		// Get the input
		$input = Factory::getApplication()->getInput();
		$pks = $input->post->get('cid', array(), 'array');

		// Redirect to the list screen.
		$this->setRedirect(Route::_('index.php?option=com_tjvendors&view=vendors&client=' . $input->get('client', '', 'STRING'), false));
	}
}
