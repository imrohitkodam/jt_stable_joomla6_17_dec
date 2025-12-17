<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\AdminModel;

/**
 * Methods supporting a jticketing media integration.
 *
 * @since  2.0.0
 */
class JticketingModelMediaXref extends AdminModel
{
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   type    $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return	JTable	A database object
	 *
	 * @since	2.0
	 */
	public function getTable($type = 'mediaxref', $prefix = 'JticketingTable', $config = array())
	{
		$app = Factory::getApplication();

		if ($app->isClient("administrator"))
		{
			return Table::getInstance($type, $prefix, $config);
		}
		else
		{
			$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');

			return Table::getInstance($type, $prefix, $config);
		}
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return    JForm    A JForm object on success, false on failure
	 *
	 * @since    2.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_jticketing.mediaxref', 'mediaxref', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  mixed  The user id on success, false on failure.
	 *
	 * @since  2.0
	 */
	public function save($data)
	{
		if (!$data)
		{
			return false;
		}

		$result    = $this->getTable('mediaxref');
		$result->load(array('media_id' => (int) $data['media_id'], 'client_id' => (int) $data['client_id'], 'client' => $data['client']));

		if ($result->id)
		{
			return;
		}

		if ($returnData = parent::save($data))
		{
			return $returnData;
		}

		return false;
	}

	/**
	 * Method to get a event's media files from media xref.
	 *
	 * @param   INT  $clientId    event id
	 *
	 * @param   INT  $clientName  clientName
	 *
	 * @param   INT  $isGallery   isGallery
	 *
	 * @return Array.
	 *
	 * @since	2.0
	 */
	public function getEventMedia($clientId, $clientName, $isGallery = 0)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'media_id', 'client_id')));
		$query->from($db->quoteName('#__tj_media_files_xref'));
		$query->where($db->quoteName('client_id') . '=' . (int) $clientId);
		$query->where($db->quoteName('is_gallery') . '=' . (int) $isGallery);
		$query->where($db->quoteName('client') . '=' . $db->quote($clientName));
		$query->order($db->quoteName('id') .  ' DESC');
		$db->setQuery($query);

		return $db->loadObjectList();
	}
}
