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
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Helper\ContentHelper;

/**
 * View class for a list of enrollments.
 *
 * @since  2.1
 */
class JTicketingViewEnrollment extends HtmlView
{
	protected $items;

	protected $pagination;

	protected $state;

	public $filterForm;

	public $activeFilters;

	public $eventoptions;

	public $canDo;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		$app          = Factory::getApplication();
		$user         = Factory::getUser();
		$this->canDo  = ContentHelper::getActions('com_jticketing');

		if ($user->get('guest'))
		{
			$return = base64_encode(Uri::getInstance());
			$login_url_with_return = Route::_('index.php?option=com_users&return=' . $return);
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'notice');
			$app->redirect($login_url_with_return, 403);
		}

		if (!($this->canDo->get('core.enrollall') || $this->canDo->get('core.enrollown')))
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'notice');

			return false;
		}

		$this->state         = $this->get('State');
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		FormHelper::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models/fields/');
		$events             = FormHelper::loadFieldType('events', false);
		$this->eventoptions = $events->getOptionsExternally();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		JticketingCommonHelper::getLanguageConstant();

		parent::display($tpl);
	}
}
