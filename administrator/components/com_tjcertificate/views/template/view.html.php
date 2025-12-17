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
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Router\Route;

/**
 * View to edit
 *
 * @since  1.0.0
 */
class TjCertificateViewTemplate extends HtmlView
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
	protected $TjCertificateHelper;

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
	 * Replacement tags based on clients
	 *
	 * @var  CMSObject
	 */
	protected $replacementTags;

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
		$this->canDo = ContentHelper::getActions('com_tjcertificate');

		if ($this->item->id && !$this->isEditable($this->canDo, Factory::getUser()->id))
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_TJCERTIFICATE_ERROR_CANNOT_ACCESS_PRIVATE_TEMPLATE'), 'notice');
			Factory::getApplication()->redirect(Route::_('index.php?option=com_tjcertificate&view=templates', false));

			return false;
		}

		if (!empty($this->item->client))
		{
			// Fetch replacement tags for the client
			$this->replacementTags = TJCERT::Template()->loadTemplateReplacementsByClient($this->item->client);
		}

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
		$app    = Factory::getApplication();
		$user   = Factory::getUser();
		$userId = $user->id;
		$isNew  = ($this->item->id == 0);
		require_once JPATH_ADMINISTRATOR . '/components/com_tjcertificate/helpers/tjcertificate.php';

		$this->TjCertificateHelper = new TjCertificateHelper;
		$checkedOut = $this->isCheckedOut($userId);

		// Built the actions for new and existing records.
		$canDo = $this->canDo;
		$layout = $app->getInput()->get("layout");

		ToolbarHelper::title(
			Text::_('COM_TJCERTIFICATE_PAGE_VIEW_CERTIFICATE_TEMPLATE')
		);

		require_once JPATH_ADMINISTRATOR . '/components/com_tjcertificate/helpers/tjcertificate.php';
		TjCertificateHelper::addSubmenu('templates');

		// For new records, check the create permission.
		if ($layout != "default")
		{
			$app->getInput()->set('hidemainmenu', true);

			ToolbarHelper::title(
				Text::_('COM_TJCERTIFICATE_PAGE_' . ($checkedOut ? 'VIEW_CERTIFICATE_TEMPLATE' :
					($isNew ? 'ADD_CERTIFICATE_TEMPLATE' : 'EDIT_CERTIFICATE_TEMPLATE'))
			), 'pencil-2 template-add'
			);

			if ($isNew)
			{
				ToolbarHelper::apply('template.apply');
				ToolbarHelper::save('template.save');
				ToolbarHelper::save2new('template.save2new');
			}
			else
			{
				$itemEditable = $this->isEditable($canDo, $userId);

				// Can't save the record if it's checked out and editable
				$this->canSave($checkedOut, $itemEditable);
			}

			// Add preview toolbar
			ToolbarHelper::modal('templatePreview', 'icon-eye', 'COM_TJCERTIFICATE_CERTIFICATE_TEMPLATE_TOOLBAR_PREVIEW');

			ToolbarHelper::cancel('template.cancel');
		}

		ToolbarHelper::divider();
	}

	/**
	 * Can't save the record if it's checked out and editable
	 *
	 * @param   boolean  $checkedOut    Checked Out
	 *
	 * @param   boolean  $itemEditable  Item editable
	 *
	 * @return void
	 */
	protected function canSave($checkedOut, $itemEditable)
	{
		if (!$checkedOut && $itemEditable)
		{
			ToolbarHelper::apply('template.apply');
			ToolbarHelper::save('template.save');
			ToolbarHelper::save2new('template.save2new');
			ToolbarHelper::save2copy('template.save2copy');
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
		return $canDo->get('template.edit') || ($canDo->get('template.edit.own') && $this->item->created_by == $userId);
	}

	/**
	 * Is Checked Out
	 *
	 * @param   integer  $userId  User ID
	 *
	 * @return boolean
	 */
	protected function isCheckedOut($userId)
	{
		return !($this->item->checked_out == 0 || $this->item->checked_out == $userId);
	}
}
