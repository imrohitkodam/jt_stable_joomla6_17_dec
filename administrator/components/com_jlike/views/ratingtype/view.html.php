<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View class for rating type
 *
 * @since  3.0.0
 */
class JlikeViewRatingtype extends HtmlView
{
	protected $state;

	protected $item;

	protected $form;

	protected $isCompInstalled;

	protected $sidebar;

	/**
	 * Display the rating types
	 *
	 * @param   string  $tpl  The name of the layout file to parse.
	 *
	 * @throws Exception
	 * @return  boolean||void
	 */
	public function display($tpl = null)
	{
		$app  = Factory::getApplication();
		$user = Factory::getUser();

		$this->state  = $this->get('State');
		$this->item   = $this->get('Item');
		$this->form   = $this->get('Form');

		$user       = Factory::getUser();
		$userId     = $user->id;

		$this->isCompInstalled = ComponentHelper::isEnabled('com_tjucm', true);

		if (!$userId)
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');

			return false;
		}

		$JlikeHelper = new JLikeHelper;
		$JlikeHelper->addSubmenu('ratingtypes');

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
	 * @since   3.0.0
	 */
	protected function addToolbar()
	{
		ToolbarHelper::title(Text::_('COM_JLIKE_VIEW_TITLE_RATING_TYPE'), 'pencil-2');
		ToolbarHelper::apply('ratingtype.apply', 'JTOOLBAR_APPLY');
		ToolbarHelper::save('ratingtype.save', 'JTOOLBAR_SAVE');
		ToolbarHelper::custom('ratingtype.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		ToolbarHelper::cancel('ratingtype.cancel', 'JTOOLBAR_CANCEL');
	}
}
