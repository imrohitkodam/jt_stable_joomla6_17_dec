/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

var template = {

	previewTemplate: function (id) {
		jQuery(document).on('click', 'button[data-bs-target="#templatePreview"]', function () {
			jQuery('#show-info').hide();
			var editorId = jQuery('#'+id);

			if (typeof tinyMCE != "undefined")
			{
			   tinyMCE.execCommand('mceToggleEditor', false, id);
			}
			else if (typeof CodeMirror != "undefined")
			{
				var editor = document.querySelector('.CodeMirror').CodeMirror;
				editorId.html(editor.getValue());
			}
			else
			{
				jQuery('#show-info').show();
			}

			jQuery('#previewTempl').empty();
			jQuery('<style>').html(jQuery('#jform_template_css').val()).appendTo('#previewTempl');
			jQuery('<div>').html(editorId.val()).appendTo('#previewTempl');
		});

		jQuery('#templatePreview').on('hidden.bs.modal', function () {

			if (typeof tinyMCE != "undefined")
			{
			   tinyMCE.execCommand('mceToggleEditor', false, id);
			}

			jQuery('#previewTempl').empty();
		});
	},
	loadDefaultTemplate: function (defaultTemplate) {

		var formData = {};

		if (defaultTemplate == '' || defaultTemplate === undefined)
		{
			return false;
		}

		formData['defaultTemplate'] = defaultTemplate;

		var promise = tjCertificateService.loadDefaultTemplate(formData);

		promise.fail(
			function(response) {
				var messages = { "error": [response.responseText]};
				Joomla.renderMessages(messages);
			}
		).done(function(response) {
			if (!response.success && response.message)
			{
				var messages = { "error": [response.message]};
				Joomla.renderMessages(messages);
			}

			if (response.messages){
				Joomla.renderMessages(response.messages);
			}

			if (response.success) {
				template.renderDefaultTemplate(response, 'jform_body');
			}
		});
	},
	renderDefaultTemplate: function (response, id)
	{
		jQuery('#' + id).empty().val(response.data);
		Joomla.editors.instances[id].setValue(response.data);
	},
	loadCustomTemplate: function (customTemplate)
	{
		var formData = {};

		if (customTemplate == '' || customTemplate === undefined)
		{
			return false;
		}

		formData['templateId'] = customTemplate;

		var promise = tjCertificateService.loadCustomTemplate(formData);

		promise.fail(
			function(response) {
				var messages = { "error": [response.responseText]};
				Joomla.renderMessages(messages);
			}
		).done(function(response) {

			if (!response.success && response.message)
			{
				var messages = { "error": [response.message]};
				Joomla.renderMessages(messages);
			}

			if (response.messages){
				Joomla.renderMessages(response.messages);
			}

			if (response.success) {
				template.renderDefaultTemplate(response, 'jform_generated_body');
			}
		});
	}
}
