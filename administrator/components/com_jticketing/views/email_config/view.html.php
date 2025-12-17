<?php
/**
 * @package	Jticketing
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;

class jticketingViewemail_config extends HtmlView
{
	function display($tpl = null)
	{
		$params = ComponentHelper::getParams('com_jticketing');
		$integration = $params->get('integration');

		// Native Event Manager.
		if($integration<1)
		{
			$this->sidebar = ""; // Joomla 6: HTMLHelperSidebar::render() removed
			ToolbarHelper::preferences('com_jticketing');
		?>
			<div class="alert alert-info alert-help-inline">
		<?php echo Text::_('COMJTICKETING_INTEGRATION_NOTICE');
		?>
			</div>
		<?php
			return false;
		}

		$JticketingHelper=new JticketingHelper();
		$JticketingHelper->addSubmenu('email_config');
		$this->_setToolBar();
		// Joomla 6: HTMLHelperSidebar::render() removed - sidebar functionality removed

		//Get the model
		$model = $this->getModel();
		$input=Factory::getApplication()->getInput();
		$option = $input->set('layout','email_config');
		$this->setLayout('email_config');

		parent::display($option);
	}

	function _setToolBar()
	{

		// Get the toolbar object instance

		$document =Factory::getDocument();
		HTMLHelper::_('stylesheet', 'components/com_jticketing/assets/css/jticketing.css');
		$bar =ToolBar::getInstance('toolbar');
		ToolbarHelper::back('COM_JTICKETING_HOME', 'index.php?option=com_jticketing&view=cp');

		// Joomla 6: JVERSION check removed
		if (false) // Legacy >= '3.0')
		{
			ToolbarHelper::title( Text::_('COM_JTICKETING_COMPONENT') . Text::_( 'TABS_TEMPLATE' ), 'folder' );
		}
		else
		{
			ToolbarHelper::title( Text::_('COM_JTICKETING_COMPONENT') . Text::_( 'TABS_TEMPLATE' ), 'icon-48-jticketing.png' );
		}

		if(JVERSION >='1.6.0')
     		ToolbarHelper::save('email_config.save','COM_JTICKETING_SAVE');
    		else
     		ToolbarHelper::save();
			ToolbarHelper::preferences('com_jticketing');
	}
}
