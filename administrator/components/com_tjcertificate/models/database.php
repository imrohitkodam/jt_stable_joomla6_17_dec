<?php
/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Component\Config\Administrator\Model\ApplicationModel;

if (JVERSION < '4.0.0')
{
	require_once JPATH_ADMINISTRATOR . '/components/com_installer/models/database.php';
	require_once JPATH_SITE . '/components/com_config/model/cms.php';
	require_once JPATH_SITE . '/components/com_config/model/form.php';
	require_once JPATH_ADMINISTRATOR . '/components/com_config/models/application.php';
}

/**
 * Manage TjCertificate database operations
 *
 * @since  1.0.0
 */
class TjCertificateModelDatabase extends BaseDatabaseModel
{
	protected $extensionName = 'com_tjcertificate';

	/**
	 * Function used to set permission as allowed for manager user groups if not set to any other
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function allowEditOwnForManagerGroup()
	{
		$db = Factory::getDbo();

		$templateEditOwn = 'template.edit.own';

		// Get order Ids of selected course
		$query = $db->getQuery(true);
		$query->select($db->quoteName('rules'));
		$query->from($db->quoteName('#__assets'));
		$query->where($db->quoteName('name') . ' = ' . $db->quote($this->extensionName));
		$db->setQuery($query);
		$temp = $db->loadResult();

		$rules = (array) json_decode($temp);

		if (empty($rules) || !in_array($templateEditOwn, $rules))
		{
			$managerGroup = Table::getInstance('Usergroup', 'JTable');
			$managerGroup->load(array('title' => 'Manager'));

			// Get Post DATA
			$permissions = array(
			'component' => $this->extensionName,
			'action'    => $templateEditOwn,
			'rule'      => $managerGroup->id,
			'value'     => '1',
			'title'     => $this->extensionName
			);

			if (JVERSION < 4.0)
			{
				// Load Permissions from Session and send to Model
				$model    = new ConfigModelApplication;
				$response = $model->storePermissions($permissions);
			}
			else
			{
				// Load Permissions from Session and send to Model
				$model    = new ApplicationModel;
				$response = $model->storePermissions($permissions);
			}
		}
	}
}
