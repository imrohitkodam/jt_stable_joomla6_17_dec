<?php
/**
 * @package    Techjoomla.Libraries
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2021 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

use \Joomla\Registry\Registry;
require_once JPATH_ADMINISTRATOR . '/components/com_tjcertificate/includes/tjcertificate.php';

/**
 * TjQueue
 *
 * @package     Techjoomla.Libraries
 * @subpackage  Tjqueue
 * @since       __DEPLOY_VERSION__
 */
class TjqueueCertificateRecords
{
	/**
	 * Plugin method with the same name as the event will be called automatically.
	 *
	 * @param   string  $message  A Message
	 *
	 * @return  boolean  This method should return acknowledgement flag
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function consume($message)
	{
		$messageBody      = $message->getBody();
		$messageData      = new Registry($messageBody);
		$messageData      = $messageData->toArray();
		$certificateModel = TJCERT::model('Certificate', array('ignore_request' => true));
		$certificateModel->save($messageData);

		return true;
	}
}
