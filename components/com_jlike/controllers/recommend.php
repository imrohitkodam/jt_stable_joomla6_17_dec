<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;


/**
 * Jlike recommend controller class.
 *
 * @since  1.0.0
 */
class JlikeControllerRecommend extends JlikeController
{
	/**
	 * Function used to recommend user.
	 *
	 * @return  redirects
	 *
	 * @since  1.0.0
	 */
	public function assignRecommendUsers()
	{
		Session::checkToken() or Session::checkToken('get') or Factory::getApplication()->close();

		$input = Factory::getApplication()->getInput();
		$post  = $input->post;

		// Store $post details in the array
		$data = array();
		$data['type']   = $post->get('type', 'reco');

		if ($data['type'] != "assign" && $data['type'] != "reco" )
		{
			$data['type']  = 'reco';
		}

		$data['sender_msg']        = $post->get('sender_msg', '', 'STRING');
		$data['start_date']        = $post->get('start_date', '', 'DATE');
		$data['due_date']          = $post->get('due_date', '', 'DATE');

		// Check self Assign
		if ($post->get('sub_type', '', 'STRING') == 'self')
		{
			$user = Factory::getUser();
			$data['recommend_friends'] = array($user->id);

			if ($post->get('todo_id', '', 'INT'))
			{
				$data['todo_id'] = $post->get('todo_id', '', 'INT');
			}
		}
		else
		{
			$data['recommend_friends'] = $post->get('recommend_friends', '', 'ARRAY');
		}

		$cid   = Factory::getApplication()->getInput()->get('cid', array(), 'array');
		$model = $this->getModel('recommend');

		$plg_name   = $post->get('plg_name', '');
		$plg_type   = $post->get('plg_type', 'content');
		$element    = $post->get('element', '');
		$element_id = $post->get('element_id', '', 'INT');

		$options = array('element' => $element, 'element_id' => $element_id, 'plg_name' => $plg_name, 'plg_type' => $plg_type);

		$successfulRecommend = $model->assignRecommendUsers($data, $options);

		if ($successfulRecommend)
		{
			$msg = ($data['type'] == 'reco') ? Text::_('COM_JLIKE_RECOMMEND_SUCCESSFULL') : Text::_("COM_JLIKE_ASSIGN_SUCCESSFULL");
		}
		else
		{
			$msg = ($data['type'] == 'reco') ? Text::_('COM_JLIKE_RECOMMEND_FAILED') : Text::_("COM_JLIKE_ASSIGN_FAILED");
		}

		// Redirect successfull message
		if ($post->get('sub_type', '', 'STRING') == 'self')
		{
			$link = 'index.php?option=com_jlike&view=recommend&layout=default_setgoal&tmpl=component';
			$msg = (empty($data['todo_id'])?Text::_("COM_JLIKE_ASSIGN_SETGOAL_SUCCESSFULL"):Text::_("COM_JLIKE_ASSIGN_UPDATEGOAL_SUCCESSFULL"));
			$this->setRedirect(
			$link . '&id=' . $element_id . '&plg_name=' . $plg_name . '&plg_type=' . $plg_type . '&element=' . $element . '&type=' . $data['type'] .
			'&assignto=' . $post->get('sub_type', '', 'STRING'), $msg
			);
		}
		else
		{
			$link = 'index.php?option=com_jlike&view=recommend&tmpl=component';
			$this->setRedirect(
			$link . '&id=' . $element_id . '&plg_name=' . $plg_name . '&plg_type=' . $plg_type . '&element=' . $element . '&type=' . $data['type'], $msg
			);
		}
	}
}
