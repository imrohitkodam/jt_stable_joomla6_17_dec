<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Jticekting
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;

// Import Csv export button
if (file_exists(JPATH_LIBRARIES . '/techjoomla/tjtoolbar/button/csvexport.php')) { require_once JPATH_LIBRARIES . '/techjoomla/tjtoolbar/button/csvexport.php'; }

/**
 * View class for a list of Jticketing.
 *
 * @since  2.2
 */
class JTicketingViewWaitinglist extends HtmlView
{
	protected $items;

	protected $pagination;

	protected $state;

	protected $canDo;

	public $filterForm;

	public $activeFilters;

	public $sidebar;

	public $extra_sidebar;

	public $enableWaitingList;

	public $selectedEmails;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		$this->canDo = ContentHelper::getActions('com_jticketing');
		$com_params  = ComponentHelper::getParams('com_jticketing');
		$layout      = Factory::getApplication()->getInput()->get('layout', 'default');

		$this->state         = $this->get('State');
		$this->items	     = $this->get('Items');
		$this->pagination	 = $this->get('Pagination');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		$this->enableWaitingList = $com_params->get('enable_waiting_list', '', 'STRING');

		if ($this->enableWaitingList == 'none')
		{
			// Joomla 6: HTMLHelperSidebar::render() removed - sidebar functionality removed
			ToolbarHelper::preferences('com_jticketing');
		?>

			<div class="alert alert-info alert-help-inline">
				<?php echo Text::_('COM_JTICKETING_ERROR_ENABLE_WAITING_LIST_SETTING'); ?>
			</div>

			<?php
			return false;
		}

		if ($layout == 'contactus')
		{
			$session     = Factory::getSession();
			$waitlistIds = $session->get('waitlist_id');

			require_once JPATH_SITE . '/components/com_jticketing/models/waitinglist.php';
			$waitinglistModel = new JTicketingModelWaitinglist;

			$this->selectedEmails = $waitinglistModel->getWaitlistUserEmails($waitlistIds);
		}

		$this->addToolbar();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		JticketingHelper::addSubmenu('waitinglist');

		if ($layout != 'contactus')
		{
			$this->sidebar = ""; // Joomla 6: HTMLHelperSidebar::render() removed
		}

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since	2.2
	 */
	protected function addToolbar()
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_jticketing'. '/helpers/jticketing.php';

		$layout      = Factory::getApplication()->getInput()->get('layout', 'default');
		$com_params  = ComponentHelper::getParams('com_jticketing');
		$autoAdvanceWaitingList = $com_params->get('auto_advance_waiting_list');

		if ($layout == 'contactus')
		{
			Factory::getApplication()->getInput()->set('hidemainmenu', true);
			ToolbarHelper::title(Text::_('COM_JTICKETING_WAITING_LIST_SEND_EMAIL'), 'jticketing email');
			ToolbarHelper::custom('waitinglist.notifyUsersByEmail', 'envelope.png', 'send_f2.png', 'COM_JTICKETING_WAITING_LIST_SEND_MAIL', false);
			ToolbarHelper::cancel('waitinglist.cancel');
		}
		else
		{
			ToolbarHelper::title(Text::_('COM_JTICKETING_TITLE_WAITINGLIST'), 'list');
			ToolbarHelper::divider();

			if (!empty($this->items))
			{
				$canDo  = $this->canDo;

				// Get an instance of the Toolbar
				$toolbar = Toolbar::getInstance('toolbar');

				ToolbarHelper::custom('waitinglist.redirectForEmail', 'mail.png', '', Text::_('COM_JTICKETING_EMAIL_TO_ALL_SELECTED_WAITLISTED_USERS'));

				if (($canDo->{'core.enrollall'} || $canDo->{'core.enrollown'}) && empty($autoAdvanceWaitingList)
					&& ($this->enableWaitingList == 'both' || $this->enableWaitingList == 'classroom_training'))
				{
					ToolbarHelper::custom('waitinglist.enroll', 'plus.png', '', Text::_('COM_JTICKETING_WAITINGLIST_ENROLLMENTS'));
				}

				$message = array();
				$message['success'] = Text::_("COM_JTICKETING_EXPORT_FILE_SUCCESS");
				$message['error'] = Text::_("COM_JTICKETING_EXPORT_FILE_ERROR");
				$message['inprogress'] = Text::_("COM_JTICKETING_EXPORT_FILE_NOTICE");

				$toolbar->appendButton('CsvExport',  $message);
			}
		}

		ToolbarHelper::preferences('com_jticketing');
		// Joomla 6: HTMLHelperSidebar::setAction() removed
		$this->extra_sidebar = '';
	}
}
