<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;

/**
 * Class for Jticketing Event view
 *
 * @package  JTicketing
 * @since    1.5
 */
class JticketingViewEvent extends HtmlView
{
	public $enable_tags;

	protected $state;

	protected $item;

	protected $params;

	protected $utilities;

	protected $integration;

	protected $joomlaFieldsData;

	public $redirect_tags_to_events;

	public $buyTicketItemId;

	public $allEventsItemid;

	public $userid;

	public $showComments;

	public $showLikeButtons;

	/**
	 * Method to display event
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		$app  = Factory::getApplication();
		$user = Factory::getUser();
		$this->params = JT::config();
		$this->integration = $this->params->get('integration', '', 'INT');
		if (file_exists(JPATH_SITE . '/components/com_jticketing/helpers/common.php')) { require_once JPATH_SITE . '/components/com_jticketing/helpers/common.php'; }
		$this->jtCommonHelper = new JticketingCommonHelper;
		$this->jticketingfrontendhelper = new jticketingfrontendhelper;
		$this->jticketingmainhelper     = new jticketingmainhelper;
		$input                          = $app->input;
		$this->userid                   = $user->id;
		$eventid                        = $input->get('id', '', 'INT');
		$this->event = JT::event($eventid);
		$this->utilities = JT::utilities();
		$this->fieldsIntegration        = $this->params->get('fields_integrate_with', '', 'INT');

		Factory::getApplication()->triggerEvent('onGetActivityScript', array('eventfeed'));

		$config = Factory::getConfig();
		$this->siteName = $config->get('sitename');

		// Native Event Manager.
		if ($this->integration < 1)
		{
			?>
			<div class="alert alert-info alert-help-inline">
		<?php echo Text::_('COMJTICKETING_INTEGRATION_NOTICE');
		?>
			</div>
		<?php
			return false;
		}

		$this->state  = $this->get('State');
		$item   = $this->get('Data');
		$this->item = "";
		$groups = $user->getAuthorisedViewLevels();

		if ((!empty($item->access) && in_array($item->access, $groups)) || empty($item->access))
		{
			$this->item = $item;
		}

		if (empty($this->item) || empty($this->item->id))
		{
		?>
			<div class="alert alert-info alert-help-inline">
				<?php echo Text::_('COM_JTICKETING_USER_UNAUTHORISED');?>
			</div>
		<?php

			return false;
		}

		if ($item->state != '1')
		{
			$app->enqueueMessage(Text::_('COM_JTICKETING_EVENT_NOT_FOUND'), 'error');

			return false;
		}

		if (!empty($this->item->catid))
		{
			$catNm = $this->getModel()->getCategoryName($this->item->catid);

			if (!empty($catNm))
			{
				$this->item->category_id_title = $catNm->title;
			}
		}

		if ($this->item->venue != 0)
		{
			$eventVenueData	= JT::model('venueform')->getItem($this->item->venue);

			$this->item->venueVideoData = array();
			$this->item->venueImageData = array();

			if ($this->params->get('venue_gallery', '', 'INT') && !empty($eventVenueData->gallery))
			{
				if (!empty($eventVenueData->gallery))
				{
					$this->item->venueGallery = $eventVenueData->gallery;
					$galleryCount             = count($this->item->venueGallery);

					if ($galleryCount)
					{
						for ($i = 0; $i <= $galleryCount; $i++)
						{
							if (isset($this->item->venueGallery[$i]->type))
							{
								$venueContentType = substr($this->item->venueGallery[$i]->type, 0, 5);

								if ($venueContentType == 'image')
								{
									$this->item->venueImageData[$i] = $this->item->venueGallery[$i];
								}
								elseif ($venueContentType == 'video')
								{
									$this->item->venueVideoData[$i] = $this->item->venueGallery[$i];
								}
							}
						}
					}
				}
			}

			$this->venueName          = $eventVenueData->name;
			$this->venueAddress       = $eventVenueData->address;
			$this->venuedatails       = $eventVenueData->params;
			$this->item->venuedatails       = $eventVenueData->params;
			$this->item->latitude     = $eventVenueData->latitude;
			$this->item->longitude    = $eventVenueData->longitude;
			$this->item->location     = $this->venueAddress;
			$this->onlineZoomEvent = 0;

			if ($eventVenueData->online == 1 && $eventVenueData->online_provider == 'zoom')
			{
				$this->onlineZoomEvent = 1;
			}

			JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');
			$customFields = FieldsHelper::getFields('com_jticketing.venue', (object) (array) $eventVenueData, true);

			if (!empty($customFields))
			{
				$eventVenueData->fields = $customFields;
				$this->venueCustomFields = $eventVenueData->fields;
			}
		}
		else 
		{
			$this->venueName          = null;
			$this->venueAddress       = null;
			$this->venuedatails       = null;
			$this->item->venuedatails       = null;
			$this->item->latitude     = null;
			$this->item->longitude    = null;
			$this->item->location     = null;
		}

		if (file_exists(JPATH_SITE . '/components/com_jticketing/models/eventform.php')) { require_once JPATH_SITE . '/components/com_jticketing/models/eventform.php'; }
		$evenformModel = JT::model('EventForm');

		if (empty($this->item->longitude) || empty($this->item->latitude))
		{
			$locationData = $evenformModel->addLatLongForEvent($this->item->venue, $this->item->location, $eventid);

			if (!empty($locationData))
			{
				$this->item->latitude = $locationData->latitude;
				$this->item->longitude = $locationData->longitude;
			}
		}

		$this->enableSelfEnrollment       = $this->params->get('enable_self_enrollment', '', 'INT');
		$this->supressBuyButton           = $this->params->get('supress_buy_button', '', 'INT');
		$this->accessLevelsForEnrollment  = $this->params->get('accesslevels_for_enrollment');

		// Shows Book Ticket Button
		require_once JPATH_SITE . "/components/com_jticketing/models/events.php";
		$Jticketingfrontendhelper = new JticketingModelEvents;

		$this->eventData = $Jticketingfrontendhelper->getTJEventDetails($this->item->id);
		$this->extraData = $this->get('DataExtra');
		$this->form_extra = $evenformModel->getFormExtra(
			array(
				"category" => $this->item->catid,
				"clientComponent" => 'com_jticketing',
				"client" => 'com_jticketing.event',
				"view" => 'event',
				"layout" => 'default',
				"content_id" => $this->item->id)
		);

		$xmlFileName = "eventform_extra" . "." . "xml";

		$results                = Factory::getApplication()->triggerEvent('onContentBeforeDisplay', array('com_jticketing.event', &$this->event, &$this->event->params));
		$this->joomlaFieldsData = trim(implode("\n", $results));

		if (!empty($this->item->short_description) )
		{
			$item = new StdClass;
			$item->text = $this->item->short_description;
			PluginHelper::importPlugin('content');
			$item->params = 'com_jticketing.short_description';
			$short_description = Factory::getApplication()->triggerEvent('onPrepareContent', array (& $item, & $item->params, 0));

			if (!empty($short_description) && $short_description['0'] != 1)
			{
				$this->item->short_description .= "<br/>" . $short_description['0'];
			}
		}

		$item->text = $this->item->long_description;
		PluginHelper::importPlugin('content');
		$jtLongDesc = 'com_jticketing.long_description';
		$long_description = Factory::getApplication()->triggerEvent('onPrepareContent', array (& $item, & $jtLongDesc, 0));

		if (!empty($long_description)  && $long_description['0'] != 1)
		{
			$this->item->long_description .= "<br/>" . $long_description['0'];
		}

		$this->item->language = Factory::getLanguage()->getTag();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		if ($this->_layout == 'edit')
		{
			$authorised = $user->authorise('core.create', 'com_jticketing');

			if ($authorised !== true)
			{
				throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'));
			}
		}

		// Get tags if avilabale.
		$this->enable_tags = $this->params->get('show_tags', '0', 'INT');
		$this->redirect_tags_to_events = $this->params->get('redirect_tags_to_events', '0', 'INT');

		if ($this->enable_tags == 1)
		{
			$this->item->tags = new TagsHelper;
			$this->item->tags->getItemTags('com_jticketing.event', $this->item->id);
		}

		$this->createEventItemid  = $this->utilities->getItemId('index.php?option=com_jticketing&view=eventform');
		$this->currentTime = Factory::getDate()->toSql();

		if ($this->_layout == 'default_playvideo')
		{
			$this->_playVideo();
		}

		// Joomla 6: JVERSION check removed
		if (false) // Legacy >= '4.0.0'
		{
			// Instantiate the model
			$couponsModel = JT::model('coupons', array("ignore_request" => true));
			$couponsModel->setState('list.start', 0);
			$couponsModel->setState('list.limit', 0);
			$couponsModel->setState('event_integration_id', $this->event->integrationId);

			// Fetch the coupons
			$this->coupons = $couponsModel->getItems();
		}

		$this->_prepareDocument();

		return parent::display($tpl);
	}

	/**
	 * Method to prepare document
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function _prepareDocument()
	{
		$app   = Factory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', Text::_('COM_JTICKETING_DEFAULT_PAGE_TITLE'));
		}

		if (empty($title))
		{
			if (!empty($this->item))
			{
				$title = $this->item->title;
			}
			else
			{
				$title = $this->params->get('page_title', '');
			}
		}

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		if (isset($this->item->meta_data))
		{
			if ($this->item->meta_data)
			{
				$this->document->setMetadata('keywords', $this->item->meta_data);
			}
			elseif ((!$this->item->meta_data && $this->params->get('menu-meta_keywords')))
			{
				// If the meta data is empty get the default menu meta_data value
				$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
			}
		}

		if (isset($this->item->meta_desc))
		{
			if (($this->item->meta_desc))
			{
				$this->document->setDescription($this->item->meta_desc);
			}
			elseif (!$this->item->meta_desc && $this->params->get('menu-meta_description'))
			{
				$this->document->setDescription($this->params->get('menu-meta_description'));
			}
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}

	/**
	 * Function to Build data to call video plugin
	 *
	 * @parmas
	 *
	 * @return  void
	 */
	protected function _playVideo()
	{
		$input = Factory::getApplication()->getInput();
		$vid   = $input->get('vid', '', 'INT');
		$type   = $input->get('type', '', 'STRING');
		$model = $this->getModel('event');

		if (!empty($vid))
		{
			// Get video data
			$this->video = $model->getVideoData($vid, $type);

			if (!empty($this->video))
			{
				$this->video_params = array();

				if (!empty($type))
				{
					$this->video->type = substr($this->video->type, 6);

					switch (trim($type))
					{
						case 'youtube' || 'vimeo':
							switch ($this->video->type)
							{
								// Video provider youtube
									case 'youtube':
										// Get youtube video ID from embed url, after explode in array 4th index contain actual video id
										$explodedUrl = explode('/', $this->video->path);

										if (!empty($explodedUrl))
										{
											$videoId = end($explodedUrl);
											$this->video_params['file'] = 'https://www.youtube.com/watch?v=' . $videoId;
											$this->video_params['videoId'] = $videoId;

											// Plugin to call to pay video
											$this->video_params['plugin'] = 'jwplayer';
										}
									break;

									// Video provider vimeo
									case 'vimeo':
										$explodedUrl = explode('/', $this->video->url);

										if (!empty($explodedUrl))
										{
											$videoId = end($explodedUrl);

											// Get youtube video ID from embed url, after explode in array 4th index contain actual video id
											$this->video_params['videoId'] = $videoId;

											// Plugin to call to pay video
											$this->video_params['plugin'] = 'vimeo';
										}
									break;

									// Other video provider than above
									default:
										// For future
									break;
							}
						break;
					}
				}

				$this->video_params['client'] = $input->get('option', '', 'STRING');
			}
		}
	}
}
