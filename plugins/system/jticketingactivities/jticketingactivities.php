<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing_Activities
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\HTML\HTMLHelper;

jimport('techjoomla.jsocial');
$lang = Factory::getLanguage();
$lang->load('plg_system_jticketingactivities', JPATH_ADMINISTRATOR);
$lang->load('com_activitystream', JPATH_SITE);

/**
 * Jticketing_Activities
 *
 * @package     Jticketing_Activities
 * @subpackage  site
 * @since       1.0
 */
class PlgSystemJticketingActivities extends CMSPlugin
{
	public $PlgSystemJticketingActivitiesHelper = null;
	/**
	 * Constructor
	 *
	 * @param   string  &$subject  subject
	 *
	 * @param   string  $config    config
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		require_once dirname(__FILE__) . '/helper.php';
		$this->PlgSystemJticketingActivitiesHelper = new PlgSystemJticketingActivitiesHelper;
	}

	/**
	 * Method to include required scripts for activity streams
	 *
	 * @param   string  $theme  Theme used
	 *
	 * @return  null
	 *
	 * @since   1.0
	 */
	public function onGetActivityScript($theme)
	{
		JLoader::import('components.com_activitystream.helper', JPATH_SITE);
		$comActivityStreamHelper = new ComActivityStreamHelper;
		$comActivityStreamHelper->getLanguageConstantForJs();
		$document = Factory::getDocument();
		$document->addScriptDeclaration('var root_url = \'' . Uri::base() . '\'');
		HTMLHelper::_('script', 'media/com_activitystream/scripts/mustache.min.js');
		HTMLHelper::_('stylesheet', 'media/com_jticketing/themes/' . $theme . '/css/theme.css');

		HTMLHelper::script('media/com_activitystream/scripts/activities.jQuery.js');
	}

	/**
	 * Function onPostActivity
	 *
	 * @param   MIXED  $data  data
	 *
	 * @return  mixed array|null..
	 *
	 * @since	1.8
	 */
	public function onPostActivity($data)
	{
		$jinput    = Factory::getApplication()->input;
		$componentName = $jinput->post->get('option');

		if ($componentName == 'com_jticketing' && !empty($data))
		{
			$result = $this->PlgSystemJticketingActivitiesHelper->onPostActivity($data);

			return $result;
		}
	}

	/**
	 * Function OnAfterEventCreate
	 *
	 * @param   Array  $eventData  Event id, new data, old data
	 *
	 * @return  void.
	 *
	 * @since  1.8
	 */
	public function onAfterJtEventCreate($eventData)
	{
		$eventId = $eventData['eventId'];

		if (isset($eventData['eventOldData']))
		{
			$oldDetails = $eventData['eventOldData'];
		}

		if (!empty($eventId))
		{
			if (isset($oldDetails))
			{
				// Activity for adding images to the event
				$result = false;
				$newGalleryImages = array();

				for ($i = 0; $i < count($eventData['gallery_file']['media']); $i++)
				{
					$imageIsNew = 1;

					if (isset($oldDetails->gallery))
					{
						foreach ($oldDetails->gallery as $k => $image)
						{
							if ($eventData['gallery_file']['media'][$i] == $oldDetails->gallery[$k]->id)
							{
								$imageIsNew = 0;
								break;
							}
							else
							{
								continue;
							}
						}
					}

					if ($imageIsNew)
					{
						$newGalleryImages[] = $eventData['gallery_file']['media'][$i];
					}
				}

				$newGalleryImages = array_filter($newGalleryImages);

				// Activity for gallery images
				if (!empty($newGalleryImages))
				{
					$result = $this->PlgSystemJticketingActivitiesHelper->addImageActivity($newGalleryImages, $eventId, $eventData);
				}

				// Event date activity
				if (!empty($eventData['enddate']) && !empty($oldDetails->enddate))
				{
					if ($eventData['enddate'] != $oldDetails->enddate)
					{
						$result = $this->PlgSystemJticketingActivitiesHelper->eventEndDateChangeActivity($eventData);
					}
				}

				// Event Book date activity
				if (!empty($eventData['booking_end_date']) && !empty($oldDetails->booking_end_date))
				{
					if ($eventData['booking_end_date'] != $oldDetails->booking_end_date)
					{
						$result = $this->PlgSystemJticketingActivitiesHelper->eventBookingEndDateChangeActivity($eventData);
					}
				}
			}
			else
			{
				$result = $this->PlgSystemJticketingActivitiesHelper->addEventActivity($eventData);
			}

			return $result;
		}
	}

	/**
	 * Function OnAfterProcessPayment
	 *
	 * @param   Array|String  $post       Event post data
	 * @param   Integer       $order_id   Event order id
	 * @param   String        $pg_plugin  Plugin Name
	 *
	 * @return  mixed array|false.
	 *
	 * @since  1.8
	 */
	public function onJtAfterProcessPayment($post, $order_id, $pg_plugin = null)
	{
		if (is_array($post))
		{
			$post = $post;
			$status = $post['status'];
		}
		else
		{
			$post = $post->getArray(array());
			$status = $post['payment_status'];
		}

		if ($status == 'C')
		{
			$orderDetails = JT::order()->loadByOrderId($order_id);

			$result = $this->PlgSystemJticketingActivitiesHelper->addEventOrderActivity($orderDetails);

			return $result;
		}

		return false;
	}

	/**
	 * Function onAfterMediaDelete
	 *
	 * @param   Integer  $mediaId  Media Id
	 *
	 * @return  void.
	 *
	 * @since  1.8
	 */
	public function onBeforeJtMediaDelete($mediaId)
	{
		if ($mediaId)
		{
			$result = $this->PlgSystemJticketingActivitiesHelper->removeMediaActivity($mediaId);
		}
	}

	/**
	 * Function OnAfterProcessPayment
	 *
	 * @param   Array    $post        Attendee post data
	 * @param   Integer  $attendeeId  Attendee id
	 * @param   String   $pg_plugin   Plugin Name
	 *
	 * @return  mixed array|false
	 *
	 * @since  1.8
	 */
	public function onJtAfterEnrollment($post, $attendeeId, $pg_plugin = null)
	{
		$status = '';

		if (!empty($post))
		{
			$post = $post;
			$status = $post['status'];
		}

		if ($status == 'A')
		{
			BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jticketing/models');
			$attendeeFormModel = BaseDatabaseModel::getInstance('AttendeeForm', 'JticketingModel');
			$attendeeDetails   = $attendeeFormModel->getItem($attendeeId);

			$result = $this->PlgSystemJticketingActivitiesHelper->addEventEnrollmentActivity($attendeeDetails);

			return $result;
		}

		return false;
	}
}
