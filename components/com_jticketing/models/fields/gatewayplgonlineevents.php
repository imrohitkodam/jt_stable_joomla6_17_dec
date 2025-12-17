<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * render plugin selection of type online event
 *
 * @since  1.0
 */
class JFormFieldGatewayplgonlineevents extends ListField
{
	protected $type = 'Gatewayplgonlineevents';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return array An array of JHtml options.
	 *
	 * @since   2.1
	 */
	protected function getOptions()
	{
		$db  = Factory::getDbo();
		$jinput = Factory::getApplication()->getInput();
		$venueid = $jinput->get('id', '', 'INT');
		PluginHelper::importPlugin('tjevents');
		$results = Factory::getApplication()->triggerEvent('onJtGetContentInfo', array());

		if (!empty($results[0]))
		{
			$options[] = HTMLHelper::_('select.option', 0, Text::_("COM_JTICKETING_SELECT_ONLINE_EVENT"));

			foreach ($results as $result)
			{
				$options[] = HTMLHelper::_('select.option', $result['id'], $result['name']);
			}
		}
		else
		{
			$options[] = HTMLHelper::_('select.option', '', Text::_("COM_JTICKETING_ENABLE_TJEVENTS_PLG"));
		}

		return $options;
	}
}
