var communityStore = {

    openModal: function (content) {
        if ($(".store-whiteout").length) {
            $(".store-whiteout").empty().html(content);
        } else {
            $("body").append("<div class='store-whiteout'>" + content + "</div>");

            $(".store-whiteout").click(function (e) {
                if (e.target != this) return;  // only allow the actual whiteout background to close the dialog
                communityStore.exitModal();
            });

            $(document).keyup("keyup.communitywhiteout", function (e) {
                if (e.keyCode === 27) {
                    communityStore.exitModal();
                    $(document).unbind("keyup.communitywhiteout");
                }
            });
        }
    }
    ,
    waiting: function () {
        communityStore.openModal("<div class='store-spinner-container'><div class='store-spinner'></div></div>");
    },

    exitModal: function () {
        $(".store-whiteout").remove();
    },

    productModal: function (pID) {
        communityStore.waiting();
        $.ajax({
            url: PRODUCTMODAL,
            data: {pID: pID},
            type: 'get',
            success: function (modalContent) {
                communityStore.openModal(modalContent);
            }
        });
    },

    displayCart: function (res) {
        $.ajax({
            type: "POST",
            data: res,
            url: CARTURL + '/getmodal',
            success: function (data) {
                communityStore.openModal(data);
            }
        });
    },

    addToCart: function (pID, type) {
        var form;
        if (type == 'modal') {
            form = $('#store-form-add-to-cart-modal-' + pID);
        } else if (type == 'list') {
            form = $('#store-form-add-to-cart-list-' + pID);
        } else {
            form = $('#store-form-add-to-cart-' + pID);
        }
        var qty = $(form).find('.store-product-qty').val();
        if (qty > 0) {
            var serial = $(form).serialize();
            communityStore.waiting();
            $.ajax({
                url: CARTURL + "/add",
                data: serial,
                type: 'post',
                success: function (data) {
                    var res = jQuery.parseJSON(data);

                    if (res.product.pAutoCheckout == '1') {
                        window.location.href = CHECKOUTURL;
                        return false;
                    }

                    communityStore.displayCart(res);
                    communityStore.refreshCartTotals();
                }
            });
        } else {
            alert(QTYMESSAGE);
        }
    },

    //Update a single item in cart
    updateItem: function (instanceID, modal) {
        var qty = $("*[data-instance-id='" + instanceID + "']").find(".cart-list-product-qty input").val();
        communityStore.waiting();
        $.ajax({
            url: CARTURL + "/update",
            data: {instance: instanceID, pQty: qty},
            type: 'post',
            success: function (data) {
                if (modal) {
                    var res = jQuery.parseJSON(data);
                    communityStore.displayCart(res);
                }

                communityStore.refreshCartTotals();
            }
        });
    },


    //Update multiple item quantities
    updateMultiple: function (instances, quantities, modal) {
        communityStore.waiting();
        $.ajax({
            url: CARTURL + "/update",
            data: {instance: instances, pQty: quantities},
            type: 'post',
            success: function (data) {
                if (modal) {
                    var res = jQuery.parseJSON(data);
                    communityStore.displayCart(res);
                }

                communityStore.refreshCartTotals();
            }
        });
    },

    removeItem: function (instanceID, modal) {
        communityStore.waiting();
        $.ajax({
            url: CARTURL + "/remove",
            data: {instance: instanceID},
            type: 'post',
            success: function (data) {
                if (modal) {
                    var res = jQuery.parseJSON(data);
                    communityStore.displayCart(res);
                }

                communityStore.refreshCartTotals();
            }
        });
    },

    clearCart: function (modal) {
        $.ajax({
            url: CARTURL + "/clear",
            success: function (data) {
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
            url: CARTURL + '/getCartSummary',
            success: function (response) {
                values = $.parseJSON(response);
                itemCount = values.itemCount;
                total = values.total;
                totalCents = values.totalCents;

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

                $(".store-total-amount").text(total);
                $(".store-total-amount").data('total-cents',totalCents);

                if (callback){
                    callback();
                }
            }
        });
    },

    // checkout
    loadViaHash: function () {
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

    updateBillingStates: function (load) {
        var countryCode = $("#store-checkout-billing-country").val();
        var selectedState;
        if (load) {
            selectedState = $("#store-checkout-saved-billing-state").val();
        } else {
            selectedState = '';
        }

        $.ajax({
            url: CHECKOUTURL + "/getstates",
            type: 'post',
            data: {country: countryCode, selectedState: selectedState, type: "billing"},
            success: function (states) {
                $("#store-checkout-billing-state").replaceWith(states);
            }
        });
    },

    updateShippingStates: function (load) {
        var countryCode = $("#store-checkout-shipping-country").val();
        var selectedState;
        if (load) {
            selectedState = $("#store-checkout-saved-shipping-state").val();
        } else {
            selectedState = '';
        }

        $.ajax({
            url: CHECKOUTURL + "/getstates",
            type: 'post',
            data: {country: countryCode, selectedState: selectedState, type: "shipping"},
            success: function (states) {
                $("#store-checkout-shipping-state").replaceWith(states);
            }
        });
    },

    nextPane: function (obj) {
        if ($(obj)[0].checkValidity()) {
            var pane = $(obj).closest(".store-checkout-form-group").find('.store-checkout-form-group-body').parent().next();
            $('.store-active-form-group').removeClass('store-active-form-group');
            pane.addClass('store-active-form-group');
            $(obj).closest(".store-checkout-form-group").addClass('store-checkout-form-group-complete');

            $('html, body').animate({
                scrollTop: pane.offset().top
            });

            pane.find('input:first-child').focus();
        }
    },

    showShippingMethods: function () {
        $.ajax({
            url: CHECKOUTURL + "/getShippingMethods",
            success: function (html) {
                $("#store-checkout-shipping-method-options").html(html);
            }
        });
    },

    showPaymentForm: function () {
        var pmID = $("#store-checkout-payment-method-options input[type='radio']:checked").attr('data-payment-method-id');
        $('.store-payment-method-container').addClass('hidden');
        $(".store-payment-method-container[data-payment-method-id='" + pmID + "']").removeClass('hidden');
    },

    copyBillingToShipping: function() {
        $("#store-checkout-shipping-first-name").val($("#store-checkout-billing-first-name").val());
        $("#store-checkout-shipping-last-name").val($("#store-checkout-billing-last-name").val());
        $("#store-checkout-shipping-email").val($("#store-checkout-billing-email").val());
        $("#store-checkout-shipping-phone").val($("#store-checkout-billing-phone").val());
        $("#store-checkout-shipping-address-1").val($("#store-checkout-billing-address-1").val());
        $("#store-checkout-shipping-address-2").val($("#store-checkout-billing-address-2").val());
        $("#store-checkout-shipping-country").val($("#store-checkout-billing-country").val());
        $("#store-checkout-shipping-city").val($("#store-checkout-billing-city").val());
        var billingstate = $("#store-checkout-billing-state").clone().val($("#store-checkout-billing-state").val()).attr("name", "store-checkout-shipping-state").attr("id", "store-checkout-shipping-state");
        $("#store-checkout-shipping-state").replaceWith(billingstate);
        $("#store-checkout-shipping-zip").val($("#store-checkout-billing-zip").val());
    }


};

communityStore.updateBillingStates(true);
communityStore.updateShippingStates(true);
communityStore.showShippingMethods();
communityStore.showPaymentForm();

$(document).ready(function () {
    $("#store-checkout-form-group-billing").submit(function (e) {
        e.preventDefault();
        var email = $("#store-email").val();
        var bfName = $("#store-checkout-billing-first-name").val();
        var blName = $("#store-checkout-billing-last-name").val();
        var bPhone = $("#store-checkout-billing-phone").val();
        var bAddress1 = $("#store-checkout-billing-address-1").val();
        var bAddress2 = $("#store-checkout-billing-address-2").val();
        var bCountry = $("#store-checkout-billing-country").val();
        var bCity = $("#store-checkout-billing-city").val();
        var bState = $("#store-checkout-billing-state").val();
        var bPostal = $("#store-checkout-billing-zip").val();
        $("#store-checkout-form-group-billing .store-checkout-form-group-body .store-checkout-errors").remove();


        $("#store-checkout-form-group-other-attributes .row").each(function(index, el) {
            var akID = $(el).data("akid");
            var value = $(el).find(".form-control").val();
            $('.store-summary-order-choices-' + akID).html(value.replace(/[\n\r]/g, '<br>'));
            $('#store-checkout-form-group-payment').append('<input name="akID[' + akID + '][value]" type="hidden" value="' + value + '">')
        });;

        communityStore.waiting();
        var obj = $(this);
        $.ajax({
            url: CHECKOUTURL + "/updater",
            type: 'post',
            data: {
                adrType: 'billing',
                email: email,
                fName: bfName,
                lName: blName,
                phone: bPhone,
                addr1: bAddress1,
                addr2: bAddress2,
                count: bCountry,
                city: bCity,
                state: bState,
                postal: bPostal
            },
            success: function (result) {
                //var test = null;
                var response = JSON.parse(result);
                if (response.error == false) {

                    if ($('#store-copy-billing').is(":checked")) {
                        communityStore.copyBillingToShipping();
                        $("#store-checkout-form-group-shipping").submit();
                    } else {
                        $(".store-whiteout").remove();
                    }

                    obj.find('.store-checkout-form-group-summary .store-summary-name').html(response.first_name + ' ' + response.last_name);
                    obj.find('.store-checkout-form-group-summary .store-summary-phone').html(response.phone);
                    obj.find('.store-checkout-form-group-summary .store-summary-email').html(response.email);
                    obj.find('.store-checkout-form-group-summary .store-summary-address').html(response.address);
                    communityStore.nextPane(obj);
                    //update tax
                    $.ajax({
                        url: CARTURL + "/getTaxTotal",
                        success: function (results) {
                            var taxes = JSON.parse(results);
                            //alert(taxes.length);
                            $("#store-taxes").html("");
                            for (var i = 0; i < taxes.length; i++) {
                                if (taxes[i].taxed === true) {
                                    $("#store-taxes").append('<li class="store-line-item store-tax-item"><strong>' + taxes[i].name + ":</strong> <span class=\"store-tax-amount\">" + taxes[i].taxamount + "</span><li>");
                                }
                            }
                        }
                    });
                    communityStore.refreshCartTotals();

                } else {
                    $("#store-checkout-form-group-billing .store-checkout-form-group-body ").prepend('<div class="store-checkout-errors"><div class="store-checkout-error alert alert-danger"></div></div>');
                    $("#store-checkout-form-group-billing .store-checkout-error").html(response.errors.join('<br>'));
                    $('.store-whiteout').remove();
                }
            },
            error: function (data) {
                $(".store-whiteout").remove();
            }
        });

    });
    $("#store-checkout-form-group-shipping").submit(function (e) {
        e.preventDefault();
        var sfName = $("#store-checkout-shipping-first-name").val();
        var slName = $("#store-checkout-shipping-last-name").val();
        var sAddress1 = $("#store-checkout-shipping-address-1").val();
        var sAddress2 = $("#store-checkout-shipping-address-2").val();
        var sCountry = $("#store-checkout-shipping-country").val();
        var sCity = $("#store-checkout-shipping-city").val();
        var sState = $("#store-checkout-shipping-state").val();
        var sPostal = $("#store-checkout-shipping-zip").val();
        $("#store-checkout-form-group-shipping .store-checkout-form-group-body .store-checkout-errors").remove();

        communityStore.waiting();
        var obj = $(this);
        $.ajax({
            url: CHECKOUTURL + "/updater",
            type: 'post',
            data: {
                adrType: 'shipping',
                fName: sfName,
                lName: slName,
                addr1: sAddress1,
                addr2: sAddress2,
                count: sCountry,
                city: sCity,
                state: sState,
                postal: sPostal
            },
            //dataType: 'json',
            success: function (result) {
                var response = JSON.parse(result);
                if (response.error == false) {
                    $(".store-whiteout").remove();
                    obj.find('.store-checkout-form-group-summary .store-summary-name').html(response.first_name + ' ' + response.last_name);
                    obj.find('.store-checkout-form-group-summary .store-summary-address').html(response.address);
                    communityStore.nextPane(obj);
                    //update tax
                    $.ajax({
                        url: CARTURL + "/getTaxTotal",
                        success: function (results) {
                            var taxes = JSON.parse(results);
                            $("#taxes").html("");
                            for (var i = 0; i < taxes.length; i++) {
                                if (taxes[i].taxed === true) {
                                    $("#store-taxes").append('<li class="store-line-item store-tax-item"><strong>' + taxes[i].name + ":</strong> <span class=\"tax-amount\">" + taxes[i].taxamount + "</span></li>");
                                }
                            }
                        }
                    });
                    communityStore.showShippingMethods();
                    communityStore.refreshCartTotals();
                } else {
                    $("#store-checkout-form-group-shipping .store-checkout-form-group-body").prepend('<div class="store-checkout-errors"><div class="alert alert-danger"></div></div>');
                    $("#store-checkout-form-group-shipping .alert").html(response.errors.join('<br>'));
                    $('.store-whiteout').remove();
                }
            },
            error: function (data) {
                $(".store-whiteout").remove();
            }
        });

    });
    $("#store-checkout-form-group-shipping-method").submit(function (e) {
        e.preventDefault();
        communityStore.waiting();
        var obj = $(this);
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
                data: { smID: smID,
                        sInstructions: sInstructions},
                url: CARTURL + "/getShippingTotal",
                success: function (total) {
                    if (total <= 0) {
                        $("#shipping-total").text($("#shipping-total").data('no-charge-label'));
                    } else {
                        $("#shipping-total").text(total);
                    }
                    $.ajax({
                        url: CARTURL + "/getTaxTotal",
                        success: function (results) {
                            var taxes = JSON.parse(results);
                            $("#taxes").html("");
                            for (var i = 0; i < taxes.length; i++) {
                                if (taxes[i].taxed === true) {
                                    $("#taxes").append('<li class="store-line-item store-tax-item"><strong>' + taxes[i].name + ":</strong> <span class=\"store-tax-amount\">" + taxes[i].taxamount + "</span></li>");
                                }
                            }
                        }
                    });

                    communityStore.refreshCartTotals(function() {
                        communityStore.nextPane(obj);
                        $('.store-whiteout').remove();
                    });

                }
            });

        }
    });
    $(".store-btn-previous-pane").click(function (e) {
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

    $("#store-checkout-payment-method-options input[type='radio']").change(function () {
        communityStore.showPaymentForm();
    });

    $('#store-cart .store-btn-cart-list-remove').click(function(e){
        $('#deleteform input[name=instance]').val($(this).data('instance'));
        $('#deleteform').submit();
        e.preventDefault();
    });

    $(document).on('click', '.store-btn-add-to-cart', function(e) {
        communityStore.addToCart($(this).data('product-id'),$(this).data('add-type'))
        e.preventDefault();
    });


    $(document).on('click', '.store-btn-cart-list-remove', function(e) {
        communityStore.removeItem($(this).data('instance-id'),$(this).data('modal'))
        e.preventDefault();
    });

    $('.store-product-quick-view').click(function(e){
        communityStore.productModal($(this).data('product-id'));
        e.preventDefault();
    });

    $('.store-cart-link-modal').click(function(e){
        communityStore.displayCart();
        e.preventDefault();
    });

    $(document).on('click', '.store-modal-exit, .store-btn-cart-modal-continue', function(e) {
        communityStore.exitModal();
        e.preventDefault();
    });

    $(document).on('click', '.store-btn-cart-modal-clear', function(e) {
        communityStore.clearCart(true)
        e.preventDefault();
    });

    $(document).on('click', '.store-btn-cart-modal-update', function(e) {
       var instances = $("#store-modal-cart input[name='instance[]']").map(function(){return $(this).val();}).get();
       var pQty = $("#store-modal-cart input[name='pQty[]']").map(function(){return $(this).val();}).get();

        communityStore.updateMultiple(instances,pQty,true);
        e.preventDefault();
    });

    $(document).on('click', '.store-btn-cart-list-update', function(e) {
        communityStore.waiting();
    });

    $('.store-cart-modal-link').click(function (e) {
        e.preventDefault();
        communityStore.displayCart();
    });
});
