<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
use Joomla\CMS\Factory;

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Filesystem\File;

/**
 * Jlike helper class
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       2.1
 */
class JticketingJlikeHelper
{
	public $jticketingmainhelper, $contentFormModel, $recommendationFormModel, $recommendationModel;
	/**
	 * Constructor
	 *
	 * @since   2.1
	 */
	public function __construct()
	{
		require_once JPATH_SITE . '/components/com_jticketing/helpers/main.php';
		$this->jticketingmainhelper = new jticketingmainhelper;

		// File inclusion will go here
		require_once JPATH_SITE . '/components/com_jlike/models/contentform.php';
		$this->contentFormModel = BaseDatabaseModel::getInstance('contentForm', 'JlikeModel');

		// Load jlike model recommendation form to call api
		$path = JPATH_SITE . '/components/com_jlike/models/recommendationform.php';

		$this->recommendationFormModel = "";

		if (File::exists($path))
		{
			if (!class_exists('JlikeModelRecommendationForm'))
			{
				JLoader::register('JlikeModelRecommendationForm', $path);
				JLoader::load('JlikeModelRecommendationForm');
			}

			$this->recommendationFormModel = new JlikeModelRecommendationForm;
		}

		// Load jlike model recommendation form to call api
		$path = JPATH_SITE . '/components/com_jlike/models/recommendations.php';

		$this->recommendationModel = "";

		if (File::exists($path))
		{
			if (!class_exists('JlikeModelRecommendations'))
			{
				JLoader::register('JlikeModelRecommendations', $path);
				JLoader::load('JlikeModelRecommendations');
			}

			$this->recommendationModel = new JlikeModelRecommendations;
		}
	}

	/**
	 * Function to insert or update todo status
	 *
	 * @param   array  $eventData  It contains data data required for todo
	 *
	 * @return  boolean
	 *
	 * @since   2.1
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function saveTodo($eventData)
	{
		extract($eventData);

		// TODO Insertion and updation operations will go here
		$date 	= Factory::getDate();
		$user 	= Factory::getUser();
		$data	= array();
		$link 	= "index.php?option=com_jticketing&view=event&id=" . $eventId;

		$contentData = array(
			'url' => $link,
			'element' => "com_jticketing.event",
			'element_id' => $eventId
		);

		if ($content_id = $this->contentFormModel->getConentId($contentData))
		{
			// Check for duplicate TODO, Call get todo api

			$data['content_id'] = $content_id;

			if (isset($assigned_to))
			{
				$data['assigned_to'] = $assigned_to;
			}

			if (isset($assigned_by))
			{
				$data['assigned_by'] = $assigned_by;
			}

			if (isset($user_id))
			{
				$data['user_id'] = $user_id;
			}

			$todos = $this->checkDuplicateTodo($data);

			// Setting up TODO data to insert or update

			$todoData = array();
			$todoData['content_id']  = $content_id;

			if (isset($assigned_to) && $assigned_to == $user->id)
			{
				$todoData['type'] = 'self';
			}
			else
			{
				$todoData['type'] = 'assign';
			}

			$todoData['context'] = '';
			$todoData['client'] = 'com_jticketing.event';

			list($plg_type, $plg_name) = explode(".", $todoData['client']);

			$todoData['plg_type'] = $plg_type;
			$todoData['plg_name'] = $plg_name;

			if (!empty($eventTitle))
			{
				$todoData['title'] = $eventTitle;
			}

			$todoData['sender_msg'] = '';

			$todoData['assigned_by'] = isset($assigned_by) ? $assigned_by : $user->id;

			$todoData['assigned_to'] = isset($assigned_to) ? $assigned_to : $user->id;

			$todoData['status'] = isset($status) ? $status : 'I';

			$todoData['created_date'] = $date->toSql(true);

			if (!empty($startDate))
			{
				$todoData['start_date'] = $startDate;
			}

			if (!empty($endDate))
			{
				$todoData['due_date'] = $endDate;
			}

			$todoData['parent_id'] = '0';
			$todoData['state'] = 1;

			if (count($todos))
			{
				// Update TODO if already exist
				$todoData['id'] = $todos[0]->id;
			}
			else
			{
				// Insert TODO
				$todoData['id']          = '';
				$todoData['status']      = 'I';
			}

			// Avoid php cs warning.
			if (!empty($notify) && $notify === true)
			{
				$todoData['notify'] = 1;
			}
			else
			{
				$todoData['notify'] = 0;
			}

			$todos_id = $this->recommendationFormModel->save($todoData);

			if (empty($todos_id))
			{
				return false;
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Function to delete todo
	 *
	 * @param   array  $eventData  It contains data data required for todo
	 *
	 * @return  boolean
	 *
	 * @since   2.1
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function deleteTodo($eventData)
	{
		extract($eventData);

		if (!isset($isEnrollment) && (int) $isEnrollment !== 1)
		{
			//  TODO deletion will go here for orders
			$orderData = $this->jticketingmainhelper->getorderinfo($orderId);
			$eventId   = $orderData['eventinfo']->id;
		}

		$link = "index.php?option=com_jticketing&view=event&id=" . $eventId;
		$contentData = array(
			'url' => $link,
			'element' => "com_jticketing.event",
			'element_id' => $eventId
		);

		if ($contentId = $this->contentFormModel->getConentId($contentData))
		{
			$data                = array();
			$data['content_id']  = $contentId;
			$data['assigned_to'] = $assigned_to;

			if (isset($assigned_by))
			{
				$data['assigned_by'] = (int) $assigned_by;
			}

			if (isset($user_id))
			{
				$data['user_id'] = $user_id;
			}

			$todos = $this->checkDuplicateTodo($data);

			if (!empty($todos['0']->id))
			{
				$TodoDeletionData       = array();
				$TodoDeletionData['id'] = $todos['0']->id;
				$deletedTodoId          = $this->recommendationFormModel->delete($TodoDeletionData);

				if (!$deletedTodoId)
				{
					return false;
				}
			}

			return true;
		}
	}

	/**
	 * Function to check duplicate todo
	 *
	 * @param   array  $eventData  It contains data data required for todo
	 *
	 * @return  boolean
	 *
	 * @since   2.1
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function checkDuplicateTodo($eventData)
	{
		extract($eventData);
		$user      = Factory::getUser();

		if (!isset($content_id))
		{
			return false;
		}

		$data['content_id'] = $content_id;
		$this->recommendationModel->setState("content_id", $data['content_id']);

		$data['type'] = isset($type)? $type : 'todos';
		$this->recommendationModel->setState("type", $data['type']);

		$data['context'] = isset($context)? $context : '';
		$this->recommendationModel->setState("context", $data['context']);

		$data['client'] = isset($client) ? $client : 'com_jticketing.event';
		list($plg_type, $plg_name) = explode(".", $data['client']);

		$data['plg_type'] = $plg_type;
		$data['plg_name'] = $plg_name;

		$data['supress_data'] = isset($supress_data) ? $supress_data : '';

		$data['status'] = isset($status) ? $status : '';
		$this->recommendationModel->setState("status", $data['status']);

		$data['assigned_to'] = isset($assigned_to) ? $assigned_to : $user->id;
		$this->recommendationModel->setState("assigned_to", $data['assigned_to']);

		$data['user_id'] = isset($user_id) ? $user_id : $user->id;
		$this->recommendationModel->setState("user_id", $data['user_id']);

		$data['state'] = isset($state) ? $state : 1;
		$this->recommendationModel->setState("state", $data['state']);

		$todos = $this->recommendationModel->getTodos($data);

		return $todos;
	}

	/**
	 * Function to create content
	 *
	 * @param   array  $contentData  It contains data data required for content
	 *
	 * @return  boolean
	 *
	 * @since   2.1
	 *
	 * @deprecated 3.2.0 The method is deprecated and will be removed in the next version.
	 */
	public function createContent($contentData)
	{
		return $this->contentFormModel->getContentID($contentData);
	}
}
