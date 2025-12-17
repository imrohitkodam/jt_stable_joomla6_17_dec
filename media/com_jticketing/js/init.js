/**
 * @package     JTicketing
 *
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (C) 2009 - 2025 Techjoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * Bootstrap the jQuery variable for Jticketing component and set global variable for this
 */

if (typeof window.JTQuery === 'undefined' && typeof window.jQuery !== 'undefined') {
    var JTQuery,
        tmp,
        version,
        // Save current global references
        old_$ = window.$,
        old_jQuery = window.jQuery;

    // @TODO Possible infinite loop if the jquery does not load 
    while (typeof window.jQuery !== 'undefined') {
        version = window.jQuery.fn.jquery.split('.');
        tmp = window.jQuery.noConflict(true);

        // Do not use versions older than 1.8
        if (!(version[0] == '1' && parseInt(version[1], 10) < 8)) {
            JTQuery = tmp;
            break;
        }
    }

    window.$ = old_$;
    window.jQuery = old_jQuery;
    window.JTQuery = JTQuery;
}