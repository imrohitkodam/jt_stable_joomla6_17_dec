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
use Joomla\CMS\Component\ComponentHelper;

/**
 * The Tj Certificate Training Records controller
 *
 * @since  __DEPLOY_VERSION__
 */
class TjCertificateControllerBulkTrainingRecord extends FormController
{
	protected $comMultiAgency = 'com_multiagency';

	/**
	 * Function to save multiple records
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * 
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 * 
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function save($key = null, $urlVar = null)
	{
		$app = Factory::getApplication();

		if (!Session::checkToken())
		{
			$app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
			echo new JsonResponse(null, null, true);
			$app->close();
		}

		$user = Factory::getUser();
		$data = $app->getInput()->post->get('jform', array(), 'array');

		$model = $this->getModel();

		// Validate the posted data.
		$form = $model->getForm($data, false);
		$data = $model->validate($form, $data);

		if ($data == false)
		{
			$errors = $model->getErrors();

			if (!empty($errors))
			{
				$msg  = array();

				// Push up to three validation messages out to the user.
				for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
				{
					if ($errors[$i] instanceof Exception)
					{
						$msg[] = $errors[$i]->getMessage();
					}
					else
					{
						$msg[] = $errors[$i];
					}
				}

				$errormsg = implode("<br>", $msg);
				echo new JsonResponse(0, $errormsg, true);
			}
		}
		else
		{
			$params              = ComponentHelper::getParams('com_tjcertificate');
			$userIds             = $data['assigned_user_id'];
			$data['client']      = "external";
			$data['is_external'] = 1;

			unset($data['assigned_user_id']);

			foreach ($userIds as $userId)
			{
				if (ComponentHelper::isEnabled($this->comMultiAgency) && $params->get('enable_multiagency'))
				{
					$manageOwn = $user->authorise('core.manage.own.agency.user', $this->comMultiAgency);
					$manage    = $user->authorise('core.manage.all.agency.user', $this->comMultiAgency);

					if ($manageOwn && empty($manage))
					{
						$agencyModel = TJCERT::model('Agency', array('ignore_request' => true));

						// Get agencies of logged-in user and assigned user
						$assignedUserAgencies = $agencyModel->getUserAgencies($userId);
						$loggedInUserAgencies = $agencyModel->getUserAgencies($user->id);

						$result = array();

						if (!empty($assignedUserAgencies && $loggedInUserAgencies))
						{
							// Convert object to array
							$assignedUserAgencyArr = array_column($assignedUserAgencies, 'id');
							$loggedInUserAgencyArr = array_column($loggedInUserAgencies, 'id');

							// Compare both users agencies
							$result = array_intersect($loggedInUserAgencyArr, $assignedUserAgencyArr);
						}

						if (empty($result))
						{
							continue;
						}
					}
				}

				$data['user_id'] = $userId;
				$returnData = array();

				if (ComponentHelper::isEnabled('com_tjqueue') && $params->get('tjqueue_records'))
				{
					$recordsModel      = TJCERT::model('BulkTrainingRecord', array('ignore_request' => true));
					$response          = $recordsModel->addToQueue($data);
					$msg               = $response ? Text::_("COM_TJCERTIFICATE_RECORDS_ADDED_TO_QUEUE_SUCCESSFULLY") : Text::_("COM_TJCERTIFICATE_RECORDS_FAILED");
					$returnData['msg'] = $msg;
				}
				else
				{
					$certificateModel = TJCERT::model('Certificate', array('ignore_request' => true));
					$certificateModel->save($data);
					$certificateIds[] = $certificateModel->getState('certificate.id');

					if ($certificateIds)
					{
						$returnData['msg'] = Text::sprintf('COM_TJCERTIFICATE_TOTAL_RECORDS_ADDED', count($certificateIds));
					}
				}
			}

			echo new JsonResponse($returnData);
			$app->close();
		}
	}
}
