<?php
/**
 * @package     JTicketing
 * @subpackage  plugin-jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Uri\Uri;

if (file_exists(JPATH_LIBRARIES . '/techjoomla/common.php')) { require_once JPATH_LIBRARIES . '/techjoomla/common.php'; }
JLoader::import('components.com_tjlms.includes.tjlms', JPATH_ADMINISTRATOR);
JLoader::register('comtjlmsHelper', JPATH_SITE . '/components/com_tjlms/helpers/main.php');

if (empty($tjlesson->params['tjlesson']['lessons']))
{
	return false;
}

?>
<div class="container-fluid">
	<form action="" method="post" name="adminForm" id="adminForm" class='form-validate'>
		<div class="row flex-row">
		<?php
			$app              = Factory::getApplication();
			$lessonData       = array();
			$comtjlmsHelper   = new comtjlmsHelper;
			$techjoomlacommon = new TechjoomlaCommon;
			$uri              = Uri::getInstance();

			foreach ($tjlesson->params['tjlesson']['lessons'] as $key=>$lessonId)
			{
				array_push($lessonData, TjLms::Lesson($lessonId));
				
				if ($lessonDataObj->course_id > 0)
				{
					$lessonData[$key]->url = $comtjlmsHelper->tjlmsRoute('index.php?option=com_tjlms&view=lesson&lesson_id=' . $lessonId . "&returnUrl=" . base64_encode($uri->toString() . "#tab7"));
				}
				else
				{
					$menuItemID            = $techjoomlacommon->getItemId('index.php?option=com_tjlms&view=lessons');
					$lessonData[$key]->url = $comtjlmsHelper->tjlmsRoute('index.php?option=com_tjlms&view=lesson&lesson_id=' . $lessonId . "&returnUrl=" . base64_encode($uri->toString() . "#tab7") . '&Itemid=' . $menuItemID, false);
				}
			}

			$tjlmsParams = ComponentHelper::getParams('com_tjlms');
			$launchLessonFullScreen = $tjlmsParams->get('launch_full_screen', '0', 'INT');

			if (!empty($lessonData))
			{
				foreach ($lessonData as $data)
				{
					$launchLesson = 0;
					JLoader::import('components.com_tjlms.models.lessontrack', JPATH_SITE);
					$lessonTrackmodel = BaseDatabaseModel::getInstance('lessonTrack', 'tjlmsModel', array('ignore_request' => true));
					$lessonTrackmodel->setState("lesson_id", $data->id);
					$lessonTrackmodel->setState("user_id", $olUserId);
					$app->setUserState("com_tjlms.lessontrack.list.ordering", "id");
					$app->setUserState("com_tjlms.lessontrack.list.direction", "DESC");
					$lessonTracks = $lessonTrackmodel->getItems();

					$completedTracks = array_map(
						function($track)
						{
							$completStatus = array("completed", "passed", "failed");

							if (in_array($track->lesson_status, $completStatus))
							{
								return ($track);
							}
						}, $lessonTracks
					);

					$completedTracks = array_values(array_filter($completedTracks));
					$data->userStatus['completedAttempts'] = count($completedTracks);
					$data->userStatus['attemptsDone'] = count($lessonTracks);
					$data->userStatus['status']         = 'not_started';

					$attemptsDoneByAvailable = $data->userStatus['attemptsDone'];

					if ($data->no_of_attempts > 0)
					{
						$attemptsDoneByAvailable .= " / " . $data->no_of_attempts;
					}
					else
					{
						$attemptsDoneByAvailable .= " / " . Text::_("COM_TJLMS_LABEL_UNLIMITED_ATTEMPTS");
						$data->no_of_attempts = Text::_('COM_TJLMS_LABEL_UNLIMITED_ATTEMPTS');
					}

					if (count($lessonTracks) >= 1)
					{
						$data->userStatus['status']         = $lessonTracks[0]->lesson_status;
					}
					
					$data->userStatus['attemptsDoneByAvailable']         = $attemptsDoneByAvailable;
					
					$currentTime = Factory::getDate()->toSql();

					$launchButton = 0;

					if((($accessibility == 1 && !empty($olUserId)) || ($accessibility == 0 && $tjlesson->isboughtEvent == 1)) && ($data->end_date >= $currentTime || $data->end_date == '0000-00-00 00:00:00') && $data->start_date <= $currentTime && ($data->userStatus['completedAttempts'] < $data->no_of_attempts || $data->no_of_attempts == 'Unlimited'))
					{
						$launchButton = 1;
						$disableLesson = 'enabled';
					}
					else
					{
						if ($accessibility == 1 && empty($olUserId))
						{
							$hoverTitle     = " rel='popover' data-original-content='" . htmlentities(Text::_('PLG_JTICKETING_TJLESSON_LOGIN_TO_ACCESS_LESSON'), ENT_QUOTES) . " ' ";
						}
						elseif ($accessibility == 0 && !$tjlesson->isboughtEvent)
						{
							$hoverTitle     = " rel='popover' data-original-content='" . htmlentities(Text::_('PLG_JTICKETING_TJLESSON_PURCHASE_TO_ACCESS_LESSON'), ENT_QUOTES) . " ' ";
						}
						elseif ($data->userStatus['completedAttempts'] == $data->no_of_attempts && $data->no_of_attempts != 'Unlimited')
						{
							$hoverTitle     = " rel='popover' data-original-content='" . htmlentities(Text::_('PLG_JTICKETING_TJLESSON_EXHAUSTED_ATTEMPT_LESSON'), ENT_QUOTES) . " ' ";
						}
						elseif ($data->start_date > $currentTime)
						{
							$hoverTitle     = " rel='popover' data-original-content='" . htmlentities(Text::sprintf('PLG_JTICKETING_TJLESSON_FUTURE_LESSON', $data->start_date), ENT_QUOTES) . " ' ";
						}
						elseif ($data->end_date < $currentTime)
						{
							$hoverTitle     = " rel='popover' data-original-content='" . htmlentities(Text::_('PLG_JTICKETING_TJLESSON_EXPIRED_LESSON'), ENT_QUOTES) . " ' ";
						}
						
						$launchButton = 0;
						$disableLesson = 'btn-disabled';
					}

					$data->userStatus['hoverTitle']  = $hoverTitle;
					$data->userStatus['launchButton']  = $launchButton;
					$data->userStatus['disableLesson'] = $disableLesson;

					$data = (array) $data;
					$data['launch_lesson_full_screen'] = $launchLessonFullScreen;

					if ($data['state'] == 1)
					{
						$layout = new FileLayout('lessonlist', JPATH_ROOT .'/plugins/jticketing/tjlesson/layouts');
						echo $layout->render($data);
					}
				}
			}
			?>
	    </div>
	</form>
</div>
