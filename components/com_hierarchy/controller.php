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
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;

/**
 * controller class for a Hierarchy.
 *
 * @since  1.6
 */
class HierarchyController extends BaseController
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController This object to support chaining.
	 * 
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		require_once JPATH_COMPONENT . '/helpers/hierarchy.php';

		$app = Factory::getApplication();
		$input = $app->getInput();
		$view = $input->getCmd('view', 'hierarchys');
		$input->set('view', $view);

		parent::display($cachable, $urlparams);

		return $this;
	}
}
