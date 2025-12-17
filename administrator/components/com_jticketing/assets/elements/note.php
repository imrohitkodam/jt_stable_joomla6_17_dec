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
defined('_JEXEC') or die();
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

if (file_exists(JPATH_ROOT . '/media/techjoomla_strapper/tjstrapper.php'))
{
	require_once JPATH_ROOT . '/media/techjoomla_strapper/tjstrapper.php';
	TjStrapper::loadTjAssets('com_jticketing');
}

/**
 * Class for gettingheader tooltip for each elements
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JFormFieldNote extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var String
	 * @since 1.6
	 */
	public $type = 'note';

	/**
	 * Get html of the element
	 *
	 * @return  Html
	 *
	 * @since  1.0.0
	 */
	public function getInput()
	{
		$html = '';

		if ($this->id == 'jform_jticketing_google_map_api_note')
		{
			$html .= '<div class="span9 alert alert-info">' . Text::_('COM_JTICKETING_GOOGLE_MAP_API_NOTE') . '</div>';
		}

		$return = $html;

		return $return;
	}
}
