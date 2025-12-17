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
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;


/**
 * Reminder controller class.
 *
 * @since  1.6
 */
class JlikeControllerReminder extends FormController
{
	/**
	 * The extension for which the categories apply.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $extension;

	/**
	 * Constructor
	 *
	 * @throws Exception
	 */
	public function __construct()
	{
		$this->view_list = 'reminders';
		parent::__construct();

		// Guess the Text message prefix. Defaults to the option.
		if (empty($this->extension))
		{
			$this->extension = $this->input->get('extension', 'com_content');
		}
	}

	/**
	 * Get all contents of the selected content type this will get called onchange of content_type.
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	public function getContentByType()
	{
		$jinput           = Factory::getApplication()->getInput();
		$content_type     = $jinput->get('content_type', '');

		$reminder_id      = $jinput->get('reminder_id', '');
		$model            = $this->getModel('reminder');
		$selected_content = $model->getContentByType($content_type, $reminder_id);
		echo json_encode($selected_content);
		Factory::getApplication()->close();
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   1.6
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$append = parent::getRedirectToItemAppend($recordId);
		$append .= '&extension=' . $this->extension;

		return $append;
	}

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   1.6
	 */
	protected function getRedirectToListAppend()
	{
		$append = parent::getRedirectToListAppend();
		$append .= '&extension=' . $this->extension;

		return $append;
	}
}
