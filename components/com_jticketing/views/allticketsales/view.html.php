<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
Use Joomla\String\StringHelper;

/**
 * Attendee list view
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingViewallticketsales extends HtmlView
{
	public $utilities;

	/**
	 * Method to display events
	 *
	 * @param   object  $tpl  tpl
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		$input = Factory::getApplication()->getInput();
		$this->jticketingmainhelper = new jticketingmainhelper;
		$mainframe = Factory::getApplication();
		$this->params     = $mainframe->getParams('com_jticketing');
		$integration = $this->params->get('integration');
		$this->PageTitle = $this->params->get('page_title', '');

		$app  = Factory::getApplication();
		$user = Factory::getUser();

		// Validate user login.
		if (!$user->id)
		{
			$msg = Text::_('COM_JTICKETING_MESSAGE_LOGIN_FIRST');

			// Get current url.
			$current = Uri::getInstance()->toString();
			$url     = base64_encode($current);
			$app->enqueueMessage($msg);
			$app->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false));
		}
		// Native Event Manager.

		if ($integration < 1)
		{
		?>
			<div class="alert alert-info alert-help-inline">
		<?php echo Text::_('COMJTICKETING_INTEGRATION_NOTICE');
		?>
			</div>
		<?php
			return;
		}

		$option         = $input->get('option');
		$search_event   = $mainframe->getUserStateFromRequest($option . 'search_event', 'search_event', '', 'string');
		$search_event   = StringHelper::strtolower($search_event);
		$user           = Factory::getUser();
		$status_event   = array();
		$eventsModel    = JT::model('events');
		$eventsModel = JT::model('events', array('ignore_request' => true));
		$eventsModel->setState('filter_creator', (int) $user->id);
		$eventlist      = $eventsModel->getItems();
		$status_event[] = HTMLHelper::_('select.option', '', Text::_('SELONE_EVENT'));

		if (!empty($eventlist))
		{
			foreach ($eventlist as $key => $event)
			{
				$eventObj = JT::event($event->id);

				if ($eventObj->getTitle())
				{
					$status_event[] = HTMLHelper::_('select.option', $eventObj->getId(), $eventObj->getTitle());
				}
			}
		}

		$eventid = Factory::getApplication()->getInput()->get('event');

		if ($eventid)
		{
			$eventcreator = JT::event($eventid)->getCreator();

			if ($user->id != $eventcreator)
			{
				$this->eventauthorisation = 0;
				echo '<b>' . Text::_('COM_JTICKETING_USER_UNAUTHORISED') . '</b>';

				return;
			}
		}

		$this->status_event = $status_event;

		$this->user_filter_options = $this->get('UserFilterOptions');

		$user_filter = $mainframe->getUserStateFromRequest('com_jticketing' . 'user_filter', 'user_filter');

		$filter_order_Dir = $mainframe->getUserStateFromRequest('com_jticketing.filter_order_Dir', 'filter_order_Dir', 'desc', 'word');
		$filter_type = $mainframe->getUserStateFromRequest('com_jticketing.filter_order', 'filter_order', 'id', 'string');

		$lists['search_event'] = $search_event;

		$Data = $this->get('Data');
		$pagination = $this->get('Pagination');

		$Itemid = $input->get('Itemid');

		if (empty($Itemid))
		{
			$Session = Factory::getSession();
			$Itemid = $Session->get("JT_Menu_Itemid");
		}

		$this->Data = $Data;
		$this->pagination = $pagination;
		$this->lists = $lists;
		$this->Itemid = $Itemid;
		$this->status_event = $status_event;
		$title = '';
		$lists['order_Dir'] = '';
		$lists['order'] = '';
		$title = $mainframe->getUserStateFromRequest('com_jticketing' . 'title', '', 'string');

		if ($title == null)
		{
			$title = '-1';
		}

		$lists['title'] = $title;
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_type;
		$lists['pagination'] = $pagination;

		$lists['user_filter'] = $user_filter;
		$this->lists = $lists;
		$this->utilities = JT::utilities();

		$model        = JT::model('allticketsales', array('ignore_request' => true));
		$model->setState('limit', 0);
		$model->setState('limitstart', 0);
		$this->allData  = $model->getData();
		$this->totalnooftickets = 0;
		$this->totalprice = 0;
		$this->totalcommission = 0;
		$this->totalearn = 0;

		foreach($this->allData as $data)
		{
			$this->totalnooftickets += $data->eticketscount;
			$this->totalprice += $data->eamount;
			$this->totalcommission += $data->ecommission;
			$this->totalearn += ($data->eamount-$data->ecommission);
		}

		parent::display($tpl);
	}
}
