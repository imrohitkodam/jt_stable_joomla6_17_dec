/*
 * @version    SVN: <svn_id>
 * @package    Tjvendors
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
var tjVAdmin = {
	vendor: {
		/*For Read More/Read Less*/
		readMore: function() {
			var showChar = 300;
			var ellipsestext = "";
			var moretext = Joomla.Text._('COM_TJVENDOR_DESCRIPTION_READ_MORE');
			var lesstext = Joomla.Text._('COM_TJVENDOR_DESCRIPTION_READ_LESS');

			jQuery('.profile__content').each(function() {
				var content = jQuery(this).html();

				if (content.length > showChar) {
					var show_content = content.substr(0, showChar);
					var hide_content = content.substr(showChar, content.length - showChar);
					var html = show_content + '<span class="show">' + ellipsestext + '</span><span><span class="collapse">' + hide_content + '</span>&nbsp;&nbsp;<a href="" class="morelink">' + moretext + '</a></span>';
					jQuery(this).html(html);
				}
			});

			jQuery(".morelink").click(function() {
				if (jQuery(this).hasClass("less")) {
					jQuery(this).removeClass("less");
					jQuery(this).html(moretext);
				}
				else {
					jQuery(this).addClass("less");
					jQuery(this).html(lesstext);
				}

				jQuery(this).parent().prev().toggle();
				jQuery(this).prev().toggle();

				return false;
			});
		},

		/*Initialize event js*/
		initVendorJs: function() {
			jQuery(document).ready(function() {
				jQuery(document).on("change", "#jform_user_id", function() {
					tjVAdmin.vendor.checkVendor();
				});
				/** global: tjvendor */
				/** global: region */
				/** global: city */
				let CommonObj = new tjvendor.UI.CommonUI();
				CommonObj.generateStates('jform_country', 1, region, city);
				CommonObj.showOtherCity('jform_city', city);
			});

			jQuery(window).on('load', function(){
				tjCommon.vendorLogoValidation();
				tjCommon.initVendorFields();
			});

			Joomla.submitbutton = function(task) {
				if (task == 'vendor.apply' || task == 'vendor.save' || task == 'vendor.save2new') {
					var validData = document.formvalidator.isValid(document.getElementById('adminForm'));
					var username = document.getElementById("jform_user_id").value;

					if (username == '') {
						var jmsgs = [Joomla.Text._('COM_TJVENDOR_USER_ERROR')];
						Joomla.renderMessages({
							'warning': jmsgs
						});
					}
					else if (validData == true) {
						Joomla.submitform(task, document.getElementById('adminForm'));
					}
				}
				else if (task == 'vendor.cancel') {
					Joomla.submitform(task, document.getElementById('adminForm'));
				}
				else {
					Joomla.submitform(task, document.getElementById('adminForm'));
				}
			}
		},

		changePayoutStatus: function(payout_id, ele) {
			var paidUnpaid1 = document.getElementById('paidUnpaid').value;
			var userObject = {};
			userObject["payout_id"] = payout_id;
			userObject["paidUnpaid"] = jQuery(ele).val();

			JSON.stringify(userObject);
			jQuery.ajax({
				type: "POST",
				dataType: "json",
				data: userObject,
				url: "index.php?option=com_tjvendors&task=payout.changePayoutStatus",
				success: function(data) {
					if (data) {
						document.location = 'index.php?option=com_tjvendors&view=reports&client=' + client;
					}
				},
			});
		},

		checkVendor: function() {
			var user = document.getElementById('jform_user_id_id').value;
			var userObject = {};
			userObject["user"] = user;
			userObject["vendor_id"] = vendor_id;
			JSON.stringify(userObject);
			jQuery.ajax({
				type: "POST",
				dataType: "json",
				data: userObject,
				url: "index.php?option=com_tjvendors&task=vendor.checkDuplicateUser",
				success: function(data) {

					if (data) {
						if (layout === "update") {
							var jmsgs = [Joomla.Text._('COM_TJVENDOR_DUPLICARE_VENDOR_ERROR')];
							Joomla.renderMessages({
								'warning': jmsgs
							});

							return vendorCheck = "exists";
						}
						else {
							document.location = 'index.php?option=com_tjvendors&view=vendor&layout=edit&client=' + client + '&vendor_id=' + data.vendor_id;
						}
					}
				},
			});
		}
	},

	vendors: {
		vendorApprove: function(vendor_id, ele) {
			vendorApprove = jQuery(ele).val();
			var userObject = {};
			userObject["vendor_id"] = vendor_id;
			userObject["vendorApprove"] = vendorApprove;
			userObject["client"] = client;

			JSON.stringify(userObject);
			jQuery.ajax({
				type: "POST",
				dataType: "json",
				data: userObject,
				url: "index.php?option=com_tjvendors&task=vendor.vendorApprove",
				success: function(data) {
					alert(data);
					jQuery('#system-message-container').empty();
					if (vendorApprove == '1') {
						var jmsgs = [Joomla.Text._('COM_TJVENDOR_VENDOR_APPROVAL')];
						Joomla.renderMessages({
							'success': jmsgs
						});
					}
					else {
						jmsgs = [Joomla.Text._('COM_TJVENDOR_VENDOR_DENIAL')];
						Joomla.renderMessages({
							'success': jmsgs
						});
					}
				},
			});
		},
	},

	fee: {
		/*Initialize event js*/
		initFeeJs: function() {
			Joomla.submitbutton = function(task) {
				if (task == 'vendorfee.apply' || task == 'vendorfee.save') {
					var percent_commission = document.getElementById("jform_percent_commission").value;
					var flat_commission = document.getElementById("jform_flat_commission").value;

					if (percent_commission > 100) {
						var jmsgs = [Joomla.Text._('COM_TJVENDORS_FEES_PERCENT_ERROR')];
						Joomla.renderMessages({
							'error': jmsgs
						});
					}
					else if (percent_commission < 0 || flat_commission < 0) {
						var jmsgs = [Joomla.Text._('COM_TJVENDORS_FEES_NEGATIVE_NUMBER_ERROR')];
						Joomla.renderMessages({
							'error': jmsgs
						});
					}
					else {
						Joomla.submitform(task, document.getElementById('vendorfee-form'));
					}
				}
				else if (task == 'vendorfee.cancel') {
					Joomla.submitform(task, document.getElementById('vendorfee-form'));
				}
			}
		}
	},

	reports: {
		/*Initialize event js*/
		initReportsJs: function() {
			jQuery(document).ready(function() {
				jQuery("#dates").blur(function() {
					document.adminForm.submit();
				});
				jQuery("#date").blur(function() {
					document.adminForm.submit();
				});
			});
		},
	}
}
var tjVSite = {
	vendor: {
		/*Initialize event js*/
		initVendorJs: function() {
			jQuery(document).ready(function() {
				jQuery(document).on("change", "#jform_payment_gateway", function() {});
				/** global: tjvendor */
				let CommonObj = new tjvendor.UI.CommonUI();
				/** global: region */
				/** global: city */
				CommonObj.generateStates('jform_country', 1, region, city);
				CommonObj.showOtherCity('jform_city', city)
			});

			jQuery(document).ready(function() {
				tjCommon.vendorLogoValidation();
				tjCommon.initVendorFields();

				document.formvalidator.setHandler('urlformat', function (value) {
						var regex = new RegExp(
							'^(https?:\\/\\/)?' + // protocol
							'((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.?)+[a-z]{2,}|' + // domain name
							'((\\d{1,3}\\.){3}\\d{1,3}))' + // OR IP (v4) address
							'(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*' + // port and path
							'(\\?[;&a-z\\d%_.~+=-]*)?' + // query string
							'(\\#[-a-z\\d_]*)?$','i' // fragment locator
						);

						return regex.test(value);
				});
			});

			Joomla.submitbutton = function(task) {
				if (task == 'vendor.save') {
					var validData = document.formvalidator.isValid(document.getElementById('adminForm'));

					if (validData == true) {
						Joomla.submitform(task, document.getElementById('adminForm'));
					}
				}
				else if (task == 'vendor.cancel') {
					Joomla.submitform(task, document.getElementById('adminForm'));
				}
				else {
					Joomla.submitform(task, document.getElementById('adminForm'));
				}
			}
		}
	},

	vendors: {
		/*Initialize event js*/
		initVendorsJs: function() {
			jQuery(document).ready(function() {
				jQuery("#dates, #date").change(function() {
					jQuery('#adminForm').submit();
				});
			});
		},

		toggleDiv: function(spanId) {
			if (jQuery(window).width() < 767) {
				jQuery("#" + spanId).toggle("slow");
			}
			else {
				jQuery("#" + spanId).toggle();
			}
			jQuery(".report_search_input").toggleClass("active");
		},
	}
}

var tjCommon = {
	vendorLogoValidation: function() {
		jQuery("#jform_vendor_logo").change(function(e) {
			var file, img;
			if ((file = this.files[0])) {
				img = new Image();
				img.onload = function() {

					if (file.size > vendorAllowedMediaSize) {
						alert(allowedMediaSizeErrorMessage);
						jQuery("#jform_vendor_logo").val('');
						return false;
					}
				};

				img.onerror = function() {
					alert(allowedImageTypeErrorMessage + file.type);
					jQuery("#jform_vendor_logo").val('');
					return false;
				};

				img.src = _URL.createObjectURL(file);
			}
		});
	},

	initVendorFields: function() {
		jQuery('.subform-repeatable-group .gateway_name').on('focus', function() {
			previous = this.value;
		});
		jQuery('.subform-repeatable-group .gateway_name').each(function() {
			jQuery(this).trigger("change");
		});
	},

	getGatewayFields: function(ele) {
		let count = 0;
		jQuery('.subform-repeatable-group .gateway_name').each(function() {
			if (this.value === ele.value) {
				count++;
			}
		});

		if (count > 1) {
			jQuery(ele).val();
			return false;
		}

		let userObject = {
			'payment_gateway': ele.value,
			'parent_tag': ele.name.replace('[payment_gateways]', "")
		};

		tjCommon.generateGatewayFields(userObject, ele.id);
	},

	generateGatewayFields: function (userObject, eleId) {
			JSON.stringify(userObject);
			jQuery.ajax({
				type: "POST",
				dataType: "json",
				data: userObject,
				url: Joomla.getOptions('system.paths').base + "/index.php?option=com_tjvendors&task=vendor.generateGatewayFields",
				success: function (response) {
					let $thisId = jQuery('#' + eleId);
					$thisId.closest('.subform-repeatable-group').find('.payment-gateway-parent').remove();

					if (response) {
						response.forEach(function(data) {
							$thisId.closest('.subform-repeatable-group').append("<div class='payment-gateway-parent'>" + data + "</div>");
						});
					} 
					else if (!response && userObject.payment_gateway != "") {
						var error_html = Joomla.Text._('COM_TJVENDOR_PAYMENTGATEWAY_NO_FIELD_MESSAGE');
						$thisId.closest('.subform-repeatable-group').append("<div class='alert alert-warning payment-gateway-parent'>" + error_html + "</div>");
					}
				}
			});
		}
}
