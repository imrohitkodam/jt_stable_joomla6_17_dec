<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Jticketing
 * @copyright  Copyright (C) 2005 - 2014. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * Ticketing is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;

/**
 * View class for a edit of Jticketing reminder.
 *
 * @since  1.6.2
 */
class JticketingViewReminder extends HtmlView
{
	protected $state;

	protected $item;

	protected $form;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$params = ComponentHelper::getParams('com_jticketing');
		$integration = $params->get('integration');

		// Native Event Manager.
		if($integration<1)
		{
		?>
			<div class="alert alert-info alert-help-inline">
		<?php echo Text::_('COMJTICKETING_INTEGRATION_NOTICE');
		?>
			</div>
		<?php
			return false;
		}

		$this->state = $this->get('State');
		$this->item = $this->get('Item');
		$this->form = $this->get('Form');

		// Check for errors.

		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->getInput()->set('hidemainmenu', true);

		$user = Factory::getUser();
		$isNew = ($this->item->id == 0);

		if (isset($this->item->checked_out))
		{
			$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		}
		else
		{
			$checkedOut = false;
		}

		$canDo = JticketingHelper::getActions();
		if ($isNew)
		{
			$viewTitle = Text::_('COM_JTICKETING_ADD_REMINDER');
		}
		else
		{
			$viewTitle = Text::_('COM_JTICKETING_EDIT_REMINDER');
		}

		// Joomla 6: JVERSION check removed
		if (false) // Legacy >= '3.0')
		{
			ToolbarHelper::title( Text::_('COM_JTICKETING_COMPONENT') . $viewTitle, 'pencil-2' );
		}
		else
		{
			ToolbarHelper::title(Text::_('COM_JTICKETING_COMPONENT') .$viewTitle, 'reminder.png');
		}

		// If not checked out, can save the item.

		if (!$checkedOut && ($canDo->{'core.edit'} || ($canDo->{'core.create'})))
		{
			ToolbarHelper::apply('reminder.apply', 'JTOOLBAR_APPLY');
			ToolbarHelper::save('reminder.save', 'JTOOLBAR_SAVE');
		}

		if (!$checkedOut && ($canDo->{'core.create'}))
		{
			ToolbarHelper::custom('reminder.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}
		// If an existing item, can save to a copy.

		if (!$isNew && $canDo->{'core.create'})
		{
			//ToolbarHelper::custom('reminder.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		}

		if (empty($this->item->id))
		{
			ToolbarHelper::cancel('reminder.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			ToolbarHelper::cancel('reminder.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
