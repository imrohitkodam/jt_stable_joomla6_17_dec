<?php
defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;


class JticketingTableRecurringEvent extends Table
{
    public function __construct(&$db)
    {
        parent::__construct('#__jticketing_recurring_events', 'r_id', $db);
    }
}