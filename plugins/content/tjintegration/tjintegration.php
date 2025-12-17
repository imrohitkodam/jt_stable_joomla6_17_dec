<?php
/**
 * @package     Content_Plugin
 * @subpackage  Tjintegration
 *
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * Joomla user group tjintegration Plugin
 *
 * @since  1.0.0
 */
class PlgContentTjIntegration extends CMSPlugin
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
	 * @param   JForm     $form  The form
	 * @param   stdClass  $data  The data
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public function onContentPrepareForm(Form $form, $data)
	{
		// Get form name to check, integrations plugins are available against it or not.
		$name = $form->getName();
		$name = substr($name, 0, strpos($name, "."));
		$name = str_replace("com_", "", $name);
		$path = JPATH_PLUGINS . "/{$this->_type}/{$this->_name}/params/tjintegration.xml";

		// Check if params file exists or if component is com_api
		if (! file_exists($path) || $name == 'api')
		{
			return;
		}

		PluginHelper::importPlugin($name);

		$formArray = Factory::getApplication()->triggerEvent('onPrepareIntegrationField', array());

		if (count($formArray))
		{
			$form->loadFile($path);
		}
		else
		{
			return;
		}

		foreach ($formArray as $val)
		{
			// Load the specific plugin parameters
			$fileXml = simplexml_load_file($val['path']);
			$integrationFields = $fileXml->xpath('//fields[@name="' . strtolower($val['name']) . '"]');
			$form->setFields($integrationFields, '', '', 'Integration');

			$hrelement = new SimpleXMLElement('<field type="spacer" class="tjintegration-hr" hr="true" label="' . Text::_($val['name']) . '" />');
			$form->setField($hrelement, '', '', 'Integration');
		}

		return true;
	}
}
