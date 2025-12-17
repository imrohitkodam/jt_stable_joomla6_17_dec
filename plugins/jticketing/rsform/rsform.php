<?php
/**
 * @package     JTicketing.Plugin
 * @subpackage  JTicketing,rsform
 *
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;
/**
 * RSform link for feedback form Plugin
 *
 * @since  2.6.1
 */
class PlgJticketingRsform extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  2.6.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * The form event. Load additional parameters when available into the field form.
	 * Only when the type of the form is of interest.
	 *
	 * @return  array
	 *
	 * @since   2.6.1
	 */
	public function onPrepareIntegrationField()
	{
		if (ComponentHelper::isEnabled('com_rsform', true))
		{
			return array(
			'path' => JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/rsform.xml', 'name' => $this->_name
			);
		}
	}

	/**
	 * Function used as a trigger after user successfully checkin for a event.
	 *
	 * @param   array  $data  Order details
	 *
	 * @return  string
	 *
	 * @since   2.6.1
	 */
	public function onJtGetFeedbackFormUrl($data)
	{
		$eventInfo = JT::event()->loadByIntegration($data['eventid']);
		$eventParams = json_decode($eventInfo->params);

		if (!empty($eventParams->rsform->onAfterJtAttendeeCheckin))
		{
			return Route::_(
				Uri::root() . 'index.php?option=com_rsform&view=rsform&formId='
				. $eventParams->rsform->onAfterJtAttendeeCheckin[0] . '&client=event&clientid=' . $eventInfo->id
			);
		}
	}
}
