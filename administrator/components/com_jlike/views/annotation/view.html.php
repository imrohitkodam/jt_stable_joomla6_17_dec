<?php
/**
 * @version     1.2
 * @package     com_jlike
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Techjoomla <contact@techjoomla.com> - http://www.techjoomla.com
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View to edit
 */
class JlikeViewAnnotation extends HtmlView
{
	protected $state;

	protected $item;

	protected $form;

	/**
	 * Display the view
	 * @param null|mixed $tpl
	 */
	public function display($tpl = null)
	{
		$this->state	 = $this->get('State');
		$this->item		 = $this->get('Item');
		$this->form		 = $this->get('Form');

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
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->getInput()->set('hidemainmenu', true);

		$user		  = Factory::getUser();
		$isNew		 = ($this->item->id == 0);
		if (isset($this->item->checked_out))
		{
			$checkedOut	 = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		}
		else
		{
			$checkedOut = false;
		}
		$canDo		 = JlikeHelper::getActions();

		ToolbarHelper::title(Text::_('COM_JLIKE_TITLE_ANNOTATION'), 'annotation.png');

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit') || ($canDo->get('core.create'))))
		{
			ToolbarHelper::apply('annotation.apply', 'JTOOLBAR_APPLY');
			ToolbarHelper::save('annotation.save', 'JTOOLBAR_SAVE');
		}

		if (empty($this->item->id))
		{
			ToolbarHelper::cancel('annotation.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			ToolbarHelper::cancel('annotation.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
