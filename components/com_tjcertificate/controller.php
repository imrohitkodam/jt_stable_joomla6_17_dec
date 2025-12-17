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

use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

require_once JPATH_ADMINISTRATOR . '/components/com_tjcertificate/includes/tjcertificate.php';
require_once JPATH_ADMINISTRATOR . '/components/com_tjcertificate/helpers/tjcertificate.php';

/**
 * Class TjCertificateController
 *
 * @since  1.0.0
 */
class TjCertificateController extends BaseController
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   mixed    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link InputFilter::clean()}.
	 *
	 * @return  JController   This object to support chaining.
	 *
	 * @since    1.0.0
	 */
	public function display($cachable = false, $urlparams = false)
	{
		parent::display();
	}
}
