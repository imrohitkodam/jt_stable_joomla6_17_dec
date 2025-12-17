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

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * jLikeViewabout form view class.
 *
 * @package     JGive
 * @subpackage  com_jlike
 * @since       1.8
 */
class JLikeViewabout extends HtmlView
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		JlikeHelper::addSubmenu('about');

		$this->addToolbar();

		if (JVERSION >= 3.0)
		{
			$this->sidebar = '';		}

		parent::display($tpl);
	}

	/**
	 * Set tool bar
	 *
	 * @return  void
	 */
	protected function addToolbar()
	{
		ToolbarHelper::title(Text::_('COM_JLIKE_TITLE_ABOUT'), 'jlike.png');
		ToolbarHelper::preferences('com_jlike');
	}
}
