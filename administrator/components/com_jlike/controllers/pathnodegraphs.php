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
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Controller\AdminController;


/**
 * Path node graph list controller class.
 *
 * @since  1.0.0
 */
class JlikeControllerPathNodeGraphs extends AdminController
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
	* @since    1.0.0
	*/
	public function getModel($name = 'pathnodegraph', $prefix = 'JlikeModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));

		return $model;
	}
}
