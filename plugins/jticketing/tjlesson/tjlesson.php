<?php
/**
 * @package     JTicketing
 * @subpackage  plugin-jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\Folder;

/**
 * JTicketing-TJLesson Integration
 *
 * @since  1.0.0
 */
class PlgJticketingTjlesson extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * The form event. Load additional parameters when available into the field form.
	 * Only when the type of the form is of interest.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function onPrepareIntegrationField()
	{
		if (ComponentHelper::isEnabled('com_tjlms', true))
		{
			return array(
			'path' => JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/tjlesson.xml', 'name' => $this->_name
			);
		}
	}

	/**
	 * Disaply layout
	 *
	 * @param   ARRAY  $tjlesson  event-lesson data
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function onEventContentAfterDisplay($tjlesson)
	{
		$accessibility = $this->params['accessibility'];
		$olUserId = Factory::getUser()->id;

		$result = array();

		// If folder doesn't exist return an empty array
		if (!Folder::exists(JPATH_SITE . '/components/com_tjlms/helpers'))
		{
			return $result;
		}

		$this->tjlmsLessonHelper = new TjlmsLessonHelper;

		$flag = false;

		if (!empty($tjlesson->params['tjlesson']))
		{
			foreach ($tjlesson->params['tjlesson']['lessons'] as $key => $lessonId)
			{
				$lessonDataObj  = $this->tjlmsLessonHelper->getLesson($lessonId);

				if ($lessonDataObj->state == '1')
				{
					$flag = true;
					break;
				}
			}
		}

		if ($flag == true)
		{
			ob_start();
			$layout = $this->buildLayoutPath('resources');

			include $layout;
			$result['html'] = ob_get_contents();
			ob_end_clean();

			$result['tabName'] = Text::_("PLG_JTICKETING_TJLESSON_TAB_RESOURCES");
		}

		return $result;
	}

	/**
	 * Internal use functions
	 *
	 * @param   STRING  $layout  layout
	 *
	 * @return  string
	 *
	 * @since 1.0.0
	 */
	public function buildLayoutPath($layout)
	{
		$app = Factory::getApplication();

		$core_file 	= dirname(__FILE__) . '/tmpl/' . $layout . '.php';
		$override = JPATH_BASE . '/templates/' . $app->getTemplate() . '/html/plugins/' . $this->_type . '/' . $this->_name . '/' . $layout . '.php';

		if (File::exists($override))
		{
			return $override;
		}
		else
		{
			return $core_file;
		}
	}
}
