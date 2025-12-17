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
use Joomla\CMS\Form\Field\ListField;

require_once JPATH_ADMINISTRATOR . '/components/com_tjcertificate/includes/tjcertificate.php';

/**
 * Custom field to list all public and logged-in user's private certificate templates
 *
 * @since  1.0.0
 */
class JFormFieldCertificateTemplates extends ListField
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

		$app  = Factory::getApplication();
		$input = $app->getInput();
		$user = Factory::getUser();
		$db   = Factory::getDbo();

		$client = $this->getAttribute('client');

		if (empty($client))
		{
			$client = $input->get('extension', '');
		}

		$options[] = HTMLHelper::_('select.option', '', Text::_('COM_TJCERTIFICATE_CERTIFICATE_TEMPLATE_FIELD_SELECT'));

		// Get Private/Created by logged-in user's templates
		if ($user->id)
		{
			// Get template model
			$model = TJCERT::model('Templates', array('ignore_request' => true));

			if (!empty($client))
			{
				$model->setState('filter.client', $client);
			}

			$model->setState('filter.state', 1);
			$model->setState('filter.is_public', 1);
			$model->setState('filter.created_by', $user->id);

			$certlist = $model->getItems();

			if (!empty($certlist))
			{
				$options[] = HTMLHelper::_('select.option', '<OPTGROUP>', Text::_('COM_TJCERTIFICATE_CERTIFICATE_TEMPLATE_FIELD_PRIVATE'));

				foreach ($certlist as $cert)
				{
					$options[] = HTMLHelper::_('select.option', $cert->id, $cert->title);
				}
			}
		}

		// Get Public templates
		// Get template model
		$model = TJCERT::model('Templates', array('ignore_request' => true));

		if (!empty($client))
		{
			$model->setState('filter.client', $client);
		}

		$model->setState('filter.state', 1);
		$model->setState('filter.is_public', 2);

		$certlist = $model->getItems();

		if (!empty($certlist))
		{
			$options[] = HTMLHelper::_('select.option', '<OPTGROUP>', Text::_('COM_TJCERTIFICATE_CERTIFICATE_TEMPLATE_FIELD_PUBLIC'));

			foreach ($certlist as $cert)
			{
				$options[] = HTMLHelper::_('select.option', $cert->id, $cert->title);
			}
		}

		return $options;
	}
}
