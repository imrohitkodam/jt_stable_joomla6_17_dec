<?php
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Component\ComponentHelper;

class PlgJticketingJtEventReminder extends CMSPlugin
{
    protected $app;

    public function __construct(&$subject, $config = [])
    {
        parent::__construct($subject, $config);
        $this->loadLanguage();
    }

    public function onAfterJtEventSave($eventData)
    {
        $db             = Factory::getDbo();
        $select_content = array();
        // Get the JLike content id associated with JT Event
        $query  = $db->getQuery(true);
        $query->select('c.id');
        $query->from('`#__jlike_content` AS c');
        $query->where($db->quoteName('c.element_id') . ' = ' . $db->quote($eventData['id']));
        $db->setQuery($query);
        $content = $db->loadObject();
        $select_content = [$content->id];

        // Check if the create_reminder value for the event. 
        if($eventData['params']['jteventreminder']['create_reminder'] == 1){
            $emailSubject   = $this->params->get('email_subject', '');
            $emailTemplate  = $this->params->get('email_template', '');
            $frequency      = $this->params->get('frequency', '0');
            $daysBefore     = $this->params->get('days_before', '0');
            $minutesBefore  = $this->params->get('minutes_before', '0');
            
            // Create remimder only for new event
            // Update reminder is not allowed as JLike supports mutiple reminder for same event.            
            if(empty($eventData['eventOldData']))
            {
                // Prepare data for the reminder
                $data = [
                        'title'             => $eventData['title'], 
                        'subject'           => $emailSubject,
                        'email_template'    => $emailTemplate,
                        'content_type'      => 'com_jticketing.event', 
                        'frequency'         => $frequency,
                        'days_before'       => $daysBefore, 
                        'minutes_before'    => $minutesBefore,
                        'select_content'    => $select_content,
                        'state'             => 1
                    ];

                // Insert data into Reminder table
                $table = Table::getInstance('Reminder', 'JlikeTable');
                $table->bind($data);
                $table->store();        
                $insertedId = $table->get('id');
                $db         = Factory::getDBO();
                
                if (!empty($eventData['id']))
                {
                    // Save content id and reminder id into jlike_reminder_contentids table.
                    foreach ($select_content as $content_id)
                    {
                        $obj              = new stdclass;
                        $obj->reminder_id = $insertedId;
                        $obj->content_id  = $content_id;
                        if (!$db->insertObject('#__jlike_reminder_contentids', $obj))
                        {
                            echo $db->stderr();

                            return false;
                        }
                    }
                }
            }else
            {
                // If event datetime is changed then modify the users reminder todos
                if($eventData['eventOldData']->startdate != $eventData['startdate'] || $eventData['eventOldData']->enddate != $eventData['enddate'])
                {                   
                    $db     = Factory::getDbo();
                    $query  = $db->getQuery(true)
                    ->update($db->quoteName('#__jlike_todos'))
                    ->set([
                        $db->quoteName('start_date') . ' = ' . $db->quote($eventData['startdate']),
                        $db->quoteName('due_date') . ' = ' . $db->quote($eventData['enddate']),
                        $db->quoteName('title') . ' = ' . $db->quote($eventData['title'])
                    ])
                    ->where($db->quoteName('content_id') . ' = ' . (int) $content->id);
                    $db->setQuery($query);
                    try {
                        $db->execute();                        
                        echo "Dates updated successfully.";
                        return true;
                    } catch (RuntimeException $e) {
                        echo "Error updating dates: " . $e->getMessage();
                        return false;
                    }
                }
            }
        }
    }

    /**
	 * The form event. Load additional parameters when available into the field form.
	 * Only when the type of the form is of interest.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function onPrepareIntegrationField()
	{
		if (ComponentHelper::isEnabled('com_jticketing', true))
		{
			return array(
			'path' => JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/jteventreminder.xml', 'name' => $this->_name
			);
		}
	}
}
?>