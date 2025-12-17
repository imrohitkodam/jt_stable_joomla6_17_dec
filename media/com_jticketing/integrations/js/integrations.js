window.onload = function(){
	jQuery(".icon-calendar").addClass('far fa-calendar-alt');
  	var element = document.getElementsByClassName('btn-clear');
 	element[0].classList.remove('btn-clear');
}

var valid = {
	positiveNumber : function(el, integration = '')
	{
		var parent = el.closest('.subform-repeatable-group');
		if (integration) {
			var title = jQuery(parent).find('.form-control.titlevalid').val();
		} else {
			var title = jQuery(parent).find('.o-form-control.titlevalid').val();
		}
		var enable = jQuery('#allowfield').val();

		if (title == '')
		{
			alert(Joomla.JText._('COM_JTICKETING_TICKET_TITLE_EMPTY'));
		}

		let returnValue = valid.getRoundedValue(el.value);

		if (returnValue)
		{
			jQuery(el.id).focus();

			let msg = {
				warning: [returnValue],
			};

			Joomla.renderMessages(msg);
		}

		let i = 0;
		for (i = 0; i < el.value.length; i++)
		{
			if ((el.value.charCodeAt(i) > 64 && el.value.charCodeAt(i) < 92) || (el.value.charCodeAt(i) > 96 && el.value.charCodeAt(i) < 123))
			{
				alert(Joomla.JText._('COM_JTICKETING_ENTER_NUMERICS'));
				el.value = el.value.substring(0, i);
				break;
			}
		}

		if (jQuery(el).hasClass('accept-empty'))
		{
			return;
		}

		if (el.value < 0 || el.value == -0)
		{
			alert(Joomla.JText._('COM_JTICKETING_ENTER_VALID_TICKET_AMOUNT'));
			el.value = 0;
		}
	},

	getRoundedValue: function(value) {
            var errorMsg = '';
			var url = '';
			value = value.isInteger ? value : Math.round(value * 100) / 100;
			if (Joomla.getOptions('system.paths').base.includes('administrator')) {
				url = Joomla.getOptions('system.paths').base + "/index.php?option=com_jticketing&format=json&task=event.getRoundedValue&price=" + value;
			} else {
				url = Joomla.getOptions('system.paths').base + "/index.php?option=com_jticketing&format=json&task=eventform.getRoundedValue&price=" + value;
			}

            jQuery.ajax({
                type: "POST",
                dataType: "json",
                data: value,
                async: false,
                url: url,
                success: function(data) {

                    if (data.data != value) {
                        roundedPrice = data.data;
                        errorMsg = Joomla.JText._('COM_JTICKETING_VALIDATE_ROUNDED_PRICE').concat(roundedPrice);
                    }

                },
            });

            return errorMsg;
        },

	seatsValidation : function(intergration, el = null)
	{
		var title = '';
		if (el) {
			var parent = el.closest('.subform-repeatable-group');
			if (intergration == 'js') {
				title = jQuery(parent).find('.form-control.titlevalid').val();
			} else {
				title = jQuery(parent).find('.o-form-control.titlevalid').val();
			}
		} else {
			title = jQuery('.titlevalid').val();
		}

		if (title == '')
		{
			alert(Joomla.JText._('COM_JTICKETING_TICKET_TITLE_EMPTY'));
		}

		if (intergration == "es")
		{
			var ticketCount = jQuery('input[name="guestlimit"]').val();
		}
		else
		{
			var ticketCount = jQuery('input[name="ticket"]').val();
		}

		var seatAvailbility = jQuery(".unlimitedseats option:selected").val();

		var res = 0;

		if (seatAvailbility == 0)
		{
			jQuery(".avail").each(function()
			{
				if (jQuery(this).val())
				{
					if(jQuery(this).val() < 0 || !/^\d+$/.test(jQuery(this).val()))
					{
						alert(Joomla.JText._('COM_JTICKETING_TICKET_SEAT_COUNT_ERROR'));

						jQuery(this).val('0');
					}

					res = res + parseInt(jQuery(this).val(), 10)
				}
			});
		}

		if (res > ticketCount && ticketCount > 0)
		{
			alert(seatCountMsg);

			jQuery(".avail").val('0');
		}
	},

	fieldDisplay : function()
	{

		var value = jQuery('.ticketFields').val();

		if (value == 1)
		{
			jQuery("#fieldTicket").css({ 'display': "block" });
		}
		else
		{
			jQuery("#fieldTicket").css({ 'display': "none" });
		}
	},

	ticketDateValidation : function(intergration, el = null) {

		var ticketEndDate = document.activeElement.value;

		var title = '';
		if (el) {
			var parent = el.closest('.subform-repeatable-group');
			if (intergration == 'js') {
				title = jQuery(parent).find('.form-control.titlevalid').val();
			} else {
				title = jQuery(parent).find('.o-form-control.titlevalid').val();
			}
		} else {
			title = jQuery('.titlevalid').val();
		}

		if (title == '')
		{
			alert(Joomla.JText._('COM_JTICKETING_TICKET_TITLE_EMPTY'));
		}

		if (intergration == 'es')
		{
			var eventDate = jQuery('input[name="endDatetime"]').val();
			ticketEndDate = new Date(jQuery(el).val());

			if (eventDate)
			{
				var eventEndDate = new Date(eventDate);

				if (ticketEndDate > eventEndDate  && ticketEndDate != 'Invalid Date')
				{
					alert(Joomla.JText._('COM_JTICKETING_ENDDATE_VALIDATION'));
					jQuery(el).val('');

					return;
				}
			}
				

			let parent = el.closest('.subform-repeatable-group');
			let ticketStartDate =  jQuery(parent).find('input.ticket-start-date').val();

			if (ticketStartDate)
			{
				ticketStartDate = new Date(ticketStartDate);

				if (ticketStartDate > ticketEndDate  && ticketStartDate != 'Invalid Date')
				{
					alert(Joomla.JText._('COM_JTICKETING_ENDDATE_GREATER_STARTDATE_VALIDATION'));
					jQuery(el).val('');

					return;
				}
			}
		}
		else if (intergration == 'js')
		{
			var eventDate = jQuery('#enddate').val();
			eventDate = eventDate + ' ' + jQuery('#endtime-hour').val() + ':' + jQuery('#endtime-min').val() + ':00';
			var eventEndDate = new Date(eventDate);

			ticketEndDate = new Date(jQuery(el).val());

			if (ticketEndDate > eventEndDate  && ticketEndDate != 'Invalid Date')
			{
				alert(Joomla.JText._('COM_JTICKETING_ENDDATE_VALIDATION'));
				jQuery(el).val('');
			}

			let parent = el.closest('.subform-repeatable-group');
			let ticketStartDate =  jQuery(parent).find('input.ticket-start-date').val();
			if (ticketStartDate)
			{
				ticketStartDate = new Date(ticketStartDate);

				if (ticketStartDate > ticketEndDate  && ticketStartDate != 'Invalid Date')
				{
					alert(Joomla.JText._('COM_JTICKETING_ENDDATE_GREATER_STARTDATE_VALIDATION'));
					jQuery(el).val('');
				}
			}
		}
		else if (intergration == 'jevents')
		{
			var noendchecked = document.adminForm.noendtime.checked;

			var end_time = document.getElementById("end_time");

			var end_date = document.getElementById("publish_down");

			if (noendchecked){
				end_time.value=start_time.value;
			}

			var eventEndDate = new Date();
			eventEndDate = eventEndDate.dateFromYMD(end_date.value);
			endtimeparts = (end_time.value=="00:00") ? [23,59] : end_time.value.split(":");
			eventEndDate.setHours(endtimeparts[0]);
			eventEndDate.setMinutes(endtimeparts[1]);

			ticketEndDate = new Date(jQuery(el).val());

			if (ticketEndDate > eventEndDate  && ticketEndDate != 'Invalid Date')
			{
				alert(eventDateMsg);
				jQuery(el).val('');
			}

			let parent = el.closest('.subform-repeatable-group');
			let ticketStartDate =  jQuery(parent).find('input.ticket-start-date').val();
			if (ticketStartDate)
			{
				ticketStartDate = new Date(ticketStartDate);

				if (ticketStartDate > ticketEndDate  && ticketStartDate != 'Invalid Date')
				{
					alert(Joomla.JText._('COM_JTICKETING_ENDDATE_GREATER_STARTDATE_VALIDATION'));
					jQuery(el).val('');
				}
			}
		}
	},

	titleValidation : function(es) {
		if (es.value == '')
		{
			alert(Joomla.JText._('COM_JTICKETING_TICKET_TITLE_EMPTY'));
		}
	},

	ticketStartDateValidation : function(intergration, el = null) {

		var ticketStartDate = document.activeElement.value;

		if (ticketStartDate)
		{
			var title = '';
			if (el) {
				var parent = el.closest('.subform-repeatable-group');
				if (intergration == 'js') {
					title = jQuery(parent).find('.form-control.titlevalid').val();
				} else {
					title = jQuery(parent).find('.o-form-control.titlevalid').val();
				}
			} else {
				title = jQuery('.titlevalid').val();
			}

			if (title == '')
			{
				alert(Joomla.JText._('COM_JTICKETING_TICKET_TITLE_EMPTY'));

				return;
			}

			if (intergration == 'es')
			{
				var eventDate = jQuery('input[name="endDatetime"]').val();
				ticketStartDate = new Date(jQuery(el).val());
				eventDate = new Date(eventDate);
				if (ticketStartDate != 'Invalid Date' && eventDate != 'Invalid Date' && ticketStartDate > eventDate)
				{
					alert(eventDateMsg);
					jQuery(el).val('');

					return;
				}

				var start_time = jQuery('input[name="startDatetime"]').val();
				var eventStartDate = new Date(start_time);

				ticketStartDate = new Date(jQuery(el).val());

				if (ticketStartDate > eventStartDate  && ticketStartDate != 'Invalid Date')
				{
					alert(Joomla.JText._('COM_JTICKETING_STARTDATE_VALIDATION'));
					jQuery(el).val('');

					return;
				}

				let parent = el.closest('.subform-repeatable-group');
				let ticketEndDate =  jQuery(parent).find('input.ticket-end-date').val();
				if (ticketEndDate)
				{
					ticketEndDate = new Date(ticketEndDate);

					if (ticketEndDate < ticketStartDate  && ticketEndDate != 'Invalid Date')
					{
						alert(Joomla.JText._('COM_JTICKETING_STARTDATE_LESS_ENDDATE_VALIDATION'));
						jQuery(el).val('');

						return;
					}
				}
			}
			else if (intergration == 'jevents')
			{
				var start_time = document.getElementById("start_time");

				let starttimeparts = start_time.value.split(":");
				let start_date = document.getElementById("publish_up");
				var eventStartDate = new Date();
				eventStartDate = eventStartDate.dateFromYMD(start_date.value);
				eventStartDate.setHours(starttimeparts[0]);
				eventStartDate.setMinutes(starttimeparts[1]);

				ticketStartDate = new Date(jQuery(el).val());

				if (ticketStartDate > eventStartDate  && ticketStartDate != 'Invalid Date')
				{
					alert(eventStartDateMsg);
					jQuery(el).val('');

					return;
				}

				let parent = el.closest('.subform-repeatable-group');
				let ticketEndDate =  jQuery(parent).find('input.ticket-end-date').val();
				if (ticketEndDate)
				{
					ticketEndDate = new Date(ticketEndDate);

					if (ticketEndDate < ticketStartDate  && ticketEndDate != 'Invalid Date')
					{
						alert(Joomla.JText._('COM_JTICKETING_STARTDATE_LESS_ENDDATE_VALIDATION'));
						jQuery(el).val('');

						return;
					}
				}
			}
			else if (intergration == 'js')
			{
				var eventDate = jQuery('#enddate').val();
				eventDate = eventDate + ' ' + jQuery('#endtime-hour').val() + ':' + jQuery('#endtime-min').val() + ':00';
				if (ticketStartDate > eventDate)
				{
					alert(eventDateMsg);

					return;
				}

				var start_time = document.getElementById("start_time");
				var start_time = jQuery('#startdate').val();
				start_time = start_time + ' ' + jQuery('#starttime-hour').val() + ':' + jQuery('#starttime-min').val() + ':00';

				var eventStartDate = new Date(start_time);

				ticketStartDate = new Date(jQuery(el).val());

				if (ticketStartDate > eventStartDate  && ticketStartDate != 'Invalid Date')
				{
					alert(Joomla.JText._('COM_JTICKETING_STARTDATE_VALIDATION'));
					jQuery(el).val('');

					return;
				}

				let parent = el.closest('.subform-repeatable-group');
				let ticketEndDate =  jQuery(parent).find('input.ticket-end-date').val();
				if (ticketEndDate)
				{
					ticketEndDate = new Date(ticketEndDate);

					if (ticketEndDate < ticketStartDate  && ticketEndDate != 'Invalid Date')
					{
						alert(Joomla.JText._('COM_JTICKETING_STARTDATE_LESS_ENDDATE_VALIDATION'));
						jQuery(el).val('');

						return;
					}
				}
			}
		}
	},
};
