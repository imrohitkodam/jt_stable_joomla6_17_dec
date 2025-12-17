<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
use Joomla\CMS\Table\Table;

/**
 * Featured Table class.
 *
 * @since  2.0
 */
class JTicketingTablePDFTemplates extends Table
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  Database connector object
	 *
	 * @since   2.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__jticketing_pdf_templates', 'id', $db);
	}
}
