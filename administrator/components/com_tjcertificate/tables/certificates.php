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

use Joomla\Data\DataObject;
use Joomla\CMS\Table\Table;

/**
 * Certificate table class
 *
 * @since  1.0.0
 */
class TjCertificateTableCertificates extends Table
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
		parent::__construct('#__tj_certificate_issue', 'id', $db);

		// Set the alias since the column is called state
		$this->setColumnAlias('published', 'state');
	}
}
