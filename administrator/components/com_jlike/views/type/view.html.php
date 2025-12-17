<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View to edit
 *
 * @since  1.6
 */
class JlikeViewType extends HtmlView
{
	protected $state;

	protected $item;

	protected $form;

	protected $params;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$app  = Factory::getApplication();
		$user = Factory::getUser();

		$this->state  = $this->get('State');
		$this->item   = $this->get('Item');

		$this->form = $this->get('Form');

		// $this->params = $app->getParams('com_jlike');

		$user       = Factory::getUser();
		$userId     = $user->get('id');

		if (!$userId)
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');

			return false;
		}

		$JlikeHelper = new JLikeHelper;
		$JlikeHelper->addSubmenu('types');

		$this->sidebar = '';
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		if ($this->_layout == 'edit')
		{
			$authorised = $user->authorise('core.create', 'com_jlike');

			if ($authorised !== true)
			{
				throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'));
			}
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT . '/helpers/jlike.php';

		ToolbarHelper::title(Text::_('COM_JLIKE_VIEW_TITLE_TYPE'));
		ToolbarHelper::apply('type.apply', 'JTOOLBAR_APPLY');
		ToolbarHelper::save('type.save', 'JTOOLBAR_SAVE');
		ToolbarHelper::custom('type.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		ToolbarHelper::cancel('type.cancel', 'JTOOLBAR_CANCEL');
	}
}
