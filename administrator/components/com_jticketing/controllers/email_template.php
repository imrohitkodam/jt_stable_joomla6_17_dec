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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Class for Jticketing Attendee List Model
 *
 * @package  JTicketing
 * @since    1.5
 */
class JticketingControllerEmail_Template extends BaseController
{
	/**
	 * Save function
	 *
	 * @return void
	 */
	public function save()
	{
		$model	= $this->getModel('email_template');

		if ($model->store())
		{
			$msg = Text::_('MENU_ITEM_SAVED');
		}
		else
		{
			$msg = Text::_('ERROR_SAVING_MENU_ITEM');
		}

		$this->setRedirect('index.php?option=com_jticketing&view=email_template');
	}

	/**
	 * Cancel function
	 *
	 * @return void
	 */
	public function cancel()
	{
		$input = Factory::getApplication()->getInput();

		switch ($input->get('task'))
		{
			case 'cancel':
			$this->setRedirect('index.php?option=com_jticketing&view=email_template');
		}
	}
}
