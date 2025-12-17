<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

if (file_exists(JPATH_ADMINISTRATOR . '/components/com_jlike/models/ratings.php')) {
	require_once JPATH_ADMINISTRATOR . '/components/com_jlike/models/ratings.php';
}

/**
 * Rating list controller class.
 *
 * @since  3.0.0
 */
class JlikeControllerRatings extends AdminController
{
	/**
	 * Function to get ratings list
	 *
	 * @return  object  activities
	 *
	 * @since   3.0.0
	 */
	public function getRatings()
	{
		$app = Factory::getApplication();
		$jinput = Factory::getApplication()->getInput();
		$model = $this->getModel('ratings');
		$contentId = $jinput->get('content_id', '', 'INT');
		$start = $jinput->get('start', '0');
		$limit = $jinput->get('limit', '10');

		// Return result related to specified content id
		if (empty($contentId))
		{
			echo new JsonResponse(null, Text::_('COM_JLIKE_RATINGS_NO_CUSTOMER_REVIEWS'), true);
			$app->close();
		}

		$model->setState('filter.content_id', $contentId);
		$model->setState('list.limit', $limit);
		$model->setState('list.start', $start);

		$result = $model->getItems();

		if (!count($result))
		{
			echo new JsonResponse(null, Text::_('COM_JLIKE_RATINGS_NO_CUSTOMER_REVIEWS'), true);
			$app->close();
		}
		else
		{
			$data['result'] = $result;
			$data['total'] = $model->getTotal();
			echo new JsonResponse($data);
			$app->close();
		}

		Factory::getApplication()->close();
	}
}
