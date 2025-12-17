jQuery(window).on('load', function() {
loadCalender();
});

var loadCalender = function() {

	var todocalendar  =
	{
		renderCalendar :function (response)
		{
			// Format data as per required for calendar.
			eventObj = [];
			if (response)
			{
				if(response.data)
				{
					var event = response.data.result;
					jQuery.each(event,function(key, val) {

						if (val.content_title && val.content_url)
						{
							var start = moment(val.start_date);
							var end = moment(val.due_date);
							start.toISOString();
							end.toISOString();
							var calendar = {};
							calendar['id']= val.id;
							calendar['title']= val.content_title;
							calendar['url']= val.content_url;
							calendar['class']= val.id;
							calendar['start']= moment(val.start_date);
							calendar['end']= moment(val.due_date);

							if (moment(val.due_date) < moment())
							{
								calendar['backgroundColor']= '#f44259';
							}
							eventObj.push(calendar);
						}
					});
				}
			}
			else
			{
				error_html = Joomla.Text._('COM_JLIKE_MY_LIKE_ERROR_MSG');
				jQuery("#system-message-container").html("<div class='alert alert-warning'>" + error_html + "</div>");
			}

			getCal(eventObj);
		}
	};

	jQuery('div[data-jlike-todos="todo"]').each(function() {
		var obj = {};
		obj["url"] = jQuery(this).attr("data-jlike-url");
		obj["type"] = jQuery(this).attr("data-jlike-type");
		obj["context"] = jQuery(this).attr("data-jlike-context");
		obj["assigned_by"] = jQuery(this).attr("data-jlike-assigned_by");
		obj["assigned_to"] = jQuery(this).attr("data-jlike-assigned_to");
		obj["start_date"] = jQuery(this).attr("data-jlike-start_date");
		obj["due_date"] = jQuery(this).attr("data-jlike-due_date");
		obj["parent_id"] = jQuery(this).attr("data-jlike-parent_id");
		obj["status"] = jQuery(this).attr("data-jlike-status");
		obj["state"] = jQuery(this).attr("data-jlike-state");
		obj["client"] = jQuery(this).attr("data-jlike-client");
		obj["content_id"] = jQuery(this).attr("data-jlike-content_id");

		jQuery(this).calendartodo({
			obj: obj,
			action: 'getTodos',
			callback: function(data) {
				todocalendar.renderCalendar(data);
			}
		});
	});
};

var setdata = 
{
	setClient: function (data) {
			jQuery('#ajax_loader').show();
			jQuery('#ajax_loader').html("<img src=" + jlike_site_root + "media/com_jlike/images/ajax-loader.gif>");
			jQuery('#ajax_loader').css('display','block');
			jQuery("#todoCalendar").attr("data-jlike-client", data.value);
			loadCalender();
			jQuery('#ajax_loader').hide();
	}
}
