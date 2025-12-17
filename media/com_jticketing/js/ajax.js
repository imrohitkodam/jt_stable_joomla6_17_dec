/**
 * @package     JTicketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * unified Ajax request handler which will work like a entry point for all ajax call generated from the JTicketing component
 */

if (typeof JTicketing == "undefined") {
    var JTicketing = {}
}

if (typeof JTicketing.Ajax == "undefined") {
    (function($) {
        JTicketing.Ajax = function(options) {
            var request = $.Deferred(),
                ajaxOptions = $.extend(true, {}, JTicketing.Ajax.defaultOptions, options),

                xhr = request.xhr = $.ajax(ajaxOptions).done(
                    function(inputs) {
                        if (typeof inputs === "string") {
                            try {
                                inputs = $.parseJSON(inputs);
                            } catch (e) {
                                request.rejectWith(request, [
                                    "Invalid JSON response detected.", "error"
                                ]);
                            }
                        }

                        if ($.isPlainObject(inputs)) {
                            request.resolveWith(request, [inputs]);
                        }

                        if (request.state() === "pending") {
                            request.resolveWith(request);
                        }

                    }).fail(function(jqXHR, status, statusText) {
                    request.rejectWith(request, [statusText, status]);
                });

            request.abort = xhr.abort;

            return request;
        }

        JTicketing.Ajax.defaultOptions = {
            type: 'POST',
            data: {
                tmpl: 'component',
                format: 'json'
            },
            cache: false,
            contentType: 'application/x-www-form-urlencoded',
            dataType: 'json'
        };
    })(JTQuery);
}