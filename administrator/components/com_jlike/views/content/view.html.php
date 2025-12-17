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
defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Content view class
 *
 * @since  1.6
 */
class JlikeViewContent extends HtmlView
{
	protected $state;

	protected $item;

	protected $form;

	protected $sidebar;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		$JlikeHelper = new JLikeHelper;
		$JlikeHelper->addSubmenu('contents');

		$this->sidebar = '';		$this->addToolbar();

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

		ToolbarHelper::title(Text::_('COM_JLIKE_VIEW_TITLE_CONTENT'));
		ToolbarHelper::apply('content.apply', 'JTOOLBAR_APPLY');
		ToolbarHelper::save('content.save', 'JTOOLBAR_SAVE');
		ToolbarHelper::custom('content.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		ToolbarHelper::cancel('content.cancel', 'JTOOLBAR_CANCEL');
	}
}
