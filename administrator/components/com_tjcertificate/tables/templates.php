<?php
/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2021 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\Data\DataObject;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\String\StringHelper;

/**
 * Templates table class
 *
 * @since  1.0.0
 */
class TjCertificateTableTemplates extends Table
{
	/**
	 * Constructor
	 *
	 * @param   DataObjectbaseDriver  &$db  Database object
	 *
	 * @since  1.0.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__tj_certificate_templates', 'id', $db);
		$this->setColumnAlias('published', 'state');
	}

	/**
	 * Overloaded check function
	 *
	 * @return  true|false
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function check()
	{
		$db   = Factory::getDbo();
		$task = Factory::getApplication()->getInput()->get('task');

		if ($task == 'save2copy')
		{
			$this->unique_code = trim($this->unique_code);

			// Check if certificate template with same unique code is present
			$table = Table::getInstance('Templates', 'TjCertificateTable', array('dbo', $db));

			if ($table->load(array('unique_code' => $this->unique_code)) && ($table->id != $this->id || $this->id == 0))
			{
				$this->unique_code = StringHelper::increment($this->unique_code, 'dash', mt_rand(100, 1000000));

				while ($table->load(array('unique_code' => $this->unique_code)))
				{
					$this->unique_code = StringHelper::increment($this->unique_code, 'dash', mt_rand(100, 1000000));
				}
			}
		}

		return parent::check();
	}
}
