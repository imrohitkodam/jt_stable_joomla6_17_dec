<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Hierarchy
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Component\ComponentHelper;

JLoader::import('components.com_hierarchy.models.hierarchys', JPATH_SITE);

/**
 * View class for a list of Hierarchy.
 *
 * @since  1.6
 */
class HierarchyViewHierarchys extends HtmlView
{
	protected $items;

	protected $pagination;

	protected $state;

	protected $params;

	/**
	 * Method to display event
	 *
	 * @param   object  $tpl  template name
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		$app = Factory::getApplication();
		$user = Factory::getUser();

		// Validate user login.
		if (empty($user->id))
		{
			$msg = Text::_('COM_HIERARCHY_MESSAGE_LOGIN_FIRST');

			// Get current url.
			$current = Uri::getInstance()->toString();
			$url     = base64_encode($current);

			$app->enqueueMessage($msg, 'notice');
			$app->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false));
		}

		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');

		// Filter by Context
		$contextName = $this->state->get('filter_context');

		$contextList = array();
		$contextList[] = HTMLHelper::_('select.option', '0', Text::_('COM_HIERARCHY_SELECT_CONTEXT'));

		if (!empty($this->items))
		{
			foreach ($this->items as $item)
			{
				// Get list of people who is managers of logged in user.
				$this->hierarchysModel = BaseDatabaseModel::getInstance('Hierarchys', 'HierarchyModel');
				$this->reportingsTo = $this->hierarchysModel->getReportingTo($item->reports_to);

				// Get avatar
				$hierarchyFrontendHelper = new HierarchyFrontendHelper;
				$this->gravatar = $hierarchyFrontendHelper->getUserAvatar($item->subuserId);

				// Context filter
				$context   = $item->context;
				$contextList[] = HTMLHelper::_('select.option', $context, $context);
			}
		}

		$this->contextList    = array_unique($contextList, SORT_REGULAR);
		$lists['contextList'] = $contextName;

		// Search filter
		$search = $this->state->get('filter_search');
		$lists['search'] = $search;
		$this->lists          = $lists;

		$this->HierarchyFrontendHelper     = new HierarchyFrontendHelper;

		// Get component params
		$this->params     = ComponentHelper::getParams('com_hierarchy');

		// Get permissions
		$this->canCreate  = $user->authorise('core.create', 'com_hierarchy');
		$this->canEdit    = $user->authorise('core.edit', 'com_hierarchy');
		$this->canCheckin = $user->authorise('core.manage', 'com_hierarchy');
		$this->canChange  = $user->authorise('core.edit.state', 'com_hierarchy');
		$this->canDelete  = $user->authorise('core.delete', 'com_hierarchy');
		$this->canViewChart = $user->authorise('core.chart.view', 'com_hierarchy');
		$this->canImportCSV = $user->authorise('core.csv.import', 'com_hierarchy');
		$this->canExportCSV = $user->authorise('core.csv.export', 'com_hierarchy');

		parent::display($tpl);
	}
}
