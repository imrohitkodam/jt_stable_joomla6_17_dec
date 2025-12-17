<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Controller\BaseController;

require_once JPATH_ADMINISTRATOR . '/components/com_jticketing'. '/controller.php';

/**
 * Controller for mypayout to show payout
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingControllermypayouts extends BaseController
{
	/**
	 * Add new payout entry opens edit payout view
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function add()
	{
		$msg      = '';
		$redirect = Route::_('index.php?option=com_jticketing&view=mypayouts&layout=edit_payout', false);
		$this->setRedirect($redirect, $msg);
	}

	/**
	 * Add new payout entry
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function save()
	{
		JRequest::checkToken() or jexit('Invalid Token');

		// Get model
		$model = $this->getModel('mypayouts');
		$post = Factory::getApplication()->getInput()->get('post');
		$obj  = new stdClass;

		if (!empty($post['edit_id']))
		{
			$result = $model->editPayout();
		}
		else
		{
			$result = $model->savePayout();
		}

		$redirect = Route::_('index.php?option=com_jticketing&view=mypayouts', false);

		if ($result)
		{
			$msg = Text::_('COM_JTICKETING_PAYOUT_SAVED');
		}
		else
		{
			$msg = Text::_('COM_JTICKETING_PAYOUT_ERROR_SAVING');
		}

		$this->setRedirect($redirect, $msg);
	}

	/**
	 * Cancels payout
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_jticketing');
	}

	/**
	 * Publish payout data
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function publish()
	{
		$input = Factory::getApplication()->getInput();
		$post  = $input->post;

		// Get some variables from the request
		$cid = $input->get('cid', array(), 'post', 'array');
		ArrayHelper::toInteger($cid);
		$model = $this->getModel('mypayouts');

		if ($model->setItemState($cid, 1))
		{
			$msg = Text::_('AMOUNT_PAID_S');
		}
		else
		{
			$msg = $model->getError();
		}

		$this->setRedirect('index.php?option=com_jticketing&view=mypayouts&layout=default', $msg);
	}

	/**
	 * Unpublish payout data
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function unpublish()
	{
		$input = Factory::getApplication()->getInput();

		// Get some variables from the request
		$cid = $input->get('cid', '', 'array');
		ArrayHelper::toInteger($cid);
		$model = $this->getModel('mypayouts');

		if ($model->setItemState($cid, 0))
		{
			$msg = Text::_('AMOUNT_UNPAID_S');
		}
		else
		{
			$msg = $model->getError();
		}

		$this->setRedirect('index.php?option=com_jticketing&view=mypayouts&layout=default', $msg);
	}

	/**
	 * Remove payout data
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function remove()
	{
		$post    = Factory::getApplication()->getInput()->get('post');
		$payeeid = $post['cid'];
		$model   = $this->getModel('mypayouts');
		$confrim = $model->delete_payout($payeeid);

		if ($confrim)
		{
			$msg = Text::_('PAOUT_DELETED_SCUSS');
		}
		else
		{
			$msg = Text::_('PAOUT_DELETED_ERROR');
		}

		// Joomla 6: JVERSION check removed
		if (false) // Legacy >= '1.6.0')
		{
			$this->setRedirect(Uri::base() . "index.php?option=com_jticketing&view=mypayouts", $msg);
		}
		else
		{
			$this->setRedirect(Uri::base() . "index.php?option=com_jticketing&view=mypayouts", $msg);
		}
	}
}
