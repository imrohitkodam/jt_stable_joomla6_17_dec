<?php
/**
 * @package    ActivityStream
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access to this file
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * ActivityStream View
 *
 * @since  0.0.1
 */
class ActivityStreamViewActivities extends HtmlView
{
	protected $input;

	/**
	 * Display the ActivityStream view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return   mixed  A string if successful, otherwise an Error object.
	 */

	public function display($tpl = null)
	{
		// To show all types of activity in list view
		$activitiesModel     = $this->getModel();
		$activitiesModel->setState("type", 'all');
		$this->input         = Factory::getApplication()->input;
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \RuntimeException(implode('<br />', $errors), 500);
		}

		// Set the tool-bar and number of found items
		$this->addToolbar();

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		$title        = Text::_('Activity Stream');
		$languageFile = Factory::getLanguage();
		$extension    = Factory::getApplication()->input->get('client', '', 'STRING');
		$base_dir     = JPATH_BASE;

		// Load the language file for particular extension

		if ($extension)
		{
			$languageFile->load($extension, $base_dir);
		}

		/*if ($this->pagination->total)
		{
			$title .= "<span style='font-size: 0.5em; vertical-align: middle;'>(" . $this->pagination->total . ")</span>";
		}*/

		ToolbarHelper::title($title, 'list');
		ToolbarHelper::addNew('activity.add');
		ToolbarHelper::editList('activity.edit');
		ToolbarHelper::publish('activity.publish');
		ToolbarHelper::unpublish('activity.publish');
		ToolbarHelper::deleteList('', 'activities.delete');
	}

	/**
	 * Method to order fields
	 *
	 * @return void
	 */
	protected function getSortFields()
	{
		return array(
			'id'           => Text::_('COM_ACTIVITYSTREAM_ACTIVITY_ID'),
			'state'        => Text::_('COM_ACTIVITYSTREAM_ACTIVITY_STATE'),
			'type'         => Text::_('COM_ACTIVITYSTREAM_ACTIVITY_TYPE'),
			'created_date' => Text::_('COM_ACTIVITYSTREAM_ACTIVITY_CREATED_DATE'),
			'updated_date' => Text::_('COM_ACTIVITYSTREAM_ACTIVITY_UPDATED_DATE')
		);
	}
}
