/*
 * @version    SVN:<SVN_ID>
 * @package    Com_Jlike
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2019 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */
var pathService = {

	siteRoot: Joomla.getOptions("system.paths").base,
	pathParamsUrl: '/index.php?option=com_jlike&task=type.getPathTypeParams&format=json',
	pathTypeCategoriesUrl: '/index.php?option=com_jlike&task=type.getPathTypeCategories&format=json',

	postData: function(url, formData, params) {
		if(!params){
			params = {};
		}

		params['url']		= this.siteRoot + url;
		params['data'] 		= formData;
		params['type'] 		= typeof params['type'] != "undefined" ? params['type'] : 'POST';
		params['async'] 	= typeof params['async'] != "undefined" ? params['async'] :false;
		params['dataType'] 	= typeof params['datatype'] != "undefined" ? params['datatype'] : 'json';
		params['contentType'] 	= typeof params['contentType'] != "undefined" ? params['contentType'] : 'application/x-www-form-urlencoded; charset=UTF-8';
		params['processData'] 	= typeof params['processData'] != "undefined" ? params['processData'] : true;

		var promise = jQuery.ajax(params);
		return promise;
	},
	getPathParams: function (formData, params) {
		return this.postData(this.pathParamsUrl, formData, params);
	},
	getPathTypeCategories: function (formData, params) {
		return this.postData(this.pathTypeCategoriesUrl, formData, params);
	}
}
