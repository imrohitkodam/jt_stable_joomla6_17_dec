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

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;

/**
 * The template controller
 *
 * @since  1.0.0
 */
class TjCertificateControllerTemplate extends FormController
{
	/**
	 * The client for which the templates are being created.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $client;

	/**
	 * The extension for which the templates are being created.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $extension;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since  1.6
	 * @see    BaseController
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$app    = Factory::getApplication();
		$jinput = $app->input;

		if (empty($this->extension))
		{
			$this->extension = $jinput->get('extension', '');
		}

		if (empty($this->client))
		{
			$this->client = $jinput->get('client', '');
		}
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   1.6
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$append = parent::getRedirectToItemAppend($recordId);

		if (!empty ($this->extension))
		{
			$append .= '&extension=' . $this->extension;
		}
		elseif (!empty ($this->client))
		{
			$append .= '&client=' . $this->client;
		}

		return $append;
	}

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since   1.6
	 */
	protected function getRedirectToListAppend()
	{
		$append = parent::getRedirectToListAppend();

		if (!empty ($this->extension))
		{
			$append .= '&extension=' . $this->extension;
		}
		elseif (!empty ($this->client))
		{
			$append .= '&client=' . $this->client;
		}

		return $append;
	}
}
