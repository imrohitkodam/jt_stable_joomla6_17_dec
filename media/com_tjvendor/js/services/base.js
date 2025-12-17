'use strict';
export class Base {
	contentType = "application/json";

	constructor(url, data, config){
		this.url = url;
		this.data = data || {};
		this.config = config || {};
		this.config.headers = {};

		if (typeof config != "undefined") {
			this.config.headers = config.headers;
		}
	}

	/**
	 * Content type
	 *
	 * @param string  contentType Content Type
	 */
	setContentType(contentType){
		this.contentType = contentType;
	}

	/**
	 * Get the data based on content type
	 */
	getData(){
		if ((this.getContentType() === "application/json") && (typeof this.data === 'object')) {
			return JSON.stringify(this.data);
		}

		return this.data;
	}

	/**
	 * Return content type
	 *
	 * @param string  Content Type
	 */
	getContentType(){
		return this.contentType;
	}

	/**
	 * Get method
	 *
	 * @param   cb      function  Callback function
	 *
	 * @return  void
	 */
	get(cb) {
		if (typeof cb !== 'function') {
			throw "base expects callback to be function";
		}

		return jQuery.ajax({
			type: "GET",
			url: this.url,
			headers: this.config.headers,
			cache: false,
			contentType: this.getContentType(),
			beforeSend: function () {
			},
			success: function (res) {
				cb(null, res);
			},
			error: function (err) {
				cb(err, null);
			}
		});
	}

	/**
	 * Post method
	 *
	 * @param   cb      function  Callback function
	 *
	 * @return void
	 */
	post(cb) {
		if (typeof cb !== 'function') {
			throw "base expects callback to be function";
		}

		return jQuery.ajax({
			type: "POST",
			url: this.url,
			data: this.getData(),
			contentType: this.getContentType(),
			headers: this.config.headers,
			beforeSend: function () {
			},
			success: function (res) {
				cb(null, res);
			},
			error: function (err) {
				cb(err, null);
			}
		});
	}

	/**
	 * Patch method
	 *
	 * @param   cb      function  Callback function
	 *
	 * @return  void
	 */
	patch(cb){
		if (typeof cb !== 'function') {
			throw "base expects callback to be function";
		}

		return jQuery.ajax({
			type: "PATCH",
			url: this.url,
			data: this.getData(),
			contentType: this.getContentType(),
			headers: this.config.headers,
			beforeSend: function () {
			},
			success: function (res) {
				cb(null, res);
			},
			error: function (xhr) {
				cb(xhr, null);
			}
		});
	}
}
