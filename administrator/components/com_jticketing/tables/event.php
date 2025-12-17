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
use Joomla\CMS\Table\Table;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Table\Observer\Tags;
Use Joomla\String\StringHelper;
use Joomla\CMS\Tag\TaggableTableTrait;
use Joomla\CMS\Tag\TaggableTableInterface;
use Joomla\CMS\Table\Observer\AbstractObserver;
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\CMS\Event\AbstractEvent;

/**
 * Hello Table class
 *
 * @since  0.0.1
 */

// Joomla 6: JVERSION check removed
		if (false) // Legacy >= '4.0.0')
{
	class JTicketingTableEvent extends Table implements VersionableTableInterface, TaggableTableInterface
	{
		use TaggableTableTrait;
		/**
		 * Constructor
		 *
		 * @param   JDatabaseDriver  &$db  A database connector object
		 */
		public function __construct(&$db)
		{
			$this->setColumnAlias('published', 'state');
			$this->typeAlias = 'com_jticketing.event';

			parent::__construct('#__jticketing_events', 'id', $db);
		}

		/**
		 * Overloaded check function
		 *
		 * @return bool
		 */
		public function check()
		{
			$db = Factory::getDbo();
			$errors = array();

			// Validate and create alias if needed
			$this->alias = trim($this->alias);

			if (!$this->alias)
			{
				$this->alias = $this->title;
			}

			if ($this->alias)
			{
				if (Factory::getConfig()->get('unicodeslugs') == 1)
				{
					$this->alias = OutputFilter::stringURLUnicodeSlug($this->alias);
				}
				else
				{
					$this->alias = OutputFilter::stringURLSafe($this->alias);
				}
			}

			// Check if event with same alias is present
			$table = Table::getInstance('Event', 'JticketingTable', array('dbo', $db));

			if ($table->load(array('alias' => $this->alias)) && ($table->id != $this->id || $this->id == 0))
			{
				$msg = Text::_('COM_JTICKETING_SAVE_ALIAS_WARNING');

				while ($table->load(array('alias' => $this->alias)))
				{
					$this->alias = StringHelper::increment($this->alias, 'dash');
				}

				Factory::getApplication()->enqueueMessage($msg, 'warning');
			}

			// Check if category with same alias is present
			$category = Table::getInstance('CategoryTable', '\\Joomla\\Component\\Categories\\Administrator\\Table\\');

			if ($category->load(array('alias' => $this->alias)))
			{
				$msg = Text::_('COM_JTICKETING_SAVE_EVENT_WARNING_DUPLICATE_CAT_ALIAS');

				while ($category->load(array('alias' => $this->alias)))
				{
					$this->alias = StringHelper::increment($this->alias, 'dash');
				}

				Factory::getApplication()->enqueueMessage($msg, 'warning');
			}

			// Check if venue with same alias is present
			$venue = Table::getInstance('Venue', 'JTicketingTable', array('dbo', $db));

			if ($venue->load(array('alias' => $this->alias)))
			{
				$msg = Text::_('COM_JTICKETING_SAVE_EVENT_WARNING_DUPLICATE_VENUE_ALIAS');

				while ($venue->load(array('alias' => $this->alias)))
				{
					$this->alias = StringHelper::increment($this->alias, 'dash');
				}

				Factory::getApplication()->enqueueMessage($msg, 'warning');
			}

			$JTicketingViews = array(
							'events','event','eventform','order',
							'orders','mytickets','mypayouts',
							'calendar','attendee_list', 'attendees','allticketsales','venues','venueform',
							'enrollment','waitinglist'
						);

			if (in_array($this->alias, $JTicketingViews))
			{
				$this->setError(Text::_('COM_JTICKETING_VIEW_WITH_SAME_ALIAS'));

				return false;
			}

			if (trim(str_replace('-', '', $this->alias)) == '')
			{
				$this->alias = Factory::getDate()->format("Y-m-d-H-i-s");
			}

			// Make sure creator is set
			if (!Factory::getUser($this->created_by)->id)
			{
				$errors['created_by'] = Text::_('COM_JTICKETING_EVENT_CREATOR_ERROR');
			}

			// End date should be later than start
			if (($this->recurring_type==='No_repeat')&&(strtotime($this->startdate) > strtotime($this->enddate)))
			{
				$errors['event_end'] = Text::_('COM_JTICKETING_EVENT_EVENT_END_DATE_ERROR');
			}

			if ($this->booking_start_date != '0000-00-00 00:00:00' && $this->booking_end_date != '0000-00-00 00:00:00'
				&& strtotime($this->booking_start_date) > strtotime($this->booking_end_date))
			{
				$errors['event_end'] = Text::_('COM_JTICKETING_EVENT_BOOKING_END_DATE_ERROR');
			}

			if (count($errors))
			{
				$this->setError(implode($errors, ', '));

				return false;
			}

			return parent::check();
		}

		/**
		 * Define a namespaced asset name for inclusion in the #__assets table
		 *
		 * @return string The asset name
		 *
		 * @see Table::_getAssetName
		 */
		protected function _getAssetName()
		{
			$k = $this->_tbl_key;

			return 'com_jticketing.event.' . (int) $this->$k;
		}

		/**
		 * Returns the parent asset's id. If you have a tree structure, retrieve the parent's id using the external key field
		 *
		 * @param   JTable  $table  database name
		 * @param   string  $id     1 0r 0
		 *
		 * @return  void
		 *
		 * @since   1.0
		 */
		protected function _getAssetParentId(Table $table = null, $id = null)
		{
			// We will retrieve the parent-asset from the Asset-table
			$assetParent   = Table::getInstance('Asset');

			// Default: if no asset-parent can be found we take the global asset
			$assetParentId = $assetParent->getRootId();

			// The item has the component as asset-parent
			$assetParent->loadByName('com_jticketing');

			// Return the found asset-parent-id
			if ($assetParent->id)
			{
				$assetParentId = $assetParent->id;
			}

			return $assetParentId;
		}

		/**
		 * Overloaded bind function to pre-process the params.
		 *
		 * @param   array   $array   Named array
		 * @param   string  $ignore  string
		 *
		 * @return   null|string    null is operation was satisfactory, otherwise returns an error
		 *
		 * @since  1.0.0
		 */
		public function bind($array, $ignore = '')
		{

			$input = Factory::getApplication()->getInput();
			$task  = $input->getString('task', '');
			$user = Factory::getUser();

			if (($task == 'save' || $task == 'apply') && (!$user->authorise('core.edit.state', 'com_jticketing.event.' . $array['id']) && $array['state'] == 1))
			{
				$array['state'] = 0;
			}

			$files = $input->files->get('jform');

			if (isset($array['params']) && is_array($array['params']))
			{
				$registry = new Registry;
				$registry->loadArray($array['params']);
				$array['params'] = (string) $registry;
			}

			if (!isset($array['latitude']) || !$array['latitude'])
			{
				$array['latitude'] = 0;
			}

			if (!isset($array['longitude']) || !$array['longitude'])
			{
				$array['longitude'] = 0;
			}

			if (isset($array['metadata']) && is_array($array['metadata']))
			{
				$registry = new Registry;
				$registry->loadArray($array['metadata']);
				$array['metadata'] = (string) $registry;
			}

			 // Add JSON encoding for recurring_params
			 if (isset($array['recurring_params']) && is_array($array['recurring_params']))
			 {
				 $array['recurring_params'] = json_encode($array['recurring_params']);
			 }


			if (Factory::getUser()->authorise('core.admin', 'com_jticketing.event.' . $array['id']))
			{
				$actions = Access::getActionsFromFile(
					JPATH_ADMINISTRATOR . '/components/com_jticketing/access.xml',
					"/access/section[@name='event']/");
				$default_actions = Access::getAssetRules('com_jticketing.event.' . $array['id'])->getData();
				$array_jaccess   = array();

				foreach ($actions as $action)
				{
					if (!empty($default_actions[$action->name]))
					{
						$array_jaccess[$action->name] = $default_actions[$action->name];
					}
				}

				$array['rules'] = $this->RulestoArray($array_jaccess);
			}

			if (isset($array['rules']) && is_array($array['rules']))
			{
				$this->setRules($array['rules']);
			}

			return parent::bind($array, $ignore);
		}

		/**
		 * This function convert an array of JAccessRule objects into an rules array.
		 *
		 * @param   array  $jaccessrules  An array of JAccessRule objects.
		 *
		 * @return  array
		 *
		 * @since  _DEPLOY_VERSION_
		 */

		private function RulestoArray($jaccessrules)
		{
			$rules = array();

			foreach ($jaccessrules as $action => $jaccess)
			{
				$actions = array();

				foreach ($jaccess->getData() as $group => $allow)
				{
					$actions[$group] = ((bool) $allow);
				}

				$rules[$action] = $actions;
			}

			return $rules;
		}

		/**
		 * Get the type alias for UCM features
		 *
		 * @return  string  The alias as described above
		 *
		 * @since   4.1.1
		 */
		public function getTypeAlias()
		{
			return $this->typeAlias;
		}
	}
}
else
{
	class JTicketingTableEvent extends Table
	{
		/**
		 * Constructor
		 *
		 * @param   JDatabaseDriver  &$db  A database connector object
		 */
	public function __construct(&$db)
	{
		$this->setColumnAlias('published', 'state');

		// Joomla 6: JObserverUpdater and JObserverMapper removed - observer pattern no longer used
		// Tags are now handled via TaggableTableInterface or manually

		parent::__construct('#__jticketing_events', 'id', $db);
	}

		/**
		 * Overloaded check function
		 *
		 * @return bool
		 */
		public function check()
		{
			$db = Factory::getDbo();
			$errors = array();

			// Validate and create alias if needed
			$this->alias = trim($this->alias);

			if (!$this->alias)
			{
				$this->alias = $this->title;
			}

			if ($this->alias)
			{
				if (Factory::getConfig()->get('unicodeslugs') == 1)
				{
					$this->alias = OutputFilter::stringURLUnicodeSlug($this->alias);
				}
				else
				{
					$this->alias = OutputFilter::stringURLSafe($this->alias);
				}
			}

			// Check if event with same alias is present
			$table = Table::getInstance('Event', 'JticketingTable', array('dbo', $db));

			if ($table->load(array('alias' => $this->alias)) && ($table->id != $this->id || $this->id == 0))
			{
				$msg = Text::_('COM_JTICKETING_SAVE_ALIAS_WARNING');

				while ($table->load(array('alias' => $this->alias)))
				{
					$this->alias = StringHelper::increment($this->alias, 'dash');
				}

				Factory::getApplication()->enqueueMessage($msg, 'warning');
			}

			// Check if category with same alias is present
			Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_categories/tables');
			$category = Table::getInstance('Category', 'CategoriesTable', array('dbo', Factory::getDbo()));

			if ($category->load(array('alias' => $this->alias)))
			{
				$msg = Text::_('COM_JTICKETING_SAVE_EVENT_WARNING_DUPLICATE_CAT_ALIAS');

				while ($category->load(array('alias' => $this->alias)))
				{
					$this->alias = StringHelper::increment($this->alias, 'dash');
				}

				Factory::getApplication()->enqueueMessage($msg, 'warning');
			}

			// Check if venue with same alias is present
			$venue = Table::getInstance('Venue', 'JTicketingTable', array('dbo', $db));

			if ($venue->load(array('alias' => $this->alias)))
			{
				$msg = Text::_('COM_JTICKETING_SAVE_EVENT_WARNING_DUPLICATE_VENUE_ALIAS');

				while ($venue->load(array('alias' => $this->alias)))
				{
					$this->alias = StringHelper::increment($this->alias, 'dash');
				}

				Factory::getApplication()->enqueueMessage($msg, 'warning');
			}

			$JTicketingViews = array(
							'events','event','eventform','order',
							'orders','mytickets','mypayouts',
							'calendar','attendee_list', 'attendees','allticketsales','venues','venueform',
							'enrollment','waitinglist'
						);

			if (in_array($this->alias, $JTicketingViews))
			{
				$this->setError(Text::_('COM_JTICKETING_VIEW_WITH_SAME_ALIAS'));

				return false;
			}

			if (trim(str_replace('-', '', $this->alias)) == '')
			{
				$this->alias = Factory::getDate()->format("Y-m-d-H-i-s");
			}

			// Make sure creator is set
			if (!Factory::getUser($this->created_by)->id)
			{
				$errors['created_by'] = Text::_('COM_JTICKETING_EVENT_CREATOR_ERROR');
			}

			// End date should be later than start
			if (strtotime($this->startdate) > strtotime($this->enddate))
			{
				$errors['event_end'] = Text::_('COM_JTICKETING_EVENT_EVENT_END_DATE_ERROR');
			}

			if ($this->booking_start_date != '0000-00-00 00:00:00' && $this->booking_end_date != '0000-00-00 00:00:00'
				&& strtotime($this->booking_start_date) > strtotime($this->booking_end_date))
			{
				$errors['event_end'] = Text::_('COM_JTICKETING_EVENT_BOOKING_END_DATE_ERROR');
			}

			if (count($errors))
			{
				$this->setError(implode($errors, ', '));

				return false;
			}

			return parent::check();
		}

		/**
		 * Define a namespaced asset name for inclusion in the #__assets table
		 *
		 * @return string The asset name
		 *
		 * @see Table::_getAssetName
		 */
		protected function _getAssetName()
		{
			$k = $this->_tbl_key;

			return 'com_jticketing.event.' . (int) $this->$k;
		}

		/**
		 * Returns the parent asset's id. If you have a tree structure, retrieve the parent's id using the external key field
		 *
		 * @param   JTable  $table  database name
		 * @param   string  $id     1 0r 0
		 *
		 * @return  void
		 *
		 * @since   1.0
		 */
		protected function _getAssetParentId(Table $table = null, $id = null)
		{
			// We will retrieve the parent-asset from the Asset-table
			$assetParent   = Table::getInstance('Asset');

			// Default: if no asset-parent can be found we take the global asset
			$assetParentId = $assetParent->getRootId();

			// The item has the component as asset-parent
			$assetParent->loadByName('com_jticketing');

			// Return the found asset-parent-id
			if ($assetParent->id)
			{
				$assetParentId = $assetParent->id;
			}

			return $assetParentId;
		}

		/**
		 * Overloaded bind function to pre-process the params.
		 *
		 * @param   array   $array   Named array
		 * @param   string  $ignore  string
		 *
		 * @return   null|string    null is operation was satisfactory, otherwise returns an error
		 *
		 * @since  1.0.0
		 */
		public function bind($array, $ignore = '')
		{
			$input = Factory::getApplication()->getInput();
			$task  = $input->getString('task', '');
			$user = Factory::getUser();

			if (($task == 'save' || $task == 'apply') && (!$user->authorise('core.edit.state', 'com_jticketing.event.' . $array['id']) && $array['state'] == 1))
			{
				$array['state'] = 0;
			}

			$files = $input->files->get('jform');

			if (isset($array['params']) && is_array($array['params']))
			{
				$registry = new Registry;
				$registry->loadArray($array['params']);
				$array['params'] = (string) $registry;
			}

			if (isset($array['metadata']) && is_array($array['metadata']))
			{
				$registry = new Registry;
				$registry->loadArray($array['metadata']);
				$array['metadata'] = (string) $registry;
			}

			if (Factory::getUser()->authorise('core.admin', 'com_jticketing.event.' . $array['id']))
			{
				$actions = Access::getActionsFromFile(
					JPATH_ADMINISTRATOR . '/components/com_jticketing/access.xml',
					"/access/section[@name='event']/");
				$default_actions = Access::getAssetRules('com_jticketing.event.' . $array['id'])->getData();
				$array_jaccess   = array();

				foreach ($actions as $action)
				{
					if (!empty($default_actions[$action->name]))
					{
						$array_jaccess[$action->name] = $default_actions[$action->name];
					}
				}

				$array['rules'] = $this->RulestoArray($array_jaccess);
			}

			if (isset($array['rules']) && is_array($array['rules']))
			{
				$this->setRules($array['rules']);
			}

			return parent::bind($array, $ignore);
		}

		/**
		 * This function convert an array of JAccessRule objects into an rules array.
		 *
		 * @param   array  $jaccessrules  An array of JAccessRule objects.
		 *
		 * @return  array
		 *
		 * @since  _DEPLOY_VERSION_
		 */

		private function RulestoArray($jaccessrules)
		{
			$rules = array();

			foreach ($jaccessrules as $action => $jaccess)
			{
				$actions = array();

				foreach ($jaccess->getData() as $group => $allow)
				{
					$actions[$group] = ((bool) $allow);
				}

				$rules[$action] = $actions;
			}

			return $rules;
		}
	}
}