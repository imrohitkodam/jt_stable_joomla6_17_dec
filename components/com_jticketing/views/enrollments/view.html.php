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
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;

if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/main.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/main.php'; }

/**
 * View class for a list of Jticketing.
 *
 * @since  2.1
 */
class JticketingViewEnrollments extends HtmlView
{
	protected $items;

	protected $pagination;

	protected $state;

	protected $canDo;

	public $filterForm;

	public $activeFilters;

	public $sidebar;

	public $extra_sidebar;

	public $modal_params;

	public $body;

	public $toolbarHTML;

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
		$this->canDo  = ContentHelper::getActions('com_jticketing');

		if (!($this->canDo->get('core.enrollall') || $this->canDo->get('core.enrollown')))
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'notice');

			return false;
		}

		$this->state         = $this->get('State');
		$this->items	     = $this->get('Items');
		$this->pagination	 = $this->get('Pagination');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Modal pop up for mass enrollment params
		$this->modal_params           = array();
		$this->modal_params['height'] = "500px";
		$this->modal_params['width']  = "500px";
		$this->modal_params['url']    = Uri::base(true) . '/index.php?option=com_jticketing&view=enrollment&tmpl=component';
		$this->body                   = "";

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		// Load backend languages
		$lang         = Factory::getLanguage();
		$extension    = 'com_jticketing';
		$base_dir     = JPATH_ADMINISTRATOR;
		$language_tag = '';
		$reload       = true;
		$lang->load($extension, $base_dir, $language_tag, $reload);

		// Jt toolbar
		$this->addTJtoolbar();

		parent::display($tpl);
	}

	/**
	 * Setup ACL based tjtoolbar
	 *
	 * @return  void
	 *
	 * @since   2.2
	 */
	protected function addTJtoolbar()
	{
		$canDo = $this->canDo;

		// Add toolbar buttons
		if (file_exists(JPATH_LIBRARIES . '/techjoomla/tjtoolbar/toolbar.php')) { require_once JPATH_LIBRARIES . '/techjoomla/tjtoolbar/toolbar.php'; }
		$tjbar = TJToolbar::getInstance('tjtoolbar', 'pull-right float-end');
		$title = Text::_('COM_JTICKETING_TITLE_ENROLLMENTS_NEW');

		if ($canDo->{'core.enrollall'} || $canDo->{'core.enrollown'})
		{
			// No use of enroll.save
			$tjbar->appendButton('custom.new', $title, '', 'class="af-d-block  af-ml-10 btn btn-sm btn-success" href="#myModal" data-toggle="modal"');
		}

		$this->toolbarHTML = $tjbar->render();
	}
}
