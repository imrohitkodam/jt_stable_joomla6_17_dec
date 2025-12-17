<?php
/**
 * @package     JTicketing
 * @subpackage  com_jticketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

class JticketingControllerRecurringEvents extends AdminController
{
    const STATE_CHECKED_IN = 1;
    const STATE_CHECKED_OUT = 0;
    /**
     * The name of the view to use with this controller.
     *
     * @return string
     */
    protected $view_list = 'recurringevents';

    /**
     * Constructor to register additional tasks.
     *
     * @param array $config Configuration array.
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->registerTask('checkin', 'checkin');
        $this->registerTask('undocheckin', 'undocheckin');
    }

    /**
     * Save the data from the form.
     *
     * @param array $data
     * @return bool
     */
    public function save($key = null, $urlVar = null)
    {
        $model = $this->getModel('RecurringEvent', 'JticketingModel');

        $data = $this->input->post->get('jform', array(), 'array');

        $model->save($data);
    }

    /**
    * Method to checkin for ticket
    *
    * @return void
    *
    * @since 1.0
    */
    public function checkIn()
    {
        // Load the frontend model
        // Joomla 6: JLoader removed - use require_once
        $checkinModelPath = JPATH_SITE . '/components/com_jticketing/models/checkin.php';
        if (file_exists($checkinModelPath))
        {
            require_once $checkinModelPath;
        }
        $input = Factory::getApplication()->getInput();
    
        // Retrieve single recurring event ID and attendee ID
        $rId = $input->getInt('cid', 0);
        $attendeeId = $input->getInt('attendee_id', 0);
    
        // Retrieve the notify flag for the specific event
        $notify = $input->get('notify_user_' . $rId[0], '',  'STRING');
        $checkinModel = new JticketingModelCheckin();
    
        // Prepare data for saving
        $data = array(
            'attendee_id' => $attendeeId,
            'state' => self::STATE_CHECKED_IN,
            'r_id' => $rId,
            'notify' => $notify,
        );
    
        // Attempt to save the check-in data
        if (!$checkinModel->save($data)) {
            $msg = $checkinModel->getError();
            $this->setRedirect(Route::_('index.php?option=com_jticketing&view=recurringevents&attendee_id=' . $attendeeId, false), $msg, 'error');
            return;
        }
    
        // Redirect with success message
        $msg = Text::_('COM_JTICKETING_CHECKIN_SUCCESS_MSG');
        $this->setRedirect(Route::_('index.php?option=com_jticketing&view=recurringevents&attendee_id=' . $attendeeId, false), $msg);
    }
    
    /**
     * Method to undo checkin for ticket
     *
     * @return void
     *
     * @since 1.0
     */
    public function undoCheckIn()
    {
        $input = Factory::getApplication()->getInput();

        // Get variables from the request
        $rId = $input->getInt('cid', 0); // Get a single recurring event ID
        $attendeeId = $input->getInt('attendee_id', 0); 
        $success = 0;

        // Ensure we have valid IDs
        if ($rId <= 0 || $attendeeId <= 0) {
            $msg = Text::_('COM_JTICKETING_INVALID_REQUEST');
            $this->setRedirect(Route::_('index.php?option=com_jticketing&view=recurringevents&attendee_id=' . $attendeeId, false), $msg, 'error');
            return;
        }

        // Joomla 6: JLoader removed - use require_once
        $checkinModelPath = JPATH_SITE . '/components/com_jticketing/models/checkin.php';
        if (file_exists($checkinModelPath))
        {
            require_once $checkinModelPath;
        }
        $checkinModel = new JticketingModelCheckin();

        // Prepare data for undoing check-in
        $data = array(
            'attendee_id' => $attendeeId,
            'state' => self::STATE_CHECKED_OUT,
            'r_id' => $rId,
        );

        // Attempt to save the undo check-in data
        if ($checkinModel->save($data)) {
            $success++;
        }

        // Handle the result
        if ($success > 0) {
            $msg = Text::_('COM_JTICKETING_CHECKIN_FAIL_MSG');
            $this->setRedirect(Route::_('index.php?option=com_jticketing&view=recurringevents&attendee_id=' . $attendeeId, false), $msg);
        } else {
            $msg = $checkinModel->getError();
            $this->setRedirect(Route::_('index.php?option=com_jticketing&view=recurringevents&attendee_id=' . $attendeeId, false), $msg, 'error');
        }
    }

}
