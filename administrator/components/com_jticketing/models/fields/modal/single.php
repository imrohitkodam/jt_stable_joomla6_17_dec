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

// Import Joomla modelitem library
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

/**
 * JFormFieldModal_Single class.
 *
 * @package  JTIcketing
 * @since    2.7.0
 */
class JFormFieldModal_Single extends FormField
{
	/**
	 * field type
	 * @var string
	 */
	protected $type = 'Modal_Single';
	/**
	 * Method to get the field input markup
	 */

	/**
	 * Method to get the field input markup
	 *
	 * @return  Array
	 *
	 * @since   1.8
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
		// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
		{
			$closeModaljs = 'document.querySelector("#jtSingle' . $this->id . ' .close").click()';
		}
		else
		{
			$closeModaljs = 'document.querySelector("#jtSingle' . $this->id . ' .btn-close").click()';
		}

		$script[] = 'if (typeof SqueezeBox === "undefined")
					{
						'. $closeModaljs .'
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
		$link = 'index.php?option=com_jticketing&amp;view=events&amp;layout=modal' . '&amp;tmpl=component&amp;function=jSelectBook_' . $this->id;

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('title');
		$query->from('#__jticketing_events');
		$query->where('id=' . (int) $this->value);
		$db->setQuery($query);

		if (!$title = $db->loadResult())
		{
		}

		if (empty($title))
		{
			$title = Text::_('COM_JTICKETING_SELECT_EVENT');
		}

		$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

		// The current book input field
		$html[] = JVERSION < '4.0.0' ? '' : '<div class="input-group">';
		$html[] = JVERSION < '4.0.0' ? '<div class="fltlft">' : '';
		$html[] = '  <input type="text" class="form-control" id="' . $this->id . '_name" value="' . $title . '" disabled="disabled" size="35" />';
		$html[] = JVERSION < '4.0.0' ? '</div>' : '';

		// The book select button
		$modalConfig = array('width' => '800px', 'height' => '300px', 'modalWidth' => 80, 'bodyHeight' => 70);
		$modalConfig['url'] = Route::_($link, false);
		$modalConfig['title'] = Text::_('COM_JTICKETING_SELECT_EVENT');
		$html[] = HTMLHelper::_('bootstrap.renderModal', 'jtSingle' . $this->id, $modalConfig);
		$html[] = '<div class="button2-left">';
		$html[] = '  <div class="blank">';

		// Joomla 6: JVERSION check removed
		if (false) // Legacy < '4.0.0')
		{
			$html[] = '<a data-target="#jtSingle' . $this->id . '" data-toggle="modal" title="' .
			Text::_('COM_JTICKETING_SELECT_EVENT') . '">' . Text::_('COM_JTICKETING_SELECT_EVENT') . '</a>';
		}
		else
		{
			$html[] = '<a class="btn btn-primary" data-bs-target="#jtSingle' . $this->id . '" data-bs-toggle="modal" title="' .
			Text::_('COM_JTICKETING_SELECT_EVENT') . '">' . Text::_('COM_JTICKETING_SELECT_EVENT') . '</a>';
		}

		$html[] = JVERSION < '4.0.0' ? '' : '  </div>';
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
