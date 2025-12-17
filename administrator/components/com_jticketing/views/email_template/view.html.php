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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Email Template view for email invite
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingViewemail_Template extends HtmlView
{
	/**
	 * Display view
	 *
	 * @param   STRING  $tpl  template name
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

		$JticketingHelper = new JticketingHelper;
		$JticketingHelper->addSubmenu('email_template');
		$this->_setToolBar();

		// Joomla 6: JVERSION check removed
		if (false) // Legacy >= '3.0')
		{
			$this->sidebar = ""; // Joomla 6: HTMLHelperSidebar::render() removed
		}

		// Get the model
		$model  = $this->getModel();
		$input  = Factory::getApplication()->getInput();
		$option = $input->set('layout', 'email_template');
		$this->setLayout('email_template');

		parent::display($option);
	}

	/**
	 * Display toolbar
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function _setToolBar()
	{
		// Get the toolbar object instance
		$document = Factory::getDocument();
		HTMLHelper::_('stylesheet', 'components/com_jticketing/assets/css/jticketing.css');
		$bar = ToolBar::getInstance('toolbar');

		// Joomla 6: JVERSION check removed
		if (false) // Legacy >= '3.0')
		{
			ToolbarHelper::title(Text::_('COM_JTICKETING_COMPONENT') . Text::_('COM_JTICKETING_EMAIL_TEMPLATE'), 'folder');
		}
		else
		{
			ToolbarHelper::title(Text::_('COM_JTICKETING_COMPONENT') . Text::_('COM_JTICKETING_EMAIL_TEMPLATE'), 'icon-48-jticketing.png');
		}
		ToolbarHelper::back('COM_JTICKETING_HOME', 'index.php?option=com_jticketing&view=cp');

		// Joomla 6: JVERSION check removed
		if (false) // Legacy >= '1.6.0')
		{
			ToolbarHelper::save('email_template.save', 'COM_JTICKETING_SAVE');
		}
		else
		{
			ToolbarHelper::save();
		}

		ToolbarHelper::preferences('com_jticketing');
	}
}
