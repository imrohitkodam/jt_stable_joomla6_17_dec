<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die();

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\Utilities\ArrayHelper;

/**
 * jLikesController  Controller
 *
 * @package     Jlike
 * @subpackage  site
 * @since       2.2
 */
class JLikeControllerlikes extends BaseController
{
	protected $view_list;

	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	/*public function &getModel($name = 'likes', $prefix = 'JlikeModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}*/

	/**
	 * Delete like from my like view.
	 *
	 * @return  void
	 *
	 * @since   1.3
	 */
	public function delete()
	{
		$app   = Factory::getApplication();
		$input = Factory::getApplication()->getInput();

		// Get like ontent id
		$cid = $input->get('cid', '', 'array');
		ArrayHelper::toInteger($cid);

		$model        = $this->getModel('likes');
		$successCount = $model->delete($cid);

		if ($successCount && $successCount >= 1)
		{
			$msg = Text::sprintf(Text::_('COM_JLIKE_LIKE_DELETED', true), $successCount);
		}
		else
		{
			$msg = Text::_('COM_JLIKE_LIKE_DEL_ERROR', true) . '</br>' . $model->getError();
		}

		$comjlikeHelper = new comjlikeHelper;
		$itemid         = $comjlikeHelper->getitemid('index.php?option=com_jlike&view=likes&layout=my');
		$redirect       = Route::_('index.php?option=com_jlike&view=likes&layout=my&Itemid=' . $itemid, false);

		// Called from Ajax ?
		$ajaxCall = $input->get('ajaxCall', 0, 'INT');

		if ($ajaxCall === 1)
		{
			$res['msg'] = $msg;

			// $res['status'] = 1;
			echo json_encode($res);
			$app->close();
		}
		else
		{
			$this->setMessage($msg);
			$this->setRedirect($redirect);
		}
	}

	/**
	 * Update the note from from my like view.
	 *
	 * @return  void
	 *
	 * @since   1.3
	 */
	public function updateNote()
	{
		$app   = Factory::getApplication();
		$data  = $app->getInput()->post->get('form', array(), 'array');
		$model = $this->getModel('likes');

		$data['user_id'] = Factory::getUser()->id;
		$res['status']   = $model->updateMyNote($data);

		if ($res)
		{
			$res['msg'] = Text::sprintf(Text::_('COM_JLIKE_LIKE_UPDATED_NOTE', true));
		}
		else
		{
			$res['msg'] = Text::_('COM_JLIKE_LIKE_UPDATE_ERROR', true) . '</br>' . $model->getError();
		}

		echo json_encode($res);
		$app->close();
	}

	/**
	 * Update the lables from from my like.
	 *
	 * @return  void
	 *
	 * @since   1.3
	 */
	public function updateMyLikeLables()
	{
		$user         = Factory::getUser();
		$app          = Factory::getApplication();
		$post         = $app->getInput()->post;
		$selectedLabs = $post->get('labelList', array(), 'ARRAY');
		$content_id   = $post->get('content_id', '', 'INT');

		$comjlikeHelper = new comjlikeHelper;
		$res['status']  = $comjlikeHelper->mapLikeWithLable($user->id, $content_id, $selectedLabs);

		echo json_encode($res);
		$app->close();
	}

	/**
	 * Using this function, users like will be mailed.
	 *
	 * @return  void
	 *
	 * @since   1.3
	 */
	public function mailMyLikes()
	{
		$app    = Factory::getApplication();
		$jinput = Factory::getApplication()->getInput();
		$post   = $jinput->post;
		$model  = $this->getModel('likes');

		// $res = $model->mailLikes();
		$res['status'] = '';
		$cid           = $post->get('cid', array(), 'ARRAY');
		$cids          = implode(',', $cid);

		if (!empty($cids))
		{
			// Setting the content_id in session.
			$session = Factory::getSession();
			$session->set('jlikeContentIds', $cids);
		}
		else
		{
			return;
		}

		// If (!empty($res['status']))
		{
			$com_invitex_installed = 0;

			// Check if JLike is installed
			if (File::exists(JPATH_ROOT . '/components/com_invitex/invitex.php'))
			{
				if (ComponentHelper::isEnabled('com_jlike', true))
				{
					$com_invitex_installed = 1;
				}
			}

			if ($com_invitex_installed === 0)
			{
				return;
			}

			$path             = JPATH_SITE . "/components/com_invitex/helper.php";

			if (!class_exists("cominvitexHelper"))
			{
				if (file_exists($path)) {
					require_once $path;
				}
			}

			$cominvitexHelper = new cominvitexHelper;
			$invite_type      = $cominvitexHelper->geTypeId_By_InernalName('jlike_likeditem');
			$invite_url       = '';
			$tempUrl          = 'index.php?option=com_invitex&view=invites&catch_act=&invite_type=' . $invite_type;

			$redirect = $tempUrl . '&invite_url=' . $invite_url . '&invite_anywhere=1';
		}

		// Called from Ajax ?
		$ajaxCall = $jinput->get('ajaxCall', 0, 'INT');

		if ($ajaxCall === 1)
		{
			$res['msg']     = '';
			$res['status']  = 1;
			$res['nextUrl'] = $redirect;
			echo json_encode($res);
			$app->close();
		}
		else
		{
			$this->setRedirect($redirect);
		}
	}
}
