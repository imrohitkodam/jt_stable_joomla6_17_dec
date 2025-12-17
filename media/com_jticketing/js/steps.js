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

(function($) {
    $(document).ready(function() {
        $(document).on('change', '#ticketsTypes', function() {
            var addItem = $(this);
            var parentDiv = $('.js-jt-ticket-listing');
            parentDiv.addClass('isloading');

            /** global: JTicketing */
            JTicketing.Ajax({
                url: Joomla.getOptions('system.paths').base + '/index.php?option=com_jticketing',
                data: {
                    task: "order.addItem",
                    'orderId': parseInt(addItem.data("order-id")),
                    'typeId': parseInt(this.value),
                    'view': 'order'
                }
            }).done(function(result) {
                if (result.success === true) {
                    parentDiv.empty();
                    parentDiv.html(result.data);

                    // trigger the event for chosen script
                    parentDiv.trigger('subform-row-add', $('.js-jt-ticket-listing'));
                } else {
                    Joomla.renderMessages({
                        'error': [result.message]
                    });
                }
            }).fail(function(content) {
                Joomla.renderMessages({
                    'error': [Joomla.JText._('COM_JTICKETING_ORDER_RELATED_AJAX_FAIL_ERROR_MESSAGE')]
                });
            }).always(function() {
                parentDiv.removeClass('isloading');
                $('#ticketsTypes').chosen();
            });
        });
    });

    $(document).on('click', '.proceedCheckout', function(e) {
        var proceedCheckout = $(this);
        var orderId = parseInt(proceedCheckout.data("order-id"));

        /** global: JTicketing */
        JTicketing.Ajax({
            url: Joomla.getOptions('system.paths').base + '/index.php?option=com_jticketing',
            data: {
                task: "order.proceedToCheckout",
                orderId: orderId
            }
        }).done(function(result) {
            if (result.success === true) {
                /** global: ga_ec_analytics */
                if (ga_ec_analytics === 1)
                {
                    /** global: jtSite */
                    jtSite.order.addEcTrackingData(orderId, 2);
                }
                window.parent.location = result.data;
            } else {
                Joomla.renderMessages({
                    'error': [result.message]
                });
            }
        }).fail(function(content) {
            Joomla.renderMessages({
                'error': [Joomla.JText._('COM_JTICKETING_ORDER_RELATED_AJAX_FAIL_ERROR_MESSAGE')]
            });
        })
    });

    $(document).on('click', '.attendeeCheckout', function(e) {
        var attendeeForm = document.attendee_field_form;
        var attendeeCheckout = $(this);
        var orderId = parseInt(attendeeCheckout.data("order-id"));
        var attendeeButton = $('#attendeeCheckout');

        if (!document.formvalidator.isValid(attendeeForm)) {
            $("html, body").animate({
                scrollTop: 0
            }, "slow");
            return e.preventDefault();
        } else {
            $(".alert-error").hide();
            var values = $('#attendee_field_form').serialize();
            values += '&orderId=' + orderId;

            /** global: JTicketing */
            JTicketing.Ajax({
                url: Joomla.getOptions('system.paths').base + '/index.php?option=com_jticketing',
                data: {
                    task: "order.addAttendee",
                    orderId: orderId,
                    attendee_field: values
                },
                beforeSend: function() {
                attendeeButton.attr('disabled', true);
                attendeeButton.addClass("btn-loading");
                }
            }).done(function(result) {
                attendeeButton.attr('disabled', false);
                if (result.success === false) {
                    Joomla.renderMessages({
                        'error': [result.message]
                    });
                    $("html, body").animate({
                        scrollTop: 0
                    }, "slow");
                } else {
                    /** global: ga_ec_analytics */
                    if (ga_ec_analytics === 1)
                    {
                        /** global: jtSite */
                        jtSite.order.addEcTrackingData(orderId, 3);
                    }
                    window.location.href = result.data;
                }
                attendeeButton.removeClass("btn-loading");
            }).fail(function(content) {
                Joomla.renderMessages({
                    'error': [Joomla.JText._('COM_JTICKETING_ORDER_RELATED_AJAX_FAIL_ERROR_MESSAGE')]
                });
            });
        }
    });

    $(document).on('click', '.removeItem', function() {
        var removeItem = $(this);
        var typeId = parseInt(removeItem.data("ticket-type-id"));
        var inputVal = parseInt($('.ticketInput' + typeId).val());
        var parentDiv = $('.js-jt-ticket-listing');
        parentDiv.addClass('isloading');
        var couponCode = "";

        if(document.getElementById("coupon_code")) {
            couponCode = document.getElementById("coupon_code").value;
        }

        if (inputVal == 0) {
            parentDiv.removeClass('isloading');
            $('#ticketsTypes').chosen();
            return;
        }

        inputVal = --inputVal;
        $('.ticketInput' + typeId).val(inputVal);

        /** global: JTicketing */
        JTicketing.Ajax({
            url: Joomla.getOptions('system.paths').base + '/index.php?option=com_jticketing',
            data: {
                task: "order.removeItem",
                'orderId': parseInt(removeItem.data("order-id")),
                'typeId': typeId,
                'couponCode': couponCode,
                'view': 'order'
            }
        }).done(function(result) {
            if (result.success === true) {
                parentDiv.empty();
                parentDiv.html(result.data);
            } else {
                // If error comes while adding ticket reset the value
                $('.ticketInput' + typeId).val(++inputVal);
                Joomla.renderMessages({
                    'error': [result.message]
                });
            }
        }).fail(function(content) {
            // If error comes while adding ticket reset the value
            $('.ticketInput' + typeId).val(++inputVal);
            Joomla.renderMessages({
                'error': [Joomla.JText._('COM_JTICKETING_ORDER_RELATED_AJAX_FAIL_ERROR_MESSAGE')]
            });
        }).always(function() {
            parentDiv.removeClass('isloading');
            $('#ticketsTypes').chosen();
        });
    });

    $(document).on('click', '.addItem', function() {
        var addItem = $(this);
        var checkoutLimit = parseInt(addItem.data("checkout-limit"));
        var unlimitedTicket = parseInt(addItem.data("unlimited-ticket"));
        var typeId = parseInt(addItem.data("ticket-type-id"));
        var inputVal = parseInt($('.ticketInput' + typeId).val());
        var parentDiv = $('.js-jt-ticket-listing');
        parentDiv.addClass('isloading');


        if (inputVal >= checkoutLimit && !unlimitedTicket) {
            /*@TODO - Check how to pass checkout limit configuration to alert message*/
            Joomla.renderMessages({
                'error': [Joomla.JText._('JT_PERUSER_PER_PURCHASE_LIMIT_ERROR')]
            });

            parentDiv.removeClass('isloading');
            $('#ticketsTypes').chosen();
            return;
        }

        $('.ticketInput' + typeId).val(++inputVal);
        $('#coupon_troption').show();

        /** global: JTicketing */
        JTicketing.Ajax({
            url: Joomla.getOptions('system.paths').base + '/index.php?option=com_jticketing',
            data: {
                task: "order.addItem",
                'orderId': parseInt(addItem.data("order-id")),
                'typeId': typeId,
                'view': 'order'
            }
        }).done(function(result) {
            if (result.success === true) {
                parentDiv.empty();
                parentDiv.html(result.data);
            } else {
                // If error comes while adding ticket reset the value
                $('.ticketInput' + typeId).val(Math.max(0, inputVal - 1));
                Joomla.renderMessages({
                    'error': [result.message]
                });
            }
        }).fail(function(content) {
            // If error comes while adding ticket reset the value
            $('.ticketInput' + typeId).val(Math.max(0, inputVal - 1));
            Joomla.renderMessages({
                'error': [Joomla.JText._('COM_JTICKETING_ORDER_RELATED_AJAX_FAIL_ERROR_MESSAGE')]
            });
        }).always(function() {
            parentDiv.removeClass('isloading');
            $('#ticketsTypes').chosen();
        });
    });

    $(document).on('click', '.removeCoupon', function() {
        var parentDiv = $('.js-jt-ticket-listing');
        parentDiv.addClass('isloading');
        var removeCoupon = $(this);

        var couponCode = $("#coupon_code").val();

        /** global: JTicketing */
        JTicketing.Ajax({
            url: Joomla.getOptions('system.paths').base + '/index.php?option=com_jticketing',
            data: {
                task: "order.removeCoupon",
                couponCode: couponCode,
                orderId: parseInt(removeCoupon.data("order-id")),
                'view': 'order'
            }
        }).done(function(result) {
            if (result.success === true) {
                parentDiv.empty();
                parentDiv.html(result.data);
            } else {
                if (result.message) {
                    Joomla.renderMessages({
                        'error': [result.message]
                    });
                }
            }
        }).fail(function(content) {
            Joomla.renderMessages({
                'error': [Joomla.JText._('COM_JTICKETING_ORDER_RELATED_AJAX_FAIL_ERROR_MESSAGE')]
            });
        }).always(function() {
            // trigger the event for chosen script
            parentDiv.removeClass('isloading');
            $('#ticketsTypes').chosen();

            // Check if single ticket div is there then only trigger the event for chosen script
            if ($("#ticketsTypes").length) {
                parentDiv.trigger('subform-row-add', $('.js-jt-ticket-listing'));
            }
        });
    });

    $(document).on('click', '.applyCoupon', function() {
        var parentDiv = $('.js-jt-ticket-listing');
        parentDiv.addClass('isloading');
        var applyCoupon = $(this);

        if ($("#coupon_code").val() == "") {
            alert(Joomla.JText._('ENTER_COP_COD'));
            parentDiv.removeClass('isloading');
            $('#ticketsTypes').chosen();
            return;
        }
        parentDiv.addClass('isloading');
        var couponCode = document.getElementById("coupon_code").value;

        /** global: JTicketing */
        JTicketing.Ajax({
            url: Joomla.getOptions('system.paths').base + '/index.php?option=com_jticketing',
            data: {
                task: "order.applyCoupon",
                couponCode: couponCode,
                orderId: parseInt(applyCoupon.data("order-id")),
                'view': 'order'
            }
        }).done(function(result) {
            if (result.success === false) {
                alert(couponCode + ' ' + result.message);
                return;
            } else {
                parentDiv.empty();
                parentDiv.html(result.data);
            }
        }).fail(function(content) {
            Joomla.renderMessages({
                'error': [Joomla.JText._('COM_JTICKETING_ORDER_RELATED_AJAX_FAIL_ERROR_MESSAGE')]
            });
        }).always(function(content) {
            parentDiv.removeClass('isloading');
            $('#ticketsTypes').chosen();

            // Check if single ticket div is there then only trigger the event for chosen script
            if ($("#ticketsTypes").length) {
                parentDiv.trigger('subform-row-add', $('.js-jt-ticket-listing'));
            }
        });
    });

    $(document).on('click', '.billingCheckout', function(e) {
        var billingForm = document.billing_info_form;
        var addItem = $(this);
        var consentItem = $('#accept_privacy_term');
        var consent = consentItem.data("consent");
        var checkoutButton = $('#billingCheckout');
        var namePattern = /^[a-zA-Z\s-]+$/;
        var regexForAttendeeMob = $('.regexForAttendeeMob').val();
        regexForAttendeeMob = new RegExp(regexForAttendeeMob);

        if (!document.formvalidator.isValid(billingForm)) {
            var msg = '';
            if (consent && document.getElementById('accept_privacy_term').checked === false) {
                msg = Joomla.JText._('COM_JTICKETING_PRIVACY_TERMS_AND_CONDITIONS_ERROR');

                Joomla.renderMessages({
                    'error': [msg]
                });
            }

            $("html, body").animate({
                scrollTop: 0
            }, "slow");
            return e.preventDefault();
        }

        if (jQuery('#fname').length)
        {
            if (jQuery('#fname').length && jQuery('#fname').val() != "") {
                if (!(namePattern.test(jQuery('#fname').val()))) {
                    var error_html = Joomla.JText._('COM_JTICKETING_INVALID_FIELD') + Joomla.JText._('COM_JTICKETING_INVALID_BILLING_FNAME');
                    Joomla.renderMessages({
                        'error': [error_html]
                    });

                    jQuery("html, body").animate({
                        scrollTop: 0
                    }, "slow");

                    return false;
                }
            }
        }

        if (jQuery('#lname').length)
        {
            if (jQuery('#lname').length && jQuery('#lname').val() != "") {
                if (!(namePattern.test(jQuery('#lname').val()))) {
                    var error_html = Joomla.JText._('COM_JTICKETING_INVALID_FIELD') + Joomla.JText._('COM_JTICKETING_INVALID_BILLING_LNAME');
                    Joomla.renderMessages({
                        'error': [error_html]
                    });

                    jQuery("html, body").animate({
                        scrollTop: 0
                    }, "slow");

                    return false;
                }
            }
        }

        if (jQuery('#phone').length)
        {
            if (jQuery('#phone').length && jQuery('#phone').val() != "") {
                if (!(regexForAttendeeMob.test(jQuery('#phone').val()))) {
                    var error_html = Joomla.JText._('COM_JTICKETING_INVALID_FIELD') + Joomla.JText._('COM_JTICKETING_INVALID_BILLING_MOB');
                    Joomla.renderMessages({
                        'error': [error_html]
                    });

                    jQuery("html, body").animate({
                        scrollTop: 0
                    }, "slow");

                    return false;
                }
            }
        }

        $(".alert-error").hide();
        var billingValues = $('#billing_info_form').serialize();
        var orderId       = parseInt(addItem.data("order-id"));

        /** global: JTicketing */
        JTicketing.Ajax({
            url: Joomla.getOptions('system.paths').base + '/index.php?option=com_jticketing',
            data: {
                task: "order.addBillingData",
                billingValues: billingValues,
                orderId: parseInt(addItem.data("order-id")),
                checkout_method: $('input[name="account_jt"]:checked').val()
            },
            beforeSend: function() {
                checkoutButton.attr('disabled', true);
                checkoutButton.addClass("btn-loading");
            }
        }).done(function(result) {
            checkoutButton.attr('disabled', false);
            if (result.success === true) {
                /** global: ga_ec_analytics */
                if (ga_ec_analytics === 1)
                {
                    var stepId = 3;
                    /** global: track_attendee_step */
                    if (track_attendee_step === 1)
                    {
                        stepId = 4;
                    }

                    /** global: jtSite */
                    jtSite.order.addEcTrackingData(orderId, stepId);
                }
                document.location = result.data;
            } else {
                Joomla.renderMessages({
                    'error': [result.message]
                });
                $("html, body").animate({
                    scrollTop: 0
                }, "slow");
            }
            checkoutButton.removeClass("btn-loading");
        }).fail(function(content) {
            Joomla.renderMessages({
                'error': [Joomla.JText._('COM_JTICKETING_ORDER_RELATED_AJAX_FAIL_ERROR_MESSAGE')]
            });
            $("html, body").animate({
                scrollTop: 0
            }, "slow");
        });
    });

    $(document).on('click', '.orderLogin', function() {
        var orderLogin = $(this);
        var buttonLogin = $('#button-login');
        JTicketing.Ajax({
            url: Joomla.getOptions('system.paths').base + "/index.php?option=com_jticketing",
            data: {
                task: "order.loginValidate",
                email: $('#loginEmail').val(),
                password: $('#loginPassword').val(),
                orderId: orderLogin.data("order-id")
            },
            beforeSend: function() {
                buttonLogin.attr('disabled', true);
                buttonLogin.addClass("btn-loading");
            }
        }).done(function(result) {
            buttonLogin.attr('disabled', false);
            if (result.success === false) {
                Joomla.renderMessages({
                    'error': [result.message]
                });
                $("html, body").animate({
                    scrollTop: 0
                }, "slow");
            } else {
                window.parent.location = result.data;
            }
            buttonLogin.removeClass("btn-loading");
        });
    });

    $(document).on('blur', '#email1', function() {
    var emailField  = $('#email1');
        var email   = emailField.val();
        var userId      = emailField.data('user-id');

        /* If logged in user */
        if (userId > 0) {
            return true;
        }

        /** global: JTicketing */
        JTicketing.Ajax({
            url: Joomla.getOptions('system.paths').base + '/index.php?option=com_jticketing&view=order&task=order.checkUserEmailId&email=' + email,
            type: 'GET',
        }).done(function(result) {
            if (result.success === true) {
             $('#billing_info_data').show();
                 $('#btnWizardNext').removeAttr('disabled');
                 $('#btnWizardNext').show();
            } else {
                $('#user_info').show();
                $('#btnWizardNext').hide();
                Joomla.renderMessages({
                    'error': [result.message]
                });
                $("html, body").animate({
                    scrollTop: 0
                }, "slow");
            }
        }).fail(function() {
            Joomla.renderMessages({
                'error': [Joomla.JText._('COM_JTICKETING_ORDER_RELATED_AJAX_FAIL_ERROR_MESSAGE')]
            });
            $("html, body").animate({
                scrollTop: 0
            }, "slow");
        });
    });
})(JTQuery);
