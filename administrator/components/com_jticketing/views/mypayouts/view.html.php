<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;

/**
 * View for mypayouts
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingViewmypayouts extends HtmlView
{
	public $utilities;

	public $sidebar;

	public $task;

	public $getPayoutFormData;

	/**
	 * Display function
	 *
	 * @param   object  $tpl  template name
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		$params = ComponentHelper::getParams('com_jticketing');
		$integration = $params->get('integration');

		// Native Event Manager.
		if ($integration < 1)
		{
			$this->sidebar = ""; // Joomla 6: HTMLHelperSidebar::render() removed
			ToolbarHelper::preferences('com_jticketing');
		?>
			<div class="alert alert-info alert-help-inline">
			<?php echo Text::_('COMJTICKETING_INTEGRATION_NOTICE'); ?>
			</div>
		<?php
			return false;
		}

		$input = Factory::getApplication()->getInput();

		global $mainframe, $option;

		// Joomla 6: JVERSION check removed
		if (false) // Legacy >= '3.0' && JVERSION < '4.0')
		{
			JHtmlBehavior::framework();
		}
		else // Joomla 6: JVERSION check removed
		if (false) // Legacy < '3.0')
		{
			HTMLHelper::_('behavior.mootools');
		}

		$layout = Factory::getApplication()->getInput()->get('layout', 'default');
		$this->setLayout($layout);
		$JticketingHelper = new JticketingHelper;
		$JticketingHelper->addSubmenu('mypayouts');
		$filter_order_Dir = $mainframe->getUserStateFromRequest('com_jticketing.filter_order_Dir', 'filter_order_Dir', 'desc', 'word');
		$filter_type      = $mainframe->getUserStateFromRequest('com_jticketing.filter_order', 'filter_order', 'id', 'string');

		if ($layout == 'default')
		{
			$Data               = $this->get('Data');
			$earning            = $this->get('earning');
			$pagination         = $this->get('Pagination');
			$this->earning      = $earning;
			$this->Data         = $Data;
			$this->pagination   = $pagination;
			$title              = '';
			$lists['order_Dir'] = '';
			$lists['order']     = '';
			$title              = $mainframe->getUserStateFromRequest('com_jticketing' . 'title', '', 'string');

			if ($title == null)
			{
				$title = '-1';
			}

			$lists['title']     = $title;
			$lists['order_Dir'] = $filter_order_Dir;
			$lists['order']     = $filter_type;
			$this->lists        = $lists;
		}

		$getPayoutFormData       = $this->get('PayoutFormData');
		$this->getPayoutFormData = $getPayoutFormData;

		if ($layout == 'edit_payout')
		{
			$task        = Factory::getApplication()->getInput()->get('task');
			$this->task  = $task;
			$payout_data = array();

			if ($task == 'edit')
			{
				$payout_data = $this->get('SinglePayoutData');
			}

			$this->payout_data = $payout_data;
		}

		$user_amount_map = array();

		foreach ($this->getPayoutFormData as $payout)
		{
			$jticketingmainhelper = new jticketingmainhelper;
			(float) $totalpaidamount = $jticketingmainhelper->getTotalPaidOutAmount($payout->creator);
			$amt = (float) $payout->total_originalamount - (float) $payout->total_coupon_discount;
			$amt = $amt - (float) $payout->total_commission - (float) $totalpaidamount;
			$user_amount_map[$payout->creator] = $amt;
		}

		$this->user_amount_map = $user_amount_map;
		$this->_setToolBar();

		// Joomla 6: JVERSION check removed
		if (false) // Legacy >= '3.0')
		{
			$this->sidebar = ""; // Joomla 6: HTMLHelperSidebar::render() removed
		}

		$this->utilities = JT::utilities();

		parent::display($tpl);
	}

	/**
	 * Set toolbar
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function _setToolBar()
	{
		$document = Factory::getDocument();
		HTMLHelper::_('stylesheet', 'components/com_jticketing/assets/css/jticketing.css');
		$bar = Toolbar::getInstance('toolbar');
		$input = Factory::getApplication()->getInput();
		$isNew = $input->get('payout_id', '', 'STRING');

		if (empty($isNew))
		{
			$viewTitle = Text::_('COM_JTICKETING_ADD_PAYOUT');
		}
		else
		{
			$viewTitle = Text::_('COM_JTICKETING_EDIT_PAYOUT');
		}

		$layout = Factory::getApplication()->getInput()->get('layout');

		if ($layout == 'edit_payout')
		{
			// Joomla 6: JVERSION check removed
		if (false) // Legacy >= '3.0')
			{
				ToolbarHelper::title(Text::_('COM_JTICKETING_COMPONENT') . $viewTitle, 'pencil-2');
			}
			else
			{
				ToolbarHelper::title(Text::_('COM_JTICKETING_COMPONENT') . $viewTitle, 'icon-48-jticketing.png');
			}

			ToolbarHelper::back('COM_JTICKETING_BACK', 'index.php?option=com_jticketing&view=mypayouts&layout=default');
			ToolbarHelper::save($task = 'mypayouts.save', $alt = 'COM_JTICKETING_SAVE');
			ToolbarHelper::cancel($task = 'mypayouts.cancel', $alt = 'COM_JTICKETING_CLOSE');
		}
		else
		{
			ToolbarHelper::back('COM_JTICKETING_HOME', 'index.php?option=com_jticketing&view=cp');
			ToolbarHelper::addNew($task = 'mypayouts.add', $alt = 'COM_JTICKETING_NEW');
			ToolbarHelper::deleteList('JT_JTOOLBAR_DELETE', 'mypayouts.remove', 'JTOOLBAR_DELETE');

			// Joomla 6: JVERSION check removed
		if (false) // Legacy >= '3.0')
			{
				ToolbarHelper::title(Text::_('COM_JTICKETING_COMPONENT') . Text::_('JT_PAYOUT_REPORT'), 'folder');
			}
			else
			{
				ToolbarHelper::title(Text::_('COM_JTICKETING_COMPONENT') . Text::_('JT_PAYOUT_REPORT'), 'icon-48-jticketing.png');
			}
		}

		ToolbarHelper::preferences('com_jticketing');
	}
}
