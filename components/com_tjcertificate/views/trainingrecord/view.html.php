<?php
/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2020 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;

/**
 * Training record view
 *
 * @since  __DEPLOY_VERSION__
 */
class TjCertificateViewTrainingRecord extends HtmlView
{
	protected $state;

	protected $item;

	protected $form;

	protected $create;

	protected $params;

	public $certificate = null;

	public $isAgencyEnabled = false;

	/**
	 * Manage own  Permissions
	 *
	 * @var  boolean
	 */
	public $manageOwn;

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
		$layout            = $this->input->get('layout');
		$id                = $this->input->getInt('id');
		$this->item        = $this->get('Item');
		$this->certificate = TJCERT::Certificate();
		$this->params      = ComponentHelper::getParams('com_tjcertificate');
		$this->manage 	   = $this->user->authorise('certificate.external.manage', 'com_tjcertificate');
		$this->manageOwn   = $this->user->authorise('certificate.external.manageown', 'com_tjcertificate');

		if (ComponentHelper::isEnabled($this->comMultiAgency) && $this->params->get('enable_multiagency'))
		{
			$this->isAgencyEnabled = true;
		}

		if (!$this->manage)
		{
			// If certificate view is private then view is available only for record owner
			if (!$this->params->get('certificate_scope') && Factory::getUser()->id != $this->item->user_id)
			{
				Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');
				Factory::getApplication()->redirect(Route::_('index.php?option=com_tjcertificate&view=certificates', false));

				return false;
			}
		}

		$this->create = $this->user->authorise('certificate.external.create', 'com_tjcertificate');

		if (!$this->create)
		{
			throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$this->state = $this->get('State');
		$this->form  = $this->get('Form');
		$this->params = ComponentHelper::getParams('com_tjcertificate');
		$this->allowedFileExtensions = $this->params->get('upload_extensions');
		$this->uploadLimit      = $this->params->get('upload_maxsize', '1024');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		parent::display($tpl);
	}
}
