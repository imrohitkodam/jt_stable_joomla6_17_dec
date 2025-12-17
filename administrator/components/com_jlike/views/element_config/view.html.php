<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class jlikeViewElement_config extends HtmlView
{
	public function display($tpl = null)
	{
		$JlikeHelper = new JLikeHelper();
		// Get the toolbar object instance
		$JlikeHelper->addSubmenu('element_config');
		$this->_setToolbar();

		//Get the model
		$model = $this->getModel();
		$input = Factory::getApplication()->getInput();

		$this->sidebar = '';
		parent::display();
	}

	public function _setToolbar()
	{
		ToolbarHelper::title(Text::_('COM_JLIKE_CONTENT_TYPE'), 'jlike.png');

		ToolbarHelper::apply('save', 'JTOOLBAR_APPLY');
	}
}
