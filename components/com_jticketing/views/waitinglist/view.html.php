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
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;

if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/main.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/main.php'; }

// Import Csv export button
if (file_exists(JPATH_LIBRARIES . '/techjoomla/tjtoolbar/button/csvexport.php')) { require_once JPATH_LIBRARIES . '/techjoomla/tjtoolbar/button/csvexport.php'; }

/**
 * View class for a list of Jticketing.
 *
 * @since  2.1
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

	public $selectedEmails;

	public $enableWaitingList;

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

		$this->enableWaitingList = $com_params->get('enable_waiting_list');

		if ($this->enableWaitingList == 'none')
		{
			?>
			<div class="alert alert-info alert-help-inline">
				<?php echo Text::_('COM_JTICKETING_ERROR_ENABLE_WAITING_LIST_SETTING'); ?>
			</div>

			<?php
			return false;
		}

		$this->state         = $this->get('State');
		$this->items	     = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		if ($layout == 'contactus')
		{
			$session     = Factory::getSession();
			$waitlistIds = $session->get('waitlist_id');

			require_once JPATH_SITE . '/components/com_jticketing/models/waitinglist.php';
			$waitinglistModel = new JticketingModelWaitinglist;

			$this->selectedEmails = $waitinglistModel->getWaitlistUserEmails($waitlistIds);
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since	2.1
	 */
	protected function addTJtoolbar()
	{
		$layout = Factory::getApplication()->getInput()->get('layout', 'default');
		$com_params = ComponentHelper::getParams('com_jticketing');
		$autoAdvanceWaitingList = $com_params->get('auto_advance_waiting_list');
		$toolbar = Toolbar::getInstance('toolbar');

		Text::script('TJTOOLBAR_NO_SELECT_MSG');
		$alert = "alert(Joomla.Text._('TJTOOLBAR_NO_SELECT_MSG'));";

		if ($layout == 'contactus')
		{
			$taskName = 'waitinglist.notifyUsersByEmail';
			$task = "Joomla.submitbutton('" . $taskName . "');";
			$task = "onclick = " . $task;

			$toolbar->appendButton('Custom', '<button type="button" ' . $task . ' class="btn btn-small">
					<span class="icon-mail"></span> ' . Text::_('COM_JTICKETING_WAITING_LIST_SEND_MAIL') . '
				</button>');

			$taskName = 'waitinglist.cancel';
			$task = "Joomla.submitbutton('" . $taskName . "');";
			$task = "onclick =" . $task;

			$toolbar->appendButton('Custom', '<button type="button" ' . $task . ' class="btn btn-small">
					<span class="icon-mail"></span> ' . Text::_('COM_JTICKETING_WAITING_LIST_CANCEL_MAIL') . '
				</button>');
		}
		else
		{
			$canDo  = $this->canDo;

			if (!empty($this->items))
			{
				$taskName = 'waitinglist.redirectForEmail';
				$task = "Joomla.submitbutton('" . $taskName . "');";
				$task = "onclick = if(document.adminForm.boxchecked.value==0){" . $alert . "}else{" . $task . "}";

				$toolbar->appendButton('Custom', '<button type="button" ' . $task . ' class="btn btn-small">
						<span class="icon-mail"></span> ' . Text::_('COM_JTICKETING_EMAIL_TO_ALL_SELECTED_WAITLISTED_USERS') . '
					</button>');

				if (($canDo->{'core.enrollall'} || $canDo->{'core.enrollown'})
					&& empty($autoAdvanceWaitingList) && ($this->enableWaitingList == 'both' || $this->enableWaitingList == 'classroom_training'))
				{
					$taskName = 'waitinglist.enroll';
					$task = "Joomla.submitbutton('" . $taskName . "');";
					$task = "onclick = if(document.adminForm.boxchecked.value==0){" . $alert . "}else{" . $task . "}";

					$toolbar->appendButton('Custom', '<button type="button" ' . $task . ' class="btn btn-small">
							<span class="icon-new icon-white"></span> ' . Text::_('COM_JTICKETING_WAITINGLIST_ENROLLMENTS') . '
						</button>');
				}

				$message = array();
				$message['success'] = Text::_("COM_JTICKETING_EXPORT_FILE_SUCCESS");
				$message['error'] = Text::_("COM_JTICKETING_EXPORT_FILE_ERROR");
				$message['inprogress'] = Text::_("COM_JTICKETING_EXPORT_FILE_NOTICE");

				$toolbar->appendButton('CsvExport',  $message);
			}
		}

		return $toolbar->render();
	}
}
