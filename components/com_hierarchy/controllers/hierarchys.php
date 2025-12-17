<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Hierarchy
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;

require_once JPATH_COMPONENT . '/controller.php';

/**
 * Hierarchys list controller class.
 *
 * @since  1.6
 */
class HierarchyControllerHierarchys extends HierarchyController
{
	/**
	 * Proxy for getModel.
	 * 
	 * @param   string  $name    name of view
	 * 
	 * @param   string  $prefix  prefix
	 * 
	 * @param   string  $config  config
	 * 
	 * @return  array
	 */
	public function &getModel($name = 'Hierarchys', $prefix = 'HierarchyModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	/**
	 * Method to get users to manage hierarchy.
	 *
	 * @return  array
	 *
	 * @since    1.6
	 */
	public function getReportsTo()
	{
		$app = Factory::getApplication();
		$input = $app->getInput();
		$userId = $input->get('user_id', '', 'int');

		// Get the model.
		$model = $this->getModel('Hierarchys', 'HierarchyModel');

		// Get the list
		$reportsTo = $model->getReportsTo($userId);

		foreach ($reportsTo as $reportTo)
		{
			$user = Factory::getUser($reportTo->user_id);
			$reportTo->name = $user->name;
			$reportTo->also = $model->getReportsTo($reportTo->user_id);
		}

		// Output json response
		header('Content-type: application/json');
		echo json_encode($reportsTo);
		exit();
	}
}
