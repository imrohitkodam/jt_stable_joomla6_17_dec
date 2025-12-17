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

use Joomla\CMS\Form\Form;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Component\ComponentHelper;

/**
 * View to edit
 *
 * @since  __DEPLOY_VERSION__
 */
class TjCertificateViewTrainingRecord extends HtmlView
{
	/**
	 * The Form object
	 *
	 * @var  Form
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var  object
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var  object
	 */
	protected $state;

	/**
	 * The actions the user is authorised to perform
	 *
	 * @var  CMSObject
	 */
	protected $canDo;

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
		$this->state = $this->get('State');
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');
		$this->input = Factory::getApplication()->input;
		$this->canDo = ContentHelper::getActions('com_tjcertificate', 'certificate', $this->item->id);
		$this->params = ComponentHelper::getParams('com_tjcertificate');
		$this->allowedFileExtensions = $this->params->get('upload_extensions');
		$this->uploadLimit      = $this->params->get('upload_maxsize', '1024');
		$this->certificate = TJCERT::Certificate();

		if (ComponentHelper::isEnabled($this->comMultiAgency) && $this->params->get('enable_multiagency'))
		{
			$this->isAgencyEnabled = true;
		}

		$layout = $this->input->get('layout', 'edit');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

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
		$user       = Factory::getUser();
		$userId     = $user->id;
		$isNew      = empty($this->item->id);

		// Built the actions for new and existing records.
		$canDo = $this->canDo;
		$app = Factory::getApplication();
		$layout = $app->getInput()->get("layout");

		require_once JPATH_ADMINISTRATOR . '/components/com_tjcertificate/helpers/tjcertificate.php';
		TjCertificateHelper::addSubmenu('certificates');

		// Sidebar is automatically rendered by the admin template in Joomla 4/6

		// For new records, check the create permission.
		if ($layout != "default")
		{
			$app->getInput()->set('hidemainmenu', true);

			ToolbarHelper::title(
				Text::_('COM_TJCERTIFICATE_PAGE_' . ($isNew ? 'ADD_TRAINING_RECORD' : 'EDIT_TRAINING_RECORD')),
				'pencil-2 certificate-add'
			);

			if ($isNew)
			{
				ToolbarHelper::apply('trainingrecord.apply');
				ToolbarHelper::save('trainingrecord.save');
				ToolbarHelper::save2new('trainingrecord.save2new');
			}
			else
			{
				$itemEditable = $this->isEditable($canDo, $userId);

				// Can't save the record if it's checked out and editable
				$this->canSave($itemEditable);
			}

			if (empty($this->item->id))
			{
				ToolbarHelper::cancel('certificate.cancel');
			}
			else
			{
				ToolbarHelper::cancel('certificate.cancel', 'JTOOLBAR_CLOSE');
			}
		}

		ToolbarHelper::divider();
	}

	/**
	 * Can't save the record if it's checked out and editable
	 *
	 * @param   boolean  $itemEditable  Item editable
	 *
	 * @return void
	 */
	protected function canSave($itemEditable)
	{
		if ($itemEditable)
		{
			ToolbarHelper::apply('trainingrecord.apply');
			ToolbarHelper::save('trainingrecord.save');
		}
	}

	/**
	 * Is editable
	 *
	 * @param   Object   $canDo   Checked Out
	 *
	 * @param   integer  $userId  User ID
	 *
	 * @return boolean
	 */
	protected function isEditable($canDo, $userId)
	{
		// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
		return $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId);
	}
}
