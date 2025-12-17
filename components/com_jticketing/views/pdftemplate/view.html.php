<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * View to edit
 *
 * @since  1.8
 */
class JticketingViewPDFTemplate extends HtmlView
{
	protected $state;

	protected $item;

	protected $form;

	protected $params;

	public $ticketPdfsLink;
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
		$app          = Factory::getApplication();

		$this->user          = Factory::getUser();
		$this->state  = $this->get('State');
		$this->item   = $this->get('Item');
		$this->form   = $this->get('Form');
		$this->params = ComponentHelper::getParams('com_jticketing');
		$this->utilities = JT::utilities();
		$this->canSave         = $this->get('CanSave');

		// Validate user login.
		if (empty($this->user->id))
		{
			$msg = Text::_('COM_JTICKETING_MESSAGE_LOGIN_FIRST');

			// Get current url.
			$current = Uri::getInstance()->toString();
			$url     = base64_encode($current);
			$app->enqueueMessage($msg);
			$app->redirect(Route::_('index.php?option=com_users&view=login&return=' . $url, false));
		}


		if (!$this->item->id)
		{
			require_once(JPATH_ADMINISTRATOR."/components/com_jticketing/config.php");
			$this->form->setValue('body', NULL, $emails_config['message_body']);
			$cssData = file_get_contents(JPATH_SITE . "/components/com_jticketing/assets/css/email.css");
			$this->form->setValue('css', NULL, $cssData);

		}

		// print_r($this->form);die;

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		$this->isAdmin = 1;

		$path = JPATH_SITE . '/components/com_jticketing/helpers/common.php';

		if (!class_exists('JticketingCommonHelper'))
		{
			JLoader::register('JticketingCommonHelper', $path);
			JLoader::load('JticketingCommonHelper');
		}

		$venuesMenuID = $this->utilities->getItemId('index.php?option=com_jticketing&view=pdftemplates');
		$this->ticketPdfsLink 		= Route::_("index.php?option=com_jticketing&view=pdftemplates&Itemid=" . $venuesMenuID);

		// Call helper function
		JticketingCommonHelper::getLanguageConstant();

		$this->existingScoUrl  = '';
		$this->editId          = $this->item->id;

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->getInput()->set('hidemainmenu', true);

		$user  = Factory::getUser();
		$isNew = ($this->item->id == 0);

		$viewTitle = Text::_('COM_JTICKETING_PDF_TEMPLATE');

		if (isset($this->item->checked_out))
		{
			$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		}
		else
		{
			$checkedOut = false;
		}

		$canDo = JTicketingHelper::getActions();
		ToolbarHelper::title($viewTitle, 'pencil-2');

		// If not checked out, can save the item.
		ToolbarHelper::apply('pdftemplate.apply', 'JTOOLBAR_APPLY');
		ToolbarHelper::save('pdftemplate.save', 'JTOOLBAR_SAVE');

		if (!$checkedOut && ($canDo->{'core.create'}))
		{
			ToolbarHelper::custom('pdftemplate.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}

		if (empty($this->item->id))
		{
			ToolbarHelper::cancel('pdftemplate.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			ToolbarHelper::cancel('pdftemplate.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
