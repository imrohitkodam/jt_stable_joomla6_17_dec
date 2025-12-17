<?php
/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\Field\ListField;

/**
 * Supports an HTML select list of allocated agencies
 *
 * @since  __DEPLOY_VERSION__
 */
class JFormFieldUsers extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.0.0
	 */
	protected $type = 'users';

	/**
	 * Method to get the field input markup.
	 *
	 * @return    string    The field input markup.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getOptions()
	{
		// Initialize array to store dropdown options
		$loggedInuser = Factory::getUser();

		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('distinct(ci.user_id), u.name');
		$query->from($db->quoteName('#__tj_certificate_issue', 'ci'));
		$query->join('LEFT', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('ci.user_id') . ' = ' . $db->quoteName('u.id'));
		$query->where($db->quoteName('u.block') . ' = 0');
		$query->order($db->escape($db->quoteName('u.name') . ' ASC'));
		$db->setQuery($query);
		$users = $db->loadObjectList();

		$options = [];

		if ($loggedInuser->authorise('certificate.external.manage', 'com_tjcertificate'))
		{
			$options[] = HTMLHelper::_('select.option', "", Text::_('COM_TJCERTIFICATE_CERTIFICATE_FILTER_ISSUED_USER_SELECT'));

			foreach ($users as $user)
			{
				$options[] = HTMLHelper::_('select.option', $user->user_id, $user->name);
			}
		}
		else
		{
			$options[] = HTMLHelper::_('select.option', $loggedInuser->id, $loggedInuser->name);
		}

		return $options;
	}
}
