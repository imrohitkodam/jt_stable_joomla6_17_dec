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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

// Import Joomla modelitem library


/**
 * JFormFieldModal_Single event class.
 *
 * @package  JTicketing
 * @since    1.8
 */
class JFormFieldModal_Event extends FormField
{
	/**
	 * field type
	 * @var string
	 */
	protected $type = 'Modal_Event';

	/**
	 * Method to get the field input markup
	 *
	 * @return  Array
	 *
	 * @since   1.0
	 */
	protected function getInput()
	{
		// Load modal behavior
		HTMLHelper::_('bootstrap.renderModal', 'a.modal');

		// Build the script
		$script   = array();
		$script[] = '    function jSelectBook_' . $this->id . '(id, title, object) {';
		$script[] = '        document.getElementById("' . $this->id . '_id").value = id;';
		$script[] = '        document.getElementById("' . $this->id . '_name").value = title;';

		$script[] = 'if (typeof SqueezeBox === "undefined")
					{
						document.querySelector("#jtSelectEvent' . $this->id . ' .close").click();
					}
					else
					{
						SqueezeBox.close();
					}';

		$script[] = '    }';

		// Add to document head
		Factory::getDocument()->addScriptDeclaration(implode("\n", $script));

		// Setup variables for display
		$html = array();
		$link = 'index.php?option=com_jticketing&amp;view=events&amp;layout=modal' . '&amp;tmpl=component&amp;function=jSelectBook_' .
		$this->id;

		$db    = Factory::getDbo();

		$query = $db->getQuery(true);
		$query->select('e.title');
		$query->from($db->quoteName('#__jticketing_integration_xref', 'i'));
		$query->join('LEFT', $db->quoteName('#__jticketing_events', 'e') . ' ON (' . $db->quoteName('i.eventid') . ' = ' . $db->quoteName('e.id') . ')');
		$query->where($db->quoteName('i.source') . ' = ' . $db->quote("com_jticketing"));
		$db->setQuery($query);
		$title = $db->loadResult();

		if (empty($title))
		{
			$title = Text::_('COM_JTICKETING_FIELD_SELECT_EVENT');
		}

		// The current book input field
		$html[] = '<div class="">';
		$html[] = '  <input type="text" id="' . $this->id . '_name" value="' . $title . '" disabled="disabled" size="35" />';
		$html[] = '</div>';

		// The book select button
		$modalConfig = array('width' => '800px', 'height' => '300px', 'modalWidth' => 80, 'bodyHeight' => 70);
		$modalConfig['url'] = $link;
		$modalConfig['title'] = Text::_('COM_JTICKETING_SELECT_EVENT_TITLE');
		$html[] = HTMLHelper::_('bootstrap.renderModal', 'jtSelectEvent' . $this->id, $modalConfig);
		$html[] = '<div class="button2-left">';
		$html[] = '  <div class="blank">';

		// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
		{
			$html[] = '    <a data-target="#jtSelectEvent' . $this->id . '" data-toggle="modal" title="' .
			Text::_('COM_JTICKETING_SELECT_EVENT_TITLE') . '">' . Text::_('COM_JTICKETING_SELECT_CHANGE') . '</a>';
		}
		else
		{
			$html[] = '    <a data-bs-target="#jtSelectEvent' . $this->id . '" data-bs-toggle="modal" title="' .
			Text::_('COM_JTICKETING_SELECT_EVENT_TITLE') . '">' . Text::_('COM_JTICKETING_SELECT_CHANGE') . '</a>';
		}

		$html[] = '  </div>';
		$html[] = '</div>';

		// The active book id field
		if (0 == (int) $this->value)
		{
			$value = '';
		}
		else
		{
			$value = (int) $this->value;
		}

		// Class='required' for client side validation
		$class = '';

		if ($this->required)
		{
			$class = ' class="required modal-value"';
		}

		$html[] = '<input type="hidden" id="' . $this->id . '_id"' . $class . ' name="' . $this->name . '" value="' . $value . '" />';

		return implode("\n", $html);
	}
}
