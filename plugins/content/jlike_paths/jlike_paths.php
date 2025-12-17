<?php
/**
 * @version    SVN: <svn_id>
 * @package    JLike
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2019 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die ('Restricted access');
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;

// Import library dependencies

/**
 * Class Jlike path content.
 *
 * @since  1.0.0
 */
class PlgContentJLike_Paths extends CMSPlugin
{
	/**
	 * It is used to replace path description tags
	 *
	 * @param   string   $context  The context of the content being passed to the plugin
	 * @param   object   &$row     The article object
	 * @param   mixed    &$params  The article params
	 * @param   integer  $page     The 'page' number
	 *
	 * @return  mixed  void or true
	 *
	 * @since   1.6
	 */
	public function onContentBeforeDisplay($context, &$row, &$params, $page = 0)
	{
		$app   = Factory::getApplication();
		$view  = $app->getInput()->get('view');

		$parsePathPreDesc = array('#({pathprogress.*?}).*?({/pathprogress})#', '#({pathcomplete.*?}).*?({/pathcomplete})#');
		$parsePathProgressDesc = array('#({pathpre.*?}).*?({/pathpre})#', '#({pathcomplete.*?}).*?({/pathcomplete})#');
		$parsePathCompleteDesc = array('#({pathpre.*?}).*?({/pathpre})#', '#({pathprogress.*?}).*?({/pathprogress})#');

		$replacePathPreTags = array('{pathpre}', '{/pathpre}');
		$replacePathProgressTags = array('{pathprogress}', '{/pathprogress}');
		$replacePathCompleteTags = array('{pathcomplete}', '{/pathcomplete}');

		if ($context === 'com_jlike.path' && ($view === 'pathdetail' || $view === 'pathusers') && $this->params->get('replace_path_description_tags'))
		{
			if (is_object($row))
			{
				switch ($row->path_status)
				{
					case "I":
						$row->path_description = preg_replace($parsePathProgressDesc, '', $row->path_description);
						$row->path_description = str_ireplace($replacePathProgressTags, '', $row->path_description);
						break;
					case "C":
						$row->path_description = preg_replace($parsePathCompleteDesc, '', $row->path_description);
						$row->path_description = str_ireplace($replacePathCompleteTags, '', $row->path_description);
						break;
					default:
						$row->path_description = preg_replace($parsePathPreDesc, '', $row->path_description);
						$row->path_description = str_ireplace($replacePathPreTags, '', $row->path_description);
						break;
				}
			}
			elseif (is_array($row))
			{
				switch ($row['isSubscribedPath'])
				{
					case "I":
						$row['node_path_description'] = preg_replace($parsePathProgressDesc, '', $row['node_path_description']);
						$row['node_path_description'] = str_ireplace($replacePathProgressTags, '', $row['node_path_description']);
						break;
					case "C":
						$row['node_path_description'] = preg_replace($parsePathCompleteDesc, '', $row['node_path_description']);
						$row['node_path_description'] = str_ireplace($replacePathCompleteTags, '', $row['node_path_description']);
						break;
					default:
						$row['node_path_description'] = preg_replace($parsePathPreDesc, '', $row['node_path_description']);
						$row['node_path_description'] = str_ireplace($replacePathPreTags, '', $row['node_path_description']);
						break;
				}
			}
		}
	}
}
