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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Joomla\Filesystem\File;


class jlikeModelElement_config extends BaseDatabaseModel
{
	/*
	 Function saves configuration data to a file
	 */
	function store(){

		$app = Factory::getApplication();

		if (JVERSION < 3.0)
		{
			$config = $app->getInput()->post->get('data', '', 'array');
		}
		else
		{
			$input = $app->input;
			$post = $input->post->getArray();
			$config = $post['data'];
		}
		
		$file_contents=str_replace("<br />","\n",$config['classifiactionlist']);
		$file_contents=strip_tags($file_contents);

		$msg 		= '';
		$msg_type	= '';
		$filename = JPATH_ROOT.DS."components".DS."com_jlike".DS."classification.ini";

		if ($config)
		{		  
			
			if(File::write($filename, $file_contents)) 
			{
				return true;
			} 
			else
			{
				return false;
			}
			
			
		}
	}//store() ends


   
	
	
}
