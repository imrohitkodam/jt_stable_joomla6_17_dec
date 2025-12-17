<?php
/**
 * @package     Structured Data - Schema
 * @subpackage  plugin-Content-Schema_Events
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;

/**
 * content - Schema_Events Plugin
 *
 * @package		Joomla.Plugin
 */
class PlgContentSchema_Events extends CMSPlugin {

/**
	 *  onJTEventSchema event to add JSON markup to the document
	 *
	 *  @return void
	 */
	public function onJTEventSchema($itemData)
	{
		$params 				= ComponentHelper::getParams('com_jticketing');
		$itemData->description 	= isset($itemData->short_description) && !empty($itemData->short_description) ? $itemData->short_description : $itemData->long_description;

		$uri 					= Uri::getInstance();
		$url 					= $uri->toString();
		$images       = array();

		$sDate 					= new Date($itemData->startdate);
		$eDate 					= new Date($itemData->enddate);

		if($itemData->online_events)
		{
			$eventAttendanceMode = "https://schema.org/OnlineEventAttendanceMode";
		}
		else
		{
			$eventAttendanceMode = "https://schema.org/OfflineEventAttendanceMode";
		}

		if (!empty($itemData->image))
		{
			$images[] = $itemData->image->media;
		}
		if (!empty($itemData->coverImage))
		{
			$images[] = $itemData->coverImage->media;
		}

		$content = [
			'@type' 				=> "Event",
			'name'					=> $itemData->title,
			'description' 			=> $itemData->description,
			'startDate' 			=> $sDate->toISO8601(),
			'endDate' 				=> $eDate->toISO8601(),
			"eventStatus" 			=> "https://schema.org/EventScheduled",
			"eventAttendanceMode" 	=> $eventAttendanceMode,
			'image' 				=> $images,
			'mainEntityOfPage' 		=> [
										'@type' => 'WebPage',
										'@id'   => $url
									   ],
		];

		// location
		if($itemData->online_events)
		{
			$content = array_merge($content, [
				"location" => [
								"@type"=> "VirtualLocation",
								"url"  => ""
							  ]
				]);
		}
		else
		{
			$content = array_merge($content, [
				"location" => [
								"@type"   => "Place",
								"address" => $itemData->event_address
							  ]
				]);
		}

		// Offers
		if(!empty($itemData->tickettypes))
		{
			$offers = [];
			foreach($itemData->tickettypes as $ticket)
			{
				if($ticket->state)
				{
					$offer = [
						'@type' 		=> 'Offer',
						"name" 			=> $ticket->title,
						"price" 		=> $ticket->price,
						"priceCurrency" => $params->get('currency')
					];

					if($itemData->booking_start_date  !== '0000-00-00 00:00:00')
					{
						$offer = ["validFrom" => $itemData->booking_start_date];
					}

					if($ticket->ticket_enddate  !== '0000-00-00 00:00:00')
					{
						$offer = array_merge($offer, ["validThrough" => $ticket->ticket_enddate]);
					}
					elseif($itemData->booking_end_date  !== '0000-00-00 00:00:00')
					{
						$offer = array_merge($offer, ["validThrough" => $itemData->booking_end_date]);
					}

					$availability = "";
					if($ticket->unlimited_seats || $ticket->count > 0)
					{
						$availability = "https://schema.org/InStock";
					}
					else
					{
						$availability = "https://schema.org/SoldOut";
					}

					$offer 	= array_merge($offer, ["availability" => $availability]);
					$offers = array_merge($offers, [$offer]);
				}

			}
			// die(print_r($offers));
			$content = array_merge($content, ["offers" => $offers]);
		}

		   if($content)
		   {
				$content = ['@context' => 'https://schema.org'] + $content;

				$jsonString = json_encode($content, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

				Factory::getDocument()->addScriptDeclaration($jsonString,'application/ld+json');

		   }
		}

}
