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

use Joomla\CMS\Form\Form;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Router\Route;

/**
 * View to edit
 *
 * @since  1.0.0
 */
class TjCertificateViewCertificate extends HtmlView
{
	/**
	 * The Form object
	 *
	 * @var  Form
	 */
	protected $form;

	/**
	 * The certificate helper
	 *
	 * @var  object
	 */
	protected $certificateHelper;

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
		$app         = Factory::getApplication();
		$this->state = $this->get('State');
		$this->item  = $this->get('Item');

		// If training record then redirect to training record form
		if ($this->item->is_external)
		{
			$app->redirect(
				Route::_('index.php?option=com_tjcertificate&view=trainingrecord&layout=edit&id=' . $this->item->id, false)
			);
		}

		$this->form  = $this->get('Form');
		$this->input = Factory::getApplication()->input;
		$this->canDo = ContentHelper::getActions('com_tjcertificate', 'certificate', $this->item->id);

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
	 * @since   1.0.0
	 */
	protected function addToolbar()
	{
		$user       = Factory::getUser();
		$userId     = $user->id;
		$isNew      = empty($this->item->id);
		require_once JPATH_ADMINISTRATOR . '/components/com_tjcertificate/helpers/tjcertificate.php';

		$this->certificateHelper = new TjCertificateHelper;

		// Built the actions for new and existing records.
		$canDo = $this->canDo;
		$app = Factory::getApplication();
		$layout = $app->getInput()->get("layout");

		ToolbarHelper::title(
			Text::_('COM_TJCERTIFICATE_PAGE_VIEW_CERTIFICATE')
		);

		require_once JPATH_ADMINISTRATOR . '/components/com_tjcertificate/helpers/tjcertificate.php';
		TjCertificateHelper::addSubmenu('certificates');

		// Sidebar is automatically rendered by the admin template in Joomla 4/6

		// For new records, check the create permission.
		if ($layout != "default")
		{
			$app->getInput()->set('hidemainmenu', true);

			ToolbarHelper::title(
				Text::_('COM_TJCERTIFICATE_PAGE_' . ($isNew ? 'ADD_CERTIFICATE' : 'EDIT_CERTIFICATE')),
				'pencil-2 certificate-add'
			);

			if ($isNew)
			{
				ToolbarHelper::apply('certificate.apply');
				ToolbarHelper::save('certificate.save');
				ToolbarHelper::save2new('certificate.save2new');
			}
			else
			{
				$itemEditable = $this->isEditable($canDo, $userId);

				// Can't save the record if it's checked out and editable
				$this->canSave($itemEditable);
			}

			ToolbarHelper::modal('templatePreview', 'icon-eye', 'COM_TJCERTIFICATE_CERTIFICATE_TEMPLATE_TOOLBAR_PREVIEW');

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
			ToolbarHelper::apply('certificate.apply');
			ToolbarHelper::save('certificate.save');
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
