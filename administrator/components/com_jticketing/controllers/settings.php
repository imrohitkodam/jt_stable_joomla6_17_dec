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
use Joomla\CMS\Session\Session;

/**
 * TaxHelper helper
 *
 * @package     Jticketing
 * @subpackage  site
 * @since       2.2
 */
class JticketingControllerSettings extends jticketingController
{
	/**
	 * Display.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function save()
	{
		$msg = '';
		Session::checkToken() or jexit('Invalid Token');
		$model = $this->getModel('settings');
		$input = Factory::getApplication()->getInput();
		$post  = $input->post;
		$task  = $input->get('task');
		$model->setState('request', $post);

		switch ($task)
		{
			case 'cancel':
				$this->setRedirect('index.php?option=com_jticketing');
				break;

			case 'save':
				if ($model->store($post))
				{
					$msg = Text::_('CONFIG_SAVED');
				}
				else
				{
					$msg = Text::_('CONFIG_SAVE_PROBLEM');
				}
				break;
		}

		$this->setRedirect("index.php?option=com_jticketing&view=settings", $msg);
	}
}
