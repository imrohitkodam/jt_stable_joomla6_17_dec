Issue :


An error has occurred.
0 Call to a member function load() on false
Call Stack
#	Function	Location
1	()	JROOT/administrator/components/com_jticketing/tables/event.php:411
2	JTicketingTableEvent->check()	JROOT/libraries/src/MVC/Model/AdminModel.php:1327
3	Joomla\CMS\MVC\Model\AdminModel->save()	JROOT/components/com_jticketing/models/eventform.php:610
4	JticketingModelEventForm->save()	JROOT/administrator/components/com_jticketing/controllers/event.php:238
5	JTicketingControllerEvent->save()	JROOT/libraries/src/MVC/Controller/BaseController.php:730
6	Joomla\CMS\MVC\Controller\BaseController->execute()	JROOT/administrator/components/com_jticketing/jticketing.php:114
7	require_once()	JROOT/libraries/src/Dispatcher/LegacyComponentDispatcher.php:71
8	Joomla\CMS\Dispatcher\LegacyComponentDispatcher::Joomla\CMS\Dispatcher\{closure}()	JROOT/libraries/src/Dispatcher/LegacyComponentDispatcher.php:73
9	Joomla\CMS\Dispatcher\LegacyComponentDispatcher->dispatch()	JROOT/libraries/src/Component/ComponentHelper.php:361
10	Joomla\CMS\Component\ComponentHelper::renderComponent()	JROOT/libraries/src/Application/AdministratorApplication.php:150
11	Joomla\CMS\Application\AdministratorApplication->dispatch()	JROOT/libraries/src/Application/AdministratorApplication.php:205
12	Joomla\CMS\Application\AdministratorApplication->doExecute()	JROOT/libraries/src/Application/CMSApplication.php:320
13	Joomla\CMS\Application\CMSApplication->execute()	JROOT/administrator/includes/app.php:58
14	require_once()	JROOT/administrator/index.php:32

Issue :

An error has occurred.
0 Class "Joomla\CMS\Filesystem\Path" not found
Call Stack
#	Function	Location
1	()	JROOT/administrator/components/com_tjcertificate/models/fields/sampletemplates.php:58
2	JFormFieldSampleTemplates->getOptions()	JROOT/libraries/src/Form/Field/ListField.php:72
3	Joomla\CMS\Form\Field\ListField->getInput()	JROOT/libraries/src/Form/FormField.php:1070
4	Joomla\CMS\Form\FormField->renderField()	JROOT/libraries/src/Form/Form.php:547
5	Joomla\CMS\Form\Form->renderField()	JROOT/administrator/components/com_tjcertificate/views/template/tmpl/edit_bs5.php:74
6	include()	JROOT/libraries/src/MVC/View/HtmlView.php:416
7	Joomla\CMS\MVC\View\HtmlView->loadTemplate()	JROOT/administrator/components/com_tjcertificate/views/template/tmpl/edit.php:20
8	include()	JROOT/libraries/src/MVC/View/HtmlView.php:416
9	Joomla\CMS\MVC\View\HtmlView->loadTemplate()	JROOT/libraries/src/MVC/View/HtmlView.php:204
10	Joomla\CMS\MVC\View\HtmlView->display()	JROOT/administrator/components/com_tjcertificate/views/template/view.html.php:111
11	TjCertificateViewTemplate->display()	JROOT/libraries/src/MVC/Controller/BaseController.php:697
12	Joomla\CMS\MVC\Controller\BaseController->display()	JROOT/administrator/components/com_tjcertificate/controller.php:45
13	TjCertificateController->display()	JROOT/libraries/src/MVC/Controller/BaseController.php:730
14	Joomla\CMS\MVC\Controller\BaseController->execute()	JROOT/administrator/components/com_tjcertificate/tjcertificate.php:31
15	require_once()	JROOT/libraries/src/Dispatcher/LegacyComponentDispatcher.php:71
16	Joomla\CMS\Dispatcher\LegacyComponentDispatcher::Joomla\CMS\Dispatcher\{closure}()	JROOT/libraries/src/Dispatcher/LegacyComponentDispatcher.php:73
17	Joomla\CMS\Dispatcher\LegacyComponentDispatcher->dispatch()	JROOT/libraries/src/Component/ComponentHelper.php:361
18	Joomla\CMS\Component\ComponentHelper::renderComponent()	JROOT/libraries/src/Application/AdministratorApplication.php:150
19	Joomla\CMS\Application\AdministratorApplication->dispatch()	JROOT/libraries/src/Application/AdministratorApplication.php:205
20	Joomla\CMS\Application\AdministratorApplication->doExecute()	JROOT/libraries/src/Application/CMSApplication.php:320
21	Joomla\CMS\Application\CMSApplication->execute()	JROOT/administrator/includes/app.php:58
22	require_once()	JROOT/administrator/index.php:32

issues :

An error has occurred.
0 mb_trim(): Argument #1 ($string) must be of type string, null given, called in /var/www/ttpl-rt-234-php83.local/public/jt_stable_17_dec/libraries/vendor/joomla/string/src/StringHelper.php on line 664
Call Stack
#	Function	Location
1	()	JROOT/libraries/vendor/symfony/polyfill-mbstring/bootstrap80.php:144
2	mb_trim()	JROOT/libraries/vendor/joomla/string/src/StringHelper.php:664
3	Joomla\String\StringHelper::trim()	JROOT/components/com_jticketing/models/couponform.php:223
4	JticketingModelCouponform->save()	JROOT/libraries/src/MVC/Controller/FormController.php:689
5	Joomla\CMS\MVC\Controller\FormController->save()	JROOT/libraries/src/MVC/Controller/BaseController.php:730
6	Joomla\CMS\MVC\Controller\BaseController->execute()	JROOT/administrator/components/com_jticketing/jticketing.php:114
7	require_once()	JROOT/libraries/src/Dispatcher/LegacyComponentDispatcher.php:71
8	Joomla\CMS\Dispatcher\LegacyComponentDispatcher::Joomla\CMS\Dispatcher\{closure}()	JROOT/libraries/src/Dispatcher/LegacyComponentDispatcher.php:73
9	Joomla\CMS\Dispatcher\LegacyComponentDispatcher->dispatch()	JROOT/libraries/src/Component/ComponentHelper.php:361
10	Joomla\CMS\Component\ComponentHelper::renderComponent()	JROOT/libraries/src/Application/AdministratorApplication.php:150
11	Joomla\CMS\Application\AdministratorApplication->dispatch()	JROOT/libraries/src/Application/AdministratorApplication.php:205
12	Joomla\CMS\Application\AdministratorApplication->doExecute()	JROOT/libraries/src/Application/CMSApplication.php:320
13	Joomla\CMS\Application\CMSApplication->execute()	JROOT/administrator/includes/app.php:58
14	require_once()	JROOT/administrator/index.php:32