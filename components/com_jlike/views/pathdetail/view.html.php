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
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;

JLoader::import('components.com_jlike.models.pathuser', JPATH_SITE);

/**
 * View to edit
 *
 * @since  1.6
 */
class JlikeViewpathdetail extends HtmlView
{
	protected $state;

	protected $item;

	protected $form;

	protected $params;

	protected $app;

	protected $user;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$this->app  = Factory::getApplication();
		$this->user = Factory::getUser();

		$this->state  = $this->get('State');
		$this->item   = $this->get('Data');

		$this->params = $this->app->getParams('com_jlike');

		if (!empty($this->item))
		{
			$this->form = $this->get('Form');
		}

		// Validate user login
		if (!$this->user->id)
		{
			$current = Uri::getInstance()->toString();
			$url     = base64_encode($current);
			$this->app->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false));
		}

		// Check if user subscribed to the path
		$pathUserModel = BaseDatabaseModel::getInstance('PathUser', 'JLikeModel');
		$isSubscribed = $pathUserModel->getPathUserDetails($this->item->path_id, $this->user->id);

		if (empty($isSubscribed))
		{
		$this->app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
		$this->app->redirect('index.php');
		}

		// Check the view access to the path detail view.
		if ($this->item->get('access-view') == false)
		{
		$this->app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
		$this->app->redirect('index.php');
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		if ($this->_layout == 'edit')
		{
			if ($this->user->authorise('core.create', 'com_jlike') !== true)
			{
				throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'));
			}
		}

		parent::display($tpl);
	}
}
