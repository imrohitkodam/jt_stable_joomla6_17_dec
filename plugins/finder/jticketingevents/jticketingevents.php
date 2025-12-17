<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing_Events
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2024 TechJoomla. All rights reserved.
 */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Component\Finder\Administrator\Indexer\Adapter;
use Joomla\Component\Finder\Administrator\Indexer\Helper;
use Joomla\Component\Finder\Administrator\Indexer\Indexer;
use Joomla\Component\Finder\Administrator\Indexer\Result;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

$lang = Factory::getLanguage();
$lang->load('plg_system_jticketingEvents', JPATH_ADMINISTRATOR);

/**
 * Jticketing_Events
 *
 * @package     Jticketing_Events
 * @subpackage  site
 * @since       1.0
 */
class PlgFinderJticketingEvents extends Adapter 
{
    /**
     * The plugin identifier.
     *
     * @var    string
     * @since  2.5
     */
    protected $context = 'Events';

    /**
     * The extension name.
     *
     * @var    string
     * @since  2.5
     */
    protected $extension = 'com_jticketing';

    /**
     * The sublayout to use when rendering the results.
     *
     * @var    string
     * @since  2.5
     */
    protected $layout = 'event';

    /**
     * The type of content that the adapter indexes.
     *
     * @var    string
     * @since  2.5
     */
    protected $type_title = 'Event';

    /**
     * The table name.
     *
     * @var    string
     * @since  2.5
     */
    protected $table = '#__jticketing_events';

    /**
     * The field the published state is stored in.
     *
     * @var    string
     * @since  2.5
     */
    protected $state_field = 'state';

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * Method to setup the indexer to be run.
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     */
    protected function setup()
    {
        return true;
    }

    /**
     * Method to remove the link information for items that have been deleted.
     *
     * @param   string  $context  The context of the action being performed.
     * @param   Table   $table    A Table object containing the record to be deleted
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    public function onFinderDelete($context, $table)
    {
        if ($context === 'com_jticketing.event') {
            $id = $table->id;
        } elseif ($context === 'com_finder.index') {
            $id = $table->link_id;
        } else {
            return true;
        }

        // Remove item from the index.
        return $this->remove($id);
    }

    /**
     * Smart Search after save content method.
     * Reindexes the link information for a category that has been saved.
     * It also makes adjustments if the access level of the category has changed.
     *
     * @param   string   $context  The context of the category passed to the plugin.
     * @param   Table    $row      A Table object.
     * @param   boolean  $isNew    True if the category has just been created.
     *
     * @return  void
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    public function onFinderAfterSave($context, $row, $isNew): void
    {
        // We only want to handle categories here.
        if ($context === 'com_jticketing.event') {
            // Check if the access levels are different.
            if (!$isNew && $this->old_access != $row->access) {
                // Process the change.
                $this->itemAccessChange($row);
            }

            // Reindex the events item.
            $this->reindex($row->id);

            // Check if the parent access level is different.
            if (!$isNew && $this->old_cataccess != $row->access) {
                $this->categoryAccessChange($row);
            }
        }
    }

    /**
     * Smart Search before content save method.
     * This event is fired before the data is actually saved.
     *
     * @param   string   $context  The context of the events passed to the plugin.
     * @param   Table    $row      A Table object.
     * @param   boolean  $isNew    True if the events is just about to be created.
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    public function onFinderBeforeSave($context, $row, $isNew)
    {
        // We only want to handle categories here.
        if ($context === 'com_jticketing.event') {
            // Query the database for the old access level and the parent if the item isn't new.
            if (!$isNew) {
                $this->checkItemAccess($row);
                $this->checkCategoryAccess($row);
            }
        }

        return true;
    }

    /**
     * Method to update the link information for items that have been changed
     * from outside the edit screen. This is fired when the item is published,
     * unpublished, archived, or unarchived from the list view.
     *
     * @param   string   $context  The context for the events passed to the plugin.
     * @param   array    $pks      An array of primary key ids of the events that has changed state.
     * @param   integer  $value    The value of the state that the events has been changed to.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onFinderChangeState($context, $pks, $value)
    {
        // We only want to handle jticketingevents here.
        if ($context === 'com_jticketing.event' || $context == 'com_jticketing.eventform') {
            /*
             * The events published state is tied to the parent events
             * published state so we need to look up all published states
             * before we change anything.
             */
            foreach ($pks as $pk) {
                $pk    = (int) $pk;
                $db = Factory::getDbo();
                $query = clone $this->getStateQuery();

                $query->where($db->quoteName('a.id') . ' = :plgFinderjticketingeventsId')
                    ->bind(':plgFinderjticketingeventsId', $pk, ParameterType::INTEGER);

                $db->setQuery($query);
                $item = $db->loadObject();

                // Translate the state.
                $state = $item->state;

                $temp = $this->translateState($value, $state);

                // Update the item.
                $this->change($pk, 'state', $temp);

                // Reindex the item.
                $this->reindex($pk);
            }
        }

        // Handle when the plugin is disabled.
        if ($context === 'com_plugins.plugin' && $value === 0) {
            $this->pluginDisable($pks);
        }
    }

    /**
     * Method to index an item. The item must be a Result object.
     *
     * @param   Result  $item  The item to index as a Result object.
     *
     * @return  void
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    protected function index(Result $item)
    {
        // Check if the extension is enabled.
        if (ComponentHelper::isEnabled($this->extension) === false) {
            return;
        }

        $item->setLanguage();

        // $extension = ucfirst(substr($extension_element, 4));

        // Initialize the item parameters.
        $item->params = new Registry($item->params);

        $item->metadata = new Registry($item->metadata);

        /*
         * Add the metadata processing instructions based on the category's
         * configuration parameters.
         */

        // Add the meta author.
        $item->metaauthor = $item->metadata->get('author');

        // Handle the link to the metadata.
        $item->addInstruction(Indexer::META_CONTEXT, 'link');
        $item->addInstruction(Indexer::META_CONTEXT, 'metakey');
        $item->addInstruction(Indexer::META_CONTEXT, 'metadesc');
        $item->addInstruction(Indexer::META_CONTEXT, 'metaauthor');
        $item->addInstruction(Indexer::META_CONTEXT, 'author');

        // Deactivated Methods
        // $item->addInstruction(Indexer::META_CONTEXT, 'created_by_alias');

        // Trigger the onContentPrepare event.
        $item->summary = Helper::prepareContent($item->summary, $item->params);

        // Create a URL as identifier to recognise items again.
        // To get event information
        $event = JT::event($item->id);
        $link = "index.php?option=com_jticketing&view=event&id=" . $event->id;

		$itemId = JT::utilities()->getItemId($link, 0, $event->catid);
        $link .= '&Itemid=' . $itemId;
        $item->url = $link;

        // $item->url = $this->getUrl($item->id, $item->extension, $this->layout);
        $item->route = $link;
        // print_R($event->getUrl());die;

        // Get the menu title if it exists.
        $title = $this->getItemMenuTitle($item->url);

        // Adjust the title if necessary.
        if (!empty($title) && $this->params->get('use_menu_title', true)) {
            $item->title = $title;
        }

        // Index the item.
        $this->indexer->index($item);
    }

    /**
     * Method to get the SQL query used to retrieve the list of content items.
     *
     * @param   mixed  $query  A DatabaseQuery object or null.
     *
     * @return  DatabaseQuery  A database object.
     *
     * @since   2.5
     */
    protected function getListQuery($query = null)
    {
        $db = Factory::getDbo();

        // Check if we can use the supplied SQL query.
        $query = $query instanceof DatabaseQuery ? $query : $db->getQuery(true);

        $query->select(
            $db->quoteName(
                [
                    'a.id',
                    'a.title',
                    'a.alias',
                    'a.meta_data',
                    'a.meta_desc',
                    'a.params',
					'a.created_by',
					'a.modified',
					'a.state'
				]
            )
        )
            ->select(
                $db->quoteName(
                    [
                        'a.short_description',
                        'a.created',
                        'a.state',
                    ],
                    [
                        'summary',
                        'start_date',
                        'access',
                    ]
                )
            );

        // Handle the alias CASE WHEN portion of the query.
        $case_when_item_alias = ' CASE WHEN ';
        $case_when_item_alias .= $query->charLength($db->quoteName('a.alias'), '!=', '0');
        $case_when_item_alias .= ' THEN ';
        $a_id = $query->castAsChar($db->quoteName('a.id'));
        $case_when_item_alias .= $query->concatenate([$a_id, 'a.alias'], ':');
        $case_when_item_alias .= ' ELSE ';
        $case_when_item_alias .= $a_id . ' END AS slug';

        $query->select($case_when_item_alias)
            ->from($db->quoteName('#__jticketing_events', 'a'))
            ->where($db->quoteName('a.id') . ' >= 1');

        return $query;
    }

    /**
     * Method to get a SQL query to load the published and access states for
     * a category and its parents.
     *
     * @return  DatabaseQuery  A database object.
     *
     * @since   2.5
     */
    protected function getStateQuery()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select(
            $db->quoteName(
                [
                    'a.id',
                    'a.state',
                ]
            )
        )
            ->select(
                $db->quoteName(
                    [
                        'a.' . $this->state_field,
                        'a.state',
                        'a.state',
                        'a.state',
                    ],
                    [
                        'state',
                        'cat_state',
                        'published',
                        'a.published'
                    ]
                )
            )
            ->from($db->quoteName('#__jticketing_events', 'a'))
            ->join(
                'INNER',
                $db->quoteName('#__jticketing_events', 'c'),
                $db->quoteName('c.id') . ' = ' . $db->quoteName('a.id')
            );

        return $query;
    }
	
}
