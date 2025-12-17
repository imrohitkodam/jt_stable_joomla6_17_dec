<?php
/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;
use Joomla\CMS\Form\Field\ListField;

/**
 * Custom field to list default sample templates and client based if client is set
 *
 * @since  1.0.0
 */
class JFormFieldSampleTemplates extends ListField
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
		$options = array();

		$app   = Factory::getApplication();
		$input = $app->getInput();

		$user = Factory::getUser();
		$db = Factory::getDbo();

		$client = $input->get('extension', '');

		$options[] = HTMLHelper::_('select.option', '', Text::_('COM_TJCERTIFICATE_CERTIFICATE_TEMPLATE_FORM_SELECT_SAMPLE_TEMPLATE'));

		// Get client based default templates
		if (!empty($client))
		{
			$component = explode(".", $client)[0];

			// Get client based sample templates
			$clientSampleTemplatePath = MEDIA_ROOT . '/' . $component . "/" . TJ_CERTIFICATE_TEMPLATE_FOLDER;

			if (Folder::exists($clientSampleTemplatePath))
			{
				$clientSampleTemplatePath = Path::clean($clientSampleTemplatePath);

				// Get a list of folders in the search path with the given filter.
				$clientSampleFolders = Folder::folders($clientSampleTemplatePath, '', false, true);

				// Build the options list from the list of folders.
				if (is_array($clientSampleFolders))
				{
					$options[] = HTMLHelper::_('select.option', '<OPTGROUP>',
						Text::sprintf('COM_TJCERTIFICATE_CERTIFICATE_TEMPLATE_FORM_SELECT_CLIENT_SAMPLE_TEMPLATE_DEFAULT', $component)
					);

					foreach ($clientSampleFolders as $folder)
					{
						// Remove the root part and the leading /
						$folder = trim(str_replace($clientSampleTemplatePath, '', $folder), '/');

						$options[] = HTMLHelper::_('select.option', $component . '.' . $folder, $folder);
					}
				}
			}
		}
		else
		{
		// Get default sample templates
		$defaultSampleTemplatePath = TJ_CERTIFICATE_DEFAULT_TEMPLATE;

		if (Folder::exists($defaultSampleTemplatePath))
		{
			$defaultSampleTemplatePath = Path::clean($defaultSampleTemplatePath);

			// Get a list of folders in the search path with the given filter.
			$defaultSampleFolders = Folder::folders($defaultSampleTemplatePath, '', false, true);

			// Build the options list from the list of folders.
			if (is_array($defaultSampleFolders))
			{
				$options[] = HTMLHelper::_('select.option', '<OPTGROUP>', Text::_('COM_TJCERTIFICATE_CERTIFICATE_TEMPLATE_FORM_SELECT_SAMPLE_TEMPLATE_DEFAULT'));

				foreach ($defaultSampleFolders as $folder)
				{
					// Remove the root part and the leading /
					$folder = trim(str_replace($defaultSampleTemplatePath, '', $folder), '/');

					$options[] = HTMLHelper::_('select.option', 'com_tjcertificate.' . $folder, $folder);
					}
				}
			}
		}

		return $options;
	}
}
