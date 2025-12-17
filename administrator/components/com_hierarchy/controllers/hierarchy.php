<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Hierarchy
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;

/**
 * The Hierarchy Controller
 *
 * @since  1.6
 */
class HierarchyControllerHierarchy extends FormController
{
	/**
	 * Constructor.
	 *
	 * @since   1.6
	 *
	 * @see     JController
	 */
	public function __construct()
	{
		$this->view_list = 'hierarchys';

		$this->db = Factory::getDbo();
		Table::addIncludePath(JPATH_ROOT . '/administrator/components/com_hierarchy/tables');
		$this->hierarchyTableObj = Table::getInstance('Hierarchy', 'HierarchyTable', array('dbo', $this->db));

		$this->msg   = Text::_('COM_HIERARCHY_REPORTEES_SAVE_MSG');
		parent::__construct();
	}

	/**
	 * Method to save a user's profile data.
	 *
	 * @param   string  $key     TO ADD
	 * @param   string  $urlVar  TO ADD
	 *
	 * @return    void
	 *
	 * @since    1.6
	 */
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app   = Factory::getApplication();
		$input = $app->getInput();
		$model = $this->getModel('hierarchy', 'HierarchyModel');

		// Get the user data.
		$data = $input->get('jform', array(), 'array');

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			$app->enqueueMessage($model->getError(), 'error');

			return false;
		}

		// Validate the posted data.
		if (!empty($form))
		{
			$data = $model->validate($form, $data);
		}

		$data['user_id']  = $input->get('user_id', '', 'int');
		$data['created_by']  = $input->get('created_by', '', 'int');
		$data['modified_by'] = $input->get('modified_by', '', 'int');

		// Get the existing managers of the user
		$hierarchysData = $model->getReportsTo($data['user_id']);

		$userIDs = array();

		foreach ($hierarchysData as $hierarchy)
		{
			$userIDs[] = $hierarchy->reports_to;
		}

		// Get array of user ids of assigned managers
		$data['reports_to'] = (array) $data['reports_to'];

		// Get the list of removed managers for the user
		$deleteUser = array_diff($userIDs, $data['reports_to']);

		// Delete records for removed managers for the user
		foreach ($deleteUser as $key => $val)
		{
			$this->hierarchyTableObj->load(array('reports_to' => (int) $val, 'user_id' => (int) $data['user_id']));
			$id = $this->hierarchyTableObj->id;
			$return = $model->delete($id);
		}

		// Save the records for new managers of the user
		foreach ($data['reports_to'] as $key => $val)
		{
			$data['reports_to'] = $val;
			$return = $model->save($data);
		}

		$id    = $input->get('id');

		if (empty($id))
		{
			$id = $return;
		}

		$task = $input->get('task');

		if ($task == 'apply')
		{
			$redirect = Route::_('index.php?option=com_hierarchy&view=hierarchy&layout=edit&id=' . $id, false);
			$app->enqueueMessage($this->msg);
			$app->redirect($redirect);
		}

		// Clear the profile id from the session.
		$app->setUserState('com_hierarchy.edit.hierarchy.id', null);

		// Check in the profile.
		if ($return)
		{
			$model->checkin($return);
		}

		// Redirect to the list screen.
		$redirect = Route::_('index.php?option=com_hierarchy&view=hierarchys', false);
		$app->enqueueMessage($this->msg);
		$app->redirect($redirect);

		// Flush the data from the session.
		$app->setUserState('com_hierarchy.edit.hierarchy.data', null);
	}

	/**
	 * Method to get users to manage hierarchy.
	 *
	 * @return  array
	 *
	 * @since    1.6
	 */
	public function getAutoSuggestUsers()
	{
		$app = Factory::getApplication();
		$input = $app->getInput();
		$userId = $input->get('user_id', '', 'int');

		// Get the model.
		$model = $this->getModel('Hierarchy', 'HierarchyModel');

		// Get the list
		$list = $model->getAutoSuggestUsers($userId);

		// Output json response
		header('Content-type: application/json');
		echo json_encode($list);
		exit();
	}
}
