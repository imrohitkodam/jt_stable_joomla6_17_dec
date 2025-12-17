<?php
/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;

/**
 * Custom field to list all public and logged-in user's private certificate templates
 *
 * @since  1.0.0
 */
class JFormFieldGetClientList extends ListField
{
	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of HTMLHelper options.
	 *
	 * @since   11.4
	 */
	protected function getOptions()
	{
		$options = [];

		$user = Factory::getUser();
		$db = Factory::getDbo();

		$clientByUser = $this->getAttribute('clientByUser');

		$options[] = HTMLHelper::_('select.option', '', Text::_('COM_TJCERTIFICATE_CERTIFICATE_FILTER_CERTIFICATE_TYPE_SELECT'));

		// Get Private/Created by logged-in user's templates
		if ($user->id)
		{
			// Create a new query object.
			$query = $db->getQuery(true);

			$query->select('DISTINCT ' . $db->quoteName('client'));
			$query->from($db->quoteName('#__tj_certificate_issue'));

			if (!$user->authorise('certificate.external.manage', 'com_tjcertificate'))
			{
				if (!empty($clientByUser))
				{
					$query->where($db->quoteName('user_id') . ' = ' . (int) $user->id);
				}
			}

			$db->setQuery($query);

			$listobjects = $db->loadObjectList();

			if (!empty($listobjects))
			{
				foreach ($listobjects as $obj)
				{
					if ($obj->client)
					{
						$client    = str_replace(".", "_", $obj->client);
						$langConst = strtoupper("COM_TJCERTIFICATE_CLIENT_" . $client);
						$options[] = HTMLHelper::_('select.option', $obj->client, Text::_($langConst));
					}
				}
			}
		}

		return $options;
	}
}
