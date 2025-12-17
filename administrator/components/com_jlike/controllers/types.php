<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access to this file
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;

/**
 * Notifications list controller class.
 *
 * @since  0.0.1
 */
class JlikeControllerTypes extends AdminController
{
/**
	* Proxy for getModel.
	*
	* @param   string  $name    Optional. Model name
	* @param   string  $prefix  Optional. Class prefix
	* @param   array   $config  Optional. Configuration array for model
	*
	* @return  object	The Model
	*
	* @since    1.6
	*/
	public function getModel($name = 'Types', $prefix = 'JLikeModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

/**
	* delete
	*
	* @return  null		nothing
	*
	* @since    1.6
	*/
	public function delete()
	{
		$modelDelete = ListModel::getInstance('Types', 'JLikeModel', array('ignore_request' => true));
		$cid = Factory::getApplication()->getInput()->get('cid', array(), 'array');
		$modelDelete->delete($cid);
		parent::delete();
	}
}
