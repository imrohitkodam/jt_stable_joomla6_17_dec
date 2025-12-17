<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
/**
 * View for todo calendar
 *
 * @package     Jlike
 * @subpackage  component
 * @since       1.0
 */
class JlikeViewTodoCalendar extends HtmlView
{
	public $options;

	public $params;

	/**
	 * Method to display events
	 *
	 * @param   object  $tpl  tpl
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		$comjlikeIntegration = new comjlikeIntegrationHelper;
		$this->options = $comjlikeIntegration->getClientOptions();
		$app  = Factory::getApplication();
		$this->params = $app->getParams('com_jlike');
		$user = Factory::getUser();

		// Validate user login.
		if (!$user->id)
		{
			$msg = Text::_('COM_JLIKE_LOGIN_MSG');

			// Get current url.
			$current = Uri::getInstance()->toString();
			$url     = base64_encode($current);
			$app->enqueueMessage($msg, 'error');
			$app->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false));
		}

		parent::display($tpl);
	}
}
