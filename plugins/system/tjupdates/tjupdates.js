/*
 * @version    SVN:<SVN_ID>
 * @package    TjUpdates
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2018 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */

var tjupdates = {
	tjvc_root_url: "https://versioncheck.techjoomla.com",

	check: function (tjvc_extension, tjvc_version, tjvc_downloadid, showUpdateNotice) {
		if (typeof (tjvc_extension) === undefined) {
			tjvc_extension = null;
		}

		if (typeof (tjvc_version) === undefined) {
			tjvc_version = null;
		}

		if (typeof (tjvc_downloadid) === undefined) {
			tjvc_downloadid = null;
		}

		if (tjvc_extension === null && tjvc_version === null) {
			return;
		}

		let vcUrl = tjupdates.tjvc_root_url + "/version/" + tjvc_extension + "/" + tjvc_version;

		if (tjvc_downloadid !== null) {
			vcUrl += "/" + tjvc_downloadid;
		}

		jQuery.ajax({
			dataType: "jsonp",
			url: vcUrl,
			timeout: 3000
		})
		.done (function(data) {
			if (showUpdateNotice) {
				tjupdates.notify(data);
			}
		})
		.fail (function(jqXHR) {
			console.log(jqXHR);
		});
	},

	notify: function (data) {
		try {
			/*If not latest version*/
			if (data.compare === -1) {
				let btnHtml = '<div class="btn-wrapper" id="tj-update" style="float: right;">';
						btnHtml += '<a href="index.php?option=com_installer&view=update" class="btn btn-small btn-danger">';
							btnHtml += '<span class="icon-warning" aria-hidden="true"></span>';
							btnHtml += Joomla.JText._('PLG_SYSTEM_TJUPDATES_UPDATE_MSG') + data.latestVersion;
						btnHtml += '</a>';
					btnHtml += '</div>';

				jQuery('#toolbar').append(btnHtml);
			}
		}
		catch (err) {
			/*console.log(err.message);*/
		}
	}
};
