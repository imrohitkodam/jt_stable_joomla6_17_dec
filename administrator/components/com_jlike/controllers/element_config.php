<?php
/**
 * @package     JLike
 * @subpackage  com_jlike
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
	defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

class jlikeControllerElement_config extends FormController
{

	function save()
	{

		$model	=$this->getModel( 'element_config' );

		if ($model->store()) {
			$msg = Text::_( 'COM_JLIKE_DATA_SAVED' );
		} else {
			$msg = Text::_( 'COM_JLIKE_DATA_SAVED_ERROR' );
		}
		$this->setRedirect( 'index.php?option=com_jlike&view=element_config',$msg);
	}

	function cancel()
	{
		$input=Factory::getApplication()->getInput();
 		switch ($input->get('task'))
		{
			case 'cancel':
				$this->setRedirect( 'index.php?option=com_jlike&view=element_config');
			}
	}

}
