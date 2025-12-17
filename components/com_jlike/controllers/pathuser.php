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
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

if (!class_exists('TjNotifications')) { require_once JPATH_LIBRARIES . '/techjoomla/tjnotifications/tjnotifications.php'; }
if (file_exists(JPATH_SITE . '/components/com_jlike/models/pathuser.php')) {
	require_once JPATH_SITE . '/components/com_jlike/models/pathuser.php';
}
if (file_exists(JPATH_SITE . '/components/com_jlike/models/paths.php')) {
	require_once JPATH_SITE . '/components/com_jlike/models/paths.php';
}

/**
 * subscription controller class.
 *
 * @since  1.6
 */
class JlikeControllerPathUser extends BaseController
{
	protected $app;

	protected $path_id;

	protected $userId;

	protected $status;

	protected $isajax;

	protected $loggedInUser;

	protected $response;

	protected $redirectMsg;

	/**
	 * Constructor
	 *
	 * @throws Exception
	 */
	public function __construct()
	{
		$this->app = Factory::getApplication();
		$jinput = $this->getApplication()->input;
		$this->path_id  = $jinput->get('path_id');
		$this->userId  = $jinput->get('user_id');
		$this->status  = $jinput->get('status');

		if (empty($this->path_id))
		{
			$this->path_id  = $jinput->post->get('path_id', '', 'int');
			$this->status  = $jinput->post->get('status', '', 'STRING');
		}

		if (empty($this->userId))
		{
			$this->userId = $jinput->post->get('user_id', '', 'int');
		}

		$this->isajax = ($jinput->server->get('HTTP_X_REQUESTED_WITH', '0', 'filter') == 'XMLHttpRequest') ? true : false;
		$this->loggedInUser = Factory::getUser();

		$this->userId = !empty($this->userId) ? $this->userId : $this->loggedInUser->id;

		parent::__construct();
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    Optional. Model name
	 * @param   string  $prefix  Optional. Class prefix
	 * @param   array   $config  Optional. config array
	 *
	 * @return  object  The Model
	 *
	 * @since    1.6
	 */
	public function &getModel( $name = 'PathUser', $prefix = 'JlikeModel', $config = array())
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	/**
	 * Method to save a record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since   12.2
	 */
	public function save($key = null, $urlVar = null)
	{
		$menu     = $this->getApplication()->getMenu();
		$menuItem = $menu->getItems('link', 'index.php?option=com_jlike&view=pathdetail&path_id=' . $this->path_id, true);

		$link['link'] = Route::_('index.php?option=com_jlike&view=pathdetail&path_id=' . $this->path_id . '&Itemid=' . $menuItem->id, false);

		$data = array();
		$data['path_id'] = $this->path_id;
		$data['user_id'] = $this->loggedInUser->id;
		$data['status'] = $this->status;

		$model = $this->getModel();

		try
		{
			if ($model->save($data))
			{
				$this->response = $data;
				$redirectUrl = $link['link'];
			}
			else
			{
				$this->response = Text::_('JERROR_ALERTNOAUTHOR');

				$this->redirectMsg = $this->getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');

				$redirectUrl = 'index.php';
			}
		}
		catch (Exception $e)
		{
			$this->response = $e;
			$redirectUrl = 'index.php';

			$this->redirectMsg = $e->getMessage();
		}

		if ($this->isajax)
		{
			echo new JsonResponse($this->response);
			Factory::getApplication()->close();
		}
		else
		{
			$this->getApplication()->enqueueMessage($this->redirectMsg);
			$this->getApplication()->redirect($redirectUrl);
		}
	}

	/**
	 * Method to confirm Path.
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since   12.2
	 */
	public function selfConfirmPath()
	{
		// Get path params
		$pathsModel = BaseDatabaseModel::getInstance('Paths', 'JlikeModel');
		$pathsModel->setState('filter.path_id', $this->path_id);
		$pathsModel->setState('filter.state', '1');
		$PathDetails = $pathsModel->getItems();
		$pathParams = json_decode($PathDetails[0]->params);
		$data = array();

		$canCompletePath = $this->loggedInUser->authorise('core.path.complete', 'com_jlike');

		// Check access for self approval
		if (($this->userId && $pathParams->core->approval === 'self') || $canCompletePath)
		{
			$menu   = $this->getApplication()->getMenu();
			$pathUserModel = BaseDatabaseModel::getInstance('PathUser', 'JLikeModel');
			$pathUserDetails = $pathUserModel->getPathUserDetails($this->path_id, $this->userId);
			$data['path_user_id'] = $pathUserDetails->path_user_id;

			$pathsNodeModel = BaseDatabaseModel::getInstance('Pathnodegraphs', 'JlikeModel');
			$pathsNodeModel->setState('filter.node_content', $this->path_id);
			$pathsNodeModel->setState('filter.isPath', 1);
			$pathsNodeDetails = $pathsNodeModel->getItems();
			$baseSubPathId = $pathsNodeDetails[0]->path_id;

			$data['path_id'] = $this->path_id;
			$data['user_id'] = $this->userId;
			$data['status'] = 'C';
			$data['cpj_status'] = 'A';

			$menuItem = $menu->getItems('link', 'index.php?option=com_jlike&view=pathdetail&path_id=' . $baseSubPathId, true);

			// If base sub path is not available then redirect to parent path
			if (empty($baseSubPathId))
			{
				$baseSubPathId = $this->path_id;
			}

			$link['link'] = Route::_('index.php?option=com_jlike&view=pathdetail&path_id=' . $baseSubPathId . '&Itemid=' . $menuItem->id, false);

			try
			{
				$model = $this->getModel();

				if ($model->save($data))
				{
					// Need to Move to the proper location. Mail will be send only after application form submit
					// Need to discuss about TJnotification trigger
					$this->response = $data;
					$redirectUrl = $link['link'];
					$redirectMsg = Text::_('COM_JLIKE_CONFIRM_PATH_MESSAGE');
				}
				else
				{
					$redirectUrl = $link['link'];
					$redirectMsg = Text::_('COM_JLIKE_MY_LIKE_ERROR_MSG');
				}
			}
			catch (Exception $e)
			{
				$this->response = $e;
				$redirectUrl = '';
				$redirectMsg = $e->getMessage();
			}

			if ($this->isajax)
			{
				echo new JsonResponse($this->response);
				Factory::getApplication()->close();
			}
			else
			{
				$this->getApplication()->enqueueMessage($redirectMsg);
				$this->getApplication()->redirect($redirectUrl);
			}
		}
	}
}
