<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/**
 * View class for list view of products.
 *
 * @package     Jlike
 * @subpackage  Jlike
 * @since       2.2
 */
class JlikeViewlikes extends HtmlView
{
	protected $params;

	protected $comjlikeHelper;

	protected $filter_likecontent_classification;

	protected $filter_likecontent_list;

	protected $content_id;

	protected $allLables;

	protected $search;

	protected $sortDirection;

	protected $sortColumn;

	protected $linechart;

	protected $todate;

	protected $fromdate;

	protected $data;

	protected $pagination;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		global $option, $mainframe;
		$mainframe = Factory::getApplication();
		$this->params = ComponentHelper::getParams('com_jlike');
		$jinput = Factory::getApplication()->getInput();
		$layout    = Factory::getApplication()->getInput()->get('layout', 'default');
		$user = Factory::getUser();
		$this->comjlikeHelper = new comjlikeHelper;

		if (empty($user->id))
		{
			if ($layout != 'all')
			{
				$msg = Text::_('COM_JLIKE_LOGIN_MSG');

				if (JVERSION > 3.0)
				{
					$uri   = $jinput->server->get('REQUEST_URI', 'default_value', 'filter');
				}
				else
				{
					$uri = Factory::getApplication()->getInput()->get('REQUEST_URI', '', 'server', 'string');
				}

				$url = base64_encode($uri);
				$mainframe->enqueueMessage($msg, 'error');
				$mainframe->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false));
			}
		}

		$model = $this->getModel();
		$this->filter_likecontent_classification = $model->Likecontent_classification($user);
		$this->filter_likecontent_list           = $model->Likecontent_list($user);

		if ($layout == 'updatelist')
		{
			// Get content like list -
			$this->content_id = $jinput->get('content_id', '', 'INT');
			$this->allLables = $model->getUpdateLableList($this->content_id, $user->id);
		}
		elseif ($layout != 'all')
		{
			$this->search        = $mainframe->getUserStateFromRequest($option . 'filter_search', 'filter_search', '', 'string');
			$this->sortDirection = $mainframe->getUserStateFromRequest($option . 'filter_order_Dir', 'filter_order_Dir', 'asc', 'word');
			$this->sortColumn    = $mainframe->getUserStateFromRequest($option . 'filter_order', 'filter_order', 'title', 'cmd');
		}
		else
		{
			$linechart       = $this->get('LineChartValues');
			$this->linechart = $linechart;
			$post  = $jinput->getArray($_POST);

			if (isset($post['todate']))
			{
				$to_date = $post['todate'];
			}
			else
			{
				$to_date = date('Y-m-d');
			}

			if (isset($post['fromdate']))
			{
				$from_date = $post['fromdate'];
			}
			else
			{
				$from_date = date('Y-m-d', strtotime(date('Y-m-d') . ' - 30 days'));
			}

			$this->todate   = $to_date;
			$this->fromdate = $from_date;
			$this->search        = $mainframe->getUserStateFromRequest($option . 'all_filter_search', 'all_filter_search', '', 'cmd');
			$this->sortDirection = $mainframe->getUserStateFromRequest($option . 'all_filter_order_Dir', 'all_filter_order_Dir', 'asc', 'word');
			$this->sortColumn    = $mainframe->getUserStateFromRequest($option . 'all_filter_order', 'all_filter_order', 'title', 'cmd');
		}

		// $myfavourites = $this->getModel('likes');
		$data         = $this->get('Items');

		// Get data from the model
		$pagination       = $this->get('Pagination');

		// Push data into the template
		$this->data       = $data;
		$this->pagination = $pagination;
		parent::display($tpl);
	}
}
