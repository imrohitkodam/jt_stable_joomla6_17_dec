<?php
/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2021 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access.
defined('_JEXEC') or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Form\Field\ListField;

/**
 * Supports an HTML select list of users
 *
 * @since  __DEPLOY_VERSION__
 */
class JFormFieldAllUsers extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	__DEPLOY_VERSION__
	 */
	protected $type = 'allusers';

	/**
	 * Fiedd to decide if options are being loaded externally and from xml
	 *
	 * @var		integer
	 * @since	__DEPLOY_VERSION__
	 */
	protected $loadExternally = 0;

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of HTMLHelper options.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getOptions()
	{
		$model = TJCERT::model('Agency', array('ignore_request' => true));
		$usersObject = $model->getUsers();

		$options = array();

		foreach ($usersObject as $user)
		{
			$options[] = HTMLHelper::_('select.option', $user->id, $user->name);
		}

		if (!$this->loadExternally)
		{
			// Merge any additional options in the XML definition.
			$options = array_merge(parent::getOptions(), $options);
		}

		return $options;
	}

	/**
	 * Method to get a list of options for a list input externally and not from xml.
	 *
	 * @return	array		An array of HTMLHelper options.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getOptionsExternally()
	{
		$this->loadExternally = 1;

		return $this->getOptions();
	}
}
