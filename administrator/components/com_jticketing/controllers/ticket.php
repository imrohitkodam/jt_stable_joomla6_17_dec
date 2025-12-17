<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

require_once JPATH_ADMINISTRATOR . '/components/com_jticketing'. '/controller.php';

/**
 * JticketingController helper
 *
 * @package     Jticketing
 * @subpackage  site
 * @since       2.2
 */
class JticketingControllerticket extends BaseController
{
	/**
	 * setRefund.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function setRefund()
	{
		$data  = Factory::getApplication()->getInput()->get('post');

		// $oid	= Factory::getApplication()->getInput()->get( 'oid', array(), 'post', 'array' );
		$model = $this->getModel('ticket');
		$val   = $model->processRefund($data);

		if ($val != 0)
		{
			$msg = Text::_('REFUND_SUCESSFULLY');
			$this->setRedirect('index.php?option=com_jticketing&view=ticket', $msg);
		}
		else
		{
			$msg = Text::_('REFUND_ERROR');
			$this->setRedirect('index.php?option=com_jticketing&view=ticket&layout=refund&id=' . $oid, $msg);
		}
	}

	/**
	 * setTransfer.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function setTransfer()
	{
		$data  = Factory::getApplication()->getInput()->get('post');

		// $oid	= Factory::getApplication()->getInput()->get( 'oid', array(), 'post', 'array' );
		$model = $this->getModel('ticket');
		$val   = $model->processTransfer($data);

		if ($val != 0)
		{
			$msg = Text::_('TRANSFER_SUCESSFULLY');
			$this->setRedirect('index.php?option=com_jticketing&view=ticket', $msg);
		}
		else
		{
			$msg = Text::_('TRANSFER_ERROR');
			$this->setRedirect('index.php?option=com_jticketing&view=ticket&layout=transfer&id=' . $oid, $msg);
		}
	}
}
