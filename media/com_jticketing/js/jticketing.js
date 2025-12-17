/*
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 *
 * @copyright  Copyright (c) 2009-2020 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
var jtSite = {
	events: {
		init: function() {
			jQuery(document).ready(function() {
				if (localStorage.getItem("flag") == 1 || localStorage.getItem("flag") != 0) {
					jQuery(".events__filter").toggleClass("hide active");
				}
			});
		},
		// For mobile view
		toggleDiv: function(spanId) {

			if (jQuery(window).width() < 767) {
				jQuery("#" + spanId).toggle();
			}

			if (localStorage.getItem("flag") == 1) {
				localStorage.setItem("flag", 0);
			} else {
				localStorage.setItem("flag", 1);
			}

			jQuery(".events__filter").toggleClass("hide active");

			if (jQuery('.events__filter').is(":visible")) {
				jQuery("#mobiledisplayFilter").html("<i class='fa mr-5 fa-minus' aria-hidden='true'></i>" + Joomla.JText._('COM_JTICKETING_FILTERS_MORE_LBL'));
			} else {
				jQuery("#mobiledisplayFilter").html("<i class='fa mr-5 fa-plus' aria-hidden='true'></i>" + Joomla.JText._('COM_JTICKETING_FILTERS_MORE_LBL'));
			}
			if (spanId == 'searchFilterInputBox') {
				jQuery(".events__search").toggleClass("active hide");

				if (jQuery('.sort-result').hasClass('show')) {
					jQuery('.sort-result').removeClass('show').addClass('hide');
				};
			}
			jQuery(".venueSearchli").toggleClass("active");
		},

		displayMobileFilter: function() {

			jQuery("#mobileFilter").toggleClass("hidden-xs");
		},

		calendarSubmit: function(day_filter, ele) {
			if (jQuery('#jtwrap.tjBs5').length) {
				var id = '#filter_day_chosen';
			} else {
				var id = '#filter_day_chzn';
			}
			if (jQuery(window).width() < 767) {
				id = '#filter_day';
			}

			var daysArray = ["today", "tomorrow", "weekend", "thisweek", "nextweek", "thismonth", "nextmonth"];

			if (day_filter != '' && jQuery.inArray(day_filter, daysArray) == -1) {
				jQuery(id).daterangepicker({
					autoUpdateInput: false,
					locale: {
						cancelLabel: 'Clear'
					}
				});

				jQuery(id).on('apply.daterangepicker', function(ev, picker) {
					jQuery(id).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
					jQuery(id).css('display', 'block');
					var optionValue = jQuery('.drp-selected').text();

					jQuery('#filter_day option[value=' + day_filter + ']').val(optionValue);
					jQuery('#filter_day').trigger("liszt:updated");
					jQuery('#filter_day').trigger("chosen:updated");

					ele.form.submit();

				});

				jQuery(id).on('cancel.daterangepicker', function() {
					jQuery(this).val('');
				});
			} else {
				ele.form.submit();
			}
		},

		resetCalendar: function(ele) {
			jQuery('#filter_day').val('');
			ele.form.submit();
		},

		localTimeEvents: function(startDate, endDate, event_id) {

			var startDate = new Date(startDate.replace(/-/g, '/'));
			startDate = jtSite.event.formatDate(startDate);
			var endDate = new Date(endDate.replace(/-/g, '/'));
			endDate = jtSite.event.formatDate(endDate);

			if (guest == 1)
			{
				startDate = startDate +' UTC';
				endDate   = endDate +' UTC';
			}

			// Event start date
			startDate = new Date(startDate.replace(/-/g, '/'));
			startDate = jtSite.event.formatTime(startDate);

			// Event end date
			endDate = new Date(endDate.replace(/-/g, '/'));
			endDate = jtSite.event.formatTime(endDate);


			var offset = new Date().toString().match(/\(([A-Za-z\s].*)\)/)[1];

			jQuery('.time' + event_id).html("(" + Joomla.JText._('COM_JTICKETING_YOUR_TIME') + startDate + " - " + endDate + " " + offset + ")");
		},
	},

	venue: {
		initVenueJs: function() {
			jQuery(document).ready(function() {
				jtSite.venue.showHideVenueSeat();
			});
		},
		onChangefun: function() {
			jQuery("#venue_gallary_filter").change(function() {
				var filterVal = document.getElementById("venue_gallary_filter").value;

				if (filterVal == "1") {
					jQuery("#venue_videos").show();
					jQuery("#venue_images").hide();
					const videoTitle = document.createElement("h5");
					const textNode = document.createTextNode(Joomla.JText._('COM_JTICKETING_GALLERY_VIDEO_TEXT'));
					videoTitle.appendChild(textNode);
					jQuery(".videosText").html(videoTitle);
					jQuery(".imagesText").hide();
				} else if (filterVal == "2") {
					jQuery("#venue_videos").hide();
					jQuery("#venue_images").show();
					const videoTitle = document.createElement("h5");
					const textNode = document.createTextNode(Joomla.JText._('COM_JTICKETING_GALLERY_IMAGE_TEXT'));
					videoTitle.appendChild(textNode);
					jQuery(".videosText").html(videoTitle);
					jQuery(".imagesText").hide();
				} else if (filterVal == "0") {
					jQuery("#venue_videos").show();
					jQuery("#venue_images").show();
					const videoTitle = document.createElement("h5");
					const textNode = document.createTextNode(Joomla.JText._('COM_JTICKETING_VENUE_VIDEOS'));
					videoTitle.appendChild(textNode);
					jQuery(".videosText").html(videoTitle);
					jQuery(".imagesText").show();
				}
			}).change();
		},
		showHideVenueSeat: function() {
			var seatCapacity = techjoomla.jQuery('#jform_seats_capacity').val();
			if (seatCapacity == '0') {
				techjoomla.jQuery('.capacityCountRow').removeClass('af-d-none');
			} else {
				techjoomla.jQuery('.capacityCountRow').addClass('af-d-none');
			}
		}
	},

	venueForm: {
		/* Google Map autosuggest  for location */
		initializeGMapSuggest: function() {
			input = document.getElementById('jform_address');
			var autocomplete = new google.maps.places.Autocomplete(input);
		},

		initVenueFormJs: function() {
			if (googleMapApiKey) {
				google.maps.event.addDomListener(window, 'load', jtSite.venueForm.initializeGMapSuggest);
			}

			jQuery(document).ready(function() {
				jQuery('input[name="jform[online]').click(function() {
					jtSite.venueForm.showOnlineOffline(jQuery('input[name="jform[online]"]:checked').val())
				});

				jtSite.venueForm.showOnlineOffline(jQuery('input[name="jform[online]"]:checked').val());

				jQuery('#jform_online_provider').change(function() {
					jtSite.venueForm.getPluginParams();
				});

				if (editId && jQuery('input[name="jform[online]"]:checked').val() == 1 || jQuery('input[name="jform[online]"]:checked').val() == 1) {
					jtSite.venueForm.getPluginParams();
				}

				try {
					jQuery.each(mediaGallery, function(key, media) {
						tjMediaFile.previewFile(media, 1);
					});
				} catch (error) {
					console.log(error);
				}

				if (getValue) {
					jQuery('#jform_online_provider').trigger('change');
				}

				jtSite.venueForm.venueCapacityCheck();
				jQuery("#jform_seats_capacity").change(function() {
					jtSite.venueForm.venueCapacityCheck();
				});
			});
		},


		venueCapacityCheck: function() {
			if (jQuery('#jform_seats_capacity').val() == 0)
			{
				jQuery('#jform_capacity_count').addClass('required');
			} else {
				jQuery('#jform_capacity_count').removeClass('required');
			}
		},

		showOnlineOffline: function(ifonline) {
			if (ifonline == 1) {
				jQuery("#jform_online_provider").closest(".online_provider_wrap").show();
				jQuery("#provider_html").show();
				jQuery("#jform_offline_provider").hide();
			} else {
				jQuery("#jform_online_provider").closest(".online_provider_wrap").hide();
				jQuery("#provider_html").hide();
				jQuery("#jform_offline_provider").show();
			}
		},

		venueFormSubmitButton: function(task) {
			if (task == 'venueform.save') {
				var venue_name = jQuery('input[name="jform[jform_name]"]:checked').val();
				var api_username = jQuery('input[name="jform[api_username]"]:checked').val();
				var api_password = jQuery('input[name="jform[api_password]"]:checked').val();
				var host_url = jQuery('input[name="jform[host_url]"]:checked').val();
				var source_sco_id = jQuery('input[name="jform[source_sco_id]"]:checked').val();
				var onlines = jQuery('input[name="jform[online]"]:checked').val();
				var onlineProvider = jQuery('#jform_online_provider').val();
				if (editId && onlines == "0") {
					jQuery('#api_username').val('');
					jQuery('#api_password').val('');
					jQuery('#host_url').val('');
					jQuery('#source_sco_id').val('');
				}

				if (!document.formvalidator.isValid(document.getElementById('form-venue'))) {
					return false;
				}

				if (jQuery('input[name="jform[online]"]:checked').val() == 1) {
					if (!onlineProvider || onlineProvider == '0') {
						alert(Joomla.JText._('COM_JTICKETING_VENUE_FORM_ONLINE_PROVIDER'));
						jQuery('#jform_online_provider').focus();
						return false;
					}

					let providerHtmlForm = document.createElement("form");

					fields = jQuery("#provider_html").find('input, textarea, select, fieldset');
					for (i = 0, l = fields.length; i < l; i++) {
						let element = fields[i].cloneNode(true);
						providerHtmlForm.appendChild(element);
					}

					if (!document.formvalidator.isValid(providerHtmlForm)) {
						return false;
					}

					jsonObj = [];
					jQuery('#provider_html input').each(function() {
						var id = jQuery(this).attr("id");
						var output = jQuery(this).val();
						item = {}
						item["id"] = id;
						item["output"] = output;

						var source = jsonObj.push(item);
						jsonString = JSON.stringify(item);
						jQuery("#venue_params").val(jsonString);
					});

				} else {
					if (!jQuery("#jform_address").val()) {
						var error_html = '';
						error_html += "<br />" + Joomla.JText._('COM_JTICKETING_INVALID_FIELD') + Joomla.JText._('COM_JTICKETING_FORM_LBL_VENUE_ADDRESS');
						Joomla.renderMessages({"warning":[error_html]});

						jQuery('#jform_address').focus();
						return false;
					}
				}

				if (!jQuery("#form-venue #jform_name").val()) {
					var error_html = '';
					error_html += "<br />" + Joomla.JText._('COM_JTICKETING_INVALID_FIELD') + Joomla.JText._('COM_JTICKETING_FORM_LBL_VENUE_TITLE');
					Joomla.renderMessages({"warning":[error_html]});

					return false;
				}

				if (jQuery('#jform_seats_capacity').val() == 0 && !jQuery('#jform_capacity_count').val()) {
					return false;
				}
				Joomla.submitform(task, document.getElementById('form-venue'));
			}

			if (task == 'venueform.cancel') {
				Joomla.submitform(task, document.getElementById('form-venue'));
			}
		},

		getPluginParams: function() {
			var element = jQuery('#jform_online_provider').val();
			var parentDiv = jQuery('#form-venue');

			parentDiv.addClass('isloading');

			/** global: JTicketing */
			JTicketing.Ajax({
				url: Joomla.getOptions('system.paths').base + '/index.php?option=com_jticketing',
				datatype: "HTML",
				data: {
					async: 'false',
					format: '',
					task: "venueform.getelementparams",
					element: element,
					venue_id: jQuery("#venue_id").val()
				}
			}).done(function(response) {
				jQuery('#provider_html').html(response.data);
				var online = jQuery('input[name="jform[online]"]:checked').val();
				jQuery('#provider_html').css('display', 'none');
				if (online == 1) {
					jQuery('#provider_html').css('display', 'block');
					jQuery('#provider_html').trigger('subform-row-add', jQuery('#provider_html'));
				}
			}).fail(function(content) {
				jQuery('#provider_html').hide();
				Joomla.renderMessages({
					'error': [Joomla.JText._('COM_JTICKETING_ORDER_RELATED_AJAX_FAIL_ERROR_MESSAGE')]
				});
				return true;
			}).always(function() {
				jQuery("html, body").animate({
					scrollTop: jQuery("#provider_html").offset().top
				}, 1500);

				// Make view password field button working
				var passwordSelection = document.getElementsByClassName("input-password-toggle");

				for(var i = 0; i < passwordSelection.length; i++) {
					(function(index) {
						passwordSelection[index].addEventListener("click", function() {
							if (jQuery(this).prev("input").attr("type") == "password")
							{
								jQuery(this).prev("input").attr("type", "text");
							}
							else
							{
								jQuery(this).prev("input").attr("type", "password");
							}
						})
					})(i);
				}

				parentDiv.removeClass('isloading');
			});

			// Google Map autosuggest  for location
			function initializeGoogleMap() {
				input = document.getElementById('jform_address');
				var autocomplete = new google.maps.places.Autocomplete(input);
			}

			if (googleMapApiKey) {
				google.maps.event.addDomListener(window, 'load', initializeGoogleMap);
			}
		},

		// Function : For finding longitude latitude of selected address
		getLongitudeLatitude: function() {
			if (googleMapApiKey)
			{
				var geocoder = new google.maps.Geocoder();
				var address = jQuery('#jform_address').val();
				geocoder.geocode({
					'address': address
				}, function(results, status) {
					if (status == google.maps.GeocoderStatus.OK) {
						var latitude = results[0].geometry.location.lat();
						var longitude = results[0].geometry.location.lng();
						jQuery('#jform_latitude').val(latitude);
						jQuery('#jform_longitude').val(longitude);
					}
				});
			}
		},

		// Function : For Get Current Location
		getCurrentLocation: function() {
			if (navigator.geolocation) {
				navigator.geolocation.getCurrentPosition(showLocation);
			} else {
				var address = Joomla.JText._('COM_JTICKETING_ADDRESS_NOT_FOUND');
				var lonlatval = Joomla.JText._('COM_JTICKETING_LONG_LAT_VAL');
				jQuery('#jform_address').val(address);
				jQuery("#jform_longitude").val(lonlatval);
				jQuery("#jform_latitude").val(lonlatval);
			}

			// Function : For Showing user current location
			function showLocation(position) {
				var latitude = position.coords.latitude;
				var longitude = position.coords.longitude;
				jQuery.ajax({
					type: 'POST',
					url: Joomla.getOptions('system.paths').base + "/index.php?option=com_jticketing&task=venueform.getLocation",
					data: 'latitude=' + latitude + '&longitude=' + longitude,
					dataType: 'json',
					success: function(data) {
						console.log(data);
						var address = data["location"];
						var longitude = data["longitude"];
						var latitude = data["latitude"];

						if (data) {
							jQuery("#jform_address").val(address);
							jQuery("#jform_longitude").val(longitude);
							jQuery("#jform_latitude").val(latitude);
						}
					}
				});
			}
		}
	},

	event: {
		onlineMeetingUrl: function(thisVal, eventId) {
			if (eventId == undefined) {
				var eventId = jQuery('#event_id').val();
			}

			var enterMeeting = jQuery('#jt-enterMeeting');
			enterMeeting.button('loading');

			/** global: JTicketing */
			JTicketing.Ajax({
				url: Joomla.getOptions('system.paths').base + '/index.php?option=com_jticketing',
				data: {
					format: '',
					task: "event.onlineMeetingUrl",
					'eventId': eventId,
					'view': 'event'
				}
			}).done(function(result) {
				if (result.success == true) {
					if (result.data == 1) {
						top.location.href = Joomla.getOptions('system.paths').base + "/index.php?option=com_users&view=login";
						return;
					}

					var win = window.open(result.data, '_blank');

					if (win) {
						//Browser has allowed it to be opened
						win.focus();
					} else {
						//Browser has blocked it
						Joomla.renderMessages({
							'notice': [Joomla.JText._('COM_JTICKETING_EVENTS_ENTER_MEETING_SITE_POPUPS')]
						});
						jQuery("html, body").animate({
							scrollTop: 0
						}, "slow");
					}
				} else {
					Joomla.renderMessages({
						'error': [result.message]
					});
					jQuery("html, body").animate({
						scrollTop: 0
					}, "slow");
				}
			}).fail(function(content) {
				Joomla.renderMessages({
					'error': [Joomla.JText._('COM_JTICKETING_ORDER_RELATED_AJAX_FAIL_ERROR_MESSAGE')]
				});
				jQuery("html, body").animate({
					scrollTop: 0
				}, "slow");
			}).always(function() {
				enterMeeting.button('reset');
			});
		},

		meetingRecordingUrl: function(thisVal, eventId) {
			if (eventId == undefined) {
				var eventId = jQuery('#event_id').val();
			}

			JTicketing.Ajax({
				url: Joomla.getOptions('system.paths').base + '/index.php?option=com_jticketing',
				data: {
					format: '',
					task: "event.meetingRecordingUrl",
					'eventId': eventId,
					'view': 'event'
				}
			}).done(function(result) {
				if (result.success == true) {
					if (result.data == 1) {
						top.location.href = Joomla.getOptions('system.paths').base + "/index.php?option=com_users&view=login";

						return;
					}

					var win = window.open(result.data, '_blank');

					if (win) {
						//Browser has allowed it to be opened
						win.focus();
					} else {
						//Browser has blocked it
						Joomla.renderMessages({
							'notice': [Joomla.JText._('COM_JTICKETING_EVENTS_ENTER_MEETING_SITE_POPUPS')]
						});
						jQuery("html, body").animate({
							scrollTop: 0
						}, "slow");
					}
				} else {
					Joomla.renderMessages({
						'error': [result.message]
					});
					jQuery("html, body").animate({
						scrollTop: 0
					}, "slow");
				}
			}).fail(function(content) {
				Joomla.renderMessages({
					'error': [Joomla.JText._('COM_JTICKETING_ORDER_RELATED_AJAX_FAIL_ERROR_MESSAGE')]
				});
				jQuery("html, body").animate({
					scrollTop: 0
				}, "slow");
			}).always(function() {});
		},

		initEventDetailJs: function() {
			jQuery(document).ready(function() {
				jQuery('[data-toggle="tooltip"]').tooltip();
				jtCounter.jtCountDown('jt-countdown', startDate, endDate, currentDate);
				jtSite.event.onChangeGetOrderData();
				
				if (guest == 1)
				{
					startDate = startDateUTC;
					endDate   = endDateUTC;
				}

				jtSite.event.localTime(startDate, endDate, guest);

				jQuery('.r-more').click(function() {
					var long_desc = jQuery(this).closest('.long_desc');
					var long_desc_extend = jQuery('.long_desc_extend');
					long_desc_extend.appendTo('.description');
					jQuery(long_desc).hide();
					jQuery(".r-more", long_desc).hide();
					jQuery(long_desc_extend).show();
					jQuery(".r-less", long_desc_extend).show();
				});

				jQuery('.r-less').click(function() {
					var long_desc_extend = jQuery('.long_desc_extend');
					var long_desc = jQuery('.long_desc');
					jQuery(long_desc_extend).hide();
					jQuery(".r-less", long_desc_extend).hide();
					jQuery('.long_desc').show();
					jQuery(".r-more", long_desc).show();
					jQuery("html, body").animate({
						scrollTop: 0
					}, "slow");
				});
			});
		},

		viewMoreAttendee: function() {

			var eventId = document.getElementById('event_id').value;

			if (gbl_jticket_pro_pic == 0) {
				gbl_jticket_pro_pic = document.getElementById('attendee_pro_pic_index').value;
			}

			techjoomla.jQuery.ajax({
				url: jtRootURL + 'index.php?option=com_jticketing&task=event.viewMoreAttendee&tmpl=component',
				type: 'POST',
				dataType: 'json',
				data: {
					eventId: eventId,
					jticketing_index: gbl_jticket_pro_pic
				},
				success: function(data) {
					gbl_jticket_pro_pic = data['jticketing_index'];
					techjoomla.jQuery("#jticketing_attendee_pic ").append(data['records']);

					if (!data['records'] || attedee_count <= gbl_jticket_pro_pic) {
						techjoomla.jQuery("#btn_showMorePic").hide();
					}
				},
				error: function(data) {
					console.log('error');
				}
			});
		},

		loadActivity: function() {
			jQuery(window).on('load', function() {
				if (jQuery('#tj-activitystream .feed-item-cover').length == '0') {
					jQuery('.todays-activity .feed-item').css('border-left', '0px');
				}

				jQuery('#postactivity').attr('disabled', true);
				jQuery('#activity-post-text').on('input', function() {
					if (jQuery('#activity-post-text').val() == '') {
						jQuery('#postactivity').attr('disabled', true);
					} else {
						jQuery('#postactivity').attr('disabled', false);
					}
					var textMax = jQuery('#activity-post-text').attr('maxlength');
					var textLength = jQuery('#activity-post-text').val().length;
					var text_remaining = textMax - textLength;
					jQuery('#activity-post-text-length').html(text_remaining + ' ' + Joomla.JText._('COM_JTICKETING_POST_TEXT_ACTIVITY_REMAINING_TEXT_LIMIT'));
				});
			});
		},
		onChangefun: function() {
			jQuery("#gallary_filter").change(function() {
				var filterVal = document.getElementById("gallary_filter").value;

				if (filterVal == "1") {
					jQuery("#videos").show();
					jQuery("#images").hide();
					const videoTitle = document.createElement("h5");
					const textNode = document.createTextNode(Joomla.JText._('COM_JTICKETING_GALLERY_VIDEO_TEXT'));
					videoTitle.appendChild(textNode);
					jQuery(".videosText").html(videoTitle);
					jQuery(".imagesText").hide();
				} else if (filterVal == "2") {
					jQuery("#videos").hide();
					jQuery("#images").show();
					const videoTitle = document.createElement("h5");
					const textNode = document.createTextNode(Joomla.JText._('COM_JTICKETING_GALLERY_IMAGE_TEXT'));
					videoTitle.appendChild(textNode);
					jQuery(".videosText").html(videoTitle);
					jQuery(".imagesText").hide();
				} else if (filterVal == "0") {
					jQuery("#videos").show();
					jQuery("#images").show();
					const videoTitle = document.createElement("h5");
					const textNode = document.createTextNode(Joomla.JText._('COM_JTICKETING_EVENT_VIDEOS'));
					videoTitle.appendChild(textNode);
					jQuery(".videosText").html(videoTitle);
					jQuery(".imagesText").show();
				}
			}).change();
		},
		eventImgPopup: function(className) {
			jQuery("." + className).magnificPopup({
				delegate: 'a',
				type: 'image',
				tLoading: 'Loading image #%curr%...',
				mainClass: 'mfp-img-mobile',
				gallery: {
					enabled: true,
					navigateByImgClick: true,
					preload: [0, 1]
				},
				image: {
					tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
					titleSrc: function(item) {
						return item.el.attr('title') + '<small></small>';
					}
				}
			});
		},

		onChangeGetOrderData: function() {

			jQuery("#event-graph-period").change(function() {
				var graphFilterVal = jQuery("#event-graph-period").val();
				var eventId = jQuery('#event_id').val();
				var ajaxcall = techjoomla.jQuery.ajax({
					type: 'GET',
					url: Joomla.getOptions('system.paths').base + "/index.php?option=com_jticketing&view=event&task=event.getEventOrderGrapgData&eventId=" + eventId + "&filtervalue=" + graphFilterVal,
					dataType: 'json',
					error: function(data) {
						console.log('error')
					}
				});

				ajaxcall.done(function(data) {
					var graphid = document.getElementById("myevent_graph").getContext('2d');
					var total_eventOrderschart = new Chart(graphid, {
						type: 'line',
						data: {
							labels: data.orderDate,
							datasets: [{
									label: data.totalOrdersAmount ? data.totalOrdersAmount : 0,
									data: data.orderAmount ? data.orderAmount : 0,
									backgroundColor: "rgba(203, 235, 230, 0.5)",
									borderColor: "rgba(55, 179, 142, 1)",
									lineTension: '0',
									borderWidth: '2',
									pointRadius: 1,
									pointBackgroundColor: "rgba(55, 179, 142, 1)",
									pointBorderColor: "rgba(55, 179, 142, 1)",
									pointHoverBackgroundColor: "rgba(55, 179, 142, 1)",
									pointHoverBorderColor: "rgba(55, 179, 142, 1)"
								},
								{
									label: data.avgOrdersAmount ? data.avgOrdersAmount : 0,
									data: data.orderAvg ? data.orderAvg : 0,
									backgroundColor: "rgba(216, 225, 180, 1)",
									borderColor: "rgba(251, 214, 20, 0.90)",
									lineTension: '0',
									borderWidth: '2',
									pointRadius: 1,
									pointBackgroundColor: "rgba(251, 214, 20, 0.90)",
									pointBorderColor: "rgba(251, 214, 20, 0.90)",
									pointHoverBackgroundColor: "rgba(251, 214, 20, 0.90)",
									pointHoverBorderColor: "rgba(251, 214, 20, 0.90)"
								}
							]
						}
					});
				});
			}).change();
		},

		searchAttendee: function() {
			var input, filter, table, tr, td, i;
			var cnt = 0;
			input = document.getElementById("attendeeInput");
			filter = input.value.toUpperCase();
			table = document.getElementById("eventAttender");
			tr = table.getElementsByTagName("tr");


			for (i = 0; i < tr.length; i++) {
				td = tr[i].getElementsByTagName("td")[1];

				if (td) {
					if (td.innerHTML.toUpperCase().indexOf(filter) > -1) {
						tr[i].style.display = "";
						cnt += 1;
					} else {
						tr[i].style.display = "none";
					}
				}
			}

			if (cnt < 5) {
				jQuery('#btn_showMorePic').hide();
			} else {
				jQuery('#btn_showMorePic').show();
			}
		},

	   localTime: function(startDate, endDate, guest) {

			var startDate = new Date(startDate.replace(/-/g, '/'));
			startDate = jtSite.event.formatDate(startDate);
			var endDate = new Date(endDate.replace(/-/g, '/'));
			endDate = jtSite.event.formatDate(endDate);

			if (guest == 1)
			{
				startDate = startDate +' UTC';
				endDate   = endDate +' UTC';
			}

			// Event start date
			startDate = new Date(startDate.replace(/-/g, '/'));
			startDate = jtSite.event.formatTime(startDate);

			// Event end date
			endDate = new Date(endDate.replace(/-/g, '/'));
			endDate = jtSite.event.formatTime(endDate);


			var offset = new Date().toString().match(/\(([A-Za-z\s].*)\)/)[1];

			jQuery(".time").html("(" + Joomla.JText._('COM_JTICKETING_YOUR_TIME') + startDate + " - " + endDate + " " + offset + ")");
		},

		formatDate: function(date) {
			 var hours = date.getHours();
			 var minutes = date.getMinutes();
			 var ampm = hours >= 12 ? 'pm' : 'am';
			 hours = hours % 12;
			 hours = hours ? hours : 12; // the hour '0' should be '12'
			 minutes = minutes < 10 ? '0'+minutes : minutes;
			 var strTime = hours + ':' + minutes + ' ' + ampm;

			 return date.getMonth()+1 + "/" + date.getDate() + "/" + date.getFullYear() + " " + strTime;
		},

		formatTime: function(date) {
			var hours = date.getHours();
			var minutes = date.getMinutes();
			var ampm = hours >= 12 ? 'pm' : 'am';
			hours = hours % 12;
			hours = hours ? hours : 12; // the hour '0' should be '12'
			minutes = minutes < 10 ? '0'+minutes : minutes;
			var strTime = hours + ':' + minutes + ' ' + ampm;
			return strTime;
		}
	},

	order: {
		checkForAlpha: function(el) {
			var i = 0;
			for (i = 0; i < el.value.length; i++) {
				if ((el.value.charCodeAt(i) > 64 && el.value.charCodeAt(i) < 92) || (el.value.charCodeAt(i) > 96 && el.value.charCodeAt(i) < 123)) {
					alert(Joomla.JText._('COM_JTICKETING_ENTER_NUMERICS'));
					el.value = el.value.substring(0, i);
					break;
				}
			}
			if (el.value < 0) {
				alert(Joomla.JText._('COM_JTICKETING_ENTER_AMOUNT_GR_ZERO'));
			}
			if (el.value % 1 !== 0) {
				alert(Joomla.JText._('COM_JTICKETING_ENTER_AMOUNT_INT'));
				el.value = 0;
				return false;
			}
		},
		validateSpecialChar: function(ele) {
			var inputVal = jQuery(ele).val();
			var checkSpecialChars = "`~!@#$%^&*()_=+[]{}|\;:'\",<.>/?";

			for (i = 0; i < checkSpecialChars.length; i++) {
				if (inputVal.indexOf(checkSpecialChars[i]) > -1) {
					jQuery(ele).val('');
					alert(Joomla.JText._('COM_JTICKETING_CHECK_SPECIAL_CHARS'));
					return false;
				}
			}
		},
		jtShowFilter: function() {
			jQuery("#jthorizontallayout").toggle();
		},
		jticketingGenerateState: function(countryId, SelectedValue, totalPrice) {
			var country = jQuery('#' + countryId).val();
			if (country == undefined) {
				return (false);
			}

			/** global: JTicketing */
			JTicketing.Ajax({
				url: Joomla.getOptions('system.paths').base + '/index.php?option=com_jticketing&task=order.loadState&country=' + country,
				type: 'GET',
			}).done(function(result) {
				if (result.success === true) {
					jtSite.order.generateOption(result.data, countryId, SelectedValue);
				}
			}).fail(function() {
				Joomla.renderMessages({
					'error': [Joomla.JText._('COM_JTICKETING_ORDER_RELATED_AJAX_FAIL_ERROR_MESSAGE')]
				});
				jQuery("html, body").animate({
					scrollTop: 0
				}, "slow");
			})

			jQuery('select').trigger("chosen:updated");
			jQuery("select").trigger("liszt:updated"); /* IMP : to update to chz-done selects*/
		},
		generateOption: function(data, countryId, SelectedValue) {
			var country = jQuery('#' + countryId).val();
			var options, index, select, option;
			if (countryId == 'country') {
				select = jQuery('#state');
				defaultOption = Joomla.JText._('COM_JTICKETING_BILLIN_SELECT_STATE');
			}
			select.find('option').remove().end();
			selected = "selected=\"selected\"";
			var op = '<option ' + selected + ' value="">' + defaultOption + '</option>';
			if (countryId == 'country') {
				jQuery('#state').append(op);
			}
			if (data !== undefined && data !== null) {
				options = data;
				for (index = 0; index < data.length; ++index) {
					selected = "";
					if (name == SelectedValue)
						selected = "selected=\"selected\"";
					var opObj = data[index];
					selected = "";
					if (opObj.id == SelectedValue) {
						selected = "selected=\"selected\"";
					}
					var op = '<option ' + selected + ' value=\"' + opObj.id + '\">' + opObj.region + '</option>'; {
						jQuery('#state').append(op);
					}
				}
			}

			jQuery('select').trigger("chosen:updated");
			jQuery("select").trigger("liszt:updated"); /* IMP : to update to chz-done selects*/
		},
		selectAttendee: function(obj) {
			var selectedAttendee = obj.value;
			if (selectedAttendee == undefined || selectedAttendee <= 0) {
				/* Find Prent div and clear all text and hidden fields */
				var parentDiv = jQuery(obj).parent().parent().parent().parent().find('div');
				var allElements = jQuery(parentDiv).find('input[id*="attendee_field_"],select[id*="attendee_field_"]');
				allElements.each(function() {
					var elementType = jQuery(this).attr('type');
					switch (elementType) {
						case 'checkbox':
							break;
						case 'radio':
							break;
						default:
							jQuery(this).val('');
					}
				});
				return (false);
			}
			var hiddenAttendeeField = jQuery(obj).parent().parent().parent().find('input[id*="attendee_field_attendee_id"]');
			hiddenAttendeeField.val(selectedAttendee);
			jQuery.ajax({
				url: jtRootURL + '?option=com_jticketing&format=json&task=order.selectAttendee&attendee_id=' + selectedAttendee + '&tmpl=component&format=raw',
				type: 'GET',
				dataType: 'json',
				success: function(data) {
					/* Prefill All data of selected Attendee */
					jQuery.each(data, function(name, val) {
						var el = jQuery(obj).parent().parent().parent().find('input[id*="attendee_field_' + name + '"],select[id*="attendee_field_' + name + '"]');
						var type = el.attr('type');
						switch (type) {
							case 'checkbox':
								el.attr('checked', 'checked');
								break;
							case 'radio':
								el.filter('[value="' + val + '"]').attr('checked', 'checked');
								break;
							default:
								el.val(val);
						}
					});
				}
			});
		},
		verifyBookingID: function() {
			var bookId = document.getElementById("online_guest").value;
			var url = "index.php?option=com_jticketing&format=json&task=order.verifyBookingID";
			jQuery.ajax({
				url: url,
				type: 'POST',
				async: false,
				data: {
					'book_id': bookId,
				},
				dataType: 'json',
				success: function(data) {
					if (data.success) {
						window.location.href = data.host_url;
					} else {
						alert(Joomla.JText._('JT_TICKET_BOOKING_ID_VALIDATION'));
					}
				}
			});
		},
		getRegistrationType: function (value) {

			jQuery("input[name='registration_type']").val(value);

			if (value == '0')
			{
				jQuery(".business_detail").hide();
				jQuery('#registration_type1').removeClass('btn-success active');
				jQuery('#registration_type0').addClass('btn-success active');
				jQuery('#business_name').removeClass('required');
			}

			if (value == '1')
			{
				jQuery(".business_detail").show();
				 jQuery('#registration_type1').addClass('btn-success active');
				jQuery('#registration_type0').removeClass('btn-success active');
				jQuery('#business_name').addClass('required');
			}
		},
		gatewayHtml: function(element, orderId) {
			var prevButtonHtml = '<button id="btnWizardPrev1" onclick="jQuery(\'#MyWizard\').wizard(\'previous\');" type="button" class="btn  btn-default  btn-prev pull-left" > <i class="icon-arrow-left" ></i>' + Joomla.JText._('COM_JTICKETING_PREV') + '</button>';
			jQuery.ajax({
				beforeSend: function() {
					jQuery('#jticketing-payHtmlDiv').addClass('isloading');
				},
				complete: function() {
					jQuery('#jticketing-payHtmlDiv').removeClass('isloading');
				},
				url: Joomla.getOptions('system.paths').base + '/index.php?option=com_jticketing&task=payment.changegateway&gateways=' + element + '&order_id=' + orderId + '&tmpl=component',
				type: 'POST',
				data: '',
				dataType: 'json',
				success: function(result) {
					if (result.success === true) {
						jQuery('#jticketing-payHtmlDiv').html(result.data);
						jQuery('#jticketing-payHtmlDiv div.form-actions').prepend(prevButtonHtml);
						jQuery('#jticketing-payHtmlDiv div.form-actions input[type="submit"]').addClass('pull-right');
						jQuery('#jticketing-payHtmlDiv div.form-actions input[type="submit"]').addClass('paymentButton');
						/** global: ga_ec_analytics */
						if (ga_ec_analytics === 1) {
							jQuery('.paymentButton').click(function() {
								var stepId = 4;
								/** global: track_attendee_step */
								if (track_attendee_step === 1) {
									stepId = 5;
								}
								jtSite.order.addEcTrackingData(orderId, stepId);
							});
						}
					}
				}
			});
		},
		// Function for login
		jtLogin: function() {
			jQuery.ajax({
				url: Joomla.getOptions('system.paths').base + '/index.php?option=com_jticketing&format=json&task=order.loginValidate&tmpl=component',
				type: 'post',
				data: jQuery('#user-info-tab #login :input'),
				dataType: 'json',
				beforeSend: function() {
					jQuery('#button-login').attr('disabled', true);
					jQuery('#button-login').after('<span class="wait">&nbsp;Loading..</span>');
				},
				complete: function() {
					jQuery('#button-login').attr('disabled', false);
					jQuery('.wait').remove();
				},
				success: function(json) {
					jQuery('.warning, .j2error').remove();
					if (json['error']) {
						jQuery('#login').prepend('<div class="warning danger" >' + json['error']['warning'] + '<button data-dismiss="alert" class="close" type="button">×</button></div>');
						jQuery('.warning').fadeIn('slow');
					} else if (json['redirect'] && !json['redirect_invoice_view']) {
						jtSite.order.updateBillingDetails();
						jQuery('#btnWizardNext').show();
					} else if (json['redirect_invoice_view']) {
						document.location = json['redirect_invoice_view'];
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {}
			});
		},
		/* Get updated billing details. */
		updateBillingDetails: function() {
			jQuery('#btnWizardNext').removeAttr('disabled');
			jQuery.ajax({
				url: Joomla.getOptions('system.paths').base + '/index.php?option=com_jticketing&format=json&task=order.getUpdatedBillingInfo&tmpl=component',
				type: 'post',
				data: jQuery('#user-info-tab #login :input'),
				dataType: 'json',
				beforeSend: function() {},
				complete: function() {},
				success: function(json) {
					if (json['error']) {} else if (json['billing_html']) {
						/* Update billing tab step HTML. */
						jQuery('#billing-info').html(json['billing_html']);
						/* Update state selct list options. */
						jtSite.order.jticketingGenerateState('country', '', '');
						jQuery('#billing_info_data').show();
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {}
			});
		},
		paymentInit: function() {
			jQuery(document).ready(function() {
				/** global: count */
				if (count == 1) {
					/** global: gateWayName */
					/** global: orderId */
					jtSite.order.gatewayHtml(gateWayName, orderId);
				}

			});
		},
		addEcTrackingData: function(orderId, stepId) {
			/** global: tjanalytics */
			if (typeof tjanalytics === "undefined") {

				return;
			}

			/** global: JTicketing */
			JTicketing.Ajax({
				url: Joomla.getOptions('system.paths').base + '/index.php?option=com_jticketing&task=order.getEcTrackingData&orderId=' + orderId + '&stepId=' + stepId,
				type: 'GET',
			}).done(function(response) {

				/** global: tjanalytics */
				tjanalytics.ga.addProduct(response.data);

				if (stepId > 0) {
					tjanalytics.ga.setAction(response.data['0']);
				} else {
					tjanalytics.ga.setTransaction(response.data['0']);
				}


			}).fail(function() {
				return true;
			});
		},
		validateAttendeeName: function() {
			// validation string for mobile number
			// removing as we are using regex from JT config in v3.3.5
			//var regexForMob = /^(\+\d{1,3}[- ]?)?\d{9}$/;
			// validation string for email
			var regexForEmail = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;

			jQuery("#attendeeCheckout").click(function() {
				var i = 0;
				while (1) {
					//to validate mob no of attendee
					var mob = "#attendee_field_3_";
					mob = mob.concat(i);

					if (!jQuery(mob).length) {
						break;
					}

					if (jQuery(mob).length && jQuery(mob).val() != "") {
						if (!(regexForAttendeeMob.test(jQuery(mob).val()))) {
							var error_html = Joomla.JText._('COM_JTICKETING_INVALID_FIELD') + Joomla.JText._('COM_JTICKETING_INVALID_ATTENDEE_MOB') + " " + (i + 1);
							Joomla.renderMessages({
								'error': [error_html]
							});

							jQuery("html, body").animate({
								scrollTop: 0
							}, "slow");

							return false;
						}
					}

					// to validate email
					var email = "#attendee_field_4_";
					email = email.concat(i);
					if (jQuery(email).length && jQuery(email).val() != "") {
						if (!regexForEmail.test(jQuery(email).val())) {
							var error_html = Joomla.JText._('COM_JTICKETING_INVALID_FIELD') + Joomla.JText._('COM_JTICKETING_INVALID_ATTENDEE_EMAIL') + " " + (i + 1);
							Joomla.renderMessages({
								'error': [error_html]
							});

							jQuery("html, body").animate({
								scrollTop: 0
							}, "slow");

							return false;
					   }
					}
					i++;
				}
			});
		},

		hideEmailField: function()
		{
			jQuery('#ticketToBuyer').on('click', function() {
				jQuery('.emailValidation').addClass('af-d-none');
				jQuery('.emailValidation').parent().addClass('af-d-none');
				jQuery('.emailValidation').removeClass('required');
				jQuery('.emailValidation').removeAttr('required');
			});
			jQuery('#ticketToAttendee').on('click', function() {
				jQuery('.emailValidation').removeClass('af-d-none');
				jQuery('.emailValidation').parent().removeClass('af-d-none');
				jQuery('.emailValidation').addClass('required');
				jQuery('.emailValidation').attr('required');
			});
		}
	},

	orders: {
		/*Initialize orders js*/
		initOrdersJs: function() {
			jQuery(document).ready(function() {
				jQuery('.jt_selectbox').attr('data-chosen', 'com_jticketing');

				jQuery("input[name='gateways']").change(function() {
					var paymentGeteway = jQuery("input[name='gateways']:checked").val();

					jQuery('#html-container').empty().html('Loading...');
					jQuery.ajax({
						url: jtRootURL + 'index.php?option=com_jticketing&tmpl=component&task=orders.retryPayment&order=' + orderID + '&gateway_name=' + paymentGeteway,
						type: 'GET',
						dataType: 'json',
						success: function(response) {
							jQuery('#html-container').removeClass('ajax-loading').html(response);
						}
					});
				});
			});
			Joomla.submitbutton = function(task) {
				if (task == 'cancel') {
					Joomla.submitform(task, document.getElementById('adminForm'));
				}
			}
		},
		selectStatusOrder: function(appid, processor, ele) {
			document.getElementById('order_id').value = appid;
			document.getElementById('payment_status').value = ele.value;
			document.getElementById('processor').value = processor;

			var selInd = ele.selectedIndex;
			var status = ele.options[selInd].value;
			var orderStatusvalue = '';
			var task = 'orders.save';

			// Generate order status language constant values
			if (status === 'RF') {
				orderStatusvalue = 'REFUND';
			}

			if (status === 'E') {
				orderStatusvalue = 'FAILED';
			}

			if (status === 'D') {
				orderStatusvalue = 'DECLINE';
			}

			if (status === 'CRV') {
				orderStatusvalue = 'CANCEL_REVERSED';
			}

			if (status === 'RV') {
				orderStatusvalue = 'REVERSED';
			}

			// Enter only if the order status is set to refund or failed or denied or cancel reversed
			if (status === 'RF' || status === 'E' || status === 'D' || status === 'CRV' || status === 'RV') {
				// Generate order status language constant
				var orderStatusLanguageConstant = 'COM_JTICKETING_ORDER_STATUS_' + orderStatusvalue;

				// Order status message to be displayed on the pop up
				var orderStatusMessage = Joomla.JText._('COM_JTICKETING_ORDER_STATUS_MESSAGE1') + Joomla.JText._(orderStatusLanguageConstant) + Joomla.JText._('COM_JTICKETING_ORDER_STATUS_MESSAGE2');

				var confirmation = confirm(orderStatusMessage);

				if (confirmation === true) {
					Joomla.submitform(task, document.getElementById('adminForm'));
				} else {
					var oldStatus = jQuery(ele).data('oldvalue');
					jQuery(ele).val(oldStatus)
					jQuery(ele).trigger('liszt:updated');
					jQuery(ele).trigger("chosen:updated");

					return false;
				}
			} else {
				Joomla.submitform(task, document.getElementById('adminForm'));
			}
		},
		printDiv: function() {
			var printContents = document.getElementById('printDiv').innerHTML;
			var originalContents = document.body.innerHTML;
			document.body.innerHTML = printContents;
			window.print();
			document.body.innerHTML = originalContents;
		},
		showPaymentGetways: function() {
			if (document.getElementById('gatewaysContent').style.display == 'none') {
				document.getElementById('gatewaysContent').style.display = 'block';
			}
			return false;
		}
	},

	eventform: {
		initEventJs: function() {
			jQuery(window).on('load', function() {
				jQuery('#jform_startdate, #jform_enddate, .time-hours , .time-minutes').on('change, blur', function() {
					if (jQuery("#jform_venue")[0].length === 0) {
						jtSite.eventform.venueDisplay();
					}
				});

				jtSite.eventform.showTicketCapacity();
			});

			jQuery(document).ready(function() {
				var radioValue = jQuery("input[name='jform[online_events]']:checked").val();
				var selectedVenueDetails = '';

				if (jQuery('#attendeefields .subform-repeatable-wrapper').length)
				{
					jQuery('#attendeefields .subform-repeatable-wrapper .subform-repeatable .btn-toolbar.hidden:first').removeClass('hidden');
				}

				var id = jQuery("input[name='jform[id]']").val();

				var venueDisplayPromise = jtSite.eventform.venueDisplay();
				jtSite.eventform.existingEvents();
				jtSite.eventform.selectExistingEventOnload();
				venueDisplayPromise.then(function (){
					jtSite.eventform.showLocation();
				});

				jQuery("input[name='jform[online_events]']").change(function() {
					jtSite.eventform.venueDisplay();
					jtSite.eventform.existingEvents();
				});

				jQuery("#jform_venue").change(function() {
					jtSite.eventform.showLocation();
				});

				jQuery(".venueCheck").change(function() {
					jtSite.eventform.selectExistingEvent();
					jtSite.eventform.showTicketCapacity();
				});

				jQuery(".existingEvent").change(function() {
					jtSite.eventform.existingEventSelection();
				});

				jQuery('.group-add').on('click', function() {
					setTimeout(function() {
						jtSite.eventform.showTicketAvailability();
					}, 150);
				});

				jQuery.each(mediaGallery, function(key, media) {
					tjMediaFile.previewFile(media, 1);
				});

				jQuery('input[type=radio][name="jform[venuechoice]"]').on('click', function() {
					var venuechoicestatus = jQuery('input[type=radio][name="jform[venuechoice]"]:checked').val();

					if (venuechoicestatus == 'existing') {
						jQuery("#existingEvent").show();
					} else {
						jQuery("#existingEvent").hide();
					}
				});

				jQuery(document).on('subform-row-add', function(event, row) {

					jQuery('input.price').change(function() {

						var returnValue = jtSite.eventform.getRoundedValue(this.value);

						if (returnValue) {
							jQuery(this.id).focus();

							var error_html = '';
							error_html += Joomla.JText._('COM_JTICKETING_INVALID_FIELD') + returnValue;
							Joomla.renderMessages({"warning":[error_html]});
							return false;
						}
					});
				});

				jQuery('input.price').change(function() {
					var returnValue = jtSite.eventform.getRoundedValue(this.value);

					if (returnValue) {
						jQuery(this.id).focus();

						var error_html = '';
						error_html += Joomla.JText._('COM_JTICKETING_INVALID_FIELD') + returnValue;
						Joomla.renderMessages({"warning":[error_html]});
						return false;
					}
				});

				jQuery("#jform_location").change(function() {
					if (googleMapLink)
					{
						var geocoder = new google.maps.Geocoder();
						var address = jQuery('#jform_location').val();
						geocoder.geocode({
							'address': address
						}, function(results, status) {
							if (status == google.maps.GeocoderStatus.OK) {
								var latitude = results[0].geometry.location.lat();
								var longitude = results[0].geometry.location.lng();
								jQuery('#jform_latitude').val(latitude);
								jQuery('#jform_longitude').val(longitude);
							}
						});
					}
				});

				jtSite.eventform.displayNotices(ticketAccessState);

				jQuery(document).on('subform-row-add', function(event, row){
					jtSite.eventform.displayNotices(ticketAccessState);
				});
			});

			Joomla.submitbutton = function(task) {
				jQuery('#eventform-save').addClass('disabled');
				if (task == 'eventform.cancel') {
					jQuery('#eventform-save').removeClass('disabled');
					Joomla.submitform(task, document.getElementById('adminForm'));
				}
		
				var returnData = 1;
				if (!document.formvalidator.isValid(document.getElementById('adminForm'))) {
					jQuery('html, body').animate({
						'scrollTop' : jQuery('#system-message-container').position().top
					});
					jQuery('#eventform-save').removeClass('disabled');

					return false;
				}

				var eventStartDate = jtSite.eventform.getStartDate();
				var eventEndDate = jtSite.eventform.getEndDate();
				var compareStartDate = new Date(eventStartDate);
				var compareEndDate = new Date(eventEndDate);
				var eventBookingStartDate = document.getElementById('jform_booking_start_date').value;
				var eventBookingEndDate = document.getElementById('jform_booking_end_date').value;
				var compareBookingStartDate = new Date(eventBookingStartDate);
				var compareBookingEndDate = new Date(eventBookingEndDate);
				var recurringType = document.querySelector('input[name="jform[recurring_type]"]:checked')?.value || null;
				let repeatType = jQuery('input[name="jform[repeat_via]"]:checked').val();
				let repeatCount = parseInt(document.getElementById('jform_repeat_count')?.value, 10);
				let repeatUntil = document.getElementById('jform_repeat_until').value;
				let repeatInterval = parseInt(document.getElementById('jform_repeat_interval')?.value, 10) || 0;
				if(task == "eventform.save" || task == "eventform.save2new" || task == "eventform.apply")
				{
				   if(eventId)
				   {
						var oldEventStartDateInFormat = Number(new Date(oldEventStartDate));
						var eventStartDateInFormat = Number(new Date(eventStartDate));
						var oldEventEndDateInFormat = Number(new Date(oldEventEndDate));
						var eventEndDateInFormat = Number(new Date(eventEndDate));

						if(oldEventStartDateInFormat != eventStartDateInFormat || oldEventEndDateInFormat != eventEndDateInFormat)
						{
							var result = confirm(Joomla.JText._('COM_JTICKETING_SAVE_THE_EVENT_CHANGED_DATES'));

							if (result != true)
							{
								jQuery('#eventform-save').removeClass('disabled');

								return false;
							}
						}
					}
				}

				var value = new Array();

				jQuery(".price").each(function() {
					let price = jQuery(this).val().isInteger ? jQuery(this).val() : Math.round(jQuery(this).val() * 100) / 100;
					returnValue = jtSite.eventform.getRoundedValue(price);
					if (returnValue) {
						value.push(returnValue);
					}
				});

				var errorCode = new Array();

				jQuery(".ticket-end-date").each(function() {
					var ticketEndDt = new Date(jQuery(this).val());

					if (ticketEndDt != 'Invalid Date') {
						if (eventBookingEndDate == '' && ticketEndDt > compareEndDate) {
							jQuery(this.id).focus();
							errorCode.push(Joomla.JText._('COM_JTICKETING_TICKET_END_DATE_GREATER_EVENT_END_DATE_ERROR'));
						} else if (compareBookingEndDate != 'Invalid Date' && ticketEndDt > compareBookingEndDate) {
							jQuery(this.id).focus();
							errorCode.push(Joomla.JText._('COM_JTICKETING_TICKET_END_DATE_GREATER_BOOKING_END_DATE_ERROR'));
						}

						if (eventBookingStartDate == '' && ticketEndDt < new Date()) {
							jQuery(this.id).focus();
							errorCode.push(Joomla.JText._('COM_JTICKETING_TICKET_END_DATE_LESS_EVENT_MODIFICATION_DATE_ERROR'));
						} else if (compareBookingStartDate != 'Invalid Date' && ticketEndDt < compareBookingStartDate) {
							jQuery(this.id).focus();
							errorCode.push(Joomla.JText._('COM_JTICKETING_TICKET_END_DATE_LESS_BOOKING_START_DATE_ERROR'));
						}
					}
				});

				if (eventStartDate && eventBookingStartDate) {
					var eventStartDateTime = new Date(eventStartDate);
					var eventBookingStartDateTime = new Date(eventBookingStartDate);

					if (eventBookingStartDateTime > eventStartDateTime) {
						jQuery('jform_startdate').focus();
						errorCode.push(Joomla.JText._('COM_JTICKETING_BOOKING_START_DATE_WITH_EVENT_DATE_ERROR'));
					}
				}

				if (task == "eventform.save") {
					if (selectedVenueDetails) {
						var totalEnteredSeats = 0;
						techjoomla.jQuery('input.avail').each(function() {
							console.log(parseInt(techjoomla.jQuery(this).val()))
							totalEnteredSeats += parseInt(techjoomla.jQuery(this).val());
						});

						if (totalEnteredSeats > selectedVenueDetails.capacity_count) {
							alert(Joomla.JText._('COM_JTICKETING_VENUE_CAPACITY_ERROR'));
							jQuery('#eventform-save').removeClass('disabled');

							return false;
						}
					}

					if (tncForCreateEvent == 1 && document.getElementById('accept_privacy_term').checked === false) {
						alert(Joomla.JText._('COM_JTICKETING_PRIVACY_TERMS_AND_CONDITIONS_ERROR'));
						jQuery('#eventform-save').removeClass('disabled');

						return false;
					}

					if (errorCode.length != 0) {
						var error_html = '';

						for (index = 0; index < errorCode.length; ++index) {
							error_html += errorCode[index] + "<br>";
						}

						Joomla.renderMessages({"warning":[error_html]});

						return false;
					} else if (value.length != 0) {
						jQuery(value).each(function() {
							var error_html = '';
							error_html += Joomla.JText._('COM_JTICKETING_INVALID_FIELD') + value;
							Joomla.renderMessages({"warning":[error_html]});
							jQuery('#eventform-save').removeClass('disabled');

							return false;
						});
					} else if (recurringType !== 'No_repeat' && repeatType === 'rep_count' && (isNaN(repeatCount) || repeatCount < 1)) {
						var error_html = '';
						error_html += "<br />" + Joomla.JText._(
							isNaN(repeatCount) || repeatCount === '' 
								? 'COM_JTICKETING_FORM_LBL_REPEAT_COUNT_REQUIRED' 
								: 'COM_JTICKETING_FORM_LBL_REPEAT_COUNT_INVALID'
						);
						Joomla.renderMessages({"warning": [error_html]});
					} else if (repeatType === 'rep_until' && (!repeatUntil || repeatUntil.trim() === '' || new Date(repeatUntil) < new Date(eventStartDate))) {
						var error_html = '';
						error_html += "<br />" + Joomla.JText._( !repeatUntil || repeatUntil.trim() === '' 
							? 'COM_JTICKETING_FORM_LBL_REPEAT_UNTIL_REQUIRED' 
							: 'COM_JTICKETING_ERROR_REPEAT_UNTIL_GREATER_THAN_STARTDATE');
						Joomla.renderMessages({"warning": [error_html]});
					} else if (isNaN(repeatInterval) || repeatInterval < 0) {
						var error_html = '';
						error_html += "<br />" + Joomla.JText._(
							isNaN(repeatInterval) || repeatInterval === '' 
								? 'COM_JTICKETING_FORM_LBL_REPEAT_INTERVAL_REQUIRED' 
								: 'COM_JTICKETING_FORM_LBL_REPEAT_INTERVAL_INVALID'
						);
						Joomla.renderMessages({"warning": [error_html]});
					} else if (recurringType==='No_repeat'&&compareEndDate <= compareStartDate) {
						var error_html = '';
						error_html += "<br />" + Joomla.JText._('COM_JTICKETING_FORM_LBL_EVENT_DATE_ERROR');
						Joomla.renderMessages({"warning":[error_html]});
					} else if (compareBookingStartDate != 'Invalid Date' && compareBookingEndDate != 'Invalid Date' && compareBookingEndDate <= compareBookingStartDate) {
						var error_html = '';
						error_html += "<br />" + Joomla.JText._('COM_JTICKETING_FORM_LBL_EVENT_BOOKING_DATE_ERROR');
						Joomla.renderMessages({"warning":[error_html]});
					} else if (compareBookingEndDate != 'Invalid Date' && compareEndDate < compareBookingEndDate) {
						var error_html = '';
						error_html += "<br />" + Joomla.JText._('COM_JTICKETING_FORM_LBL_EVENT_BOOKING_EVENT_END_ERROR');
						Joomla.renderMessages({"warning":[error_html]});
					} else {
						var i = 0;
						while(1)
						{
							var unlimited = '#jform_tickettypes__tickettypes' + i + '__unlimited_seats';
							var flag=0;
							var available = '#jform_tickettypes__tickettypes' + i + '__available';

							if (!jQuery(unlimited).length) {
								break;
							}

							if (jQuery(unlimited).val() == 0) {
								if(jQuery(available).val() <= 0 || !/^\d+$/.test(jQuery(available).val())) {
									flag=1;
									var error_html = '';
									error_html = Joomla.JText._('COM_JTICKETING_ERROR') + "<br>" + Joomla.JText._('COM_JTICKETING_INVALID_SEAT_COUNT_ERROR');

									Joomla.renderMessages({"warning":[error_html]});
									jQuery(available).css('border-color', 'red');
									jQuery(available).focus();
									break;
								   }
								}
							i++;
						}

						jQuery('#tickettypes .subform-repeatable-group').each(function(key) {
							var thisSubForm = jQuery(this);
							if (thisSubForm.find('select.unlimited-seats').val() == 0 || thisSubForm.find('select.unlimited-seats').val() == '0') {
								if (!thisSubForm.find('input.avail').val()) {
									var alertError = Joomla.JText._('COM_JTICKETING_ENTER_VALID_SEAT_COUNT_FOR_TICKET') + ' ' + thisSubForm.find('input.title').val();
									alert(alertError);
									returnData = 0;
								}
							}
						});
			
						if (jQuery('#accept_privacy_term').length && jQuery('#accept_privacy_term').prop('checked') != true) {
							alert(Joomla.JText._('COM_JTICKETING_PRIVACY_TERMS_AND_CONDITIONS_ERROR'))
							returnData = 0;
						}

						var validData = document.formvalidator.isValid(document.getElementById('adminForm'));
						if (validData == true && flag == 0) {
							Joomla.submitform(task, document.getElementById('adminForm'));
						}
						
			
						if (returnData == 1) {
							Joomla.submitform(task, document.getElementById('adminForm'));
						}
					}
				} else if (task == 'eventform.cancel') {
					Joomla.submitform(task, document.getElementById('adminForm'));
				} else {
					Joomla.submitform(task, document.getElementById('adminForm'));
				}

				jQuery('#eventform-save').removeClass('disabled');
			}
		},
		getRoundedValue: function(value) {
			var errorMsg = '';

			jQuery.ajax({
				type: "POST",
				dataType: "json",
				data: {
					'price' : value
				},
				async: false,
				url: Joomla.getOptions('system.paths').base + "/index.php?option=com_jticketing&format=json&task=eventform.getRoundedValue&price=" + value,
				success: function(data) {

					if (data.data != value) {
						roundedPrice = data.data;
						errorMsg = Joomla.JText._('COM_JTICKETING_VALIDATE_ROUNDED_PRICE').concat(roundedPrice);
					} else {
						return value;
					}
				},
			});

			return errorMsg;

		},
		showLocation: function() {
			var venue = parseInt(jQuery('#jform_venue').val());
			var onlineOfflineVenues = jQuery("input[name='jform[online_events]']:checked").val();

			/** global: selectedVenue */
			if (parseInt(enableOnlineVenues) == 0) {
				if (selectedVenue > 0 && venue === 0) {
					jQuery('#event-location').show();
					jQuery("#jform_location").prop('required', true);
					jQuery("#jform_location").addClass("required");
				} else if (selectedVenue > 0) {
					jQuery('#event-location').hide();
					jQuery("#jform_location").prop('required', false);
					jQuery("#jform_location").removeClass("required");
				} else if (venue == 0 || !venue) {
					jQuery('#event-location').show();
					jQuery("#jform_location").prop('required', true);
					jQuery("#jform_location").addClass("required");
				} else {
					jQuery('#event-location').hide();
					jQuery("#jform_location").prop('required', false);
					jQuery("#jform_location").removeClass("required");
				}
			} else {
				if (selectedVenue > 0 && !venue) {
					jQuery('#event-location').show();
					jQuery("#jform_location").prop('required', true);
					jQuery("#jform_location").addClass("required");
				} else if (venue == 0 && onlineOfflineVenues === "0") {
					jQuery('#event-location').show();
					jQuery("#jform_location").prop('required', true);
					jQuery("#jform_location").addClass("required");
				} else if (!venue && onlineOfflineVenues === "0") {
					var venuevalue = jQuery('#foorm_venue').val();
					jQuery('#event-location').show();
					jQuery("#jform_location").prop('required', true);
					jQuery("#jform_location").addClass("required");
				} else {
					jQuery('#event-location').hide();
					jQuery("#jform_location").prop('required', false);
					jQuery("#jform_location").removeClass("required");
				}
			}
		},
		getStartDate:function() {
            return document.getElementById('jform_eventstart_date').value + ' ' + document.getElementById('jform_start_time').value;
        },
        getEndDate: function() {
			let startDateStr = document.getElementById('jform_eventstart_date').value + ' ' + document.getElementById('jform_start_time').value;
			let repeatType = jQuery('input[name="jform[repeat_via]"]:checked').val();
			let recurringType = jQuery('input[name="jform[recurring_type]"]:checked').val();
			let repeatCount = parseInt(document.getElementById('jform_repeat_count')?.value, 10) - 1;
			let repeatUntil = document.getElementById('jform_repeat_until').value + ' ' + document.getElementById('jform_end_time').value;
			let repeatInterval = parseInt(document.getElementById('jform_repeat_interval')?.value, 10) || 0;
			let startDate = new Date(startDateStr);
			let endDate = new Date(startDate);
			if (repeatType === "rep_until") {
				return repeatUntil;
			} else if (repeatType === "rep_count" && repeatCount >= 0) {
				switch (recurringType) {
					case "Daily":
						if (repeatInterval === 0) {
							endDate.setDate(startDate.getDate() + repeatCount);
						} else {
							endDate.setDate(startDate.getDate() + (repeatCount * (repeatInterval + 1)));
						}
						break;

					case "Weekly":
						if (repeatInterval === 0) {
							endDate.setDate(startDate.getDate() + (repeatCount * 7));
						} else {
							endDate.setDate(startDate.getDate() + (repeatCount * ((repeatInterval + 1) * 7)));
						}
						break;

					case "Monthly":
						if (repeatInterval === 0) {
							endDate.setMonth(startDate.getMonth() + repeatCount);
						} else {
							endDate.setMonth(startDate.getMonth() + (repeatCount * (repeatInterval + 1)));
						}
						break;

					case "Yearly":
						if (repeatInterval === 0) {
							endDate.setFullYear(startDate.getFullYear() + repeatCount);
						} else {
							endDate.setFullYear(startDate.getFullYear() + (repeatCount * (repeatInterval + 1)));
						}
						break;
					default:
						return document.getElementById('jform_eventend_date').value + ' ' + document.getElementById('jform_end_time').value;
				}
			} else {
				return document.getElementById('jform_eventend_date').value + ' ' + document.getElementById('jform_end_time').value;
			}
			return endDate.toISOString().split('T')[0] + ' ' + endDate.toTimeString().split(' ')[0];
		},
		venueDisplay: function() {
			return new Promise (function (resolve, reject) {
				var radioValue = jQuery("input[name='jform[online_events]']:checked").val();
				var eventStartDate = jtSite.eventform.getStartDate();
				var eventEndDate = jtSite.eventform.getEndDate();
				if (silentVendor == 1) {
					var created_by = jQuery("input[name='jform[created_by]']").val();
				}
	
				var onlineOfflineVenues = jQuery("input[name='jform[online_events]']:checked").val();
				var selectedVenue = jQuery('#jform_venue').val();
	
				if (onlineOfflineVenues == '1') {
					jQuery('#event-location').hide();
					jQuery('#venuechoice_id').show();
					jQuery('#existingEvent').show();
				} else {
					jQuery('#venuechoice_id').hide();
					jQuery('#existingEvent').hide();
				}
				var userObject = {};
				userObject["radioValue"] = radioValue;
				userObject["eventStartTime"] = eventStartDate;
				userObject["eventEndTime"] = eventEndDate;
				userObject["silentVendor"] = silentVendor;
				userObject["eventId"] = eventId;
				userObject["venueId"] = venueId;
	
				if (silentVendor == 0) {
					userObject["vendor_id"] = vendor_id;
				} else {
					userObject["created_by"] = created_by;
				}
				jQuery('#jform_venue, .chzn-results').empty();
				JSON.stringify(userObject);
				jQuery.ajax({
					type: "POST",
					data: userObject,
					dataType: "json",
					url: Joomla.getOptions('system.paths').base + "/index.php?option=com_jticketing&format=json&task=eventform.getVenueList",
					success: function(data) {
						if (data == '' && eventId == 0 && radioValue == 1) {
							jQuery("#jform_venue").prop("disabled", true);
							var op = "<option value='0' selected='selected'>" + Joomla.JText._('COM_JTICKETING_NO_ONLINE_VENUE_ERROR') + "</option>";
							jQuery('#jform_venue').append(op);
							jQuery("#jform_venue").trigger("liszt:updated");
							jQuery('#jform_venue').trigger("chosen:updated");
							var error_html = '';
							error_html += "<br />" + Joomla.JText._('COM_JTICKETING_NO_VENUE_ERROR_MSG');
							Joomla.renderMessages({"warning":[error_html]});
						} else {
							if (eventId != 0) {
								var onlineOfflineVenues = jQuery("input[name='jform[online_events]']:checked").val();
	
								if (onlineOfflineVenues == 1) {
									jQuery("#jform_venue").prop("disabled", true);
	
								}
	
								if (venueId == 0) {
									jQuery('#jform_venue, .chzn-results').empty();
									venueName = Joomla.JText._('COM_JTICKETING_CUSTOM_LOCATION');
								}
								var op = "<option value='" + venueId + "' selected='selected'>" + venueName + " </option>";
								jQuery('#jform_venue').append(op);
								jQuery("#jform_venue").trigger("liszt:updated");
								jQuery('#jform_venue').trigger("chosen:updated");
								for (index = 0; index < data.length; ++index) {
									var op = "<option value='" + data[index].value + "' > " + data[index]['text'] + "</option>";
									jQuery('#jform_venue').append(op);
									jQuery("#jform_venue").trigger("liszt:updated");
									jQuery('#jform_venue').trigger("chosen:updated");
								}
							} else {
								jQuery("#jform_venue").prop("disabled", false);
								var op = "<option value='' selected='selected'>" + Joomla.JText._('COM_JTICKETING_FORM_EVENT_DEFAULT_VENUE_OPTION') + "</option>";
								jQuery('#jform_venue').append(op);
								jQuery("#jform_venue").trigger("liszt:updated");
								jQuery('#jform_venue').trigger("chosen:updated");
								
								for (index = 0; index < data.length; ++index) {
									var op = "<option value='" + data[index].value + "' > " + data[index]['text'] + "</option>";
									jQuery('#jform_venue').append(op);
									jQuery("#jform_venue").trigger("liszt:updated");
									jQuery('#jform_venue').trigger("chosen:updated");
								}
							}
						}

						resolve();
					},
				});
			});
		},
		ConvertTimeformat: function(date, hours, minutes, amPm) {
			hours = parseInt(hours);
			minutes = parseInt(minutes);
			if (amPm == "PM" && hours < 12) hours = hours + 12;
			if (amPm == "AM" && hours == 12) hours = hours - 12;
			if (hours < 10) hours = "0" + hours;
			if (minutes < 10) minutes = "0" + minutes;
			var time = date + " " + hours + ":" + minutes + ":00";
			var utcDateTime = new Date(time).toISOString();
			utcDateTime = utcDateTime.substring(0, utcDateTime.length - 5);
			var formattedUtcDateTime = utcDateTime.replace("T", " ");
			return formattedUtcDateTime;
		},
		existingEvents: function() {
			var radioValue = jQuery("input[name='jform[online_events]']:checked").val();
			var existingEventChoice = jQuery("input[name='jform[venuechoice]']:checked").val();
			if (existingEventChoice == 'new' && radioValue == '1') {
				jQuery('#form_existingEvent').hide();
			} else {
				jQuery('#form_existingEvent').show();
			}
		},
		selectExistingEvent: function() {
			var venueId = document.getElementById("jform_venue").value;
			var venuestatus = jQuery('input[type=radio][name="jform[online_events]"]:checked').val();

			jQuery('#jform_existing_event option').remove();
			var eventDropdown = jQuery("#jform_existing_event");
			var op = "<option value='' selected='selected'>" + Joomla.JText._('COM_JTICKETING_FORM_SELECT_EXISTING_EVENT_OPTION') + "</option>";
			eventDropdown.append(op);
			eventDropdown.trigger("liszt:updated");
			eventDropdown.trigger("chosen:updated");

			if (venueId != '0' && venuestatus == '1') {

				/** global: JTicketing */
				JTicketing.Ajax({
					url: Joomla.getOptions('system.paths').base + '/index.php?option=com_jticketing',
					data: {
						task: "eventform.getAllMeetings",
						'venueId': venueId
					}
				}).done(function(result) {
					if (result.success === true) {
						var eventList = result.data;
						if (eventList !== undefined && eventList !== null && Array.isArray(eventList)) {

							eventList.forEach(function(event) {

								if (existing_url == event.id) {
									op = "<option value='" + event.id + "' selected='selected'>" + event.title + " (" + event.start_time + ")" + "</option>";
								} else {
									op = "<option value='" + event.id + "' >" + event.title + " (" + event.start_time + ")" + "</option>";
								}
								eventDropdown.append(op);
							});
						}
					} else {
						//                        Joomla.renderMessages({
						//                            'error': [result.message]
						//                        });
					}
				}).fail(function(content) {
					Joomla.renderMessages({
						'error': [Joomla.JText._('COM_JTICKETING_EVENT_RELATED_AJAX_FAIL_ERROR_MESSAGE')]
					});
				}).always(function() {
					eventDropdown.trigger("liszt:updated");
					eventDropdown.trigger("chosen:updated");
				});

			}
		},
		selectExistingEventOnload: function() {
			if (existing_url) {
				return;
			}
			var venuestatus = jQuery('input[type=radio][name="jform[online_events]"]:checked').val();

			jQuery('#jform_existing_event option').remove();
			var eventDropdown = jQuery("#jform_existing_event");
			var op = "<option value='' selected='selected'>" + Joomla.JText._('COM_JTICKETING_FORM_SELECT_EXISTING_EVENT_OPTION') + "</option>";
			eventDropdown.append(op);
			eventDropdown.trigger("liszt:updated");
			eventDropdown.trigger("chosen:updated");

			if (venueId != '0' && venuestatus == '1') {

				/** global: JTicketing */
				JTicketing.Ajax({
					url: Joomla.getOptions('system.paths').base + '/index.php?option=com_jticketing',
					data: {
						task: "eventform.getAllMeetings",
						'venueId': venueId
					}
				}).done(function(result) {
					if (result.success === true) {
						var eventList = result.data;
						if (eventList !== undefined && eventList !== null && Array.isArray(eventList)) {

							eventList.forEach(function(event) {

								if (existing_url == event.id) {
									op = "<option value='" + event.id + "' selected='selected'>" + event.title + " (" + event.start_time + ")" + "</option>";
								} else {
									op = "<option value='" + event.id + "' >" + event.title + " (" + event.start_time + ")" + "</option>";
								}
								eventDropdown.append(op);
							});
						}
					} else {
						//                        Joomla.renderMessages({
						//                            'error': [result.message]
						//                        });
					}
				}).fail(function(content) {
					Joomla.renderMessages({
						'error': [Joomla.JText._('COM_JTICKETING_EVENT_RELATED_AJAX_FAIL_ERROR_MESSAGE')]
					});
				}).always(function() {
					eventDropdown.trigger("liszt:updated");
					eventDropdown.trigger("chosen:updated");
				});
			}
		},
		existingEventSelection: function() {
			var radioValue = jQuery("input[name='jform[online_events]']:checked").val();
			var existingEventChoice = jQuery("input[name='jform[venuechoice]']:checked").val();

			if (existingEventChoice == 'new' && radioValue == '1') {
				jQuery('#form_existingEvent').hide();
			} else {
				jQuery('#form_existingEvent').show();
			}
		},
		displayNotices: function(access) {
			if (access == 'exclude')
			{
				jQuery('.accessNote').html(Joomla.JText._('COM_JTICKETING_TICKET_ACCESS_EXCLUDE'));
			}
			else
			{
				jQuery('.accessNote').html(Joomla.JText._('COM_JTICKETING_TICKET_ACCESS_INCLUDE'));
			}
		},
		showTicketCapacity: function() {
			var venue = parseInt(jQuery('#jform_venue').val());
			techjoomla.jQuery('.ticketCapacityNote').addClass('af-d-none');
			techjoomla.jQuery( ".unlimited-seats option[value='1']").prop("disabled", false);
			jQuery(".unlimited-seats").trigger("liszt:updated");
        	jQuery(".unlimited-seats").trigger("chosen:updated");
			
			if (venue) {
				var requestObject = {};
				requestObject["venue"] = venue;
				JSON.stringify(requestObject);
				jQuery.ajax({
					type: "POST",
					dataType: "json",
					data: requestObject,
					url: Joomla.getOptions('system.paths').base + "/index.php?option=com_jticketing&format=json&task=venueform.getCapacity",
					success: function(result) {
						if (result) {
							if (result.data && result.data.seats_capacity == 0) {
								techjoomla.jQuery(".unlimited-seats").each( function() {
									if(!techjoomla.jQuery(this).val()) {
										techjoomla.jQuery(this).val('');
									}
								});
								// techjoomla.jQuery(".unlimited-seats").not(".unlimited-seats option[value='1']").val('');
								techjoomla.jQuery(".unlimited-seats option[value='1']").prop("disabled", true);
								jQuery(".unlimited-seats").trigger("liszt:updated");
        						jQuery(".unlimited-seats").trigger("chosen:updated");
								techjoomla.jQuery('.ticketCapacityNote').removeClass('af-d-none');
								techjoomla.jQuery('.ticketCapacityNote').html(Joomla.JText._('COM_JTICKETING_TICKET_CAPACITY_ALERT') + result.data.capacity_count);
								selectedVenueDetails = result.data;
							} else {
								if (result.data == 1) {
									alert(result.message);
								}
								selectedVenueDetails = '';
							}
						}
					},
				});
			} else {
				selectedVenueDetails = '';
			}
		},
		showTicketAvailability: function() {
			var venue = jQuery('#jform_venue').val() ? parseInt(jQuery('#jform_venue').val()) : 0;
			
			if (venue && selectedVenueDetails) {
				techjoomla.jQuery(".unlimited-seats").each( function() {
					if(!techjoomla.jQuery(this).val()) {
						techjoomla.jQuery(this).val('');
					}
				});
				// techjoomla.jQuery(".unlimited-seats").not(".unlimited-seats option[value='1']").val('');
				techjoomla.jQuery(".unlimited-seats option[value='1']").prop("disabled", true);
				jQuery(".unlimited-seats").trigger("liszt:updated");
				jQuery(".unlimited-seats").trigger("chosen:updated");
				techjoomla.jQuery('.ticketCapacityNote').removeClass('af-d-none');
				techjoomla.jQuery('.ticketCapacityNote').html(Joomla.JText._('COM_JTICKETING_TICKET_CAPACITY_ALERT') + selectedVenueDetails.capacity_count);
			}
		}
	},

	couponform: {
		initCouponFormJs: function() {
			jQuery(document).ready(function() {
				jQuery("#jform_value").change(function() {
					validation.checkForZeroAndAlpha(this, '46', Joomla.JText._('COM_JTICKETING_ENTER_NUMERICS'));

					if (jQuery('#couponform #jform_val_type').length && jQuery('#couponform #jform_val_type').val() == '1')
					{
						if (jQuery("#jform_value").val() && parseInt(jQuery("#jform_value").val()) > 100)
						{
							alert(Joomla.JText._('COM_JTICKETING_COUPON_PERCENTAGE_ERROR'));
							jQuery("#jform_value").val('');
						}
					}
				});

				jQuery('#couponform #jform_val_type').change(function() {
					if (jQuery('#couponform #jform_val_type').length && jQuery('#couponform #jform_val_type').val() == '1')
					{
						if (jQuery("#jform_value").val() && parseInt(jQuery("#jform_value").val()) > 100)
						{
							alert(Joomla.JText._('COM_JTICKETING_COUPON_PERCENTAGE_ERROR'));
							jQuery("#jform_value").val('');
						}
					}
				});

				var ticketsContainer = jQuery("#group_discount_tickets_container");

				if (jQuery("input[name='jform[group_discount]']:checked").val() !== "1") {
					ticketsContainer.addClass("d-none");
				}

				jQuery('input[name="jform[group_discount]"]').change(function() {
					ticketsContainer.toggleClass("d-none", jQuery(this).val() !== "1");
				});

				jQuery("#jform_limit").change(function() {
					validation.checkForZeroAndAlpha(this, '46', Joomla.JText._('COM_JTICKETING_ENTER_NUMERICS'));
				});
				jQuery("#jform_max_per_user").change(function() {
					validation.checkForZeroAndAlpha(this, '46', Joomla.JText._('COM_JTICKETING_ENTER_NUMERICS'));
				});
			});
		},

		couponSubmitButton: function(task) {
			if (jQuery('#couponform #jform_val_type').length && jQuery('#couponform #jform_val_type').val() == '1')
			{
				if (jQuery("#jform_value").val() && parseInt(jQuery("#jform_value").val()) > 100)
				{
					alert(Joomla.JText._('COM_JTICKETING_COUPON_PERCENTAGE_ERROR'));
					jQuery("#jform_value").val('');
					
					return false;
				}
			}

			if (task == 'couponform.cancel') {
				Joomla.submitform(task, document.getElementById('coupon-form'));
			} else {
				if (task != 'couponform.cancel' && document.formvalidator.isValid(document.getElementById('coupon-form'))) {
					Joomla.submitform(task, document.getElementById('coupon-form'));
				}
			}
		},
	},

	coupons: {
		couponsSubmitData: function(task) {
			if (task == 'coupons.delete') {
				var r = confirm(Joomla.JText._('COM_JTICKETING_ARE_YOU_SURE_YOU_TO_DELETE_THE_COUPON'));

				if (r !== true) {
					return;
				}
			}

			Joomla.submitform(task);
		}
	},
	myEvents: {
		myEventsSubmitData: function(task) {
			if (task == 'eventform.add')
			{
				Joomla.submitform(task);
				return;
			}

			if (document.adminForm.boxchecked.value==0) { 
				alert(Joomla.Text._('TJTOOLBAR_NO_SELECT_MSG'));
			} else {
				Joomla.submitform(task);
			}
		}
	}
}
var jtAdmin = {
	event: {
		/*Initialize event js*/
		initEventJs: function() {
			jQuery(window).on('load', function() {
				jQuery('#jform_startdate, #jform_enddate, .time-hours , .time-minutes').on('change, blur', function() {
					if (jQuery("#jform_venue")[0].length === 0) {
						jtSite.eventform.venueDisplay();
					}
				});

				jtAdmin.event.showTicketCapacity();
			});

			jQuery(document).ready(function() {
				var radioValue = jQuery("input[name='jform[online_events]']:checked").val();
				var id = document.getElementById('jform_id').value;
				var selectedVenueDetails = '';

				if (jQuery('#attendeefields .subform-repeatable-wrapper').length)
				{
					jQuery('#attendeefields .subform-repeatable-wrapper .subform-repeatable .btn-toolbar.hidden:first').removeClass('hidden');
				}

				var venueDisplayPromise = jtAdmin.event.venueDisplay();
				jtAdmin.event.existingEvents();

				venueDisplayPromise.then(function(){
					jtAdmin.event.showLocation();
				});

				jtAdmin.event.selectExistingEventOnload();
				jQuery("input[name='jform[online_events]']").change(function() {
					jtAdmin.event.venueDisplay();
					jtAdmin.event.existingEvents();
					jtAdmin.event.showTicketCapacity();
				});

				jQuery('.group-add').on('click', function() {
					setTimeout(function() {
						jtAdmin.event.showTicketAvailability();
					}, 150);
				});

				jQuery.each(mediaGallery, function(key, media) {
					tjMediaFile.previewFile(media, 1);
				});
				jQuery("#jform_venue").change(function() {
					jtAdmin.event.showLocation();
				});

				jQuery(".venueCheck").change(function() {
					jtAdmin.event.selectExistingEvent();
					jtAdmin.event.showTicketCapacity();
				});

				jQuery(".existingEvent").change(function() {
					jtAdmin.event.existingEventSelection();
				});
				jQuery("#jform_created_by").change(function() {
					jtAdmin.event.checkUserEmail();
				});
				jQuery('input[type=radio][name="jform[venuechoice]"]').on('click', function() {
					var venuechoicestatus = jQuery('input[type=radio][name="jform[venuechoice]"]:checked').val();
					if (venuechoicestatus == 'existing') {
						jQuery("#existingEvent").show();
					} else {
						jQuery("#existingEvent").hide();
					}
				});

				jQuery(document).on('subform-row-add', function(event, row) {

					jQuery('input.price').change(function() {

						var returnValue = jtAdmin.event.getRoundedValue(this.value);

						if (returnValue) {
							jQuery(this.id).focus();

							var error_html = '';
							error_html += Joomla.JText._('COM_JTICKETING_INVALID_FIELD') + returnValue;
							Joomla.renderMessages({"warning":[error_html]});
							return false;
						}
					});
				});

				jQuery('input.price').change(function() {
					var returnValue = jtAdmin.event.getRoundedValue(this.value);

					if (returnValue) {
						jQuery(this.id).focus();

						var error_html = '';
						error_html += Joomla.JText._('COM_JTICKETING_INVALID_FIELD') + returnValue;
						Joomla.renderMessages({"warning":[error_html]});
						return false;
					}
				});

				jQuery("#jform_location").change(function() {
					if (googleMapApiKey)
					{
						var geocoder = new google.maps.Geocoder();
						var address = jQuery('#jform_location').val();
						geocoder.geocode({
							'address': address
						}, function(results, status) {
							if (status == google.maps.GeocoderStatus.OK) {
								var latitude = results[0].geometry.location.lat();
								var longitude = results[0].geometry.location.lng();
								jQuery('#jform_latitude').val(latitude);
								jQuery('#jform_longitude').val(longitude);
							}
						});
					}
				});

				jtSite.eventform.displayNotices(ticketAccessState);

				jQuery(document).on('subform-row-add', function(event, row){
					jtSite.eventform.displayNotices(ticketAccessState);
				});
			});
			
			Joomla.submitbutton = function(task) {
				jQuery('#toolbar-apply, #toolbar-save, #toolbar-save-new').addClass('disabled');
				if (task == 'event.cancel') {

					jQuery('#toolbar-apply, #toolbar-save, #toolbar-save-new').removeClass('disabled');
					Joomla.submitform(task, document.getElementById('adminForm'));
				} else if (!document.formvalidator.isValid(document.getElementById('adminForm'))) {
					jQuery('html, body').animate({
						'scrollTop' : jQuery('#system-message-container').position().top
					});
					jQuery('#toolbar-apply, #toolbar-save, #toolbar-save-new').removeClass('disabled');

					return false;
				} else if (jQuery('#accept_privacy_term').length && jQuery('#accept_privacy_term').prop('checked') != true) {
					alert(Joomla.JText._('COM_JTICKETING_PRIVACY_TERMS_AND_CONDITIONS_ERROR'));
					jQuery('#toolbar-apply, #toolbar-save, #toolbar-save-new').removeClass('disabled');
					
					return false;
				}

				var eventStartDate = jtAdmin.event.getStartDate();
				var eventEndDate = jtAdmin.event.getEndDate();
				var compareStartDate = new Date(eventStartDate);
				var compareEndDate = new Date(eventEndDate);
				var eventBookingStartDate = document.getElementById('jform_booking_start_date').value;
				var eventBookingEndDate = document.getElementById('jform_booking_end_date').value;
				var compareBookingStartDate = new Date(eventBookingStartDate);
				var compareBookingEndDate = new Date(eventBookingEndDate);
				var recurringType = document.querySelector('input[name="jform[recurring_type]"]:checked')?.value || null;
				let repeatType = jQuery('input[name="jform[repeat_via]"]:checked').val();
				let repeatCount = parseInt(document.getElementById('jform_repeat_count')?.value, 10);
				let repeatUntil = document.getElementById('jform_repeat_until').value;
				let repeatInterval = parseInt(document.getElementById('jform_repeat_interval')?.value, 10) || 0;
				// Confirmation of saving edit event startdate and enddate
				if(task == "event.save" || task == "event.save2new" || task == "event.apply")
				{
					var id = document.getElementById('jform_id').value;

				   if(id != '0')
				   {
						var oldEventStartDateInFormat = Number(new Date(oldEventStartDate));
						var eventStartDateInFormat = Number(new Date(eventStartDate));
						var oldEventEndDateInFormat = Number(new Date(oldEventEndDate));
						var eventEndDateInFormat = Number(new Date(eventEndDate));

						if(oldEventStartDateInFormat != eventStartDateInFormat || oldEventEndDateInFormat != eventEndDateInFormat)
						{
							var result = confirm(Joomla.JText._('COM_JTICKETING_SAVE_THE_EVENT_CHANGED_DATES'));

							if (result != true)
							{
								jQuery('#toolbar-apply, #toolbar-save, #toolbar-save-new').removeClass('disabled');

								return false;
							}
						}
					}
				}

				var value = new Array();

				jQuery(".price").each(function() {
					let price = jQuery(this).val().isInteger ? jQuery(this).val() : Math.round(jQuery(this).val() * 100) / 100;
					returnValue = jtAdmin.event.getRoundedValue(price);
					if (returnValue) {
						value.push(returnValue);
					}
				});

				var errorCode = new Array();

				jQuery(".ticket-end-date").each(function() {
					var ticketEndDt = new Date(jQuery(this).val());

					if (ticketEndDt != 'Invalid Date') {
						if (eventBookingEndDate == '' && ticketEndDt > compareEndDate) {
							jQuery(this.id).focus();
							errorCode.push(Joomla.JText._('COM_JTICKETING_TICKET_END_DATE_GREATER_EVENT_END_DATE_ERROR'));
						} else if (compareBookingEndDate != 'Invalid Date' && ticketEndDt > compareBookingEndDate) {
							jQuery(this.id).focus();
							errorCode.push(Joomla.JText._('COM_JTICKETING_TICKET_END_DATE_GREATER_BOOKING_END_DATE_ERROR'));
						}

						if (eventBookingStartDate == '' && ticketEndDt < new Date()) {
							jQuery(this.id).focus();
							errorCode.push(Joomla.JText._('COM_JTICKETING_TICKET_END_DATE_LESS_EVENT_MODIFICATION_DATE_ERROR'));
						} else if (compareBookingStartDate != 'Invalid Date' && ticketEndDt < compareBookingStartDate) {
							jQuery(this.id).focus();
							errorCode.push(Joomla.JText._('COM_JTICKETING_TICKET_END_DATE_LESS_BOOKING_START_DATE_ERROR'));
						}
					}
				});

				if (eventStartDate && eventBookingStartDate) {
					var eventStartDateTime = new Date(eventStartDate);
					var eventBookingStartDateTime = new Date(eventBookingStartDate);

					if (eventBookingStartDateTime > eventStartDateTime) {
						jQuery('jform_startdate').focus();
						errorCode.push(Joomla.JText._('COM_JTICKETING_BOOKING_START_DATE_WITH_EVENT_DATE_ERROR'));
					}
				}

				if (task == "event.save" || task == "event.save2new" || task == "event.apply") {

					if (tncForCreateEvent == 1 && document.getElementById('accept_privacy_term').checked === false) {
						alert(Joomla.JText._('COM_JTICKETING_PRIVACY_TERMS_AND_CONDITIONS_ERROR'));
						jQuery('#toolbar-apply, #toolbar-save, #toolbar-save-new').removeClass('disabled');

						return false;
					}

					if (selectedVenueDetails) {
						var totalEnteredSeats = 0;
						techjoomla.jQuery('input.avail').each(function() {
							totalEnteredSeats += parseInt(techjoomla.jQuery(this).val());
						});

						if (totalEnteredSeats > selectedVenueDetails.capacity_count) {
							alert(Joomla.JText._('COM_JTICKETING_VENUE_CAPACITY_ERROR'));
							jQuery('#toolbar-apply, #toolbar-save, #toolbar-save-new').removeClass('disabled');

							return false;
						}
					}

					if (errorCode.length != 0) {
						var error_html = '';

						for (index = 0; index < errorCode.length; ++index) {
							error_html += errorCode[index] + "<br>";
						}

						Joomla.renderMessages({"warning":[error_html]});
						return false;
					} else if (value.length != 0) {
						jQuery(value).each(function() {
							var error_html = '';
							error_html += Joomla.JText._('COM_JTICKETING_INVALID_FIELD') + value;
							Joomla.renderMessages({"warning":[error_html]});
							jQuery('#toolbar-apply, #toolbar-save, #toolbar-save-new').removeClass('disabled');
							
							return false;
						});
					} else if (recurringType !== 'No_repeat' && repeatType === 'rep_count' && (isNaN(repeatCount) || repeatCount < 1)) {
						var error_html = '';
						error_html += "<br />" + Joomla.JText._(
							isNaN(repeatCount) || repeatCount === '' 
								? 'COM_JTICKETING_FORM_LBL_REPEAT_COUNT_REQUIRED' 
								: 'COM_JTICKETING_FORM_LBL_REPEAT_COUNT_INVALID'
						);
						Joomla.renderMessages({"warning": [error_html]});
					} else if (repeatType === 'rep_until' && (!repeatUntil || repeatUntil.trim() === '' || new Date(repeatUntil) < new Date(eventStartDate))) {
						var error_html = '';
						error_html += "<br />" + Joomla.JText._( !repeatUntil || repeatUntil.trim() === '' 
							? 'COM_JTICKETING_FORM_LBL_REPEAT_UNTIL_REQUIRED' 
							: 'COM_JTICKETING_ERROR_REPEAT_UNTIL_GREATER_THAN_STARTDATE');
						Joomla.renderMessages({"warning": [error_html]});
					} else if (isNaN(repeatInterval) || repeatInterval < 0) {
						var error_html = '';
						error_html += "<br />" + Joomla.JText._(
							isNaN(repeatInterval) || repeatInterval === '' 
								? 'COM_JTICKETING_FORM_LBL_REPEAT_INTERVAL_REQUIRED' 
								: 'COM_JTICKETING_FORM_LBL_REPEAT_INTERVAL_INVALID'
						);
						Joomla.renderMessages({"warning": [error_html]});
					} else if (recurringType==='No_repeat'&&compareEndDate <= compareStartDate) {
						var error_html = '';
						error_html += "<br />" + Joomla.JText._('COM_JTICKETING_FORM_LBL_EVENT_DATE_ERROR');
						Joomla.renderMessages({"warning":[error_html]});
					} else if (compareBookingStartDate != 'Invalid Date' && compareBookingEndDate != 'Invalid date' && compareBookingEndDate <= compareBookingStartDate) {
						var error_html = '';
						error_html += "<br />" + Joomla.JText._('COM_JTICKETING_FORM_LBL_EVENT_BOOKING_DATE_ERROR');
						Joomla.renderMessages({"warning":[error_html]});
					} else if (compareBookingEndDate != 'Invalid date' && compareEndDate < compareBookingEndDate) {
						var error_html = '';
						error_html += "<br />" + Joomla.JText._('COM_JTICKETING_FORM_LBL_EVENT_BOOKING_EVENT_END_ERROR');
						Joomla.renderMessages({"warning":[error_html]});
					} else {
						var i = 0;
						while(1)
						{
							var unlimited = '#jform_tickettypes__tickettypes' + i + '__unlimited_seats';
							var flag=0;
							var available = '#jform_tickettypes__tickettypes' + i + '__available';

							if (!jQuery(unlimited).length) {
								break;
							}

							if (jQuery(unlimited).val() == 0) {
								if(jQuery(available).val() <= 0 || !/^\d+$/.test(jQuery(available).val())) {
									flag=1;
									var error_html = '';

									error_html += Joomla.JText._('COM_JTICKETING_INVALID_FIELD') + ': ' + Joomla.JText._('COM_JTICKETING_INVALID_SEAT_COUNT_ERROR');
									Joomla.renderMessages({"warning":[error_html]});
									jQuery(available).focus();
									break;
								   }
								}
							i++;
						}

						var validData = document.formvalidator.isValid(document.getElementById('adminForm'));
						if (validData == true && flag == 0) {
							jtAdmin.event.showLocation();
							Joomla.submitform(task, document.getElementById('adminForm'));
						}
					}
				} else if (task == 'event.cancel') {
					Joomla.submitform(task, document.getElementById('adminForm'));
				} else {
					Joomla.submitform(task, document.getElementById('adminForm'));
				}

				jQuery('#toolbar-apply, #toolbar-save, #toolbar-save-new').removeClass('disabled');
			}
		},
		showLocation: function() {
			var venue = parseInt(jQuery('#jform_venue').val());
			var onlineOfflineVenues = jQuery("input[name='jform[online_events]']:checked").val();

			/** global: enableOnlineVenues */
			if (parseInt(enableOnlineVenues) == 0) {
				if (selectedVenue > 0 && venue === 0) {
					jQuery('#event-location').show();
					jQuery("#jform_location").prop('required', true);
					jQuery("#jform_location").addClass("required");
				} else if (selectedVenue > 0) {
					jQuery('#event-location').hide();
					jQuery("#jform_location").prop('required', false);
					jQuery("#jform_location").removeClass("required");
				} else if (venue == 0 || !venue) {
					jQuery('#event-location').show();
					jQuery("#jform_location").prop('required', true);
					jQuery("#jform_location").addClass("required");
				} else {
					jQuery('#event-location').hide();
					jQuery("#jform_location").prop('required', false);
					jQuery("#jform_location").removeClass("required");
				}
			} else {
				if (selectedVenue > 0 && !venue) {
					jQuery('#event-location').show();
					jQuery("#jform_location").prop('required', true);
					jQuery("#jform_location").addClass("required");
				} else if (venue == 0 && onlineOfflineVenues === "0") {
					jQuery('#event-location').show();
					jQuery("#jform_location").prop('required', true);
					jQuery("#jform_location").addClass("required");
				} else if (!venue && onlineOfflineVenues === "0") {
					var venuevalue = jQuery('#jform_venue').val();
					jQuery('#event-location').show();
					jQuery("#jform_location").prop('required', true);
					jQuery("#jform_location").addClass("required");
				} else {
					jQuery('#event-location').hide();
					jQuery("#jform_location").prop('required', false);
					jQuery("#jform_location").removeClass("required");
				}
			}
		},
		showTicketCapacity: function() {
			var venue = parseInt(jQuery('#jform_venue').val());
			techjoomla.jQuery('.ticketCapacityNote').addClass('af-d-none');
			techjoomla.jQuery( ".unlimited-seats option[value='1']").prop("disabled", false);
			jQuery(".unlimited-seats").trigger("liszt:updated");
        	jQuery(".unlimited-seats").trigger("chosen:updated");
			
			if (venue) {
				var requestObject = {};
				requestObject["venue"] = venue;
				JSON.stringify(requestObject);
				jQuery.ajax({
					type: "POST",
					dataType: "json",
					data: requestObject,
					url: Joomla.getOptions('system.paths').base + "/index.php?option=com_jticketing&format=json&task=venue.getCapacity",
					success: function(result) {
						if (result) {
							if (result.data && result.data.seats_capacity == 0) {
								techjoomla.jQuery(".unlimited-seats").each( function() {
									if(!techjoomla.jQuery(this).val()) {
										techjoomla.jQuery(this).val('');
									}
								});
								// techjoomla.jQuery(".unlimited-seats").not(".unlimited-seats option[value='1']").val('');
								techjoomla.jQuery(".unlimited-seats option[value='1']").prop("disabled", true);
								jQuery(".unlimited-seats").trigger("liszt:updated");
        						jQuery(".unlimited-seats").trigger("chosen:updated");
								techjoomla.jQuery('.ticketCapacityNote').removeClass('af-d-none');
								techjoomla.jQuery('.ticketCapacityNote').html(Joomla.JText._('COM_JTICKETING_TICKET_CAPACITY_ALERT') + result.data.capacity_count);
								selectedVenueDetails = result.data;
							} else {
								if (result.data == 1) {
									alert(result.message);
								}
								
								selectedVenueDetails = '';
							}
						}
					},
				});
			} else {
				selectedVenueDetails = '';
			}
		},
		showTicketAvailability: function() {
			var venue = jQuery('#jform_venue').val() ? parseInt(jQuery('#jform_venue').val()) : 0;
			
			if (venue && selectedVenueDetails) {
				techjoomla.jQuery(".unlimited-seats").each( function() {
					if(!techjoomla.jQuery(this).val()) {
						techjoomla.jQuery(this).val('');
					}
				});
				// techjoomla.jQuery(".unlimited-seats").not(".unlimited-seats option[value='1']").val('');
				techjoomla.jQuery(".unlimited-seats option[value='1']").prop("disabled", true);
				jQuery(".unlimited-seats").trigger("liszt:updated");
				jQuery(".unlimited-seats").trigger("chosen:updated");
				techjoomla.jQuery('.ticketCapacityNote').removeClass('af-d-none');
				techjoomla.jQuery('.ticketCapacityNote').html(Joomla.JText._('COM_JTICKETING_TICKET_CAPACITY_ALERT') + selectedVenueDetails.capacity_count);
			}
		},
		checkUserEmail: function() {
			var user = document.getElementById('jform_created_by_id').value;
			var userObject = {};
			userObject["user"] = user;
			JSON.stringify(userObject);
			jQuery.ajax({
				type: "POST",
				dataType: "json",
				data: userObject,
				url: "index.php?option=com_jticketing&format=json&task=event.checkUserEmail",
				success: function(data) {
					jQuery('#warning_message').empty();
					if (data.check) {
						if (array_check == 1 || handle_transactions == 1) {
							jQuery("#warning_message").html('<div class="alert alert-warning">' +
								Joomla.JText._('COM_JTICKETING_PAYMENT_DETAILS_ERROR_MSG1') +
								'<a href= "index.php?option=com_tjvendors&view=vendor&layout=update&client=com_jticketing&vendor_id=' + data.vendor_id + '" target="_blank">' +
								Joomla.JText._('COM_JTICKETING_VENDOR_FORM_LINK') + '</a>' +
								Joomla.JText._('COM_JTICKETING_PAYMENT_DETAILS_ERROR_MSG2'));
						}

					}
				},
			});
		},

		getRoundedValue: function(value) {

			var errorMsg = '';

			jQuery.ajax({
				type: "POST",
				dataType: "json",
				data: {
					'price' : value,
				},
				async: false,
				url: "index.php?option=com_jticketing&format=json&task=event.getRoundedValue&price=" + value,
				success: function(data) {

					if (data.data != value) {
						roundedPrice = data.data;
						errorMsg = Joomla.JText._('COM_JTICKETING_VALIDATE_ROUNDED_PRICE').concat(roundedPrice);
					} else {
						return value;
					}
				},
			});

			return errorMsg;
		},
		getStartDate:function() {
            return document.getElementById('jform_eventstart_date').value + ' ' + document.getElementById('jform_start_time').value;
        },
        getEndDate: function() {
			let startDateStr = document.getElementById('jform_eventstart_date').value + ' ' + document.getElementById('jform_start_time').value;
			let repeatType = jQuery('input[name="jform[repeat_via]"]:checked').val();
			let recurringType = jQuery('input[name="jform[recurring_type]"]:checked').val();
			let repeatCount = parseInt(document.getElementById('jform_repeat_count')?.value, 10) - 1;
			let repeatUntil = document.getElementById('jform_repeat_until').value + ' ' + document.getElementById('jform_end_time').value;
			let repeatInterval = parseInt(document.getElementById('jform_repeat_interval')?.value, 10) || 0;
			let startDate = new Date(startDateStr);
			let endDate = new Date(startDate);
			if (repeatType === "rep_until") {
				return repeatUntil;
			} else if (repeatType === "rep_count" && repeatCount >= 0) {
				switch (recurringType) {
					case "Daily":
                if (repeatInterval === 0) {
                    endDate.setDate(startDate.getDate() + repeatCount);
                } else {
                    endDate.setDate(startDate.getDate() + (repeatCount * (repeatInterval + 1)));
                }
                break;

            case "Weekly":
                if (repeatInterval === 0) {
                    endDate.setDate(startDate.getDate() + (repeatCount * 7));
                } else {
                    endDate.setDate(startDate.getDate() + (repeatCount * ((repeatInterval + 1) * 7)));
                }
                break;

            case "Monthly":
                if (repeatInterval === 0) {
                    endDate.setMonth(startDate.getMonth() + repeatCount);
                } else {
                    endDate.setMonth(startDate.getMonth() + (repeatCount * (repeatInterval + 1)));
                }
                break;

            case "Yearly":
                if (repeatInterval === 0) {
                    endDate.setFullYear(startDate.getFullYear() + repeatCount);
                } else {
                    endDate.setFullYear(startDate.getFullYear() + (repeatCount * (repeatInterval + 1)));
                }
                break;
					default:
						return document.getElementById('jform_eventend_date').value + ' ' + document.getElementById('jform_end_time').value;
				}
			} else {
				return document.getElementById('jform_eventend_date').value + ' ' + document.getElementById('jform_end_time').value;
			}
			return endDate.toISOString().split('T')[0] + ' ' + endDate.toTimeString().split(' ')[0];
		},
		venueDisplay: function() {
			return new Promise (function (resolve, reject) {
				var radioValue = jQuery("input[name='jform[online_events]']:checked").val();
				var eventStartDate = jtAdmin.event.getStartDate();
				var eventEndDate = jtAdmin.event.getEndDate();
				var created_by = document.getElementById('jform_created_by_id').value;
	
				var onlineOfflineVenues = jQuery("input[name='jform[online_events]']:checked").val();
				var selectedVenue = jQuery('#jform_venue').val();
	
				if (onlineOfflineVenues == '1') {
					jQuery('#event-location').hide();
					jQuery('#venuechoice_id').show();
					jQuery('#existingEvent').show();
				} else {
					jQuery('#venuechoice_id').hide();
					jQuery('#existingEvent').hide();
				}
	
				var userObject = {};
				userObject["radioValue"] = radioValue;
				userObject["eventStartTime"] = eventStartDate;
				userObject["eventEndTime"] = eventEndDate;
				userObject["created_by"] = created_by;
				userObject["eventId"] = eventId;
				userObject["venueId"] = venueId;
				jQuery('#jform_venue, .chzn-results').empty();
				JSON.stringify(userObject);
				jQuery.ajax({
					type: "POST",
					data: userObject,
					dataType: "json",
					url: "index.php?option=com_jticketing&format=json&task=event.getVenueList",
					success: function(data) {
						if (data == '' && eventId == 0 && radioValue == 1) {
							jQuery("#jform_venue").prop("disabled", true);
							var op = "<option value='0' selected='selected'>" + Joomla.JText._('COM_JTICKETING_NO_ONLINE_VENUE_ERROR') + "</option>";
							jQuery('#jform_venue').append(op);
							jQuery("#jform_venue").trigger("liszt:updated");
							jQuery('#jform_venue').trigger("chosen:updated");
							var error_html = '';
							error_html += "<br />" + Joomla.JText._('COM_JTICKETING_NO_VENUE_ERROR_MSG');
							Joomla.renderMessages({"warning":[error_html]});
						} else {
							if (eventId != 0) {
								var onlineOfflineVenues = jQuery("input[name='jform[online_events]']:checked").val();
	
								if (onlineOfflineVenues == 1) {
									jQuery("#jform_venue").prop("disabled", true);
	
								}
	
								if (venueId == 0) {
									jQuery('#jform_venue, .chzn-results').empty();
									venueName = Joomla.JText._('COM_JTICKETING_CUSTOM_LOCATION');
								}
								var op = "<option value='" + venueId + "' selected='selected'>" + venueName + " </option>";
								jQuery('#jform_venue').append(op);
								jQuery("#jform_venue").trigger("liszt:updated");
								jQuery('#jform_venue').trigger("chosen:updated");
								for (index = 0; index < data.length; ++index) {
									var op = "<option value='" + data[index].value + "' > " + data[index]['text'] + "</option>";
									jQuery('#jform_venue').append(op);
									jQuery("#jform_venue").trigger("liszt:updated");
									jQuery('#jform_venue').trigger("chosen:updated");
								}
							} else {
								jQuery("#jform_venue").prop("disabled", false);
								var op = "<option value='' selected='selected'>" + Joomla.JText._('COM_JTICKETING_FORM_EVENT_DEFAULT_VENUE_OPTION') + "</option>";
								jQuery('#jform_venue').append(op);
								jQuery("#jform_venue").trigger("liszt:updated");
								jQuery('#jform_venue').trigger("chosen:updated");
	
								for (index = 0; index < data.length; ++index) {
									var op = "<option value='" + data[index].value + "' > " + data[index]['text'] + "</option>";
									jQuery('#jform_venue').append(op);
									jQuery("#jform_venue").trigger("liszt:updated");
									jQuery('#jform_venue').trigger("chosen:updated");
								}
							}
						}

						resolve();
					},
				});
			});
		},
		ConvertTimeformat: function(date, hours, minutes, amPm) {
			hours = parseInt(hours);
			minutes = parseInt(minutes);
			if (amPm == "PM" && hours < 12) hours = hours + 12;
			if (amPm == "AM" && hours == 12) hours = hours - 12;
			if (hours < 10) hours = "0" + hours;
			if (minutes < 10) minutes = "0" + minutes;
			var time = date + " " + hours + ":" + minutes + ":00";
			var utcDateTime = new Date(time).toISOString();
			utcDateTime = utcDateTime.substring(0, utcDateTime.length - 5);
			var formattedUtcDateTime = utcDateTime.replace("T", " ");
			return formattedUtcDateTime;
		},
		/* To hide and show existing events on load*/
		existingEvents: function() {
			var radioValue = jQuery("input[name='jform[online_events]']:checked").val();
			var existingEventChoice = jQuery("input[name='jform[venuechoice]']:checked").val();
			if (existingEventChoice == 'new' && radioValue == '1') {
				jQuery('#form_existingEvent').hide();
			} else {
				jQuery('#form_existingEvent').show();
			}
		},
		selectExistingEvent: function() {
			var venueId = document.getElementById("jform_venue").value;
			var venuestatus = jQuery('input[type=radio][name="jform[online_events]"]:checked').val();

			jQuery('#jform_existing_event option').remove();
			var eventDropdown = jQuery("#jform_existing_event");
			var op = "<option value='' selected='selected'>" + Joomla.JText._('COM_JTICKETING_FORM_SELECT_EXISTING_EVENT_OPTION') + "</option>";
			eventDropdown.append(op);

			if (venueId != '0' && venuestatus == '1') {
				/** global: JTicketing */
				JTicketing.Ajax({
					url: Joomla.getOptions('system.paths').base + '/index.php?option=com_jticketing',
					data: {
						task: "event.getAllMeetings",
						'venueId': venueId
					}
				}).done(function(result) {
					if (result.success === true) {
						var eventList = result.data;
						if (eventList !== undefined && eventList !== null && Array.isArray(eventList)) {

							eventList.forEach(function(event) {

								if (existing_url == event.id) {
									op = "<option value='" + event.id + "' selected='selected'>" + event.title + " (" + event.start_time + ")" + "</option>";
								} else {
									op = "<option value='" + event.id + "' >" + event.title + " (" + event.start_time + ")" + "</option>";
								}
								eventDropdown.append(op);
							});
						}
					} else {
						//                        Joomla.renderMessages({
						//                            'error': [result.message]
						//                        });
					}
				}).fail(function(content) {
					Joomla.renderMessages({
						'error': [Joomla.JText._('COM_JTICKETING_EVENT_RELATED_AJAX_FAIL_ERROR_MESSAGE')]
					});
				}).always(function() {
					eventDropdown.trigger("liszt:updated");
					eventDropdown.trigger("chosen:updated");
				});
			}
		},
		selectExistingEventOnload: function() {
			if (existing_url) {
				return;
			}

			var venuestatus = jQuery('input[type=radio][name="jform[online_events]"]:checked').val();
			if (venueId != '0' && venuestatus == '1') {
				jQuery('#jform_existing_event option').remove();
				var eventDropdown = jQuery("#jform_existing_event");
				var op = "<option value='' selected='selected'>" + Joomla.JText._('COM_JTICKETING_FORM_SELECT_EXISTING_EVENT_OPTION') + "</option>";
				eventDropdown.append(op);

				/** global: JTicketing */
				JTicketing.Ajax({
					url: Joomla.getOptions('system.paths').base + '/index.php?option=com_jticketing',
					data: {
						task: "event.getAllMeetings",
						'venueId': venueId
					}
				}).done(function(result) {
					if (result.success === true) {
						var eventList = result.data;
						if (eventList !== undefined && eventList !== null && Array.isArray(eventList)) {
							eventList.forEach(function(event) {
								if (existing_url == event.id) {
									op = "<option value='" + event.id + "' selected='selected'>" + event.title + " (" + event.start_time + ")" + "</option>";
								} else {
									op = "<option value='" + event.id + "' >" + event.title + " (" + event.start_time + ")" + "</option>";
								}
								eventDropdown.append(op);
							});
						}
					} else {
						//                        Joomla.renderMessages({
						//                            'error': [result.message]
						//                        });
					}
				}).fail(function(content) {
					Joomla.renderMessages({
						'error': [Joomla.JText._('COM_JTICKETING_EVENT_RELATED_AJAX_FAIL_ERROR_MESSAGE')]
					});
				}).always(function() {
					eventDropdown.trigger("liszt:updated");
					eventDropdown.trigger("chosen:updated");
				});
			}
		},
		existingEventSelection: function() {
			var venueId = document.getElementById("jform_venue").value;
			var venueurl = jQuery('#jform_existing_event :selected').val();
			jQuery("#event_url").val(venueurl);
			jQuery.ajax({
				type: 'POST',
				async: false,
				data: {
					'venueId': venueId,
					'venueurl': venueurl
				},
				dataType: 'json',
				url: 'index.php?option=com_jticketing&format=json&task=event.getScoID',
				success: function(data) {
					jQuery("#event_sco_id").val(data);
				},
				error: function(response) {
					// show ckout error msg
					console.log(' ERROR!!');
					return;
				}
			});
		},
		validateDates: function() {
			var event_start_date_old = jQuery('#jform_startdate').val();
			var event_end_date_old = jQuery('#jform_enddate').val();
			var booking_start_date_old = jQuery('#jform_booking_start_date').val();
			var booking_end_date_old = jQuery('#jform_booking_end_date').val();
			return true;
		}
	},
	orders: {
		/*Initialize orders js*/
		initOrdersJs: function() {
			Joomla.submitbutton = function(task) {
				if (task == 'orders.remove') {
					var result = confirm(Joomla.JText._('COM_JTICKETING_ORDER_DELETE_CONF'));

					if (result != true) {
						return false;
					}

					Joomla.submitform(task, document.getElementById('adminForm'));
				}
			}
		},
		selectStatusOrder: function(appid, processor, ele) {
			document.getElementById('order_id').value = appid;
			document.getElementById('payment_status').value = ele.value;
			document.getElementById('processor').value = processor;
			var selInd = ele.selectedIndex;
			var status = ele.options[selInd].value;
			var orderStatusvalue = '';
			var task = 'orders.save';

			// Generate order status language constant values
			if (status === 'RF') {
				orderStatusvalue = 'REFUND';
			}

			if (status === 'E') {
				orderStatusvalue = 'FAILED';
			}

			if (status === 'D') {
				orderStatusvalue = 'DECLINE';
			}

			if (status === 'CRV') {
				orderStatusvalue = 'CANCEL_REVERSED';
			}

			if (status === 'RV') {
				orderStatusvalue = 'REVERSED';
			}

			// Enter only if the order status is set to refund or failed or denied or cancel reversed
			if (status === 'RF' || status === 'E' || status === 'D' || status === 'CRV' || status === 'RV') {
				// Generate order status language constant
				var orderStatusLanguageConstant = 'COM_JTICKETING_ORDER_STATUS_' + orderStatusvalue;

				// Order status message to be displayed on the pop up
				var orderStatusMessage = Joomla.JText._('COM_JTICKETING_ORDER_STATUS_MESSAGE1') + Joomla.JText._(orderStatusLanguageConstant) + Joomla.JText._('COM_JTICKETING_ORDER_STATUS_MESSAGE2');

				var confirmation = confirm(orderStatusMessage);

				if (confirmation === true) {
					Joomla.submitform(task, document.getElementById('adminForm'));
				} else {
					var oldStatus = jQuery(ele).data('oldvalue');
					jQuery(ele).val(oldStatus)
					jQuery(ele).trigger('liszt:updated');
					jQuery(ele).trigger("chosen:updated");

					return false;
				}
			} else {
				Joomla.submitform(task, document.getElementById('adminForm'));
			}
		}
	},
	venue: {
		/* Google Map autosuggest  for location */
		initializeGMapSuggest: function() {
			input = document.getElementById('jform_address');
			var autocomplete = new google.maps.places.Autocomplete(input);
		},

		initVenueJs: function() {
			if (googleMapApiKey) {
				google.maps.event.addDomListener(window, 'load', jtAdmin.venue.initializeGMapSuggest);
			}

			jQuery(document).ready(function() {
				jQuery('input[name="jform[online]').click(function() {
					jtAdmin.venue.showOnlineOffline(jQuery('input[name="jform[online]"]:checked').val())
				});

				jtAdmin.venue.showOnlineOffline(jQuery('input[name="jform[online]"]:checked').val());

				jQuery('#jform_online_provider').change(function() {
					jtAdmin.venue.getPluginParams();
				});

				jQuery.each(mediaGallery, function(key, media) {
					tjMediaFile.previewFile(media, 1);
				});

				if (editId && jQuery('input[name="jform[online]"]:checked').val() == 1) {
					jtAdmin.venue.getPluginParams();
				}

				if (getValue) {
					jQuery('#jform_online_provider').trigger('change');
				}

				jtAdmin.venue.venueCapacityCheck();
				jQuery("#jform_seats_capacity").change(function() {
					jtAdmin.venue.venueCapacityCheck();
				});
			});
		},
		showOnlineOffline: function(ifonline) {
			if (ifonline == 1) {
				jQuery("#jform_online_provider").closest(".control-group").show();
				jQuery("#provider_html").show();
				jQuery("#jform_offline_provider").hide();
			} else {
				jQuery("#jform_online_provider").closest(".control-group").hide();
				jQuery("#provider_html").hide();
				jQuery("#jform_offline_provider").show();
			}
		},
		venueCapacityCheck: function() {
			if (jQuery('#jform_seats_capacity').val() == 0)
			{
				jQuery('#jform_capacity_count').addClass('required');
			} else {
				jQuery('#jform_capacity_count').removeClass('required');
			}
		},
		venueSubmitButton: function(task) {
			if (task == 'venue.apply' || task == 'venue.save' || task == 'venue.save2new') {
				var venue_name = jQuery('input[name="jform[jform_name]"]:checked').val();
				var api_username = jQuery('input[name="jform[api_username]"]:checked').val();
				var api_password = jQuery('input[name="jform[api_password]"]:checked').val();
				var host_url = jQuery('input[name="jform[host_url]"]:checked').val();
				var source_sco_id = jQuery('input[name="jform[source_sco_id]"]:checked').val();
				var onlines = jQuery('input[name="jform[online]"]:checked').val();
				var onlineProvider = jQuery('#jform_online_provider').val();
				if (editId && onlines == "0") {
					jQuery('#api_username').val('');
					jQuery('#api_password').val('');
					jQuery('#host_url').val('');
					jQuery('#source_sco_id').val('');
				}

				if (!document.formvalidator.isValid(document.getElementById('venue-form'))) {
					jQuery('html, body').animate({
						'scrollTop' : jQuery("#system-message-container").position().top
					});
					return false;
				}
				if (jQuery('input[name="jform[online]"]:checked').val() == 1) {
					if (!onlineProvider || onlineProvider == '0') {
						error_html = Joomla.JText._('COM_JTICKETING_INVALID_FIELD') + Joomla.JText._('COM_JTICKETING_ONLINE_EVENTS_PROVIDER')
						Joomla.renderMessages({"warning":[error_html]});

						return false;
					}

					let providerHtmlForm = document.createElement("form");

					fields = jQuery("#provider_html").find('input, textarea, select, fieldset');
					for (i = 0, l = fields.length; i < l; i++) {
						let element = fields[i].cloneNode(true);
						providerHtmlForm.appendChild(element);
					}

					if (!document.formvalidator.isValid(providerHtmlForm)) {
						return false;
					}

					jsonObj = [];
					jQuery('#provider_html input').each(function() {
						var id = jQuery(this).attr("id");
						var output = jQuery(this).val();
						item = {}
						item["id"] = id;
						item["output"] = output;
						var source = jsonObj.push(item);
						jsonString = JSON.stringify(item);
						jQuery("#venue_params").val(jsonString);
					});
				} else {
					if (!jQuery("#jform_address").val()) {
						var error_html = '';
						if (!jQuery("#jform_address").val()) {
							error_html += "<br />" + Joomla.JText._('COM_JTICKETING_INVALID_FIELD') + Joomla.JText._('COM_JTICKETING_FORM_LBL_VENUE_ADDRESS');
						}
						Joomla.renderMessages({"warning":[error_html]});

						return false;
					}
				}
				if (!jQuery("#jform_name").val()) {
					var error_html = '';
					error_html += "<br />" + Joomla.JText._('COM_JTICKETING_INVALID_FIELD') + Joomla.JText._('COM_JTICKETING_FORM_LBL_VENUE_TITLE');
					Joomla.renderMessages({"warning":[error_html]});

					return false;
				}
				jQuery("#system-message-container").html("");
				Joomla.submitform(task, document.getElementById('venue-form'));
			}
			if (task == 'venue.cancel') {
				Joomla.submitform(task, document.getElementById('venue-form'));
			}
		},
		getPluginParams: function() {
			var element = jQuery('#jform_online_provider').val();
			jQuery.ajax({
				type: 'POST',
				url: 'index.php?option=com_jticketing&task=venue.getelementparams',
				data: {
					element: element,
					venue_id: jQuery("#venue_id").val()
				},
				datatype: "HTML",
				async: 'false',
				success: function(response) {
					jQuery('#provider_html').html(JSON.parse(response).data);
					var online = jQuery('input[name="jform[online]"]:checked').val();
					jQuery('#provider_html').css('display', 'none');
					if (online == 1) {
						jQuery('#provider_html').css('display', 'block');
						jQuery('#provider_html').trigger('subform-row-add', jQuery('#provider_html'));
					}

					// Make view password field button working
					var passwordSelection = document.getElementsByClassName("input-password-toggle");

					for(var i = 0; i < passwordSelection.length; i++) {
						(function(index) {
							passwordSelection[index].addEventListener("click", function() {
								if (jQuery(this).prev("input").attr("type") == "password")
								{
									jQuery(this).prev("input").attr("type", "text");
								}
								else
								{
									jQuery(this).prev("input").attr("type", "password");
								}
							})
						})(i);
					}
				},
				error: function() {
					jQuery('#provider_html').hide();
					return true;
				},
			});

			// Google Map autosuggest  for location
			function initializeGoogleMap() {
				input = document.getElementById('jform_address');
				var autocomplete = new google.maps.places.Autocomplete(input);
			}

			if (googleMapApiKey) {
				google.maps.event.addDomListener(window, 'load', initializeGoogleMap);
			}
		},
		// Function : For finding longitude latitude of selected address
		getLongitudeLatitude: function() {
			if (googleMapApiKey) {
				var geocoder = new google.maps.Geocoder();
				function calculateCoordinates() {
				var address = jQuery('#jform_address').val();
				geocoder.geocode({
					'address': address
				}, function(results, status) {
						if (status == google.maps.GeocoderStatus.OK)
						{
							var latitude = results[0].geometry.location.lat();
							var longitude = results[0].geometry.location.lng();
							jQuery('#jform_latitude').val(latitude);
							jQuery('#jform_longitude').val(longitude);
						}
					});
				}

				// Trigger calculation on keyboard input (e.g., Enter key)
				jQuery('#jform_address').on('keyup', function(e) {
					if (e.key === "Enter") {
						calculateCoordinates();
					}
				});
				// Trigger calculation on mouse click or field blur
				jQuery('#jform_address').on('blur', function() {
					calculateCoordinates();
				});
			}
		},
		// Function : For Get Current Location
		getCurrentLocation: function() {
			if (navigator.geolocation) {
				navigator.geolocation.getCurrentPosition(showLocation);
			} else {
				var address = Joomla.JText._('COM_JTICKETING_ADDRESS_NOT_FOUND');
				var lonlatval = Joomla.JText._('COM_JTICKETING_LONG_LAT_VAL');
				jQuery('#jform_address').val(address);
				jQuery("#jform_longitude").val(lonlatval);
				jQuery("#jform_latitude").val(lonlatval);
			}
			// Function : For Showing user current location
			function showLocation(position) {
				var latitude = position.coords.latitude;
				var longitude = position.coords.longitude;
				jQuery.ajax({
					type: 'POST',
					url: 'index.php?option=com_jticketing&task=venue.getLocation',
					data: 'latitude=' + latitude + '&longitude=' + longitude,
					dataType: 'json',
					success: function(data) {
						console.log(data);
						var address = data["location"];
						var longitude = data["longitude"];
						var latitude = data["latitude"];
						if (data) {
							jQuery("#jform_address").val(address);
							jQuery("#jform_longitude").val(longitude);
							jQuery("#jform_latitude").val(latitude);
						}
					}
				});
			}
		}
	},

	coupon: {
		initCouponJs: function() {
			jQuery(document).ready(function() {
				jQuery("#jform_vendor_id").change(function() {
					jtAdmin.coupon.updateVendorEventsList(this);
				});
				jQuery("#jform_value").change(function() {
					validation.checkForZeroAndAlpha(this, '46', Joomla.JText._('COM_JTICKETING_ENTER_NUMERICS'));

					if (jQuery('#coupon-form input[name="jform[val_type]"]:checked').length && jQuery('#coupon-form input[name="jform[val_type]"]:checked').val() == '1')
					{
						if (jQuery("#jform_value").val() && parseInt(jQuery("#jform_value").val()) > 100)
						{
							alert(Joomla.JText._('COM_JTICKETING_COUPON_PERCENTAGE_ERROR'));
							jQuery("#jform_value").val('');
						}
					}
				});

				var ticketsContainer = jQuery("#group_discount_tickets_container");

				if (jQuery("input[name='jform[group_discount]']:checked").val() !== "1") {
					ticketsContainer.addClass("d-none");
				}

				jQuery('input[name="jform[group_discount]"]').change(function() {
					ticketsContainer.toggleClass("d-none", jQuery(this).val() !== "1");
				});

				jQuery('#coupon-form input[name="jform[val_type]').change(function() {
					if (jQuery('#coupon-form input[name="jform[val_type]"]:checked').length && jQuery('#coupon-form input[name="jform[val_type]"]:checked').val() == '1')
					{
						if (jQuery("#jform_value").val() && parseInt(jQuery("#jform_value").val()) > 100)
						{
							alert(Joomla.JText._('COM_JTICKETING_COUPON_PERCENTAGE_ERROR'));
							jQuery("#jform_value").val('');
						}
					}
				});

				jQuery("#jform_limit").change(function() {
					validation.checkForZeroAndAlpha(this, '46', Joomla.JText._('COM_JTICKETING_ENTER_NUMERICS'));
				});

				jQuery("#jform_max_per_user").change(function() {
					validation.checkForZeroAndAlpha(this, '46', Joomla.JText._('COM_JTICKETING_ENTER_NUMERICS'));
				});
			});
		},

		couponSubmitButton: function(task) {
			if (jQuery('#coupon-form input[name="jform[val_type]"]:checked').length && jQuery('#coupon-form input[name="jform[val_type]"]:checked').val() == '1')
				{
				if (jQuery("#jform_value").val() && parseInt(jQuery("#jform_value").val()) > 100)
				{
					alert(Joomla.JText._('COM_JTICKETING_COUPON_PERCENTAGE_ERROR'));
					jQuery("#jform_value").val('');

					return false;
				}
			}

			if (task == 'coupon.cancel') {
				Joomla.submitform(task, document.getElementById('coupon-form'));
			} else {
				if (task != 'coupon.cancel' && document.formvalidator.isValid(document.getElementById('coupon-form'))) {
					Joomla.submitform(task, document.getElementById('coupon-form'));
				}
			}
		},

		updateVendorEventsList: function(element) {
			var vendorId = element.value;

			jQuery.ajax({
				/** global: jtRootURL */
				url: Joomla.getOptions('system.paths').base + "/index.php?option=com_jticketing&task=coupons.getVendorSpecificEvents",
				type: "GET",
				data: 'vendorId=' + vendorId,
				dataType: "json",
				success: function(data) {

					if (data.error !== 1 || data !== null) {
						jQuery('#jform_event_ids').find('option').remove();
						jQuery("#jform_event_ids").trigger('liszt:updated');
						jQuery('#jform_event_ids').trigger("chosen:updated");

                        if (jQuery('.sa-coupon.bs5').length)
                        {
                            if (Object.keys(data.events).length !== 0) {
                                var selectJFormEventIds = techjoomla.jQuery("#jform_event_ids");
                                techjoomla.jQuery.each(data.events, function(index, item) {
                                    console.log("ID:", item.id, "Title:", item.title);
                                    selectJFormEventIds.append(techjoomla.jQuery('<option>', {
                                        value: item['id'],
                                        text: item['title']
                                    }));
                                });
                            } else {
                                alert(Joomla.JText._('COM_JTICKETING_COUPON_NO_EVENT_FOUND'));
                            }
                        }
                        else
                        {
                            if (data.events.length !== 0) {
                                for (var i = 0; i < data.events.length; i++) {
                                    /** global: Option */
                                    jQuery("#jform_event_ids").append(new Option(data.events[i]['title'], data.events[i]['id']));
                                    jQuery("#jform_event_ids").trigger('liszt:updated');
                                    jQuery('#jform_event_ids').trigger("chosen:updated");
                                }
                            } else {
                                alert(Joomla.JText._('COM_JTICKETING_COUPON_NO_EVENT_FOUND'));
                            }
                        }
					}
				}
			});
		}
	}
}

// To show online/offline venue
function showOnlineOffline(ifonline) {
	if (ifonline == 1) {
		jQuery("#jform_online_provider").closest(".control-group").show();
		jQuery("#provider_html").show();
		jQuery("#jform_offline_provider").hide();
	} else {
		jQuery("#jform_online_provider").closest(".control-group").hide();
		jQuery("#provider_html").hide();
		jQuery("#jform_offline_provider").show();
	}
}

// To show online/offline venue
function validateOnlineOffline(ifonline) {
	if (ifonline == 1) {
		jQuery("#jform_online_provider").closest(".control-group").show();
		jQuery("#provider_html").show();
		jQuery("#jform_offline_provider").hide();
		jQuery('#jform_address').removeAttr("required");
		jQuery('#jform_address').removeClass("required");
	} else {
		jQuery("#jform_online_provider").closest(".control-group").hide();
		jQuery("#provider_html").hide();
		jQuery("#jform_offline_provider").show();
		jQuery('#api_username').removeAttr("required");
		jQuery('#api_username').removeClass("required");
		jQuery('#host_url').removeAttr("required");
		jQuery('#host_url').removeClass("required");
		jQuery('#api_password').removeAttr("required");
		jQuery('#api_password').removeClass("required");
		jQuery('#source_sco_id').removeAttr("required");
		jQuery('#source_sco_id').removeClass("required");
	}
}

var tjMediaFile = {
	validateFile: function(thisFile, isGallary, isAdmin, task) {
		if (!(thisFile instanceof jQuery)){
			thisFile = jQuery(thisFile);
		}

		var uploadType = jQuery(thisFile).attr('type');

		if (uploadType == 'file') {
			var uploadedfile = jQuery(thisFile)[0].files[0];

			if (mediaSize < (uploadedfile.size / 1000000)) {
				alert(Joomla.JText._('COM_TJMEDIA_VALIDATE_MEDIA_SIZE'));

				return false;
			}

			tjMediaFile.uploadFile(uploadedfile, thisFile, uploadType, isGallary, isAdmin, task);

		} else {
			fileLink = jQuery('#jform_gallery_link').val();
			tjMediaFile.uploadFile(fileLink, thisFile, 'link', isGallary, isAdmin, task);
		}
	},

	uploadFile: function(uploadedfile, thisFile, uploadType, isGallary, isAdmin, task) {
		var mediaformData = new FormData();

		if (uploadType == 'file') {
			mediaformData.append('file[]', uploadedfile);
			mediaformData.append('upload_file', uploadType);
			mediaformData.append('isGallary', isGallary);
		} else if (uploadType == 'link') {
			mediaformData.append('upload_type', uploadType);
			mediaformData.append('name', uploadedfile);
			mediaformData.append('type', 'youtube');
		}

		if (isAdmin != 0) {
			url = "index.php?option=com_jticketing&format=json&task=" + task + ".uploadMedia";
		} else {
			url = Joomla.getOptions('system.paths').base + "/index.php?option=com_jticketing&format=json&task=" + task + ".uploadMedia";
		}

		this.ajaxObj = jQuery.ajax({
			type: "POST",
			url: url,
			dataType: 'JSON',
			contentType: false,
			processData: false,
			data: mediaformData,
			xhr: function() {
				var myXhr = jQuery.ajaxSettings.xhr();

				if (myXhr.upload) {
					myXhr.upload.addEventListener('progress', function(e) {

						if (e.lengthComputable) {
							var percentage = Math.floor((e.loaded / e.total) * 100);
							tjMediaFile.progressBar.updateStatus(thisFile.id, percentage);
						}
					}, false);
				}

				return myXhr;
			},

			beforeSend: function(x) {
				tjMediaFile.progressBar.init(thisFile.id, '');
			},

			success: function(data) {
				jQuery('#system-message-container').empty();

				if (data.success) {
					if (isGallary === 1) {
						if (data.data[0].type == 'video.youtube' || data.data[0].valid === 0) {
							jQuery('#jform_gallery_link').val('');
						} else {
							jQuery(thisFile).val('');
						}
					}

					tjMediaFile.previewFile(data.data[0], isGallary);
					tjMediaFile.progressBar.statusMsg(thisFile.id, 'success', data.message);
				} else if (uploadType == 'link') {
					var jmsgs = [data.message];
					Joomla.renderMessages({
						'warning': jmsgs
					});
				} else {
					jQuery(thisFile).val('');
					jQuery('#' + thisFile.id).siblings('.progress').remove();
					tjMediaFile.progressBar.statusMsg(thisFile.id, 'error', data.message);
					alert(data.message);
				}
			},

			error: function(xhr, status, error) {
				tjMediaFile.progressBar.statusMsg(thisFile.id, 'error', error);
			}
		});
	},
	previewFile: function(data, isGallary) {
		if (data.id) {
			if (isGallary == 1) {
				tjMediaFile.tjMediaGallery.appendMediaToGallary(data);
			 } else if (isGallary == 2 )      //Check whether the uploaded image is a cover image
			{
				jQuery('#uploaded_media_cover').attr('src', data[eventMainImage]);
				jQuery('#uploaded_media_cover').closest('.thumbnails').removeClass('hide_jtdiv');
				jQuery('#jform_event_cover_old_image').val(jQuery('#jform_event_coverImage').val());
				jQuery('#jform_event_coverImage').val(data.id);
			}
			else {
				jQuery('#uploaded_media').attr('src', data[eventMainImage]);
				jQuery('#uploaded_media').closest('.thumbnails').removeClass('hide_jtdiv');
				jQuery('#jform_event_old_image').val(jQuery('#jform_event_image').val());
				jQuery('#jform_event_image').val(data.id);
			}
		}

		return false;
	},

	progressBar: {
		init: function(divId, msg) {
			jQuery('#' + divId).siblings('.alert').remove();
			jQuery('#' + divId).siblings('.progress').remove();
			this.progress = jQuery("<div class='progress progress-striped active'><div class='bar'></div><button onclick='return tjexport.abort();' class='btn btn-danger btn-small pull-right'>Abort</button></div>");
			this.statusBar = this.progress.find('.bar');
			this.abort = jQuery("<div class='abort'><span>Abort</span></div>").appendTo(this.statusbar);
			jQuery('#' + divId).closest('.controls').append(this.progress);
		},

		updateStatus: function(divId, percentage) {
			this.statusBar.css("width", percentage + '%');
			this.statusBar.text(percentage + '%');
		},

		abort: function() {
			if (!confirm(Joomla.JText._('LIB_TECHJOOMLA_CSV_EXPORT_CONFIRM_ABORT'))) {
				return false;
			}

			this.ajaxObj.abort();
		},

		statusMsg: function(divId, alert, msg) {
			setTimeout(function() {
				jQuery('#' + divId).siblings('.progress').remove();
			}, 2000);

			var closeBtn = "<a href='#' class='close' data-dismiss='alert' aria-label='close' title='close'>×</a>";
			var msgDiv = jQuery("<div class='alert alert-" + alert + "'><strong>" + msg + "</strong>" + closeBtn + "</div>");
			jQuery('#' + divId).closest('.controls').append(msgDiv);
		}
	},

	tjMediaGallery: {
		appendMediaToGallary: function(mediaData) {
			var $newMedia = jQuery('.gallary__media li.gallary__media--li:first-child').clone();
			var type = mediaData.type.split('.');

			if (type[0] === 'video') {
				var videoWidth = jQuery('.jt-event-frontend-edit').length ? '107' : '160';
				if (type[1] === 'youtube') {
					mediaTag = "<iframe width=' "+ videoWidth +"' height='113' src=" + mediaData.path + "> </iframe>";
				} else {
					mediaTag = "<video width=' "+ videoWidth +"' height='113' class='media_video_width' preload='metadata' controls ><source src=" + mediaData.path + "></video>";
				}

			} else if (type[0] === 'image') {
				mediaTag = "<img src=" + mediaData[galleryImage] + " class='media_image_width'>";
			}
			$newMedia.removeClass('hide');
			$newMedia.find('.thumbnail').append(mediaTag);
			$newMedia.find(".media_field_value").val(mediaData.id);
			$newMedia.find(".media_field_value").attr('id', 'media_id_' + mediaData.id);
			$newMedia.appendTo('.gallary__media');
		},

		deleteMedia: function(currentDiv, isAdmin, clientId, task) {
			var $currentDiv = jQuery(currentDiv);

			if (isAdmin == 1) {
				url = "index.php?option=com_jticketing&format=json&task=" + task + ".deleteMedia";
			} else {
				url = Joomla.getOptions('system.paths').base + "/index.php?option=com_jticketing&format=json&task=" + task + ".deleteMedia";
			}

			if (!confirm(Joomla.JText._('JGLOBAL_CONFIRM_DELETE'))) {
				return false;
			}

			var mediaId = $currentDiv.next().val();

			jQuery.ajax({
				type: "POST",
				url: url,
				dataType: 'JSON',
				data: {
					id: mediaId,
					client_id: clientId
				},
				success: function(data) {
					$currentDiv.closest('.gallary__media--li').remove();
				},
				error: function(xhr, status, error) {
					tjMediaFile.progressBar.statusMsg($currentDiv.id, 'error', error);
				}
			});
		}
	}
};

var attendees = {

	enrollmentStatusBar: {
		 // Create status bar for attendee import for event enrollment
		 createStatusbar: function (obj){
			 this.statusbar = jQuery("<div class='statusbar'></div>");
			 this.filename = jQuery("<div class='filename'></div>").appendTo(this.statusbar);
			 this.size = jQuery("<div class='filesize'></div>").appendTo(this.statusbar);
			 this.success = jQuery('<div class="msg alert"></div>').appendTo(this.statusbar);
			 this.error = jQuery('<div class="msg alert"></div>').appendTo(this.statusbar);
			 obj.closest('.controls').append(this.statusbar);

			 this.setFileNameSize = function(name,size)
			 {
				var sizeStr="";
				var sizeKB = size/1024;
				if(parseInt(sizeKB) > 1024)
				{
				   var sizeMB = sizeKB/1024;
				   sizeStr = sizeMB.toFixed(2)+" MB";
				}
				else
				{
				   sizeStr = sizeKB.toFixed(2)+" KB";
				}

				this.filename.html(name);
				this.size.html(sizeStr);
			 }

			this.setMsg = function(msg,classname)
			{
				this.statusbar.show();
				if(msg['errormsg'])
				{
				  var error = msg['errormsg'];
				  this.error.addClass("alert alert-error");
				  this.error.html(error);
				  this.error.show();
				}

				if(msg['successmsg'])
				{
				  var success = msg['successmsg'];
				  this.success.addClass("alert alert-success");
				  this.success.html(success);
				  this.success.show();
				}

				if(msg['messages'])
				{
					var $message = jQuery('<div>').addClass('import-messages');
					this.success.removeClass('msg alert');

					jQuery.each(msg['messages'], function(i,value){
						var key = Object.keys(value)[0];
						var curMessage = jQuery('<div>').addClass('alert alert-' + key).html(value[key]).get(0);
						$message.append(curMessage);
					});

					this.success.html($message);
					this.success.show();
	   			}
			}
		}
  	}
}

var jtCommon = {

	enrollment: {
		/*function validate import*/
		validate_import: function(thisfile, userImport, loaderId, csvType)
		{
			if (!jQuery(thisfile).val()) {
				return false;
			}
		   jQuery(thisfile).closest('.controls').children( ".statusbar" ).remove();

		   /* Hide all alerts msgs */
		   var obj = jQuery(thisfile);
		   var status = new attendees.enrollmentStatusBar.createStatusbar(obj); //Using this we can set progress.

		   /* Get uploaded file object */
		   var uploadedfile   =   jQuery(thisfile)[0].files[0];

		   // Attendee email nNotification config value
		   var notifyUser = jQuery("#notify_user_enroll").prop('checked');

		   /* Get uploaded file name */
		   var filename = uploadedfile.name;

		   /* pop out extension of file*/
		   var fileExt = filename.split('.').pop();

		   if (fileExt != 'csv')
		   {
			  var finalMsg =new Object();
			  finalMsg['errormsg'] = nonvalid_extension;
			  status.setMsg(finalMsg,'alert-error');

			  return false;
		   }

		   /* Show prgress bar */
		   jtCommon.enrollment.progressBar(loaderId, true);

		   /* IF evrything is correct so far, popolate file name in fileupload-preview*/

		   var file_name_container   =   jQuery(".fileupload-preview",jQuery(thisfile).closest('.fileupload-new'));

		   jQuery(file_name_container).show();
		   jQuery(file_name_container).text(filename);

		   jtCommon.enrollment.startImporting(uploadedfile, status, thisfile, userImport, loaderId, csvType, notifyUser);
		},

		progressBar: function(loaderId, action){
		   if (!loaderId) {
			  return false;
		   }

		   if (action == true) {
			  jQuery("<div class='control-group'><div class='progress-line'></div></div>").prependTo(loaderId);
		   }else
		   {
			  jQuery(loaderId + " .progress-line").remove();
		   }

		   jQuery(loaderId +" :input").prop("disabled", action);
		   jQuery(loaderId +" :button").prop("disabled", action);

		   return true;
		},

		startImporting: function(file, status, thisfile, userImport, loaderId, csvType, notifyUser){
		   var finalMsg = new Object();
		   if(file === undefined)
		   {
			  status.setMsg(file_not_selected_error, 'alert-error');
			  return false;
		   }

		   if(window.FormData !== undefined)  // for HTML5 browsers
		   {
			  if (userImport == 1)
			  {
				 var userImports = 1;
				 var newfilename = jtCommon.enrollment.sendImportFileToServer(file, status, thisfile, userImports, loaderId, notifyUser);
			  }

			  return false;

		   }
		   else  /*for older browsers*/
		   {
			  alert("You need to upgrade your browser as it does not support FormData");
		   }
		},

		sendImportFileToServer: function(file, status, thisfile, userImports, loaderId, notifyUser){
		   var formData = new FormData();
		   formData.append( 'FileInput', file );
		   formData.append( 'notifyUser', notifyUser );
		   var returnvar = true;

		   var jqXHR = jQuery.ajax({
			  url: 'index.php?option=com_jticketing&task=attendees.csvImport&tmpl=component',
			  type: 'POST',
			  data:  formData,
			  mimeType:"multipart/form-data",
			  contentType: false,
			  dataType:'json',
			  cache: false,
			  processData:false,
			  success: function(response)
			  {
				 var output = response['OUTPUT'];
				 var result   =   output['return'];
				 if(result == 1)
				 {
					status.setMsg(output);
					jQuery("#user-csv-upload").val('');
					jtCommon.enrollment.progressBar(loaderId, false);
				 }
			  },
			  error: function(jqXHR, textStatus = '', errorThrown = '')
			  {
				 var finalMsg = new Object();
				 finalMsg['errormsg'] = jqXHR.responseText;
				 status.setMsg(finalMsg,'alert-error');
				 jtCommon.enrollment.progressBar(loaderId, false);

				 returnvar   = false;
			  }
		   });

		   return returnvar;
		   status.setAbort(jqXHR);
		},

		updateEnrollment: function(elementId, id, task, isAdmin) {
			var contextValue = '';

			var notify = jQuery('#notify_user_' + elementId).is(':checked');
			var eid = jQuery('#eid_' + elementId).val();

			if (task === 'update') {
				contextValue = jQuery("#assign_" + elementId + " option:selected").val();
			} else if (task === 'moveAttendee') {
				contextValue = 'M';
			}

			if (contextValue === 'M') {
				jQuery('#move_attendee').modal('show');

				var ownerId = jQuery('#owner_' + elementId).val();
				jQuery('#userId').val(ownerId);
				jQuery('#eventId').val(eid);
				jQuery('#attendeeId').val(id);
				jQuery("#selected_event option[value='" + eid + "']").remove();
				jQuery("#selected_event").trigger("liszt:updated");
				jQuery('#selected_event').trigger("chosen:updated");
			} else {
				if (isAdmin === 1) {
					url = 'index.php?option=com_jticketing&task=attendees.' + task;
				} else {
					url = jtRootURL + 'index.php?option=com_jticketing&task=attendees.' + task;
				}

				jQuery('#ajax_loader')
							.html(
								"<img src=" +
								Joomla
								.getOptions('system.paths').base +
								"/media/com_jticketing/images/ajax-loader.gif>");
				jQuery('#ajax_loader').show();

				jQuery.ajax({
					url: url,
					type: 'POST',
					dataType: 'json',
					data: {
						id: id,
						value: contextValue,
						notify: notify,
						eid: eid
					},
					beforeSend: function() {
						jQuery('#ajax_loader')
							.html(
								"<img src=" +
								Joomla
								.getOptions('system.paths').base +
								"/media/com_jticketing/images/ajax-loader.gif>");
					},
					complete: function() {
						jQuery('#ajax_loader').hide();
					},
					success: function(result) {
						Joomla.renderMessages(result.messages);
						location.reload();
					},
				});
			}
		},

		saveEnrollment: function(task) {
			// Check for at least requirement
			if (document.adminForm.boxchecked.value == 0) {
				alert(Joomla.JText._('COM_JTICKETING_SELECT_USER_TO_ENROLL'));
				return false;
			}

			if (!jQuery('#selected_events').val()) {
				alert(Joomla.JText._('COM_JTICKETING_SELECT_EVENT_TO_ENROLL'));
				return false;
			}

			Joomla.submitform('enrollment.' + task);
		},

		deleteEnrollment: function(id) {
			if(confirm(Joomla.JText._('COM_JTICKETING_ARE_YOU_SURE_YOU_TO_DELETE_THE_ATTENDEE')))
			{
				url = Joomla.getOptions('system.paths').base + '/index.php?option=com_jticketing&task=attendees.remove';

				jQuery.ajax({
					url: url,
					type: 'POST',
					dataType: 'json',
					data: {
						id: id
					},
					beforeSend: function() {
						jQuery('#ajax_loader')
							.html(
								"<img src=" +
								Joomla
								.getOptions('system.paths').base +
								"/media/com_jticketing/images/ajax-loader.gif>");
					},
					complete: function() {
						jQuery('#ajax_loader').hide();
					},
					success: function(result) {
						alert(result.message)
						location.reload();
					},
					error: function(data) {
						console.log(data.message)
					},
				});
			}
		},
	},

	waitinglist: {
		changeStatus: function(elementId, ids) {

			var statusValue = jQuery("#assign_" + elementId + " option:selected").val();
			var eventId = jQuery('#event_id_' + elementId).val();
			var userId = jQuery('#user_id_' + elementId).val();

			if (statusValue == 'C') {
				document.getElementById('wid').value = ids;

				Joomla.submitform('waitinglist.enroll');
			} else if (statusValue == 'CA') {
				if (isAdmin === 1) {
					url = 'index.php?option=com_jticketing&format=json&task=waitlistform.changeStatus';
				} else {
					url = jtRootURL + 'index.php?option=com_jticketing&format=json&task=waitlistform.changeStatus';
				}

				jQuery.ajax({
					url: url,
					type: 'POST',
					dataType: 'json',
					data: {
						id: ids,
						status: statusValue,
						eventid: eventId,
						userid: userId
					},
					beforeSend: function() {
						jQuery('#ajax_loader').html("<img src=" + Joomla.getOptions('system.paths').base + "/media/com_jticketing/images/ajax-loader.gif>");
					},
					complete: function() {
						jQuery('#ajax_loader').hide();
					},
					success: function(result) {
						if (result.success) {
							var jmsgs = [result.message];
							Joomla.renderMessages({
								'success': jmsgs
							});
						} else {
							var msg = [result.message];
							Joomla.renderMessages({
								'error': msg
							});
						}
					},
				});
			}
		}
	},
	attendees: {
        attendeesSubmitButton: function(task,isAdmin) {

            // if task is empty then return
            if (task === '')
			{
				return false;
			}

            var emailIds = document.getElementById("selected_emailids");
            var messageSubject  = document.getElementById('jt-message-subject');
            var messageBody     = tinyMCE.activeEditor.getContent();
            var action          = task.split('.');

            // If task is not cancelEmail then validate messageSubject and messageBody
            if (action[1] != 'cancelEmail')
            {
                var message         = {};
                    message.error   = [];

                // Check messageSubject for empty value, If value is empty then display message
                if(messageSubject.value === '')
                {
					jQuery('#system-message-container').removeClass('span10');
					jQuery('#system-message-container').addClass('span12');
                    message.error.push(Joomla.JText._('COM_JTICKETING_EMAIL_SUBJECT_ERROR_MSG'));
                    Joomla.renderMessages(message);
                    return false;
                }

                // Check messageBody for empty value, If value is empty then display message
                if(messageBody === '')
                {
					jQuery('#system-message-container').removeClass('span10');
					jQuery('#system-message-container').addClass('span12');
                    message.error.push(Joomla.JText._('COM_JTICKETING_EMAIL_BODY_ERROR_MSG'));
                    Joomla.renderMessages(message);
                    return false;
                }
            }
            else
            {
                Joomla.submitform(task);
            }

            // Call ajax request to send the emails to selected attendees

            url = jtRootURL + 'index.php?option=com_jticketing&format=json&task='+task;

            jQuery.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                data: {
                    messageSubject: messageSubject.value,
                    messageBody: messageBody,
                    isAdmin:isAdmin,
                    emailIds:emailIds.value
                },
                beforeSend: function() {
                    document.getElementById('ajax_loader').style.backgroundImage="url("+ jtRootURL + "/media/com_jticketing/images/loadinfo.gif)";
                    jQuery('#ajax_loader').show();
                },
                complete: function() {
                    jQuery('#ajax_loader').hide();
                },
                success: function(result) {
                    if (result.success)
                    {
                        Joomla.renderMessages({
                            'success': [result.message]
                        });

                        // on success redirect to attendees view
                        window.onbeforeunload = null;
                        window.location.href = result.data;

                    }
                    else
                    {
                        Joomla.renderMessages({
                            'error': [result.message]
                        });
                    }
                },
            });
        }
    },
};

var jtCounter = {
	jtCountDown: function(divId, startDate, endDate, currentDate, isReverse) {
		var counterDate = startDate;
		if (isReverse) {
			counterDate = endDate;
		}
		jQuery('#' + divId).countdown(counterDate)
			.on('update.countdown', function(event) {
				var format = msg = '';
				if (event.offset.totalDays > 0) {
					msg = Joomla.JText._('JT_EVENT_COUNTER_STARTS_IN_DAYS');
					if (isReverse) {
						msg = Joomla.JText._('JT_EVENT_COUNTER_ENDS_IN_DAYS');
					}
					format = msg.replace("%s", "%-D");
				} else if (event.offset.totalDays == 0) {
					msg = Joomla.JText._('JT_EVENT_COUNTER_STARTS_IN_TIME');
					if (isReverse) {
						msg = Joomla.JText._('JT_EVENT_COUNTER_ENDS_IN_TIME');
					}
					format = msg.replace("%s", "%H:%M:%S");
				}
				jQuery(this).html(event.strftime(format));
			})
			.on('finish.countdown', function(event) {
				if (endDate > currentDate) {
					jtCounter.jtCountDown(divId, startDate, endDate, currentDate, '1');
				}

				if (endDate < currentDate) {
					jQuery(this).html(Joomla.JText._('JT_EVENT_COUNTER_EXPIRE'));
				}
			})
			.on('stoped.countdown', function(event) {
				jQuery(this).html(Joomla.JText._('JT_EVENT_COUNTER_EXPIRE'));
			});
	}
};

var validation = {

	positiveNumber: function() {
		jQuery(window).on('load', function() {
			document.formvalidator.setHandler('positive-number', function(value, element) {
				value = punycode.toASCII(value);
				var regex = /^[+]?([0-9]+(?:[\.][0-9]*)?|\.[0-9]+)$/;
				return regex.test(value);
			});
		});
	},
	/*Convert calendar date-format to SQL date-format*/
	formatDateTime: function(dateTime) {
		var year = dateTime.getFullYear();
		var month = '' + (dateTime.getMonth() + 1);
		var date = '' + dateTime.getDate();
		var hour = '' + dateTime.getHours();
		var minutes = '' + dateTime.getMinutes();
		var seconds = '' + dateTime.getSeconds();

		if (month.length < 2) {
			month = '0' + month;
		}

		if (date.length < 2) {
			date = '0' + date;
		}

		if (hour.length < 2) {
			hour = '0' + hour;
		}

		if (minutes.length < 2) {
			minutes = '0' + minutes;
		}

		if (seconds.length < 2) {
			seconds = '0' + seconds;
		}

		var formattedDateTime = year + '-' + month + '-' + date + ' ' + hour + ':' + minutes + ':' + seconds;

		return formattedDateTime;
	},

	checkForZeroAndAlpha: function(ele, allowedChar, msg) {
		if (ele.value <= 0) {
			alert(Joomla.JText._('COM_JTICKETING_MIN_AMT_SHOULD_GREATER_MSG'));
			ele.value = '';
		}
	}
};
