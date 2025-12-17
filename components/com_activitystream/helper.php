<?php
/**
 * @version    SVN: <svn_id>
 * @package    ActivityStream
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;

/**
 * Class ActivitystreamHelper
 *
 * @since  3.3
 */
class ComActivityStreamHelper
{
	/**
	 * Function to get language constants for JS
	 *
	 * @return null
	 *
	 * @since   2.2
	 */
	public function getLanguageConstantForJs()
	{
		Text::script('COM_ACTIVITYSTREAM_POST_TEXT_ACTIVITY_REMAINING_TEXT_LIMIT');
		Text::script('COM_ACTIVITYSTREAM_LOAD_MORE_ACTIVITIES');
	}
}
