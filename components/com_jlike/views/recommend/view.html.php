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
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\Filesystem\File;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Plugin\PluginHelper;


/**
 * View class for a list of JLike.
 *
 * @since  1.0.0
 */
class JlikeViewrecommend extends HtmlView
{
	protected $state;

	protected $items;

	protected $pagination;

	protected $logged_userid;

	protected $peopleToRecommend;

	protected $element;

	protected $techJoomlaCommonPath;

	protected $TechjoomlaCommon;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		$techJoomlaCommonPath = JPATH_SITE . '/libraries/techjoomla/common.php';
		$this->TechjoomlaCommon = "";

		if (File::exists($techJoomlaCommonPath))
		{
			if (!class_exists('TechjoomlaCommon'))
			{
				JLoader::register('TechjoomlaCommon', $techJoomlaCommonPath);
				JLoader::load('TechjoomlaCommon');
			}

			$this->TechjoomlaCommon = new TechjoomlaCommon;
		}

		$app   = Factory::getApplication();
		$input = Factory::getApplication()->getInput();
		$this->state		= $this->get('State');
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');

		$this->logged_userid = Factory::getUser()->id;

		if (!$this->logged_userid)
		{
			$msg = Text::_('COM_JLIKE_LOGIN_MSG');
			$uri = $input->server->get('REQUEST_URI', '', 'STRING');
			$url = base64_encode($uri);
			$app->enqueueMessage($msg, 'error');
			$app->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false));
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		$this->peopleToRecommend = $this->items;

		// Get selected content/element data
		$this->element = $this->_getElementData();

		parent::display($tpl);
	}

	/**
	 * Get Element Data
	 *
	 * @return  Array  Element data
	 *
	 * @since  1.5
	 *
	 */
	public function _getElementData()
	{
		$input = Factory::getApplication()->getInput();
		$plg_type            = $input->get('plg_type', 'content');
		$plg_name            = $input->get('plg_name', '');
		$elementId           = $input->get('id', '', 'INT');
		$element             = $input->get('element', '', 'INT');

		// Get URL and title form respective component
		PluginHelper::importPlugin($plg_type, $plg_name);
		$elementdata = Factory::getApplication()->triggerEvent('onAfter' . $plg_name . 'GetElementData', array($elementId));

		return $elementdata[0];
	}
}
