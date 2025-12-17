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

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;

/**
 * View to edit
 *
 * @since  __DEPLOY_VERSION__
 */
class TjCertificateViewBulkTrainingRecord extends HtmlView
{
	/**
	 * The Form object
	 *
	 * @var  Form
	 */
	protected $form;

	public $isAgencyEnabled = false;

	protected $comMultiAgency = 'com_multiagency';

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$this->form   = $this->get('Form');
		$this->input  = Factory::getApplication()->input;
		$this->params = ComponentHelper::getParams('com_tjcertificate');

		if (ComponentHelper::isEnabled($this->comMultiAgency) && $this->params->get('enable_multiagency'))
		{
			$this->isAgencyEnabled = true;
		}

		$layout = $this->input->get('layout', 'edit');

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function addToolbar()
	{
		$app = Factory::getApplication();

		$layout = $app->getInput()->get("layout");

		require_once JPATH_ADMINISTRATOR . '/components/com_tjcertificate/helpers/tjcertificate.php';
		TjCertificateHelper::addSubmenu('certificates');

		// Sidebar is automatically rendered by the admin template in Joomla 4/6

		if ($layout != "default")
		{
			$app->getInput()->set('hidemainmenu', true);

			ToolbarHelper::title(
				Text::_('COM_TJCERTIFICATE_PAGE_ADD_TRAINING_RECORDS'),
				'pencil-2 certificate-add'
			);

			$layout = '<button onclick="certificate.addRecords()" type="button" class="btn btn-small button-apply btn-success">
			<span class="icon-apply icon-white" aria-hidden="true"></span>' . Text::_('JTOOLBAR_APPLY') . '</button>';

			Toolbar::getInstance('toolbar')->appendButton('Custom', $layout);
			ToolbarHelper::cancel('certificate.cancel');
		}
	}
}
