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

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Training records view
 *
 * @since  __DEPLOY_VERSION__
 */
class TjCertificateViewBulkTrainingRecord extends HtmlView
{
	protected $form;

	protected $params;

	public $isAgencyEnabled = false;

	/**
	 * Manage Permissions
	 *
	 * @var  boolean
	 */
	public $manage;

	protected $comMultiAgency = 'com_multiagency';

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function display($tpl = null)
	{
		$app               = Factory::getApplication();
		$this->input       = $app->input;
		$this->user        = Factory::getUser();
		$this->params      = ComponentHelper::getParams('com_tjcertificate');
		$this->manage 	   = $this->user->authorise('certificate.external.manage', 'com_tjcertificate');

		if (!$this->manage)
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');

			return false;
		}

		if (ComponentHelper::isEnabled($this->comMultiAgency) && $this->params->get('enable_multiagency'))
		{
			$this->isAgencyEnabled = true;
		}

		$this->form  = $this->get('Form');

		parent::display($tpl);
	}
}
