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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;

/**
 * View to edit
 *
 * @since  1.8
 */
class JticketingViewVenue extends HtmlView
{
	protected $state;

	protected $item;

	protected $form;

	protected $params;

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
		$this->state  = $this->get('State');
		$this->item   = $this->get('Item');
		$this->form   = $this->get('Form');
		$this->params = ComponentHelper::getParams('com_jticketing');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		$this->isAdmin = 1;

		$path = JPATH_SITE . '/components/com_jticketing/helpers/common.php';

		if (!class_exists('JticketingCommonHelper'))
		{
			JLoader::register('JticketingCommonHelper', $path);
			JLoader::load('JticketingCommonHelper');
		}

		// Call helper function
		JticketingCommonHelper::getLanguageConstant();

		$this->existingScoUrl  = '';
		$this->editId          = $this->item->id;
		$this->existingParams  = $this->item->params;
		$this->mediaGalleryObj = 0;

		if (isset($this->item->gallery))
		{
			$this->mediaGalleryObj = json_encode($this->item->gallery);
		}

		// Venue gallery show hide setting
		$this->showVenueGallery = $this->params->get('venue_gallery', 0);

		// Event detail view resized image setting
		$this->venueGalleryImage = $this->params->get('admin_venue_gallery_view', 'media_s');

		// Get component params
		$this->googleMapApiKey = $this->params->get('google_map_api_key');
		$this->mediaSize       = $this->params->get('jticketing_media_size', '15');

		if (!empty($this->googleMapApiKey))
		{
			$this->googleMapLink = 'https://maps.googleapis.com/maps/api/js?libraries=places&key=' . $this->googleMapApiKey;
		} else {
			$this->googleMapLink = 'https://maps.googleapis.com/maps';
		}

		$this->EnableOnlineEvents = $this->params->get('enable_online_events');

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->getInput()->set('hidemainmenu', true);

		$user  = Factory::getUser();
		$isNew = ($this->item->id == 0);

		if ($isNew)
		{
			$viewTitle = Text::_('COM_JTICKETING_TITLE_VENUES');
		}
		else
		{
			$viewTitle = Text::_('COM_JTICKETING_TITLE_VENUES');
		}

		if (isset($this->item->checked_out))
		{
			$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		}
		else
		{
			$checkedOut = false;
		}

		$canDo = JTicketingHelper::getActions();
		ToolbarHelper::title($viewTitle, 'pencil-2');

		// If not checked out, can save the item.
		ToolbarHelper::apply('venue.apply', 'JTOOLBAR_APPLY');
		ToolbarHelper::save('venue.save', 'JTOOLBAR_SAVE');

		if (!$checkedOut && ($canDo->{'core.create'}))
		{
			ToolbarHelper::custom('venue.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}

		if (empty($this->item->id))
		{
			ToolbarHelper::cancel('venue.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			ToolbarHelper::cancel('venue.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
