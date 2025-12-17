<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Unauthorized Access');
$integration = JT::getIntegration(true);

if ($integration == 3)
{
    $startDate = date('Ymd\THis', $event->startdate);
    $endDate = date('Ymd\THis', $event->enddate);
}
else 
{
    $startDate = date('Ymd\THis', strtotime(str_replace('-', '/', $event->startdate)));
    $endDate = date('Ymd\THis', strtotime(str_replace('-', '/', $event->enddate)));
}
// Check if the event is an online event
if ($event->online_events)
{
    $eventParams = json_decode($event->params, false);
    // Get the join URL
    $location = $eventParams->join_url;
    $description = strip_tags($event->long_description);
    $description .= " <br><br> Join with Link - ". $location;
}
else
{
    // If the event is offline, set the offline event's location
    $location = $event->location;
    $description = $event->title;
}
?>
BEGIN:VCALENDAR

VERSION:2.0

PRODID:-//hacksw/handcal//NONSGML v1.0//EN

CALSCALE:GREGORIAN

METHOD:PUBLISH

TRANSP:OPAQUE

BEGIN:VEVENT

UID:<?php echo md5(uniqid(mt_rand(), true));?>

DTSTAMP:<?php echo gmdate('Ymd') . 'T' . gmdate('His');?>

DTSTART:<?php echo $startDate;?>

DTEND:<?php  echo $endDate;?>

SUMMARY:<?php echo $event->title;?>

DESCRIPTION:<?php echo $description;?>

LOCATION:<?php echo $location;?>

END:VEVENT

END:VCALENDAR
