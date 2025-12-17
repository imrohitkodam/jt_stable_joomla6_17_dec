/*
 * @version    SVN:<SVN_ID>
 * @package    Com_Jlike
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2019 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */
var path = {
	getPathParams: function (defaultParams) {

		var formData = {};

		if (!defaultParams)
		{
			formData['pathTypeId'] = jQuery("#jform_path_type").val();
		}
		else
		{
			formData['defaultPathType'] = 1;
		}

		var promise = pathService.getPathParams(formData);

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
				path.renderPathParamField(response);
			}
		});
	},
	renderPathParamField: function (response)
	{
		var jsonObj = JSON.parse(JSON.stringify(response));

		if (jsonObj && typeof jsonObj === "object" && jsonObj.data != null)
		{
			jQuery('#jform_params').val(JSON.stringify(response.data, null, 4));
		}
		else
		{
			jQuery('#jform_params').val('');
		}
	},
	getPathTypeCategories: function (pathType, categoryId)
	{
		var formData = {};

		formData['pathType'] = pathType;

		var promise = pathService.getPathTypeCategories(formData);

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

			path.renderPathCategories(response, categoryId);
		});
	},
	renderPathCategories: function (response, categoryId)
	{
		var catData = JSON.parse(response.data);
		var selected = '';

		jQuery("#jform_category_id").empty().append("<option value='0'>" + Joomla.Text._("COM_JLIKE_PATH_CATEGORY") + "</option>");

		if (catData !== undefined && catData != null)
		{
			catData.forEach(function (arrayItem) {
				if (categoryId != undefined && categoryId == arrayItem.id)
				{
					selected = "selected='selected'";
				}
				else
				{
					selected = '';
				}
				jQuery("#jform_category_id").append("<option value=" + arrayItem.id + " " + selected + ">" + arrayItem.path + "</option>");
			});
		}

		jQuery("#jform_category_id").trigger("liszt:updated");
	}
}
