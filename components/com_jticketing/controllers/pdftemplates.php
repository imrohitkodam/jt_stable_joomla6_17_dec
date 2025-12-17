<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Controller\AdminController;

/**
 * PDFTemplates list controller class.
 *
 * @since  1.6
 */
class JticketingControllerPDFTemplates extends AdminController
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional
	 * @param   array   $config  Configuration array for model. Optional
	 *
	 * @return object	The model
	 *
	 * @since	1.6
	 */
	public function &getModel($name = 'pdftemplates', $prefix = 'JticketingModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}

	/**
	 * Publish the element
	 *
	 * @return  boolean
	 */
	public function publish()
	{
		$app = Factory::getApplication();

		// Get items to remove from the request.
		$cid = Factory::getApplication()->input->get('cid', array(), 'array');

		if (!is_array($cid) || count($cid) < 1)
		{
			Log::add(Text::_($this->text_prefix . '_NO_ITEM_SELECTED'), Log::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$model = $this->getModel('pdftemplate');

			// Make sure the item ids are integers
			ArrayHelper::toInteger($cid);
			$stateId = Factory::getApplication()->input->get('task', '', 'STRING');

			if ($stateId == "publish")
			{
				echo $buttonVal = "1";
				$this->setMessage(Text::_('COM_JTICKETING_PDF_TEMPLATE_PUBLISHED'));
			}
			elseif($stateId == "unpublish")
			{
				echo $buttonVal = "0";
				$this->setMessage(Text::_('COM_JTICKETING_PDF_TEMPLATE_UNPUBLISHED'));
			}
			elseif($stateId == "trash")
			{
				echo $buttonVal = "-2";
				$this->setMessage(Text::_('COM_JTICKETING_PDF_TEMPLATE_TRASHED'));
			}
			else
			{
				echo $buttonVal = "";
			}

			foreach ($cid as $vid)
			{
				$model->publish($vid, $buttonVal);
			}
		}

		$this->setRedirect(Route::_('index.php?option=com_jticketing&view=PDFTemplates', false));
	}

	/**
	 * Function to delete the record
	 *
	 * Ajax Call from frontend coupon form view
	 *
	 * @return  void
	 *
	 * @since 1.0.0
	 */
	public function delete()
	{
		$input = Factory::getApplication()->input;
		$app = Factory::getApplication();

		// Get venue ids to delete
		$cid = $input->get('cid', '', 'ARRAY');

		ArrayHelper::toInteger($cid);

		// Call model function
		$model                    = $this->getModel('pdftemplate');

		if (!empty($cid))
		{
			$model->delete($cid);
			$msg = Text::sprintf(Text::_('COM_JTICKETING_PDF_TEMPLATE_TRASHED'), count($cid));
			$app->enqueueMessage($msg);
		}
		
		if ($app->isClient("administrator"))
		{
			$this->setRedirect(Route::_('index.php?option=com_jticketing&view=PDFTemplates', false));
		}
		else 
		{
			$menu       = $app->getMenu();
			$menuItem   = $menu->getItems('link', 'index.php?option=com_jticketing&view=pdftemplates', true);

			$itemId = 0;

			if (!empty($menuItem->id))
			{
				$itemId = $menuItem->id;
			}

			$this->setRedirect(Route::_('index.php?option=com_jticketing&view=PDFTemplates&Itemid=' . $itemId, false));
		}
	}
}
