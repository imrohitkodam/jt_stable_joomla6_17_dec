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
2025-12-17T06:15:47+00:00	CRITICAL 127.0.0.1	error	Uncaught Throwable of type Error thrown with message "Class "Joomla\CMS\Filesystem\Path" not found". Stack trace: #0 [ROOT]/libraries/src/Form/Field/ListField.php(72): JFormFieldSampleTemplates->getOptions()
#1 [ROOT]/libraries/src/Form/FormField.php(1070): Joomla\CMS\Form\Field\ListField->getInput()
#2 [ROOT]/libraries/src/Form/Form.php(547): Joomla\CMS\Form\FormField->renderField()
#3 [ROOT]/administrator/components/com_tjcertificate/views/template/tmpl/edit_bs5.php(74): Joomla\CMS\Form\Form->renderField()
#4 [ROOT]/libraries/src/MVC/View/HtmlView.php(416): include('...')
#5 [ROOT]/administrator/components/com_tjcertificate/views/template/tmpl/edit.php(20): Joomla\CMS\MVC\View\HtmlView->loadTemplate()
#6 [ROOT]/libraries/src/MVC/View/HtmlView.php(416): include('...')
#7 [ROOT]/libraries/src/MVC/View/HtmlView.php(204): Joomla\CMS\MVC\View\HtmlView->loadTemplate()
#8 [ROOT]/administrator/components/com_tjcertificate/views/template/view.html.php(111): Joomla\CMS\MVC\View\HtmlView->display()
#9 [ROOT]/libraries/src/MVC/Controller/BaseController.php(697): TjCertificateViewTemplate->display()
#10 [ROOT]/administrator/components/com_tjcertificate/controller.php(45): Joomla\CMS\MVC\Controller\BaseController->display()
#11 [ROOT]/libraries/src/MVC/Controller/BaseController.php(730): TjCertificateController->display()
#12 [ROOT]/administrator/components/com_tjcertificate/tjcertificate.php(31): Joomla\CMS\MVC\Controller\BaseController->execute()
#13 [ROOT]/libraries/src/Dispatcher/LegacyComponentDispatcher.php(71): require_once('...')
#14 [ROOT]/libraries/src/Dispatcher/LegacyComponentDispatcher.php(73): Joomla\CMS\Dispatcher\LegacyComponentDispatcher::Joomla\CMS\Dispatcher\{closure}()
#15 [ROOT]/libraries/src/Component/ComponentHelper.php(361): Joomla\CMS\Dispatcher\LegacyComponentDispatcher->dispatch()
#16 [ROOT]/libraries/src/Application/AdministratorApplication.php(150): Joomla\CMS\Component\ComponentHelper::renderComponent()
#17 [ROOT]/libraries/src/Application/AdministratorApplication.php(205): Joomla\CMS\Application\AdministratorApplication->dispatch()
#18 [ROOT]/libraries/src/Application/CMSApplication.php(320): Joomla\CMS\Application\AdministratorApplication->doExecute()
#19 [ROOT]/administrator/includes/app.php(58): Joomla\CMS\Application\CMSApplication->execute()
#20 [ROOT]/administrator/index.php(32): require_once('...')
#21 {main}
2025-12-17T06:17:16+00:00	CRITICAL 127.0.0.1	error	Uncaught Throwable of type TypeError thrown with message "mb_trim(): Argument #1 ($string) must be of type string, null given, called in [ROOT]/libraries/vendor/joomla/string/src/StringHelper.php on line 664". Stack trace: #0 [ROOT]/libraries/vendor/joomla/string/src/StringHelper.php(664): mb_trim()
#1 [ROOT]/components/com_jticketing/models/couponform.php(223): Joomla\String\StringHelper::trim()
#2 [ROOT]/libraries/src/MVC/Controller/FormController.php(689): JticketingModelCouponform->save()
#3 [ROOT]/libraries/src/MVC/Controller/BaseController.php(730): Joomla\CMS\MVC\Controller\FormController->save()
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
2025-12-17T06:21:24+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.0, falling back to com_jticketing
2025-12-17T06:21:24+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.0, falling back to com_jticketing
2025-12-17T06:21:24+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T06:21:24+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T06:21:24+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T06:21:24+00:00	WARNING 127.0.0.1	jerror	Class `` does not exist, could not create a toolbar button.
2025-12-17T06:21:24+00:00	CRITICAL 127.0.0.1	error	Uncaught Throwable of type UnexpectedValueException thrown with message "Button not defined for type = CsvExport". Stack trace: #0 [ROOT]/libraries/src/Toolbar/Toolbar.php(327): Joomla\CMS\Toolbar\Toolbar->renderButton()
#1 [ROOT]/administrator/modules/mod_toolbar/src/Dispatcher/Dispatcher.php(37): Joomla\CMS\Toolbar\Toolbar->render()
#2 [ROOT]/libraries/src/Dispatcher/AbstractModuleDispatcher.php(63): Joomla\Module\Toolbar\Administrator\Dispatcher\Dispatcher->getLayoutData()
#3 [ROOT]/libraries/src/Helper/ModuleHelper.php(289): Joomla\CMS\Dispatcher\AbstractModuleDispatcher->dispatch()
#4 [ROOT]/libraries/src/Helper/ModuleHelper.php(160): Joomla\CMS\Helper\ModuleHelper::renderRawModule()
#5 [ROOT]/libraries/src/Document/Renderer/Html/ModuleRenderer.php(99): Joomla\CMS\Helper\ModuleHelper::renderModule()
#6 [ROOT]/libraries/src/Document/Renderer/Html/ModulesRenderer.php(51): Joomla\CMS\Document\Renderer\Html\ModuleRenderer->render()
#7 [ROOT]/libraries/src/Document/HtmlDocument.php(575): Joomla\CMS\Document\Renderer\Html\ModulesRenderer->render()
#8 [ROOT]/libraries/src/Document/HtmlDocument.php(894): Joomla\CMS\Document\HtmlDocument->getBuffer()
#9 [ROOT]/libraries/src/Document/HtmlDocument.php(647): Joomla\CMS\Document\HtmlDocument->_renderTemplate()
#10 [ROOT]/libraries/src/Application/CMSApplication.php(1132): Joomla\CMS\Document\HtmlDocument->render()
#11 [ROOT]/libraries/src/Application/AdministratorApplication.php(459): Joomla\CMS\Application\CMSApplication->render()
#12 [ROOT]/libraries/src/Application/CMSApplication.php(325): Joomla\CMS\Application\AdministratorApplication->render()
#13 [ROOT]/administrator/includes/app.php(58): Joomla\CMS\Application\CMSApplication->execute()
#14 [ROOT]/administrator/index.php(32): require_once('...')
#15 {main}
2025-12-17T06:21:44+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T06:21:44+00:00	WARNING 127.0.0.1	jerror	Class `` does not exist, could not create a toolbar button.
2025-12-17T06:21:44+00:00	CRITICAL 127.0.0.1	error	Uncaught Throwable of type UnexpectedValueException thrown with message "Button not defined for type = CsvExport". Stack trace: #0 [ROOT]/libraries/src/Toolbar/Toolbar.php(327): Joomla\CMS\Toolbar\Toolbar->renderButton()
#1 [ROOT]/administrator/modules/mod_toolbar/src/Dispatcher/Dispatcher.php(37): Joomla\CMS\Toolbar\Toolbar->render()
#2 [ROOT]/libraries/src/Dispatcher/AbstractModuleDispatcher.php(63): Joomla\Module\Toolbar\Administrator\Dispatcher\Dispatcher->getLayoutData()
#3 [ROOT]/libraries/src/Helper/ModuleHelper.php(289): Joomla\CMS\Dispatcher\AbstractModuleDispatcher->dispatch()
#4 [ROOT]/libraries/src/Helper/ModuleHelper.php(160): Joomla\CMS\Helper\ModuleHelper::renderRawModule()
#5 [ROOT]/libraries/src/Document/Renderer/Html/ModuleRenderer.php(99): Joomla\CMS\Helper\ModuleHelper::renderModule()
#6 [ROOT]/libraries/src/Document/Renderer/Html/ModulesRenderer.php(51): Joomla\CMS\Document\Renderer\Html\ModuleRenderer->render()
#7 [ROOT]/libraries/src/Document/HtmlDocument.php(575): Joomla\CMS\Document\Renderer\Html\ModulesRenderer->render()
#8 [ROOT]/libraries/src/Document/HtmlDocument.php(894): Joomla\CMS\Document\HtmlDocument->getBuffer()
#9 [ROOT]/libraries/src/Document/HtmlDocument.php(647): Joomla\CMS\Document\HtmlDocument->_renderTemplate()
#10 [ROOT]/libraries/src/Application/CMSApplication.php(1132): Joomla\CMS\Document\HtmlDocument->render()
#11 [ROOT]/libraries/src/Application/AdministratorApplication.php(459): Joomla\CMS\Application\CMSApplication->render()
#12 [ROOT]/libraries/src/Application/CMSApplication.php(325): Joomla\CMS\Application\AdministratorApplication->render()
#13 [ROOT]/administrator/includes/app.php(58): Joomla\CMS\Application\CMSApplication->execute()
#14 [ROOT]/administrator/index.php(32): require_once('...')
#15 {main}
2025-12-17T06:21:47+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T06:21:47+00:00	WARNING 127.0.0.1	jerror	Class `` does not exist, could not create a toolbar button.
2025-12-17T06:21:47+00:00	CRITICAL 127.0.0.1	error	Uncaught Throwable of type UnexpectedValueException thrown with message "Button not defined for type = CsvExport". Stack trace: #0 [ROOT]/libraries/src/Toolbar/Toolbar.php(327): Joomla\CMS\Toolbar\Toolbar->renderButton()
#1 [ROOT]/administrator/modules/mod_toolbar/src/Dispatcher/Dispatcher.php(37): Joomla\CMS\Toolbar\Toolbar->render()
#2 [ROOT]/libraries/src/Dispatcher/AbstractModuleDispatcher.php(63): Joomla\Module\Toolbar\Administrator\Dispatcher\Dispatcher->getLayoutData()
#3 [ROOT]/libraries/src/Helper/ModuleHelper.php(289): Joomla\CMS\Dispatcher\AbstractModuleDispatcher->dispatch()
#4 [ROOT]/libraries/src/Helper/ModuleHelper.php(160): Joomla\CMS\Helper\ModuleHelper::renderRawModule()
#5 [ROOT]/libraries/src/Document/Renderer/Html/ModuleRenderer.php(99): Joomla\CMS\Helper\ModuleHelper::renderModule()
#6 [ROOT]/libraries/src/Document/Renderer/Html/ModulesRenderer.php(51): Joomla\CMS\Document\Renderer\Html\ModuleRenderer->render()
#7 [ROOT]/libraries/src/Document/HtmlDocument.php(575): Joomla\CMS\Document\Renderer\Html\ModulesRenderer->render()
#8 [ROOT]/libraries/src/Document/HtmlDocument.php(894): Joomla\CMS\Document\HtmlDocument->getBuffer()
#9 [ROOT]/libraries/src/Document/HtmlDocument.php(647): Joomla\CMS\Document\HtmlDocument->_renderTemplate()
#10 [ROOT]/libraries/src/Application/CMSApplication.php(1132): Joomla\CMS\Document\HtmlDocument->render()
#11 [ROOT]/libraries/src/Application/AdministratorApplication.php(459): Joomla\CMS\Application\CMSApplication->render()
#12 [ROOT]/libraries/src/Application/CMSApplication.php(325): Joomla\CMS\Application\AdministratorApplication->render()
#13 [ROOT]/administrator/includes/app.php(58): Joomla\CMS\Application\CMSApplication->execute()
#14 [ROOT]/administrator/index.php(32): require_once('...')
#15 {main}
2025-12-17T06:21:50+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T06:21:50+00:00	WARNING 127.0.0.1	jerror	Class `` does not exist, could not create a toolbar button.
2025-12-17T06:21:50+00:00	CRITICAL 127.0.0.1	error	Uncaught Throwable of type UnexpectedValueException thrown with message "Button not defined for type = CsvExport". Stack trace: #0 [ROOT]/libraries/src/Toolbar/Toolbar.php(327): Joomla\CMS\Toolbar\Toolbar->renderButton()
#1 [ROOT]/administrator/modules/mod_toolbar/src/Dispatcher/Dispatcher.php(37): Joomla\CMS\Toolbar\Toolbar->render()
#2 [ROOT]/libraries/src/Dispatcher/AbstractModuleDispatcher.php(63): Joomla\Module\Toolbar\Administrator\Dispatcher\Dispatcher->getLayoutData()
#3 [ROOT]/libraries/src/Helper/ModuleHelper.php(289): Joomla\CMS\Dispatcher\AbstractModuleDispatcher->dispatch()
#4 [ROOT]/libraries/src/Helper/ModuleHelper.php(160): Joomla\CMS\Helper\ModuleHelper::renderRawModule()
#5 [ROOT]/libraries/src/Document/Renderer/Html/ModuleRenderer.php(99): Joomla\CMS\Helper\ModuleHelper::renderModule()
#6 [ROOT]/libraries/src/Document/Renderer/Html/ModulesRenderer.php(51): Joomla\CMS\Document\Renderer\Html\ModuleRenderer->render()
#7 [ROOT]/libraries/src/Document/HtmlDocument.php(575): Joomla\CMS\Document\Renderer\Html\ModulesRenderer->render()
#8 [ROOT]/libraries/src/Document/HtmlDocument.php(894): Joomla\CMS\Document\HtmlDocument->getBuffer()
#9 [ROOT]/libraries/src/Document/HtmlDocument.php(647): Joomla\CMS\Document\HtmlDocument->_renderTemplate()
#10 [ROOT]/libraries/src/Application/CMSApplication.php(1132): Joomla\CMS\Document\HtmlDocument->render()
#11 [ROOT]/libraries/src/Application/AdministratorApplication.php(459): Joomla\CMS\Application\CMSApplication->render()
#12 [ROOT]/libraries/src/Application/CMSApplication.php(325): Joomla\CMS\Application\AdministratorApplication->render()
#13 [ROOT]/administrator/includes/app.php(58): Joomla\CMS\Application\CMSApplication->execute()
#14 [ROOT]/administrator/index.php(32): require_once('...')
#15 {main}
2025-12-17T06:21:50+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T06:21:50+00:00	WARNING 127.0.0.1	jerror	Class `` does not exist, could not create a toolbar button.
2025-12-17T06:21:50+00:00	CRITICAL 127.0.0.1	error	Uncaught Throwable of type UnexpectedValueException thrown with message "Button not defined for type = CsvExport". Stack trace: #0 [ROOT]/libraries/src/Toolbar/Toolbar.php(327): Joomla\CMS\Toolbar\Toolbar->renderButton()
#1 [ROOT]/administrator/modules/mod_toolbar/src/Dispatcher/Dispatcher.php(37): Joomla\CMS\Toolbar\Toolbar->render()
#2 [ROOT]/libraries/src/Dispatcher/AbstractModuleDispatcher.php(63): Joomla\Module\Toolbar\Administrator\Dispatcher\Dispatcher->getLayoutData()
#3 [ROOT]/libraries/src/Helper/ModuleHelper.php(289): Joomla\CMS\Dispatcher\AbstractModuleDispatcher->dispatch()
#4 [ROOT]/libraries/src/Helper/ModuleHelper.php(160): Joomla\CMS\Helper\ModuleHelper::renderRawModule()
#5 [ROOT]/libraries/src/Document/Renderer/Html/ModuleRenderer.php(99): Joomla\CMS\Helper\ModuleHelper::renderModule()
#6 [ROOT]/libraries/src/Document/Renderer/Html/ModulesRenderer.php(51): Joomla\CMS\Document\Renderer\Html\ModuleRenderer->render()
#7 [ROOT]/libraries/src/Document/HtmlDocument.php(575): Joomla\CMS\Document\Renderer\Html\ModulesRenderer->render()
#8 [ROOT]/libraries/src/Document/HtmlDocument.php(894): Joomla\CMS\Document\HtmlDocument->getBuffer()
#9 [ROOT]/libraries/src/Document/HtmlDocument.php(647): Joomla\CMS\Document\HtmlDocument->_renderTemplate()
#10 [ROOT]/libraries/src/Application/CMSApplication.php(1132): Joomla\CMS\Document\HtmlDocument->render()
#11 [ROOT]/libraries/src/Application/AdministratorApplication.php(459): Joomla\CMS\Application\CMSApplication->render()
#12 [ROOT]/libraries/src/Application/CMSApplication.php(325): Joomla\CMS\Application\AdministratorApplication->render()
#13 [ROOT]/administrator/includes/app.php(58): Joomla\CMS\Application\CMSApplication->execute()
#14 [ROOT]/administrator/index.php(32): require_once('...')
#15 {main}
2025-12-17T06:21:57+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T06:22:24+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T06:22:39+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T06:42:21+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T06:42:21+00:00	WARNING 127.0.0.1	jerror	Class `` does not exist, could not create a toolbar button.
2025-12-17T06:42:21+00:00	CRITICAL 127.0.0.1	error	Uncaught Throwable of type UnexpectedValueException thrown with message "Button not defined for type = CsvExport". Stack trace: #0 [ROOT]/libraries/src/Toolbar/Toolbar.php(327): Joomla\CMS\Toolbar\Toolbar->renderButton()
#1 [ROOT]/administrator/modules/mod_toolbar/src/Dispatcher/Dispatcher.php(37): Joomla\CMS\Toolbar\Toolbar->render()
#2 [ROOT]/libraries/src/Dispatcher/AbstractModuleDispatcher.php(63): Joomla\Module\Toolbar\Administrator\Dispatcher\Dispatcher->getLayoutData()
#3 [ROOT]/libraries/src/Helper/ModuleHelper.php(289): Joomla\CMS\Dispatcher\AbstractModuleDispatcher->dispatch()
#4 [ROOT]/libraries/src/Helper/ModuleHelper.php(160): Joomla\CMS\Helper\ModuleHelper::renderRawModule()
#5 [ROOT]/libraries/src/Document/Renderer/Html/ModuleRenderer.php(99): Joomla\CMS\Helper\ModuleHelper::renderModule()
#6 [ROOT]/libraries/src/Document/Renderer/Html/ModulesRenderer.php(51): Joomla\CMS\Document\Renderer\Html\ModuleRenderer->render()
#7 [ROOT]/libraries/src/Document/HtmlDocument.php(575): Joomla\CMS\Document\Renderer\Html\ModulesRenderer->render()
#8 [ROOT]/libraries/src/Document/HtmlDocument.php(894): Joomla\CMS\Document\HtmlDocument->getBuffer()
#9 [ROOT]/libraries/src/Document/HtmlDocument.php(647): Joomla\CMS\Document\HtmlDocument->_renderTemplate()
#10 [ROOT]/libraries/src/Application/CMSApplication.php(1132): Joomla\CMS\Document\HtmlDocument->render()
#11 [ROOT]/libraries/src/Application/AdministratorApplication.php(459): Joomla\CMS\Application\CMSApplication->render()
#12 [ROOT]/libraries/src/Application/CMSApplication.php(325): Joomla\CMS\Application\AdministratorApplication->render()
#13 [ROOT]/administrator/includes/app.php(58): Joomla\CMS\Application\CMSApplication->execute()
#14 [ROOT]/administrator/index.php(32): require_once('...')
#15 {main}
2025-12-17T06:42:27+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T06:42:27+00:00	WARNING 127.0.0.1	jerror	Class `` does not exist, could not create a toolbar button.
2025-12-17T06:42:27+00:00	CRITICAL 127.0.0.1	error	Uncaught Throwable of type UnexpectedValueException thrown with message "Button not defined for type = CsvExport". Stack trace: #0 [ROOT]/libraries/src/Toolbar/Toolbar.php(327): Joomla\CMS\Toolbar\Toolbar->renderButton()
#1 [ROOT]/administrator/modules/mod_toolbar/src/Dispatcher/Dispatcher.php(37): Joomla\CMS\Toolbar\Toolbar->render()
#2 [ROOT]/libraries/src/Dispatcher/AbstractModuleDispatcher.php(63): Joomla\Module\Toolbar\Administrator\Dispatcher\Dispatcher->getLayoutData()
#3 [ROOT]/libraries/src/Helper/ModuleHelper.php(289): Joomla\CMS\Dispatcher\AbstractModuleDispatcher->dispatch()
#4 [ROOT]/libraries/src/Helper/ModuleHelper.php(160): Joomla\CMS\Helper\ModuleHelper::renderRawModule()
#5 [ROOT]/libraries/src/Document/Renderer/Html/ModuleRenderer.php(99): Joomla\CMS\Helper\ModuleHelper::renderModule()
#6 [ROOT]/libraries/src/Document/Renderer/Html/ModulesRenderer.php(51): Joomla\CMS\Document\Renderer\Html\ModuleRenderer->render()
#7 [ROOT]/libraries/src/Document/HtmlDocument.php(575): Joomla\CMS\Document\Renderer\Html\ModulesRenderer->render()
#8 [ROOT]/libraries/src/Document/HtmlDocument.php(894): Joomla\CMS\Document\HtmlDocument->getBuffer()
#9 [ROOT]/libraries/src/Document/HtmlDocument.php(647): Joomla\CMS\Document\HtmlDocument->_renderTemplate()
#10 [ROOT]/libraries/src/Application/CMSApplication.php(1132): Joomla\CMS\Document\HtmlDocument->render()
#11 [ROOT]/libraries/src/Application/AdministratorApplication.php(459): Joomla\CMS\Application\CMSApplication->render()
#12 [ROOT]/libraries/src/Application/CMSApplication.php(325): Joomla\CMS\Application\AdministratorApplication->render()
#13 [ROOT]/administrator/includes/app.php(58): Joomla\CMS\Application\CMSApplication->execute()
#14 [ROOT]/administrator/index.php(32): require_once('...')
#15 {main}
2025-12-17T06:42:28+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T06:42:28+00:00	WARNING 127.0.0.1	jerror	Class `` does not exist, could not create a toolbar button.
2025-12-17T06:42:28+00:00	CRITICAL 127.0.0.1	error	Uncaught Throwable of type UnexpectedValueException thrown with message "Button not defined for type = CsvExport". Stack trace: #0 [ROOT]/libraries/src/Toolbar/Toolbar.php(327): Joomla\CMS\Toolbar\Toolbar->renderButton()
#1 [ROOT]/administrator/modules/mod_toolbar/src/Dispatcher/Dispatcher.php(37): Joomla\CMS\Toolbar\Toolbar->render()
#2 [ROOT]/libraries/src/Dispatcher/AbstractModuleDispatcher.php(63): Joomla\Module\Toolbar\Administrator\Dispatcher\Dispatcher->getLayoutData()
#3 [ROOT]/libraries/src/Helper/ModuleHelper.php(289): Joomla\CMS\Dispatcher\AbstractModuleDispatcher->dispatch()
#4 [ROOT]/libraries/src/Helper/ModuleHelper.php(160): Joomla\CMS\Helper\ModuleHelper::renderRawModule()
#5 [ROOT]/libraries/src/Document/Renderer/Html/ModuleRenderer.php(99): Joomla\CMS\Helper\ModuleHelper::renderModule()
#6 [ROOT]/libraries/src/Document/Renderer/Html/ModulesRenderer.php(51): Joomla\CMS\Document\Renderer\Html\ModuleRenderer->render()
#7 [ROOT]/libraries/src/Document/HtmlDocument.php(575): Joomla\CMS\Document\Renderer\Html\ModulesRenderer->render()
#8 [ROOT]/libraries/src/Document/HtmlDocument.php(894): Joomla\CMS\Document\HtmlDocument->getBuffer()
#9 [ROOT]/libraries/src/Document/HtmlDocument.php(647): Joomla\CMS\Document\HtmlDocument->_renderTemplate()
#10 [ROOT]/libraries/src/Application/CMSApplication.php(1132): Joomla\CMS\Document\HtmlDocument->render()
#11 [ROOT]/libraries/src/Application/AdministratorApplication.php(459): Joomla\CMS\Application\CMSApplication->render()
#12 [ROOT]/libraries/src/Application/CMSApplication.php(325): Joomla\CMS\Application\AdministratorApplication->render()
#13 [ROOT]/administrator/includes/app.php(58): Joomla\CMS\Application\CMSApplication->execute()
#14 [ROOT]/administrator/index.php(32): require_once('...')
#15 {main}
2025-12-17T06:42:56+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T06:45:42+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T06:45:43+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T06:45:53+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T06:45:53+00:00	INFO 127.0.0.1	controller	Holding edit ID com_jticketing.edit.event.1 Array (     [0] => 1 ) 
2025-12-17T06:45:53+00:00	INFO 127.0.0.1	controller	Checking edit ID com_jticketing.edit.event.1: 1 Array (     [0] => 1 ) 
2025-12-17T06:45:53+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T06:45:53+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T06:45:53+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T06:46:02+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T06:46:02+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T06:46:02+00:00	INFO 127.0.0.1	controller	Releasing edit ID com_jticketing.edit.event.1 Array ( ) 
2025-12-17T06:46:02+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T06:46:08+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T06:46:27+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T06:46:33+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T06:46:43+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T06:46:44+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:04:39+00:00	INFO 127.0.0.1	joomlafailure	Username and password do not match or you do not have an account yet.
2025-12-17T07:04:39+00:00	WARNING 127.0.0.1	jerror	Username and password do not match or you do not have an account yet.
2025-12-17T07:07:44+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:07:45+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:07:45+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:21:45+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:21:45+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:21:46+00:00	CRITICAL 127.0.0.1	error	Uncaught Throwable of type Joomla\CMS\WebAsset\Exception\UnknownAssetException thrown with message "There is no "chosen" asset of a "preset" type in the registry.". Stack trace: #0 [ROOT]/libraries/src/WebAsset/WebAssetManager.php(274): Joomla\CMS\WebAsset\WebAssetRegistry->get()
#1 [ROOT]/libraries/src/WebAsset/WebAssetManager.php(208): Joomla\CMS\WebAsset\WebAssetManager->useAsset()
#2 [ROOT]/libraries/src/HTML/Helpers/FormBehavior.php(98): Joomla\CMS\WebAsset\WebAssetManager->__call()
#3 [ROOT]/libraries/src/HTML/HTMLHelper.php(307): Joomla\CMS\HTML\Helpers\FormBehavior::chosen()
#4 [ROOT]/libraries/src/HTML/HTMLHelper.php(150): Joomla\CMS\HTML\HTMLHelper::call()
#5 [ROOT]/components/com_jticketing/views/eventform/tmpl/default_bs5.php(22): Joomla\CMS\HTML\HTMLHelper::_()
#6 [ROOT]/libraries/src/MVC/View/HtmlView.php(416): include('...')
#7 [ROOT]/components/com_jticketing/views/eventform/tmpl/default.php(13): Joomla\CMS\MVC\View\HtmlView->loadTemplate()
#8 [ROOT]/libraries/src/MVC/View/HtmlView.php(416): include('...')
#9 [ROOT]/libraries/src/MVC/View/HtmlView.php(204): Joomla\CMS\MVC\View\HtmlView->loadTemplate()
#10 [ROOT]/components/com_jticketing/views/eventform/view.html.php(284): Joomla\CMS\MVC\View\HtmlView->display()
#11 [ROOT]/libraries/src/MVC/Controller/BaseController.php(697): JticketingViewEventform->display()
#12 [ROOT]/administrator/components/com_jticketing/controller.php(59): Joomla\CMS\MVC\Controller\BaseController->display()
#13 [ROOT]/libraries/src/MVC/Controller/BaseController.php(730): JticketingController->display()
#14 [ROOT]/components/com_jticketing/jticketing.php(134): Joomla\CMS\MVC\Controller\BaseController->execute()
#15 [ROOT]/libraries/src/Dispatcher/LegacyComponentDispatcher.php(71): require_once('...')
#16 [ROOT]/libraries/src/Dispatcher/LegacyComponentDispatcher.php(73): Joomla\CMS\Dispatcher\LegacyComponentDispatcher::Joomla\CMS\Dispatcher\{closure}()
#17 [ROOT]/libraries/src/Component/ComponentHelper.php(361): Joomla\CMS\Dispatcher\LegacyComponentDispatcher->dispatch()
#18 [ROOT]/libraries/src/Application/SiteApplication.php(217): Joomla\CMS\Component\ComponentHelper::renderComponent()
#19 [ROOT]/libraries/src/Application/SiteApplication.php(271): Joomla\CMS\Application\SiteApplication->dispatch()
#20 [ROOT]/libraries/src/Application/CMSApplication.php(320): Joomla\CMS\Application\SiteApplication->doExecute()
#21 [ROOT]/includes/app.php(58): Joomla\CMS\Application\CMSApplication->execute()
#22 [ROOT]/index.php(51): require_once('...')
#23 {main}
2025-12-17T07:22:15+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:22:15+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:22:15+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:22:16+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:22:16+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:22:16+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:22:16+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:22:16+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:22:16+00:00	CRITICAL 127.0.0.1	error	Uncaught Throwable of type Joomla\CMS\WebAsset\Exception\UnknownAssetException thrown with message "There is no "chosen" asset of a "preset" type in the registry.". Stack trace: #0 [ROOT]/libraries/src/WebAsset/WebAssetManager.php(274): Joomla\CMS\WebAsset\WebAssetRegistry->get()
#1 [ROOT]/libraries/src/WebAsset/WebAssetManager.php(208): Joomla\CMS\WebAsset\WebAssetManager->useAsset()
#2 [ROOT]/libraries/src/HTML/Helpers/FormBehavior.php(98): Joomla\CMS\WebAsset\WebAssetManager->__call()
#3 [ROOT]/libraries/src/HTML/HTMLHelper.php(307): Joomla\CMS\HTML\Helpers\FormBehavior::chosen()
#4 [ROOT]/libraries/src/HTML/HTMLHelper.php(150): Joomla\CMS\HTML\HTMLHelper::call()
#5 [ROOT]/components/com_jticketing/views/eventform/tmpl/default_bs5.php(22): Joomla\CMS\HTML\HTMLHelper::_()
#6 [ROOT]/libraries/src/MVC/View/HtmlView.php(416): include('...')
#7 [ROOT]/components/com_jticketing/views/eventform/tmpl/default.php(13): Joomla\CMS\MVC\View\HtmlView->loadTemplate()
#8 [ROOT]/libraries/src/MVC/View/HtmlView.php(416): include('...')
#9 [ROOT]/libraries/src/MVC/View/HtmlView.php(204): Joomla\CMS\MVC\View\HtmlView->loadTemplate()
#10 [ROOT]/components/com_jticketing/views/eventform/view.html.php(284): Joomla\CMS\MVC\View\HtmlView->display()
#11 [ROOT]/libraries/src/MVC/Controller/BaseController.php(697): JticketingViewEventform->display()
#12 [ROOT]/administrator/components/com_jticketing/controller.php(59): Joomla\CMS\MVC\Controller\BaseController->display()
#13 [ROOT]/libraries/src/MVC/Controller/BaseController.php(730): JticketingController->display()
#14 [ROOT]/components/com_jticketing/jticketing.php(134): Joomla\CMS\MVC\Controller\BaseController->execute()
#15 [ROOT]/libraries/src/Dispatcher/LegacyComponentDispatcher.php(71): require_once('...')
#16 [ROOT]/libraries/src/Dispatcher/LegacyComponentDispatcher.php(73): Joomla\CMS\Dispatcher\LegacyComponentDispatcher::Joomla\CMS\Dispatcher\{closure}()
#17 [ROOT]/libraries/src/Component/ComponentHelper.php(361): Joomla\CMS\Dispatcher\LegacyComponentDispatcher->dispatch()
#18 [ROOT]/libraries/src/Application/SiteApplication.php(217): Joomla\CMS\Component\ComponentHelper::renderComponent()
#19 [ROOT]/libraries/src/Application/SiteApplication.php(271): Joomla\CMS\Application\SiteApplication->dispatch()
#20 [ROOT]/libraries/src/Application/CMSApplication.php(320): Joomla\CMS\Application\SiteApplication->doExecute()
#21 [ROOT]/includes/app.php(58): Joomla\CMS\Application\CMSApplication->execute()
#22 [ROOT]/index.php(51): require_once('...')
#23 {main}
2025-12-17T07:24:07+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:24:07+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:24:08+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:24:08+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:24:08+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:24:08+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:24:08+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:24:08+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:24:08+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:24:08+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:24:09+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:24:11+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:24:11+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:24:11+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:24:13+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:24:13+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:24:13+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:24:13+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:24:13+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:24:13+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:24:13+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:24:13+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:24:13+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:24:16+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:24:16+00:00	WARNING 127.0.0.1	assets	No asset found for com_jticketing.event.1, falling back to com_jticketing
2025-12-17T07:24:17+00:00	CRITICAL 127.0.0.1	error	Uncaught Throwable of type Joomla\CMS\WebAsset\Exception\UnknownAssetException thrown with message "There is no "chosen" asset of a "preset" type in the registry.". Stack trace: #0 [ROOT]/libraries/src/WebAsset/WebAssetManager.php(274): Joomla\CMS\WebAsset\WebAssetRegistry->get()
#1 [ROOT]/libraries/src/WebAsset/WebAssetManager.php(208): Joomla\CMS\WebAsset\WebAssetManager->useAsset()
#2 [ROOT]/libraries/src/HTML/Helpers/FormBehavior.php(98): Joomla\CMS\WebAsset\WebAssetManager->__call()
#3 [ROOT]/libraries/src/HTML/HTMLHelper.php(307): Joomla\CMS\HTML\Helpers\FormBehavior::chosen()
#4 [ROOT]/libraries/src/HTML/HTMLHelper.php(150): Joomla\CMS\HTML\HTMLHelper::call()
#5 [ROOT]/components/com_jticketing/views/eventform/tmpl/default_bs5.php(22): Joomla\CMS\HTML\HTMLHelper::_()
#6 [ROOT]/libraries/src/MVC/View/HtmlView.php(416): include('...')
#7 [ROOT]/components/com_jticketing/views/eventform/tmpl/default.php(13): Joomla\CMS\MVC\View\HtmlView->loadTemplate()
#8 [ROOT]/libraries/src/MVC/View/HtmlView.php(416): include('...')
#9 [ROOT]/libraries/src/MVC/View/HtmlView.php(204): Joomla\CMS\MVC\View\HtmlView->loadTemplate()
#10 [ROOT]/components/com_jticketing/views/eventform/view.html.php(284): Joomla\CMS\MVC\View\HtmlView->display()
#11 [ROOT]/libraries/src/MVC/Controller/BaseController.php(697): JticketingViewEventform->display()
#12 [ROOT]/administrator/components/com_jticketing/controller.php(59): Joomla\CMS\MVC\Controller\BaseController->display()
#13 [ROOT]/libraries/src/MVC/Controller/BaseController.php(730): JticketingController->display()
#14 [ROOT]/components/com_jticketing/jticketing.php(134): Joomla\CMS\MVC\Controller\BaseController->execute()
#15 [ROOT]/libraries/src/Dispatcher/LegacyComponentDispatcher.php(71): require_once('...')
#16 [ROOT]/libraries/src/Dispatcher/LegacyComponentDispatcher.php(73): Joomla\CMS\Dispatcher\LegacyComponentDispatcher::Joomla\CMS\Dispatcher\{closure}()
#17 [ROOT]/libraries/src/Component/ComponentHelper.php(361): Joomla\CMS\Dispatcher\LegacyComponentDispatcher->dispatch()
#18 [ROOT]/libraries/src/Application/SiteApplication.php(217): Joomla\CMS\Component\ComponentHelper::renderComponent()
#19 [ROOT]/libraries/src/Application/SiteApplication.php(271): Joomla\CMS\Application\SiteApplication->dispatch()
#20 [ROOT]/libraries/src/Application/CMSApplication.php(320): Joomla\CMS\Application\SiteApplication->doExecute()
#21 [ROOT]/includes/app.php(58): Joomla\CMS\Application\CMSApplication->execute()
#22 [ROOT]/index.php(51): require_once('...')
#23 {main}
