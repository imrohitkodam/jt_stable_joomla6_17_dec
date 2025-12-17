<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;

BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models');
$tjvendorsModelVendors = BaseDatabaseModel::getInstance('Venue', 'JticketingModel');
$venueDetails = $tjvendorsModelVendors->getItem($data['venue']);
$location = $venueDetails->name . '' . $venueDetails->address;

echo "BEGIN:VCALENDAR

VERSION:2.0

PRODID:-//hacksw/handcal//NONSGML v1.0//EN

CALSCALE:GREGORIAN

METHOD:PUBLISH

TRANSP:OPAQUE

BEGIN:VEVENT

UID:" . $a = md5(uniqid(mt_rand(), true)) . "

DTSTAMP: " . gmdate('Ymd') . 'T' . gmdate('His') . "

DTSTART:" . Factory::getDate($data['startdate'])->format('Ymd\THis', true) . "

DTEND:" . Factory::getDate($data['enddate'])->format('Ymd\THis', true) . "

SUMMARY:" . $data['title'] . "

DESCRIPTION:" . $data['long_description'] . "

LOCATION:" . $location . "

END:VEVENT

END:VCALENDAR";

?>
