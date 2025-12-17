<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

if (file_exists(JPATH_ADMINISTRATOR . '/components/com_jlike/models/rating.php')) {
	require_once JPATH_ADMINISTRATOR . '/components/com_jlike/models/rating.php';
}

/**
 * rating controller class.
 *
 * @since  3.0.0
 */
class JlikeControllerRating extends FormController
{
	/**
	 * Method to save a record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since   3.0.0
	 */
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app = Factory::getApplication();
		$model = $this->getModel();
		$table = $model->getTable();

		// Get the user data.
		$data = $app->getInput()->get('jform', array(), 'array');
		$data['rating'] = $app->getInput()->get('rating', '0', 'int');
		$table->load(array('content_id' => $data['content_id'], 'submitted_by' => $data['submitted_by']));

		if ($table->id)
		{
			echo new JsonResponse(null, Text::_('COM_JLIKE_RATING_ALREADY_SUBMITTED'), true);
			$app->close();
		}

		// Validate the posted data.
		$form = $model->getForm();

		if (!$model->validate($form, $data))
		{
			echo new JsonResponse(null, Text::_('COM_JLIKE_RATING_MANDATORY_FIELD'), true);
			$app->close();
		}

		$recordId = $model->save($data);

		if (!$recordId)
		{
			$errors = $this->getErrors();

			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof \Exception)
				{
					echo new JsonResponse(null, Text::_($errors[$i]->getMessage()), true);
					$app->close();
				}
				else
				{
					echo new JsonResponse(null, Text::_($errors[$i]), true);
					$app->close();
				}
			}
		}

		$data['userName'] = Factory::getUser($data['submitted_by'])->name;

			// Get date in local time zone
		$data['created_date'] = HTMLHelper::date($data['created_date'], 'Y-m-d h:i:s');

			// Get extra date info
		$data['created_day'] = date_format(date_create($data['created_date']), "D");
		$data['created_date_month'] = date_format(date_create($data['created_date']), "d, M, y");

		echo new JsonResponse($data, Text::_('COM_JLIKE_RATING_SUCCESS_MESSAGE'));
		$app->close();
	}

	/**
	 * Method to get the saved a record.
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since   3.0.0
	 */
	public function getLoggedInUserRating()
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app = Factory::getApplication();
		$data = $app->getInput()->get('jform', array(), 'array');
		$contentId = $data['content_id'];

		if ($contentId && Factory::getUser()->id)
		{
			$ratingsModelPath = JPATH_ADMINISTRATOR . '/components/com_jlike/models/ratings.php';
			if (file_exists($ratingsModelPath)) {
				require_once $ratingsModelPath;
			}
			$ratingsModel = BaseDatabaseModel::getInstance('ratings', 'JLikeModel');
			$ratingsModel->setState('filter.content_id', $contentId);
			$ratingsModel->setState('filter.submitted_by', Factory::getUser()->id);
			$ratings = $ratingsModel->getItems();
			$rating = $ratings[0];
		}

		if ($rating->id)
		{
			echo new JsonResponse($rating, Text::_('COM_JLIKE_RATING_GET_RATING'));
			$app->close();
		}
		else
		{
			echo new JsonResponse(null, Text::_('COM_JLIKE_RATINGS_NO_CUSTOMER_REVIEWS'), true);
			$app->close();
		}
	}
}
