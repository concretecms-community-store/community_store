var communityStore = {

    openModal: function(content, animatecart) {
        var whiteout = $(".store-whiteout");

        if (whiteout.length) {
            whiteout.empty().html(content);
        } else {
            $(".ccm-page").append("<div class='store-whiteout'>" + content + "</div>");

            setTimeout(function() {
                $('.store-cart-modal').addClass('store-cart-modal-active');
            }, 10);

            whiteout.click(function(e) {
                if (e.target != this) return; // only allow the actual whiteout background to close the dialog
                communityStore.exitModal();
            });

            $(document).keyup("keyup.communitywhiteout", function(e) {
                if (e.keyCode === 27) {
                    communityStore.exitModal();
                    $(document).unbind("keyup.communitywhiteout");
                }
            });
        }

        if (animatecart) {
            setTimeout(function() {
                $('.store-cart-modal').addClass('store-cart-modal-active');
            }, 10);
        } else {
            $('.store-cart-modal').addClass('store-cart-modal-active');
        }
    },

    waiting: function() {
        communityStore.openModal("<div class='store-spinner-container'><div class='store-spinner'></div></div>");
    },

    exitModal: function() {
        $(".store-whiteout").remove();
    },

    productModal: function(pID, locale) {
        communityStore.waiting();
        $.ajax({
            url: PRODUCTMODAL,
            data: { pID: pID, locale: locale },
            type: 'get',
            cache: false,
            dataType: 'text',
            success: function(modalContent) {
                communityStore.openModal(modalContent);
            }
        });
    },

    displayCart: function(res, animatecart) {
        $.ajax({
            type: "POST",
            cache: false,
            dataType: 'text',
            data: res,
            url: CARTURL + '/getmodal' + TRAILINGSLASH,
            success: function(data) {
                communityStore.openModal(data, animatecart);
            }
        });
    },

    addToCart: function(pID, type) {
        var form;
        if (type == 'modal') {
            form = $('#store-form-add-to-cart-modal-' + pID);
        } else if (type == 'list') {
            form = $('#store-form-add-to-cart-list-' + pID);
        } else {
            form = $('#store-form-add-to-cart-' + pID);
        }

        var valid = true;


        var priceinput = $(form).find('.store-product-customer-price-entry-field');

        if (priceinput.length > 0) {
            var max = parseFloat(priceinput.attr('max'));
            var min = parseFloat(priceinput.attr('min'));
            var customerprice = parseFloat(priceinput.val());

            if (customerprice < min || customerprice > max || !isFinite(customerprice)) {
                priceinput.focus();
                valid = false;
            }

        }

        $(form).find('input,textarea,select').filter('[required]').each(function(i, requiredField) {
            if ($(requiredField).val() == '') {
                $(requiredField).focus();
                valid = false;
            }
        });

        if (!valid) {
            return false;
        }

        var qtyfield = $(form).find('.store-product-qty');
        var qty = qtyfield.val();
        if (qty > 0) {
            var serial = $(form).serialize();
            communityStore.waiting();
            $.ajax({
                url: CARTURL + "/add" + TRAILINGSLASH,
                data: serial,
                type: 'post',
                cache: false,
                dataType: 'text',
                success: function(data) {
                    var res = jQuery.parseJSON(data);

                    if (res.product.pAutoCheckout == '1') {
                        window.location.href = CHECKOUTURL;
                        return false;
                    }

                    communityStore.displayCart(res, true);
                    communityStore.refreshCartTotals();
                }
            });
        } else {
            alert(QTYMESSAGE);
            qtyfield.focus();
        }
    },

    //Update a single item in cart
    updateItem: function(instanceID, modal) {
        var qty = $("*[data-instance-id='" + instanceID + "']").find(".store-cart-list-product-qty .form-control").val();
        var ccm_token = $('#store-modal-cart').find('[name=ccm_token]').val();
        //communityStore.waiting();
        $.ajax({
            url: CARTURL + "/update" + TRAILINGSLASH,
            data: { instance: instanceID, pQty: qty, ccm_token: ccm_token },
            type: 'post',
            cache: false,
            dataType: 'text',
            success: function(data) {
                if (modal) {
                    var res = jQuery.parseJSON(data);
                    communityStore.displayCart(res);
                }

                communityStore.refreshCartTotals();
            }
        });
    },


    //Update multiple item quantities
    updateMultiple: function(instances, quantities, modal) {
        var ccm_token = $('#store-modal-cart').find('[name=ccm_token]').val();

        $.ajax({
            url: CARTURL + "/update" + TRAILINGSLASH,
            data: { instance: instances, pQty: quantities, ccm_token: ccm_token },
            type: 'post',
            cache: false,
            dataType: 'text',
            success: function(data) {
                if (modal) {
                    var res = jQuery.parseJSON(data);
                    communityStore.displayCart(res);
                }

                communityStore.refreshCartTotals();
            }
        });
    },

    removeItem: function(instanceID, modal) {
        var ccm_token = $('#store-modal-cart').find('[name=ccm_token]').val();
        $.ajax({
            url: CARTURL + "/remove" + TRAILINGSLASH,
            data: { instance: instanceID, ccm_token: ccm_token },
            type: 'post',
            cache: false,
            dataType: 'text',
            success: function(data) {
                if (modal) {
                    var res = jQuery.parseJSON(data);
                    communityStore.displayCart(res);
                }

                communityStore.refreshCartTotals();
            }
        });
    },

    clearCart: function(modal) {
        var ccm_token = $('#store-modal-cart').find('[name=ccm_token]').val();
        $.ajax({
            url: CARTURL + "/clear" + TRAILINGSLASH,
            type: 'post',
            cache: false,
            dataType: 'text',
            data: { clear: 1, ccm_token: ccm_token },
            success: function(data) {
                if (modal) {
                    var res = jQuery.parseJSON(data);
                    communityStore.displayCart(res);
                }

                $(".store-utility-links .store-items-counter").text(0);
                $(".store-utility-links .store-total-cart-amount").text("");
                $(".store-utility-links").addClass('store-cart-empty');
            }
        });
    },

    refreshCartTotals: function(callback) {
        $.ajax({
            url: CARTURL + '/getCartSummary' + TRAILINGSLASH,
            cache: false,
            dataType: 'text',
            success: function(response) {
                var values = $.parseJSON(response);
                var itemCount = values.itemCount;
                var subTotal = values.subTotal;
                var total = values.total;
                var totalCents = values.totalCents;
                var taxes = values.taxes;
                var shippingTotal = values.shippingTotal;
                var shippingTotalRaw = values.shippingTotalRaw;

                if (itemCount == 0) {
                    $(".store-utility-links .store-items-counter").text(0);
                    $(".store-utility-links .store-total-cart-amount").text("");
                    $(".store-utility-links").addClass('store-cart-empty');
                } else {
                    $(".store-utility-links .store-items-counter").text(itemCount);
                    $(".store-cart-grand-total-value").text(total);
                    $(".store-utility-links .store-total-cart-amount").text(total);
                    $(".store-utility-links").removeClass('store-cart-empty');
                }

                if (shippingTotalRaw === false) {
                    $("#shipping-total").text($("#shipping-total").data('unknown-label'));
                } else if (shippingTotalRaw <= 0) {
                    $("#shipping-total").text($("#shipping-total").data('no-charge-label'));
                } else {
                    $("#shipping-total").text(shippingTotal);
                }

                $("#store-taxes").html("");
                for (var i = 0; i < taxes.length; i++) {
                    if (taxes[i].taxed === true) {
                        $("#store-taxes").append('<li class="store-line-item store-tax-item list-group-item"><strong>' + taxes[i].name + ":</strong> <span class=\"store-tax-amount\">" + taxes[i].taxamount + "</span></li>");
                    }
                }

                $(".store-sub-total-amount").text(subTotal);
                $(".store-total-amount").text(total);
                $(".store-total-amount").data('total-cents', totalCents);

                if (callback) {
                    callback();
                }
            }
        });
    },

    // checkout
    loadViaHash: function() {
        var hash = window.location.hash;
        hash = hash.replace('#', '');
        if (hash != "") {
            $(".store-active-form-group").removeClass('store-active-form-group');
            var pane = $("#store-checkout-form-group-" + hash);
            pane.addClass('store-active-form-group');

            $('html, body').animate({
                scrollTop: pane.offset().top
            });
        }
    },

    updateBillingStates: function(load, callback) {
        var countryCode = $("#store-checkout-billing-country").val();
        var selectedState;
        var classList = $("#store-checkout-billing-state").attr('class').toString();
        var dataList = JSON.stringify($("#store-checkout-billing-state").data());
        if (load) {
            selectedState = $("#store-checkout-saved-billing-state").val();
        } else {
            selectedState = '';
        }

        $.ajax({
            url: CHECKOUTURL + "/getstates" + TRAILINGSLASH,
            type: 'post',
            cache: false,
            dataType: 'text',
            data: { country: countryCode, selectedState: selectedState, type: "billing", class: classList, data: dataList },
            success: function(states) {
                $("#store-checkout-billing-state").replaceWith(states);
                if (callback) {
                    callback();
                }
            }
        });
    },

    updateShippingStates: function(load, callback) {
        var countryCode = $("#store-checkout-shipping-country").val();
        var selectedState;
        if (load) {
            selectedState = $("#store-checkout-saved-shipping-state").val();
        } else {
            selectedState = '';
        }

        $.ajax({
            url: CHECKOUTURL + "/getstates" + TRAILINGSLASH,
            type: 'post',
            cache: false,
            dataType: 'text',
            data: { country: countryCode, selectedState: selectedState, type: "shipping" },
            success: function(states) {
                $("#store-checkout-shipping-state").replaceWith(states);
                if (callback) {
                    callback();
                }
            }
        });
    },

    nextPane: function(obj) {
        if (typeof $(obj)[0].checkValidity === "undefined" || $(obj)[0].checkValidity()) {
            var pane = $(obj).closest(".store-checkout-form-group").find('.store-checkout-form-group-body').parent().next();
            $('.store-active-form-group').removeClass('store-active-form-group');
            pane.addClass('store-active-form-group');
            $(obj).closest(".store-checkout-form-group").addClass('store-checkout-form-group-complete');

            $('html, body').animate({
                scrollTop: pane.offset().top
            });

            pane.find('input').first().focus();
        }
    },

    showShippingMethods: function(callback) {
        $.ajax({
            url: CHECKOUTURL + "/getShippingMethods" + TRAILINGSLASH,
            cache: false,
            dataType: 'text',
            success: function(html) {
                $("#store-checkout-shipping-method-options").html(html);
                $('.store-whiteout').remove();

                if (callback) {
                    callback();
                }
            },
            failure: function() {
                $('.store-whiteout').remove();

                if (callback) {
                    callback();
                }
            }
        });
    },

    showPaymentForm: function() {
        var pmID = $("#store-checkout-payment-method-options input[type='radio']:checked").attr('data-payment-method-id');
        $('.store-payment-method-container').addClass('hidden');
        $(".store-payment-method-container[data-payment-method-id='" + pmID + "']").removeClass('hidden');
    },

    copyBillingToShipping: function() {
        $("#store-checkout-shipping-first-name").val($("#store-checkout-billing-first-name").val());
        $("#store-checkout-shipping-last-name").val($("#store-checkout-billing-last-name").val());
        $("#store-checkout-shipping-email").val($("#store-checkout-billing-email").val());
        $("#store-checkout-shipping-phone").val($("#store-checkout-billing-phone").val());

        if ($("#store-checkout-billing-company")) {
            $("#store-checkout-shipping-company").val($("#store-checkout-billing-company").val());
        }

        $("#store-checkout-shipping-address-1").val($("#store-checkout-billing-address-1").val());
        $("#store-checkout-shipping-address-2").val($("#store-checkout-billing-address-2").val());
        $("#store-checkout-shipping-country").val($("#store-checkout-billing-country").val());
        $("#store-checkout-shipping-city").val($("#store-checkout-billing-city").val());
        var billingstate = $("#store-checkout-billing-state").clone().val($("#store-checkout-billing-state").val()).attr("name", "store-checkout-shipping-state").attr("id", "store-checkout-shipping-state");
        $("#store-checkout-shipping-state").replaceWith(billingstate);
        $("#store-checkout-shipping-zip").val($("#store-checkout-billing-zip").val());
    },

    sortNumber: function(a, b) {
        return a - b;
    },
    hasFormValidation: function() {
        return (typeof document.createElement('input').checkValidity == 'function');
    },
    submitProductFilter: function(element) {
        var filterform = element.closest('form');
        var checkboxes = filterform.find(':checked');
        var search = {};
        var matchtypes = {};

        checkboxes.each(function(index, field) {
            var name = field.name.replace('[]', '');
            var value = encodeURIComponent(field.value);
            var matchtype = field.getAttribute('data-matching');

            if (name in search) {
                search[name].push(value);
            } else {
                search[name] = [value];
                matchtypes[name] = [matchtype === 'or' ? '|' : ';'];
            }
        });

        var strings = [];

        $.each(search, function(key, value) {
            strings.push(key + '=' + value.join(matchtypes[key]));
        });

        var price = filterform.find("[name='price']");

        if (price.length) {
            var min = parseFloat(price.data('min'), 2);
            var max = parseFloat(price.data('max'), 2);

            var pricerange = price.val();

            if (min + '-' + max !== pricerange) {
                strings.push('price=' + price.val());
            }
        }

        var searchstring = strings.join('&');

        var params = {};
        var hasparams = false;

        location.search.substr(1).split("&").forEach(function(item) {
            var key = item.split("=")[0];
            if (key.indexOf('sort') === 0) {
                params[key] = item.split("=")[1];
                hasparams = true;
            }
        });

        if (hasparams) {
            searchstring = searchstring + '&' + $.param(params);
        }

        var id = filterform.attr('id');

        if (id) {
            searchstring += '#' + filterform.attr('id');
        }

        window.location = '?' + searchstring;
    },
    clearProductFilter: function(element) {
        var filterform = element.closest('form');
        var checkboxes = filterform.find(':checked');
        var search = {};

        checkboxes.each(function(index, field) {
            checkboxes.prop('checked', false);
        });

        filterform.find('[name="price"]').val('');

        communityStore.submitProductFilter(element);
    },
    processOtherAttributes: function() {

        var extrafields = $('#store-checkout-form-group-payment #store-extrafields');

        if (extrafields.length) {
            extrafields.remove();
        }

        $('#store-checkout-form-group-payment').append('<div id="store-extrafields" style="display: none"></div>');

        $("#store-checkout-form-group-other-attributes .row").each(function(index, el) {

            var novalue = $("#store-checkout-form-group-other-attributes").data('no-value');

            if (!novalue) {
                novalue = '';
            }

            var yesvalue = $("#store-checkout-form-group-other-attributes").data('yes-value');

            if (!yesvalue) {
                yesvalue = 'Yes';
            }

            var akID = $(el).data("akid");
            var field = $(el).find(".form-control");

            var value = '0';
            var displayvalue = '';

            // look for checkbox
            if (!field.length) {
                field = $(el).find("[type=checkbox]").first();
                var label = $(el).find(".checkbox label").first();

                if (!label.text()) {
                    label = $(field).next();
                }

                if (field) {
                    if (field.is(':checked')) {
                        value = '1';
                        displayvalue = yesvalue;
                    } else {
                        displayvalue = novalue;
                    }
                }
            } else {
                value = field.val();

                if (field.is('select')) {
                    displayvalue = field.children(':selected').text();
                } else {
                    displayvalue = value;
                }
            }

            if (field.length) {
                displayvalue = displayvalue.replace(/[\n\r]/g, '<br>').trim();

                if (!displayvalue) {
                    displayvalue = '-';
                }

                $('.store-summary-order-choices-' + akID).html(displayvalue);
                $('#akIDinput' + akID).remove();
                field.clone().hide().appendTo($('#store-checkout-form-group-payment #store-extrafields'));
            }
        });

    }

};

$(document).ready(function() {
    if ($('.store-checkout-form-shell form').length > 0) {
        communityStore.updateBillingStates(true);
        communityStore.updateShippingStates(true);
        communityStore.showShippingMethods();
        communityStore.showPaymentForm();
    }

    $("#store-checkout-form-group-billing").submit(function(e) {
        e.preventDefault();
        var email = $("#store-email").val();
        var bfName = $("#store-checkout-billing-first-name").val();
        var blName = $("#store-checkout-billing-last-name").val();
        var bPhone = $("#store-checkout-billing-phone").val();

        var bCompany = '';

        if ($("#store-checkout-billing-company")) {
            bCompany = $("#store-checkout-billing-company").val();
        }

        var bAddress1 = $("#store-checkout-billing-address-1").val();
        var bAddress2 = $("#store-checkout-billing-address-2").val();
        var bCountry = $("#store-checkout-billing-country").val();
        var bCity = $("#store-checkout-billing-city").val();
        var bState = $("#store-checkout-billing-state").val();
        var bPostal = $("#store-checkout-billing-zip").val();
        $("#store-checkout-form-group-billing .store-checkout-form-group-body .store-checkout-errors").remove();

        var ccm_token = $(this).find('[name=ccm_token]').val();

        if ($('#store-checkout-form-group-billing #store-checkout-form-group-other-attributes').length) {
            communityStore.processOtherAttributes();
        }

        communityStore.waiting();
        var obj = $(this);
        $.ajax({
            url: CHECKOUTURL + "/updater" + TRAILINGSLASH,
            type: 'post',
            cache: false,
            dataType: 'text',
            data: {
                ccm_token: ccm_token,
                adrType: 'billing',
                email: email,
                fName: bfName,
                lName: blName,
                phone: bPhone,
                company: bCompany,
                addr1: bAddress1,
                addr2: bAddress2,
                count: bCountry,
                city: bCity,
                state: bState,
                postal: bPostal
            },
            success: function(result) {
                //var test = null;
                var response = JSON.parse(result);

                if (response.error == false) {

                    if ($('#store-copy-billing').is(":checked")) {
                        communityStore.copyBillingToShipping();
                        $("#store-checkout-form-group-shipping").submit();
                        $('#store-copy-billing').prop('checked', false)
                    } else {
                        $(".store-whiteout").remove();
                    }

                    obj.find('.store-checkout-form-group-summary .store-summary-name').html(response.first_name + ' ' + response.last_name);
                    obj.find('.store-checkout-form-group-summary .store-summary-phone').html(response.phone);
                    obj.find('.store-checkout-form-group-summary .store-summary-email').html(response.email);
                    obj.find('.store-checkout-form-group-summary .store-summary-address').html(response.address);
                    obj.find('.store-checkout-form-group-summary .store-summary-company').html(response.company);
                    communityStore.nextPane(obj);
                    communityStore.refreshCartTotals();

                } else {
                    $("#store-checkout-form-group-billing .store-checkout-form-group-body ").prepend('<div class="store-checkout-errors"><div class="store-checkout-error alert alert-danger"></div></div>');
                    $("#store-checkout-form-group-billing .store-checkout-error").html(response.errors.join('<br>'));
                    $('.store-whiteout').remove();
                }
            },
            error: function(data) {
                $(".store-whiteout").remove();
            }
        });

    });

    $("#store-checkout-form-group-shipping").submit(function(e) {
        e.preventDefault();
        var sfName = $("#store-checkout-shipping-first-name").val();
        var slName = $("#store-checkout-shipping-last-name").val();
        var sCompany = '';

        if ($("#store-checkout-shipping-company")) {
            sCompany = $("#store-checkout-shipping-company").val();
        }

        var sAddress1 = $("#store-checkout-shipping-address-1").val();
        var sAddress2 = $("#store-checkout-shipping-address-2").val();
        var sCountry = $("#store-checkout-shipping-country").val();
        var sCity = $("#store-checkout-shipping-city").val();
        var sState = $("#store-checkout-shipping-state").val();
        var sPostal = $("#store-checkout-shipping-zip").val();
        $("#store-checkout-form-group-shipping .store-checkout-form-group-body .store-checkout-errors").remove();

        var ccm_token = $(this).find('[name=ccm_token]').val();

        if ($('#store-checkout-form-group-shipping #store-checkout-form-group-other-attributes').length) {
            communityStore.processOtherAttributes();
        }

        communityStore.waiting();
        var obj = $(this);
        $.ajax({
            url: CHECKOUTURL + "/updater" + TRAILINGSLASH,
            type: 'post',
            cache: false,
            dataType: 'text',
            data: {
                ccm_token: ccm_token,
                adrType: 'shipping',
                fName: sfName,
                lName: slName,
                company: sCompany,
                addr1: sAddress1,
                addr2: sAddress2,
                count: sCountry,
                city: sCity,
                state: sState,
                postal: sPostal
            },
            //dataType: 'json',
            success: function(result) {
                var response = JSON.parse(result);
                if (response.error == false) {
                    obj.find('.store-checkout-form-group-summary .store-summary-name').html(response.first_name + ' ' + response.last_name);
                    obj.find('.store-checkout-form-group-summary .store-summary-address').html(response.address);
                    obj.find('.store-checkout-form-group-summary .store-summary-company').html(response.company);
                    if (response.vat_number != '') {
                        obj.find('.store-checkout-form-group-summary .store-summary-vat-number').html(response.vat_number);
                    } else {
                        obj.find('.store-checkout-form-group-summary .store-summary-vat-number').html('-');
                    }
                    communityStore.showShippingMethods(function() {
                        communityStore.refreshCartTotals();
                        communityStore.nextPane(obj);
                    });
                } else {
                    $("#store-checkout-form-group-shipping .store-checkout-form-group-body").prepend('<div class="store-checkout-errors"><div class="alert alert-danger"></div></div>');
                    $("#store-checkout-form-group-shipping .alert").html(response.errors.join('<br>'));
                    $('.store-whiteout').remove();
                }
            },
            error: function(data) {
                $(".store-whiteout").remove();
            }
        });

    });

    $("#store-checkout-form-group-vat").submit(function(e) {
        e.preventDefault();
        var vat_number = $("#store-checkout-shipping-vat-number").val();
        $("#store-checkout-form-group-vat .store-checkout-errors").remove();
        var ccm_token = $(this).find('[name=ccm_token]').val();

        if ($('#store-checkout-form-group-vat #store-checkout-form-group-other-attributes').length) {
            communityStore.processOtherAttributes();
        }

        communityStore.waiting();
        var obj = $(this);
        $.ajax({
            url: CHECKOUTURL + "/setVatNumber" + TRAILINGSLASH,
            type: 'post',
            cache: false,
            dataType: 'text',
            data: {
                vat_number: vat_number,
                ccm_token: ccm_token
            },
            success: function(result) {
                //var test = null;
                var response = JSON.parse(result);
                if (response.error == false) {
                    if (response.vat_number != '') {
                        obj.find('.store-checkout-form-group-summary .store-summary-vat-number').html(response.vat_number);
                    } else {
                        obj.find('.store-checkout-form-group-summary .store-summary-vat-number').html(obj.find('.store-checkout-form-group-summary .store-summary-vat-number').data('vat-blank'));
                    }
                    communityStore.refreshCartTotals();
                    communityStore.nextPane(obj);
                    $('.store-whiteout').remove();
                } else {


                    $("#store-checkout-form-group-vat .store-checkout-form-group-body ").prepend('<div class="store-checkout-errors"><div class="store-checkout-error alert alert-danger"></div></div>');
                    $("#store-checkout-form-group-vat .store-checkout-error").html(response.errors.join('<br>'));
                    $('.store-whiteout').remove();
                }
            },
            error: function(data) {
                $(".store-whiteout").remove();
            }
        });

    });

    $("#store-checkout-form-group-shipping-method").submit(function(e) {
        e.preventDefault();
        communityStore.waiting();
        var obj = $(this);
        var ccm_token = $(this).find('[name=ccm_token]').val();

        if ($("#store-checkout-shipping-method-options input[type='radio']:checked").length < 1) {
            $('.store-whiteout').remove();
            alert($('#store-checkout-shipping-method-options').data('error-message'));
        } else {
            var smID = $("#store-checkout-shipping-method-options input[type='radio']:checked").val();
            var methodText = $.trim($("#store-checkout-shipping-method-options input[type='radio']:checked").parent().find('.store-shipping-details').html());
            obj.find('.summary-shipping-method').html(methodText);
            var sInstructions = $('#store-checkout-shipping-instructions').val();
            obj.find('.summary-shipping-instructions').html(sInstructions);

            $.ajax({
                type: 'post',
                cache: false,
                dataType: 'text',
                data: {
                    smID: smID,
                    sInstructions: sInstructions,
                    ccm_token: ccm_token
                },
                url: CHECKOUTURL + "/selectShipping" + TRAILINGSLASH,
                success: function(total) {
                    communityStore.refreshCartTotals(function() {
                        communityStore.nextPane(obj);
                        $('.store-whiteout').remove();
                    });
                }
            });

        }
    });

    $(".store-btn-previous-pane").click(function(e) {
        //hide the body of the current pane, go to the next pane, show that body.
        var pane = $(this).closest(".store-checkout-form-group").find('.store-checkout-form-group-body').parent().prev();
        $('.store-active-form-group').removeClass('store-active-form-group');
        pane.addClass('store-active-form-group');

        $('html, body').animate({
            scrollTop: pane.parent().offset().top
        });

        $(this).closest(".store-checkout-form-group").prev().removeClass("store-checkout-form-group-complete");
        e.preventDefault();
    });

    $("#store-checkout-payment-method-options input[type='radio']").change(function() {
        communityStore.showPaymentForm();
    });

    $('#store-cart .store-btn-cart-list-remove').click(function(e) {
        $('#deleteform input[name=instance]').val($(this).data('instance'));
        $('#deleteform').submit();
        e.preventDefault();
    });

    $(document).on('click', '.store-btn-add-to-cart', function(e) {
        var add = false;

        if ($(this).data('invalid') == '1') {
            $(this).data('invalid', '0');
        } else {

            if (communityStore.hasFormValidation()) {
                if (!$(this).closest('form')[0].checkValidity()) {

                    $(this).data('invalid', '1');
                    $(this).click();
                } else {
                    add = true;
                }
            } else {
                add = true;
            }

            if (add) {
                communityStore.addToCart($(this).data('product-id'), $(this).data('add-type'));
                e.preventDefault();
            }
        }

    });

    $(document).on('submit', '.store-product-block', function(e) {
        if ($(this).find('.store-btn-add-to-cart').length > 0) {
            communityStore.addToCart($(this).data('product-id'), $(this).data('add-type'));
        }
        e.preventDefault();
    });

    $(document).on('click', '.store-price-suggestion', function(e) {
        var productform = $(this).closest('form');
        productform.find('.store-product-customer-price-entry-field').val($(this).data('suggestion-value'));
        communityStore.addToCart(productform.data('product-id'), $(this).data('add-type'));
        e.preventDefault();
    });

    $(document).on('click', '.store-btn-cart-list-remove', function(e) {
        communityStore.removeItem($(this).data('instance-id'), $(this).data('modal'));
        e.preventDefault();
    });

    $('.store-product-quick-view').click(function(e) {
        communityStore.productModal($(this).data('product-id'), $(this).data('locale'));
        e.preventDefault();
    });

    $('.store-cart-link-modal').click(function(e) {
        communityStore.displayCart(false, true);
        e.preventDefault();
    });

    $(document).on('click', '.store-modal-exit, .store-btn-cart-modal-continue', function(e) {
        communityStore.exitModal();
        e.preventDefault();
    });

    $(document).on('click', '.store-btn-cart-modal-clear', function(e) {
        communityStore.clearCart(true);
        $(this).addClass('disabled');
        e.preventDefault();
    });

    $(document).on('click', '.store-btn-cart-modal-update', function(e) {
        var update = false;

        if ($(this).data('invalid') == '1') {
            $(this).data('invalid', '0');
        } else {
            if (communityStore.hasFormValidation()) {
                if (!$(this).closest('form')[0].checkValidity()) {
                    $(this).data('invalid', '1');
                    $(this).click();
                } else {
                    update = true;
                }
            } else {
                update = true;
            }

            if (update) {
                var instances = $("#store-modal-cart input[name='instance[]']").map(function() {
                    return $(this).val();
                }).get();
                var pQty = $("#store-modal-cart input[name='pQty[]']").map(function() {
                    return $(this).val();
                }).get();

                communityStore.updateMultiple(instances, pQty, true);
                $(this).addClass('disabled');
                e.preventDefault();
            }
        }

    });

    $('.store-cart-modal-link').click(function(e) {
        e.preventDefault();
        communityStore.displayCart(false, true);
    });

    $('.store-btn-filter').click(function(e) {
        e.preventDefault();
        communityStore.submitProductFilter($(this));
    });

    $(document).on('change', '.store-product-filter-block-auto input[type="checkbox"]', function(e) {
        communityStore.submitProductFilter($(this));
    });

    $(document).on('click', '.store-btn-filter-clear', function(e) {
        communityStore.clearProductFilter($(this));
    });

});