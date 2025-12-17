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
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * jticketing Model
 *
 * @since  0.0.1
 */
class JTicketingModelAttendeefields extends AdminModel
{
	/**
	 * @var      string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_JTICKETING';

	/**
	 * @var   string  Alias to manage history control
	 * @since   3.2
	 */
	public $typeAlias = 'com_jticketing.attendeefields';

	/**
	 * @var null  Item data
	 * @since  1.6
	 */
	protected $item = null;

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed    A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			'com_jticketing.attendeefields',
			'attendeefields',
			array(
				'control' => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   string  $type    Data for the form.
	 * @param   string  $prefix  True if the form is to load its own data (default case), false if not.
	 * @param   array   $config  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  table
	 *
	 * @since   1.6
	 */
	public function getTable($type = 'Attendeefields', $prefix = 'JticketingTable', $config = array())
	{
		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * This is used to get custom user entry fields
	 *
	 * @param   int  $params  params
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getUserEntryField($params = '')
	{
		$details = '';
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('attnds.id')));
		$query->from($db->quoteName('#__jticketing_attendees', 'attnds'));

		if (isset($params['attendee_id']))
		{
			$query->where($db->quoteName('attnds.id') . ' = ' . $db->quote($params['attendee_id']));
		}

		$db->setQuery($query);
		$attendeeIds = $db->loadObjectlist();

		foreach ($attendeeIds AS $attendeeId)
		{
			$result = '';
			$query = $db->getQuery(true);
			$query->select($db->qn(array('fv.field_value','fv.field_id', 'fields.name' )));
			$query->from($db->qn('#__jticketing_attendees', 'attnds'));
			$query->join('INNER', $db->qn('#__jticketing_attendee_field_values', 'fv')
			. 'ON (' . $db->qn('attnds.id') . ' = ' . $db->qn('fv.attendee_id') . ')');
			$query->join('INNER', $db->qn('#__jticketing_attendee_fields', 'fields') .
			'ON (' . $db->qn('fields.id') . ' = ' . $db->qn('fv.field_id') . ')');
			$query->where($db->qn('field_source') . ' = "com_jticketing"');
			$query->where($db->qn('attnds.id') . ' = ' . $db->quote($attendeeId->id));

			if (isset($params['user_id']))
			{
				$query->where($db->quoteName('attnds.owner_id') . ' = ' . $db->quote($params['user_id']));
			}

			if (isset($params['field_id']))
			{
				$query->where($db->quoteName('fv.field_id') . ' = ' . $db->quote($params['field_id']));
			}

			$db->setQuery($query);
			$result = $db->loadObjectlist();

			if ($result)
			{
				$details[$attendeeId->id] = $result;
			}
		}

		return $details;
	}

	/**
	 * This is used to get custom user entry fields from tjfields
	 *
	 * @param   int  $params  params
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getUniversalUserEntryField($params = '')
	{
		$fieldManagerPath = JPATH_SITE . '/components/com_tjfields/helpers/tjfields.php';

		if (file_exists($fieldManagerPath))
		{
			$TjfieldsHelper          = new TjfieldsHelper;
			$universalAttendeeFields = $TjfieldsHelper->getUniversalFields('com_jticketing.ticket');
			$details = '';

			$db = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('attnds.id')));
			$query->from($db->quoteName('#__jticketing_attendees', 'attnds'));

			if (isset($params['attendee_id']))
			{
				$query->where($db->quoteName('attnds.id') . ' = ' . $db->quote($params['attendee_id']));
			}

			$db->setQuery($query);
			$attendeeIds = $db->loadObjectlist();
			$result = '';

			if ($universalAttendeeFields)
			{
				foreach ($attendeeIds AS $attendeeId)
				{
					$i = 0;

					foreach ($universalAttendeeFields AS $field)
					{
						$query = $db->getQuery(true);
						$query->select($db->qn(array('fv.field_value','fv.field_id')));
						$query->from($db->qn('#__jticketing_attendees', 'attnds'));
						$query->join('INNER', $db->qn('#__jticketing_attendee_field_values', 'fv')
						. 'ON (' . $db->qn('attnds.id') . ' = ' . $db->qn('fv.attendee_id') . ')');
						$query->where($db->qn('fv.field_source') . ' = ' . $db->quote('com_tjfields.com_jticketing.ticket'));
						$query->where($db->qn('fv.field_id') . ' = ' . $db->quote($field->id));
						$query->where($db->qn('attnds.id') . ' = ' . $db->quote($attendeeId->id));

						if (isset($params['user_id']))
						{
							$query->where($db->quoteName('attnds.owner_id') . ' = ' . $db->quote($params['user_id']));
						}

						if (isset($params['field_id']))
						{
							$query->where($db->quoteName('fv.field_id') . ' = ' . $db->quote($params['field_id']));
						}

						$db->setQuery($query);
						$resultObj = $db->loadObject();

						if (!empty($resultObj))
						{
							$result[$i]           = $resultObj;
							$result[$i]->name     = $field->name;
							$result[$i]->field_id = $field->id;
							$i++;
						}
					}

					if (!empty($result))
					{
						$details[$attendeeId->id] = $result;
					}
				}
			}

			if (!empty($details))
			{
				return $details;
			}
		}
	}

	/**
	 * check for attendee field values
	 *
	 * @param   integer  $attendeeFieldId  id for the attendee field
	 *
	 * @return integer   $res           id if there exists attendee field value against it
	 *
	 * @since  2.1
	 */
	public function checkAttendeeFieldValue($attendeeFieldId)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('id'));
		$query->from($db->quoteName('#__jticketing_attendee_field_values'));

		if (!empty($attendeeFieldId))
		{
			$query->where($db->quoteName('field_id') . ' = ' . $db->quote($attendeeFieldId));
		}

		$db->setQuery($query);
		$res = $db->loadResult();

		return $res;
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   Array    &$pks   A list of the primary keys to change.
	 * @param   Integer  $state  The value of the published state.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.3.4
	 */
	public function publish(&$pks, $state=1)
	{
		if (parent::publish($pks, $state))
		{
			$extension = Factory::getApplication()->getInput()->get('option');

			// Include the content plugins for the change of category state event.
			PluginHelper::importPlugin('jticketing');

			// Trigger the onCategoryChangeState event.
			Factory::getApplication()->triggerEvent('onAfterJtAttendeeFieldsChangeState', array($extension, $pks, $state));

			return true;
		}
	}

	/**
	 * Returns array of order object for frontend listing.
	 *
	 * @param   array  $options  Filters
	 *
	 * @since  2.5.0
	 *
	 * @return  mixed   The return value or null if the query failed.
	 */
	public function getFields($options = array())
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('DISTINCT *');
		$query->from($db->quoteName('#__jticketing_attendee_fields'));

		if (isset($options['state']))
		{
			$query->where($db->quoteName('state') . ' = ' . $db->quote($options['state']));
		}

		if (isset($options['eventid']))
		{
			$query->where($db->quoteName('eventid') . ' = ' . $db->quote($options['eventid']));
		}

		if (isset($options['core']))
		{
			$query->where($db->quoteName('core') . ' = ' . $db->quote($options['core']));
		}

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Method to get all attendee fields with values
	 *
	 * @param   integer  $eventId     Event detail Id
	 * @param   integer  $attendeeId  Attendee id for each attendee.
	 *
	 * @since  2.5.0
	 *
	 * @return array
	 */
	public function getAttendeeFields($eventId, $attendeeId, $categoryId = '')
	{
		$attendeeFields = $this->getFields(
			array('core' => 1,
				'state' => 1)
		);

		// Get the event Specific fields.
		$eventspecificFields = $this->getFields(
		array('core' => 0,
			'state' => 1,
			'eventid' => $eventId)
		);

		if (is_array($attendeeFields))
		{
			// Merge the event specific fields inside the attendeeFields.
			$attendeeFields = array_merge($attendeeFields, $eventspecificFields);

			// Add the soucre inside the core and event specific elements.
			$attendeeFields = array_map(
				function ($each) {
					$each->source    = 'com_jticketing';

					return $each;
				}, $attendeeFields
			);
		}

		$tjfields = '';

		// Find  fields which are created using field manager.
		if (file_exists(JPATH_ROOT . '/components/com_tjfields/helpers/tjfields.php'))
		{
			$filedHelperPath = JPATH_ROOT . '/components/com_tjfields/helpers/tjfields.php';

			if (!class_exists('TjfieldsHelper'))
			{
				JLoader::register('TjfieldsHelper', $filedHelperPath);
				JLoader::load('TjfieldsHelper');
			}

			$TjfieldsHelper	= new TjfieldsHelper;
			$tjfields 		= $TjfieldsHelper->getUniversalFields('com_jticketing.ticket');

			if ($categoryId)
			{
				$categoriesTJFields	= $TjfieldsHelper->getCategoryFields('com_jticketing.ticket', $categoryId);

				if ($categoriesTJFields)
				{
					$tjfields = array_merge($categoriesTJFields, $tjfields);
				}
			}

			if ($tjfields)
			{
				foreach ($tjfields AS $key => &$val)
				{
					$val->default_selected_option = $TjfieldsHelper->getOptions($val->id);

					// Set as universal fields, this is important and set the source.
					$val->is_universal 	= 1;
					$val->source    	= 'com_tjfields.com_jticketing.ticket';
				}
			}
		}

		if (is_array($attendeeFields))
		{
			// Merge the event specific fields inside the attendeeFields.
			$attendeeFields = array_merge($attendeeFields, $tjfields);
		}

		if (!empty($attendeeId))
		{
			$attendeeFields = $this->getFieldsValue($attendeeFields, $attendeeId);
		}

		return $attendeeFields;
	}

	/**
	 * Method to get all the values of the attendee fields
	 *
	 * @param   Array  $attendeeFields  Fields array with all details
	 * @param   INT    $attendeeId      Attendee id for each attendee.
	 *
	 * @since  2.5.0
	 *
	 * @return array
	 */
	public function getFieldsValue($attendeeFields, $attendeeId)
	{
		foreach ($attendeeFields as $field)
		{
			$db		= Factory::getDbo();
			$query 	= $db->getQuery(true);

			$query->select($db->qn(array('f.field_value','f.attendee_id','f.field_source')));
			$query->from($db->qn('#__jticketing_attendee_field_values', 'f'));
			$query->where($db->qn('f.field_source') . '=' . $db->quote($field->source));
			$query->where($db->qn('f.field_id') . '=' . (int) $field->id);

			if (!empty($attendeeId))
			{
				$query->where($db->qn('f.attendee_id') . '=' . (int) $attendeeId);
			}

			$db->setQuery($query);
			$attendeeFieldsValue = $db->loadObjectlist('attendee_id');

			// Perform opreations on the value
			foreach ($attendeeFieldsValue as $value)
			{
				// Check for only tjfield entry for that field is present or not
				if ($value->field_source == 'com_tjfields.com_jticketing.ticket')
				{
					if ($field->type == 'radio' || $field->type == 'multi_select' || $field->type == 'single_select' || $field->type == 'tjlist')
					{
						$op_name            = '';
						$option_value       = explode('|', $value->field_value);
						$option_value_array = json_encode($option_value);
						$option_name		= array();

						if (file_exists(JPATH_ROOT . '/components/com_tjfields/helpers/tjfields.php'))
						{
							$filedHelperPath = JPATH_ROOT . '/components/com_tjfields/helpers/tjfields.php';

							if (!class_exists('TjfieldsHelper'))
							{
								JLoader::register('TjfieldsHelper', $filedHelperPath);
								JLoader::load('TjfieldsHelper');
							}

							$TjfieldsHelper  	= new TjfieldsHelper;
							$option_name        = $TjfieldsHelper->getOptions($field->id, $option_value_array);
						}

						foreach ($option_name as $on)
						{
							$op_name .= $on->options . " ";
						}

						$value->field_value = $op_name;
					}
				}
				else
				{
					$value->field_value = str_replace('|', ' ', $value->field_value);
				}
			}

			$field->attendee_value = $attendeeFieldsValue;
		}

		return $attendeeFields;
	}

	/**
	 * Method to get extra fields label used in csv
	 *
	 * @param   int  $event_id     event id of the event to export to csv
	 * @param   int  $attendee_id  attendee id to to export to csv
	 *
	 * @return  object
	 */
	public function extraFieldslabel($event_id = '', $attendee_id = '', $categoryId = '')
	{
		$labelArrayJT = array();
		$db           = Factory::getDbo();
		$query        = $db->getQuery(true);
		$query->select($db->quoteName(array('f.id','f.label','f.name')));
		$query->from($db->quoteName('#__jticketing_attendee_fields', 'f'));
		$query->where($db->quoteName('f.state') . '=1');
		$query->andwhere(
				array(
						$db->quoteName('f.eventid') . '=0',
						$db->quoteName('f.eventid') . ' = ' . (int) $event_id
				)
				);

		$db->setQuery($query);
		$AttendeefieldsJticketing = $db->loadObjectlist();

		// Put label in array created
		foreach ($AttendeefieldsJticketing as $afj)
		{
			$afj->source    = 'com_jticketing';
			$labelArrayJT[] = $afj;
		}

		$TjfieldsHelperPath = JPATH_ROOT . '/components/com_tjfields/helpers/tjfields.php';

		if (!class_exists('TjfieldsHelper'))
		{
			JLoader::register('TjfieldsHelper', $TjfieldsHelperPath);
			JLoader::load('TjfieldsHelper');
		}

		$TjfieldsHelper   = new TjfieldsHelper;
		$AttendeefieldsFM = $TjfieldsHelper->getUniversalFields('com_jticketing.ticket');

		if ($categoryId)
		{
			$categoriesTJFields	= $TjfieldsHelper->getCategoryFields('com_jticketing.ticket', $categoryId);

			if ($categoriesTJFields)
			{
				$AttendeefieldsFM = array_merge($categoriesTJFields, $AttendeefieldsFM);
			}
		}

		if ($AttendeefieldsFM)
		{
			foreach ($AttendeefieldsFM as $FMFields)
			{
				$obj            = new stdclass;
				$obj->id        = $FMFields->id;
				$obj->name      = $FMFields->name;
				$obj->label     = $FMFields->label;
				$obj->type      = $FMFields->type;
				$obj->source    = 'com_tjfields.com_jticketing.ticket';
				$labelArrayJT[] = $obj;
			}
		}

		foreach ($labelArrayJT as $lab)
		{
			$query = $db->getQuery(true);

			$query->select($db->quoteName(array('f.field_value','f.attendee_id','f.field_source')));
			$query->from($db->quoteName('#__jticketing_attendee_field_values', 'f'));
			$query->where($db->quoteName('f.field_source') . '=' . $db->quote($lab->source));
			$query->where($db->quoteName('f.field_id') . '=' . (int) $lab->id);

			if ($attendee_id)
			{
				$query->where($db->quoteName('f.attendee_id') . '=' . (int) $attendee_id);
			}

			$db->setQuery($query);
			$attendee_f_value = $db->loadObjectlist('attendee_id');

			foreach ($attendee_f_value as $af)
			{
				if ($af->field_source == 'com_tjfields.com_jticketing.ticket')
				{
					if ($lab->type == 'radio' || $lab->type == 'multi_select' || $lab->type == 'single_select')
					{
						$op_name            = '';
						$option_value       = explode('|', $af->field_value);
						$option_value_array = json_encode($option_value);
						$option_name        = $TjfieldsHelper->getOptions($lab->id, $option_value_array);

						foreach ($option_name as $on)
						{
							$op_name .= $on->options . " ";
						}

						$af->field_value = $op_name;
					}
				}
				else
				{
					$af->field_value = str_replace('|', ' ', $af->field_value);
				}
			}

			$lab->attendee_value = $attendee_f_value;
		}

		return $labelArrayJT;
	}
}
