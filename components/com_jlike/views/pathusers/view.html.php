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


/**
 * View to edit
 *
 * @since  1.6
 */
class JLikeViewPathUsers extends HtmlView
{
	protected $state;

	protected $item;

	protected $form;

	protected $params;

	protected $canSave;

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

		// Validate user login
		if (!$this->user->id)
		{
			$current = Uri::getInstance()->toString();
			$url     = base64_encode($current);
			$this->app->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false));
		}

		$this->state      = $this->get('State');
		$this->items = $this->get('Items');

		$this->pagination = $this->get('Pagination');
		$this->params     = $this->app->getParams('com_jlike');

		$this->filterForm = $this->get('FilterForm');

		$this->activeFilters = $this->get('ActiveFilters');

		$model = $this->getModel();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		parent::display($tpl);
	}
}
