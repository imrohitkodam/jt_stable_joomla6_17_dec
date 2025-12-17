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
use Joomla\CMS\Router\Route;

// Import CSV library view
if (file_exists(JPATH_LIBRARIES . '/techjoomla/view/csv.php')) { require_once JPATH_LIBRARIES . '/techjoomla/view/csv.php'; }

/**
 * View for events
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingViewWaitinglist extends TjExportCsv
{
	public $fileName;

	/**
	 * Display view
	 *
	 * @param   STRING  $tpl  template name
	 *
	 * @return  Object|Boolean in case of success instance and failure - boolean
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		$app   = Factory::getApplication();
		$input = $app->input;
		$user  = Factory::getUser();
		$userAuthorisedExport = $user->authorise('core.create', 'com_jticketing');
		$this->fileName = preg_replace('/\s+/', '', Text::_('COM_JTICKETING') . '_' . Text::_('COM_JTICKETING_TITLE_WAITINGLIST'));

		if ($userAuthorisedExport !== true || !$user->id)
		{
			// Redirect to the list screen.
			$redirect = Route::_('index.php?option=com_jticketing&view=waitinglist', false);
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'));
			$app->redirect($redirect);

			return false;
		}
		else
		{
			if ($input->get('task') == 'download')
			{
				$fileName = $input->get('file_name');
				$this->download($fileName);
				Factory::getApplication()->close();
			}
			else
			{
				parent::display();
			}
		}
	}
}
