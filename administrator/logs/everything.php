#
#<?php die('Forbidden.'); ?>
#Date: 2025-12-17 06:09:33 UTC
#Software: Joomla! 6.0.0 Stable [ Kuimarisha ] 14-October-2025 16:00 UTC

#Fields: datetime	priority clientip	category	message
2025-12-17T06:09:33+00:00	INFO 127.0.0.1	updater	Loading information from update site #4 with name "JTicketing" and URL https://techjoomla.com/updates/stream/jticketing.xml?format=xml took 1.52 seconds
2025-12-17T06:10:49+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.0, falling back to com_jticketing
2025-12-17T06:10:49+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.0, falling back to com_jticketing
2025-12-17T06:10:49+00:00	CRITICAL 127.0.0.1	error	Uncaught Throwable of type Error thrown with message "Call to a member function load() on false". Stack trace: #0 [ROOT]/libraries/src/MVC/Model/AdminModel.php(1327): JTicketingTableEvent->check()
#1 [ROOT]/components/com_jticketing/models/eventform.php(610): Joomla\CMS\MVC\Model\AdminModel->save()
#2 [ROOT]/administrator/components/com_jticketing/controllers/event.php(238): JticketingModelEventForm->save()
#3 [ROOT]/libraries/src/MVC/Controller/BaseController.php(730): JTicketingControllerEvent->save()
#4 [ROOT]/administrator/components/com_jticketing/jticketing.php(114): Joomla\CMS\MVC\Controller\BaseController->execute()
#5 [ROOT]/libraries/src/Dispatcher/LegacyComponentDispatcher.php(71): require_once('...')
#6 [ROOT]/libraries/src/Dispatcher/LegacyComponentDispatcher.php(73): Joomla\CMS\Dispatcher\LegacyComponentDispatcher::Joomla\CMS\Dispatcher\{closure}()
#7 [ROOT]/libraries/src/Component/ComponentHelper.php(361): Joomla\CMS\Dispatcher\LegacyComponentDispatcher->dispatch()
#8 [ROOT]/libraries/src/Application/AdministratorApplication.php(150): Joomla\CMS\Component\ComponentHelper::renderComponent()
#9 [ROOT]/libraries/src/Application/AdministratorApplication.php(205): Joomla\CMS\Application\AdministratorApplication->dispatch()
#10 [ROOT]/libraries/src/Application/CMSApplication.php(320): Joomla\CMS\Application\AdministratorApplication->doExecute()
#11 [ROOT]/administrator/includes/app.php(58): Joomla\CMS\Application\CMSApplication->execute()
#12 [ROOT]/administrator/index.php(32): require_once('...')
#13 {main}
