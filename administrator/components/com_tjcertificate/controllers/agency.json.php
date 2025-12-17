<?php
/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2021 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * The Tj Certificate Agency controller
 *
 * @since  __DEPLOY_VERSION__
 */
class TjCertificateControllerAgency extends FormController
{
	protected $comMultiAgency = 'com_multiagency';

	/**
	 * Function to get agency users
	 *
	 * @return  void|boolean
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getAgencyUsers()
	{
		$app = Factory::getApplication();

		if (!Session::checkToken())
		{
			$app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
			echo new JsonResponse(null, null, true);
			$app->close();
		}

		// Get the current user id
		$user = Factory::getuser();

		if (!$user->id)
		{
			return false;
		}

		if (!$user->authorise('core.manage.own.agency.user', $this->comMultiAgency))
		{
			return false;
		}

		$agencyId = $app->getInput()->get('agency_id', 0, 'INT');

		$model = $this->getModel();

		if ($agencyId)
		{
			if (!$model->validateUserAgency($agencyId))
			{
				$app->enqueueMessage(Text::_('COM_TJCERTIFICATE_INVALID_ORGANIZATION'), 'error');
				echo new JsonResponse(null, null, true);
				$app->close();
			}
		}

		$userOptions = array();

		// Initialize array to store dropdown options
		$userOptions[] = HTMLHelper::_('select.option', "", Text::_('COM_TJCERTIFICATE_AGENCY_USER_SELECT'));

		$users = $model->getUsers($agencyId);

		if (!empty($users))
		{
			foreach ($users as $user)
			{
				$userOptions[] = HTMLHelper::_('select.option', $user->id, trim($user->name));
			}
		}

		echo new JsonResponse($userOptions);
		$app->close();
	}
}
