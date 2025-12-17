<?php
/**
 * @version     1.2
 * @package     com_jlike
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Techjoomla <contact@techjoomla.com> - http://www.techjoomla.com
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\FormController;


/**
 * Annotation controller class.
 */
class JlikeControllerAnnotation extends FormController
{

    function __construct() {
        $this->view_list = 'annotations';
        parent::__construct();
    }

}