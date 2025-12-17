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

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/**
 * Certificate list controller class.
 *
 * @since  1.0.0
 */
class TjCertificateControllerCertificates extends AdminController
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   STRING  $name    model name
	 * @param   STRING  $prefix  model prefix
	 *
	 * @return  object  The model.
	 *
	 * @since  1.0.0
	 */
	public function getModel($name = 'Certificate', $prefix = 'TjCertificateModel', $config = [])
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	/**
	 * Method to remove a record.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function delete()
	{
		$this->checkToken();

		// Get items to remove from the request.
		$cid = $this->input->get('cid', array(), 'array');

		$extension = $this->input->getCmd('extension', null);

		if (!is_array($cid) || count($cid) < 1)
		{
			$this->setMessage(Text::_($this->text_prefix . '_NO_ITEM_SELECTED'), 'warning');
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			$cid = ArrayHelper::toInteger($cid);

			// Remove the items.
			if ($model->delete($cid))
			{
				$this->setMessage(Text::plural($this->text_prefix . '_N_ITEMS_DELETED', count($cid)));
			}
			else
			{
				$this->setMessage($model->getError());
			}
		}

		$this->setRedirect(Route::_('index.php?option=com_tjcertificate&view=certificates', false));
	}
}
