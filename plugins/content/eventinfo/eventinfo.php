<?php
/**
 * @package     JTicketing
 * @subpackage  EventInfo
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (c) 2009-2020 TechJoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\Filesystem\Folder;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\Layout\LayoutHelper;

if (Folder::exists(JPATH_SITE . '/components/com_jticketing'))
{
	require_once JPATH_SITE . '/components/com_jticketing/includes/jticketing.php';
}

$lang = Factory::getLanguage()->load('com_jticketing', JPATH_SITE);

/**
 * Plug-in
 *
 * @since  3.0.0
 */
class PlgContentEventInfo extends CMSPlugin
{
	/**
	 * Load plugin language file automatically so that it can be used inside component
	 *
	 * @var    boolean
	 * @since  3.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Plugin that returns Event information with HTML
	 * 
	 * @param   integer  $eventId  event id
	 * @param   string   $client   client
	 *
	 * @return  string|boolean
	 */
	public function onContentPrepareTjHtml($eventId, $client)
	{
		if ($client != 'com_jticketing.event')
		{
			return false;
		}

		$app = Factory::getApplication();
		$data = $this->onGetCertificateClientData($eventId, $client);

		$data->eventPrice = $data->getPriceText();
		$data->eventUrl     = $data->getUrl();
		$data->eventImage   = $data->getAvatar();
		$data->descLength   = JT::config()->get('desc_length');

		// To get the venue and address of event
		if ($data->venue != "0")
		{
			$venueData          = JT::venue($data->venue);
			$venue              = new stdClass;
			$venue->location    = $venueData->name;
			$venue->venuAddress = $venueData->address;

			$data->location = isset($venue->venuAddress) ? $venue->location . $venue->venuAddress : $venue->location;
		}

		$basePath = JPATH_SITE . '/plugins/content/eventinfo/layouts';
		$template = $app->getTemplate(true)->template;
		$override = JPATH_SITE . '/templates/' . $template . '/html/layouts/plg_content_eventinfo/layouts/';

		if (File::exists($override . 'eventinfo.php'))
		{
			$basePath = $override;
		}

		$layout = new FileLayout('eventinfo', $basePath);

		return $layout->render($data);
	}

	/**
	 * Plugin trigger that provides certificate provider data
	 * 
	 * @param   integer  $clientId  clientId
	 * @param   string   $client    client
	 *
	 * @return  string
	 */
	public function onGetCertificateClientData($clientId, $client)
	{
		if ($client == 'com_jticketing.event')
		{
			return JT::event($clientId);
		}
	}
}
