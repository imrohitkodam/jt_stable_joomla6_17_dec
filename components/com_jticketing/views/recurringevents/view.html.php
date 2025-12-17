<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;


class JticketingViewRecurringEvents extends HtmlView
{
    public $recurringEvents;
    public $attendeeId;
	public $tmpl;

    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $jinput = $app->input;
		$this->tmpl  = $jinput->get('tmpl', '');
    
        $this->attendeeId = $jinput->get('attendee_id', 0, 'INT');

        if (empty($this->attendeeId)) {
            $app->enqueueMessage(Text::_('COM_JTICKETING_ERROR_NO_ATTENDEE_ID'), 'error');
            return false;
        }
    
        // Load the recurring event model
		JLoader::register('JticketingModelRecurringEvents', JPATH_ADMINISTRATOR . '/components/com_jticketing/models/recurringevent.php');
		$recurringModel = new JticketingModelRecurringEvents();

        $this->recurringEvents = $recurringModel->getRecurringEvents($this->attendeeId);
        if (!is_array($this->recurringEvents)) {
            $this->recurringEvents = [];
        }
    
        $errors = $this->get('Errors');
        if (!is_array($errors)) {
            $errors = []; 
        }
    
        if (count($errors)) {
            throw new Exception(implode("\n", $errors));
        }
    
        parent::display($tpl);
    }
}
