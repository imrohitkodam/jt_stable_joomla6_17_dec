/**
 * @package     TJCertificate
 * @subpackage  com_tjcertificate
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2019 Techjoomla. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

var tjCertificateService = {

	siteRoot: Joomla.getOptions("system.paths").base,
	loadDefaultTemplateUrl: '/index.php?option=com_tjcertificate&task=template.loadDefaultTemplate&format=json',
	loadCustomTemplateUrl: '/index.php?option=com_tjcertificate&task=template.loadCustomTemplate&format=json',
	deleteAttachmentUrl: '/index.php?option=com_tjcertificate&task=trainingrecord.deleteAttachment&format=json',
	deleteItemUrl: '/index.php?option=com_tjcertificate&task=trainingrecord.delete&format=json',
	getAgencyUsersUrl: '/index.php?option=com_tjcertificate&task=agency.getAgencyUsers&format=json',
	addRecordsUrl: '/index.php?option=com_tjcertificate&task=bulktrainingrecord.save&format=json',

	postData: function(url, formData, params) {
		if(!params){
			params = {};
		}

		params['url']		    = this.siteRoot + url;
		params['data'] 		    = formData;
		params['type'] 		    = typeof params['type'] != "undefined" ? params['type'] : 'POST';
		params['async'] 	    = typeof params['async'] != "undefined" ? params['async'] :false;
		params['dataType'] 	    = typeof params['datatype'] != "undefined" ? params['datatype'] : 'json';
		params['contentType'] 	= typeof params['contentType'] != "undefined" ? params['contentType'] : 'application/x-www-form-urlencoded; charset=UTF-8';
		params['processData'] 	= typeof params['processData'] != "undefined" ? params['processData'] : true;

		var promise = jQuery.ajax(params);
		return promise;
	},
	loadDefaultTemplate: function (formData, params) {
		return this.postData(this.loadDefaultTemplateUrl, formData, params);
	},
	loadCustomTemplate: function (formData, params) {
		return this.postData(this.loadCustomTemplateUrl, formData, params);
	},
	deleteAttachment: function (formData, params) {
		return this.postData(this.deleteAttachmentUrl, formData, params);
	},
	deleteItem: function (formData, params) {
		return this.postData(this.deleteItemUrl, formData, params);
	},
	getAgencyUsers: function (formData, params) {
		return this.postData(this.getAgencyUsersUrl, formData, params);
	},
	addRecords: function (formData, params) {
		return this.postData(this.addRecordsUrl, formData, params);
	},
}
