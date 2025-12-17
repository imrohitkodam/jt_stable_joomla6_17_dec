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
use Joomla\CMS\MVC\Controller\FormController;


/**
 * VAnnotation controller class.
 *
 * @package     Jlike
 * @subpackage  Jlike
 * @since       2.2
 */
class JlikeControllerAnnotation extends FormController
{
	/**
	 * Constructor
	 *
	 * @since   1.2
	 *
	 * @throws  Exception
	 */
	public function __construct()
	{
		$this->view_list = 'annotations';
		parent::__construct();
	}
}
