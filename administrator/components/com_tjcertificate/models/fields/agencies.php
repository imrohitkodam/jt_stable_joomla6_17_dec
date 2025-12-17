<?php
/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2021 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Field\ListField;

/**
 * Supports an HTML select list of agencies
 *
 * @since  __DEPLOY_VERSION__
 */
class JFormFieldAgencies extends ListField
{
	/**
	 * Fiedd to decide if options are being loaded externally and from xml
	 *
	 * @var     integer
	 * @since   __DEPLOY_VERSION__
	 */
	protected $loadExternally = 0;

	protected $comMultiAgency = 'com_multiagency';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array       An array of HTMLHelper options.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getOptions()
	{
		$db     = Factory::getDbo();
		$user   = Factory::getUser();
		$query  = $db->getQuery(true);

		$canManageAllAgencyUser = $user->authorise('core.manage.all.agency.user', $this->comMultiAgency);

		// Select the required fields from the table.
		$query->select('DISTINCT ml.id, ml.title');
		$query->from($db->quoteName('#__tjmultiagency_multiagency', 'ml'));
		$query->join('INNER', $db->quoteName('#__tj_clusters', 'c') . ' ON (' . $db->quoteName('c.client_id') . ' = ' . $db->quoteName('ml.id') . ')');
		$query->join('INNER', $db->quoteName('#__tj_cluster_nodes', 'cn') . ' ON ' . $db->quoteName('cn.cluster_id') . ' = ' . $db->quoteName('c.id'));
		$query->where($db->quoteName('c.state') . ' = ' . 1);

		if (!$canManageAllAgencyUser)
		{
			$query->where($db->quoteName('cn.user_id') . ' = ' . (int) $user->id);
		}

		$query->order($db->escape($db->quoteName('ml.title') . ' ASC'));
		$db->setQuery($query);

		// Get all agencies.
		$agencies = $db->loadObjectList();

		$options = [];
		$options[] = HTMLHelper::_('select.option', "", Text::_('COM_TJCERTIFICATE_ORG_SELECT'));

		foreach ($agencies as $agency)
		{
			$options[] = HTMLHelper::_('select.option', $agency->id, $agency->title);
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
	 * @return  array       An array of HTMLHelper options.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getOptionsExternally()
	{
		$this->loadExternally = 1;

		return $this->getOptions();
	}
}
