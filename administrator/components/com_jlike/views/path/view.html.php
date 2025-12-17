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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View to edit
 *
 * @since  1.6
 */
class JlikeViewPath extends HtmlView
{
	protected $state;

	protected $item;

	protected $form;

	protected $params;

	protected $sidebar;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @throws Exception
	 * @return void
	 */
	public function display($tpl = null)
	{
		$app  = Factory::getApplication();
		$user = Factory::getUser();

		$this->state  = $this->get('State');
		$this->item   = $this->get('Item');

		if (!empty($this->item->category_id))
		{
			$JlikeHelper             = new JLikeHelper;
			$categories              = $JlikeHelper->getCategory($this->item->category_id);
			$this->item->category_id = $categories;
		}

		$this->form = $this->get('Form');
		$user       = Factory::getUser();
		$userId     = $user->get('id');

		if (!$userId)
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');

			return false;
		}

		$JlikeHelper = new JLikeHelper;
		$JlikeHelper->addSubmenu('paths');

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

		ToolbarHelper::title(Text::_('COM_JLIKE_VIEW_PATH'));
		ToolbarHelper::apply('path.apply', 'JTOOLBAR_APPLY');
		ToolbarHelper::save('path.save', 'JTOOLBAR_SAVE');
		ToolbarHelper::custom('path.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		ToolbarHelper::cancel('path.cancel', 'JTOOLBAR_CANCEL');
	}
}
