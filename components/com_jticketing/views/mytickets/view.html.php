<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * View to edit
 *
 * @since  1.6
 */
class JticketingViewmytickets extends HtmlView
{
	public $JTRouteHelper = null;

	public $user;

	public $eventId;

	public $attendeeId = null;

	public $eventCreator = 0;

	public $data = null;

	public $html = array();

	protected $jticketingmainhelper;

	protected $items;

	protected $pagination;

	protected $state;

	public $filterForm;

	public $activeFilters;

	public $utilities;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return  mixed  An object of data on success, false on failure.
	 *
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$app          = Factory::getApplication();
		$com_params   = ComponentHelper::getParams('com_jticketing');
		$integration  = $com_params->get('integration');
		$this->currency = $com_params->get('currency');
		$this->Itemid = $app->getInput()->get('Itemid');
		$layout       = Factory::getApplication()->getInput()->get('layout', 'default');

		$this->state         = $this->get('State');
		$input               = Factory::getApplication()->getInput();
		$this->items	     = $this->get('Items');
		$this->pagination	 = $this->get('Pagination');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->user          = Factory::getUser();

		// Validate user login.
		if (!$this->user->id)
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
			return false;
		}

		JLoader::register('JTRouteHelper', JPATH_SITE . '/components/com_jticketing/helpers/route.php');

		$this->JTRouteHelper        = new JTRouteHelper;

		$this->jticketingmainhelper = new jticketingmainhelper;

		if ($layout == 'ticketprint')
		{
			$this->attendeeId 	= $input->get('attendee_id', '', 'INT');
			$isAdmin  = $app->isClient("administrator");

			if (file_exists(JPATH_SITE . '/components/com_jticketing/models/attendeeform.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/attendeeform.php'; }
			$model = BaseDatabaseModel::getInstance('AttendeeForm', 'JticketingModel');
			$orderData = $model->getItem($this->attendeeId);

			$event         = JT::event()->loadByIntegration($orderData->event_id);
			$this->eventId = $event->getId();
			$this->data    = $this->jticketingmainhelper->getticketDetails($this->eventId, $this->attendeeId);
			$eventCreator  = $event->getCreator();

			if (!empty($this->data) && ($this->user->id == $orderData->owner_id || $this->user->id == $eventCreator || $isAdmin))
			{
				if (isset($this->data->ticketprice))
				{
					$this->data->ticketprice = $this->data->ticketprice;
				}

				if (isset($this->data->ticketscount))
				{
					$this->data->nofotickets = $this->data->ticketscount;
				}

				if (isset($this->data->amount))
				{
					$this->data->totalprice = $this->data->amount;
				}

				$this->data->evid = $this->eventId;
				$this->data->orderEventId = $orderData->event_id;

				$this->html = $this->jticketingmainhelper->getticketHTML($this->data, $jticketing_usesess = 0);
			}
			else
			{
				echo '<b>' . Text::_('NO_TICKET') . '</b>';
			}
		}

		$this->utilities = JT::utilities();

		parent::display($tpl);
	}
}
