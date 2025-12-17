Issue :


The requested page can't be found.
An error has occurred while processing your request.

You may not be able to visit this page because of:

an out-of-date bookmark/favourite
a mistyped address
a search engine that has an out-of-date listing for this site
you have no access to this page
Go to the Home Page


If difficulties persist, please contact the website administrator and report the error below.

0 There is no "chosen" asset of a "preset" type in the registry.
Call Stack
#	Function	Location
1	()	JROOT/libraries/src/WebAsset/WebAssetRegistry.php:135
2	Joomla\CMS\WebAsset\WebAssetRegistry->get()	JROOT/libraries/src/WebAsset/WebAssetManager.php:274
3	Joomla\CMS\WebAsset\WebAssetManager->useAsset()	JROOT/libraries/src/WebAsset/WebAssetManager.php:208
4	Joomla\CMS\WebAsset\WebAssetManager->__call()	JROOT/libraries/src/HTML/Helpers/FormBehavior.php:98
5	Joomla\CMS\HTML\Helpers\FormBehavior::chosen()	JROOT/libraries/src/HTML/HTMLHelper.php:307
6	Joomla\CMS\HTML\HTMLHelper::call()	JROOT/libraries/src/HTML/HTMLHelper.php:150
7	Joomla\CMS\HTML\HTMLHelper::_()	JROOT/components/com_jticketing/views/eventform/tmpl/default_bs5.php:22
8	include()	JROOT/libraries/src/MVC/View/HtmlView.php:416
9	Joomla\CMS\MVC\View\HtmlView->loadTemplate()	JROOT/components/com_jticketing/views/eventform/tmpl/default.php:13
10	include()	JROOT/libraries/src/MVC/View/HtmlView.php:416
11	Joomla\CMS\MVC\View\HtmlView->loadTemplate()	JROOT/libraries/src/MVC/View/HtmlView.php:204
12	Joomla\CMS\MVC\View\HtmlView->display()	JROOT/components/com_jticketing/views/eventform/view.html.php:284
13	JticketingViewEventform->display()	JROOT/libraries/src/MVC/Controller/BaseController.php:697
14	Joomla\CMS\MVC\Controller\BaseController->display()	JROOT/administrator/components/com_jticketing/controller.php:59
15	JticketingController->display()	JROOT/libraries/src/MVC/Controller/BaseController.php:730
16	Joomla\CMS\MVC\Controller\BaseController->execute()	JROOT/components/com_jticketing/jticketing.php:134
17	require_once()	JROOT/libraries/src/Dispatcher/LegacyComponentDispatcher.php:71
18	Joomla\CMS\Dispatcher\LegacyComponentDispatcher::Joomla\CMS\Dispatcher\{closure}()	JROOT/libraries/src/Dispatcher/LegacyComponentDispatcher.php:73
19	Joomla\CMS\Dispatcher\LegacyComponentDispatcher->dispatch()	JROOT/libraries/src/Component/ComponentHelper.php:361
20	Joomla\CMS\Component\ComponentHelper::renderComponent()	JROOT/libraries/src/Application/SiteApplication.php:217
21	Joomla\CMS\Application\SiteApplication->dispatch()	JROOT/libraries/src/Application/SiteApplication.php:271
22	Joomla\CMS\Application\SiteApplication->doExecute()	JROOT/libraries/src/Application/CMSApplication.php:320
23	Joomla\CMS\Application\CMSApplication->execute()	JROOT/includes/app.php:58
24	require_once()	JROOT/index.php:51

Issue :

