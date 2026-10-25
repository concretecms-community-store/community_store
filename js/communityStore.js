var communityStore = {

    // Helpers

    /**
     * Run a callback once the DOM is ready.
     */
    ready: function(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
        } else {
            callback();
        }
    },

    /**
     * Resolve a selector, element or jQuery-like object to a DOM element.
     */
    element: function(element) {
        if (typeof element === 'string') {
            return document.querySelector(element);
        }
        if (element && element.jquery) {
            return element[0] || null;
        }
        return element || null;
    },

    /**
     * Attach a delegated event listener to the document.
     */
    on: function(eventName, selector, handler) {
        document.addEventListener(eventName, function(e) {
            var target = e.target && e.target.closest ? e.target.closest(selector) : null;
            if (target) {
                handler.call(target, e, target);
            }
        });
    },

    /**
     * Get the value of a field by ID, or an empty string if not present.
     */
    val: function(id) {
        var field = document.getElementById(id);
        return field ? field.value : '';
    },

    /**
     * Serialize the named, enabled fields of a form (or any container) in the same way as a form submission.
     */
    serialize: function(container) {
        var params = new URLSearchParams();
        container.querySelectorAll('input, select, textarea').forEach(function(field) {
            if (!field.name || field.disabled) {
                return;
            }
            var type = (field.type || '').toLowerCase();
            if (['file', 'submit', 'button', 'reset', 'image'].indexOf(type) !== -1) {
                return;
            }
            if ((type === 'checkbox' || type === 'radio') && !field.checked) {
                return;
            }
            if (field.tagName === 'SELECT' && field.multiple) {
                Array.prototype.forEach.call(field.selectedOptions, function(option) {
                    params.append(field.name, option.value);
                });
                return;
            }
            params.append(field.name, field.value);
        });

        return params.toString();
    },

    /**
     * Convert an object to a URL encoded string, using the same conventions as PHP (arrays as name[], objects as name[key]).
     */
    param: function(data, prefix, params) {
        params = params || new URLSearchParams();

        if (data === null || data === undefined || data === false && !prefix) {
            return params;
        }

        if (Array.isArray(data)) {
            data.forEach(function(value) {
                if (value !== null && typeof value === 'object') {
                    communityStore.param(value, prefix + '[]', params);
                } else {
                    params.append(prefix + '[]', value === null || value === undefined ? '' : value);
                }
            });
        } else if (typeof data === 'object') {
            Object.keys(data).forEach(function(key) {
                var name = prefix ? prefix + '[' + key + ']' : key;
                var value = data[key];
                if (value !== null && typeof value === 'object') {
                    communityStore.param(value, name, params);
                } else {
                    params.append(name, value === null || value === undefined ? '' : value);
                }
            });
        }

        return params;
    },

    /**
     * Perform an AJAX request, returning a Promise that resolves with the response text.
     *
     * options:
     *  - method: GET (default) or POST
     *  - data: an object, or an already encoded string
     */
    request: function(url, options) {
        options = options || {};
        var method = (options.method || 'GET').toUpperCase();
        var data = options.data;
        var body = null;

        if (data && typeof data !== 'string') {
            data = communityStore.param(data).toString();
        }

        if (method === 'GET') {
            if (data) {
                url += (url.indexOf('?') === -1 ? '?' : '&') + data;
            }
        } else {
            body = data || '';
        }

        return fetch(url, {
            method: method,
            credentials: 'same-origin',
            cache: 'no-store',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body
        }).then(function(response) {
            if (!response.ok) {
                throw new Error(response.statusText);
            }
            return response.text();
        });
    },

    /**
     * Replace the contents of an element with HTML, running any scripts it contains (in order).
     */
    setHTML: function(element, html) {
        element = communityStore.element(element);
        if (!element) {
            return Promise.resolve();
        }
        element.innerHTML = html;
        return communityStore.runScripts(element);
    },

    /**
     * Replace an element with HTML, running any scripts it contains (in order).
     */
    replaceWith: function(element, html) {
        element = communityStore.element(element);
        if (!element) {
            return Promise.resolve();
        }
        var template = document.createElement('template');
        template.innerHTML = html;
        var wrapper = document.createElement('div');
        wrapper.appendChild(template.content);
        var nodes = Array.prototype.slice.call(wrapper.childNodes);
        nodes.forEach(function(node) {
            element.parentNode.insertBefore(node, element);
        });
        element.remove();

        return nodes.reduce(function(promise, node) {
            return promise.then(function() {
                return node.nodeType === 1 ? communityStore.runScripts(node, true) : null;
            });
        }, Promise.resolve());
    },

    /**
     * Execute the scripts inside an element, which do not run when inserted via innerHTML.
     */
    runScripts: function(element, includeSelf) {
        var scripts = Array.prototype.slice.call(element.querySelectorAll('script'));
        if (includeSelf && element.tagName === 'SCRIPT') {
            scripts.unshift(element);
        }

        return scripts.reduce(function(promise, oldScript) {
            return promise.then(function() {
                var type = (oldScript.getAttribute('type') || '').toLowerCase();
                if (type && ['text/javascript', 'application/javascript', 'module'].indexOf(type) === -1) {
                    return null;
                }

                return new Promise(function(resolve) {
                    var script = document.createElement('script');
                    Array.prototype.forEach.call(oldScript.attributes, function(attr) {
                        script.setAttribute(attr.name, attr.value);
                    });
                    if (oldScript.src) {
                        script.async = false;
                        script.onload = resolve;
                        script.onerror = resolve;
                    } else {
                        script.text = oldScript.text;
                    }
                    oldScript.parentNode.replaceChild(script, oldScript);
                    if (!oldScript.src) {
                        resolve();
                    }
                });
            });
        }, Promise.resolve());
    },

    /**
     * Dispatch a submit event on a form, which will be handled by any listeners attached to it.
     */
    triggerSubmit: function(form) {
        form = communityStore.element(form);
        if (!form) {
            return;
        }
        var event = new Event('submit', {bubbles: true, cancelable: true});
        if (form.dispatchEvent(event)) {
            form.submit();
        }
    },

    /**
     * Smoothly scroll the page to an element, taking into account the configured offset.
     */
    scrollTo: function(element) {
        if (!element) {
            return;
        }
        var top = element.getBoundingClientRect().top + window.pageYOffset - (window.CHECKOUTSCROLLOFFSET || 0);
        window.scrollTo({top: top, behavior: 'smooth'});
    },

    setText: function(selector, text) {
        document.querySelectorAll(selector).forEach(function(element) {
            element.textContent = text;
        });
    },

    // Modal

    openModal: function(content, animatecart) {
        var whiteout = document.querySelector('.store-whiteout');

        if (whiteout) {
            communityStore.setHTML(whiteout, content);
        } else {
            whiteout = document.createElement('div');
            whiteout.className = 'store-whiteout';
            (document.querySelector('.ccm-page') || document.body).appendChild(whiteout);
            communityStore.setHTML(whiteout, content);

            whiteout.addEventListener('click', function(e) {
                if (e.target !== whiteout) return; // only allow the actual whiteout background to close the dialog
                communityStore.exitModal();
            });

            setTimeout(communityStore.activateModal, 10);
        }

        if (animatecart) {
            setTimeout(communityStore.activateModal, 30);
        } else {
            communityStore.activateModal();
        }
    },

    activateModal: function() {
        document.querySelectorAll('.store-cart-modal').forEach(function(modal) {
            modal.classList.add('store-cart-modal-active');
        });
    },

    waiting: function() {
        communityStore.openModal("<div class='store-spinner-container'><div class='store-spinner'></div></div>");
    },

    exitModal: function() {
        document.querySelectorAll('.store-whiteout').forEach(function(whiteout) {
            whiteout.remove();
        });
    },

    productModal: function(pID, locale) {
        communityStore.waiting();
        communityStore.request(PRODUCTMODAL, {data: {pID: pID, locale: locale}})
            .then(function(modalContent) {
                communityStore.openModal(modalContent);
            });
    },

    // Cart

    updateCartList: function() {
        var cartList = document.querySelector('.store-checkout-cart-contents #cart');

        if (cartList) {
            communityStore.request(CHECKOUTURL + '/getCartList' + TRAILINGSLASH)
                .then(function(data) {
                    communityStore.replaceWith(cartList, data);
                });
        }
    },

    displayCart: function(res, animatecart) {
        communityStore.request(CARTURL + '/getmodal' + TRAILINGSLASH + '?t=' + Date.now(), {method: 'POST', data: res || ''})
            .then(function(data) {
                communityStore.openModal(data, animatecart);
            });
    },

    addToCart: function(form) {
        form = communityStore.element(form);
        if (!form) {
            return false;
        }

        var valid = true;
        var priceinput = form.querySelector('.store-product-customer-price-entry-field');

        if (priceinput) {
            var max = parseFloat(priceinput.getAttribute('max'));
            var min = parseFloat(priceinput.getAttribute('min'));
            var customerprice = parseFloat(priceinput.value);

            if (customerprice < min || customerprice > max || !isFinite(customerprice)) {
                priceinput.focus();
                valid = false;
            }
        }

        form.querySelectorAll('input[required], textarea[required], select[required]').forEach(function(requiredField) {
            if (requiredField.value === '') {
                requiredField.focus();
                valid = false;
            }
        });

        if (!valid) {
            return false;
        }

        var qtyfield = form.querySelector('.store-product-qty');
        var qty = qtyfield ? qtyfield.value : 0;

        if (qty > 0) {
            communityStore.waiting();
            communityStore.request(CARTURL + '/add' + TRAILINGSLASH, {method: 'POST', data: communityStore.serialize(form)})
                .then(function(data) {
                    var res = JSON.parse(data);

                    if (res.product.pAutoCheckout == '1') {
                        window.location.href = CHECKOUTURL;
                        return false;
                    }

                    communityStore.displayCart(res, true);
                    communityStore.refreshCartTotals();
                })
                .catch(function() {
                    communityStore.exitModal();
                });
        } else {
            alert(QTYMESSAGE);
            if (qtyfield) {
                qtyfield.focus();
            }
        }
    },

    getModalToken: function() {
        var token = document.querySelector('#store-modal-cart [name=ccm_token]');
        return token ? token.value : '';
    },

    // Update a single item in cart
    updateItem: function(instanceID, modal) {
        var qtyField = document.querySelector("[data-instance-id='" + instanceID + "'] .store-cart-list-product-qty .form-control");

        communityStore.request(CARTURL + '/update' + TRAILINGSLASH, {
            method: 'POST',
            data: {instance: instanceID, pQty: qtyField ? qtyField.value : '', ccm_token: communityStore.getModalToken()}
        }).then(function(data) {
            if (modal) {
                communityStore.displayCart(JSON.parse(data));
                communityStore.refreshCartTotals();
            }
        });
    },

    // Update multiple item quantities
    updateMultiple: function(instances, quantities, modal) {
        communityStore.request(CARTURL + '/update' + TRAILINGSLASH, {
            method: 'POST',
            data: {instance: instances, pQty: quantities, ccm_token: communityStore.getModalToken()}
        }).then(function(data) {
            if (modal) {
                communityStore.displayCart(JSON.parse(data));
                communityStore.refreshCartTotals();
            }
        });
    },

    removeItem: function(instanceID, modal) {
        communityStore.request(CARTURL + '/remove' + TRAILINGSLASH, {
            method: 'POST',
            data: {instance: instanceID, ccm_token: communityStore.getModalToken()}
        }).then(function(data) {
            if (modal) {
                communityStore.displayCart(JSON.parse(data));
                communityStore.refreshCartTotals();
            }
        });
    },

    clearCart: function(modal) {
        communityStore.request(CARTURL + '/clear' + TRAILINGSLASH, {
            method: 'POST',
            data: {clear: 1, ccm_token: communityStore.getModalToken()}
        }).then(function(data) {
            communityStore.broadcastCartRefresh({
                action: 'clear'
            });
            if (modal) {
                communityStore.displayCart(JSON.parse(data));
            }
            communityStore.setText('.store-utility-links .store-items-counter', 0);
            communityStore.setText('.store-utility-links .store-total-cart-amount', '');
            document.querySelectorAll('.store-utility-links').forEach(function(links) {
                links.classList.add('store-cart-empty');
            });
        });
    },

    refreshCartTotals: function(callback, nobroadcast) {
        nobroadcast = nobroadcast || false;
        communityStore.request(CARTURL + '/getCartSummary' + TRAILINGSLASH + '?t=' + Date.now())
            .then(function(response) {
                var values = JSON.parse(response);
                var itemCount = values.itemCount;
                var subTotal = values.subTotal;
                var total = values.total;
                var totalCents = values.totalCents;
                var taxes = values.taxes;
                var shippingTotal = values.shippingTotal;
                var shippingTotalRaw = values.shippingTotalRaw;

                if (!nobroadcast) {
                    communityStore.broadcastCartRefresh({
                        action: itemCount > 0 ? 'refresh' : 'clear'
                    });
                }

                var utilityLinks = document.querySelectorAll('.store-utility-links');

                if (itemCount == 0) {
                    communityStore.setText('.store-utility-links .store-items-counter', 0);
                    communityStore.setText('.store-utility-links .store-total-cart-amount', '');
                    utilityLinks.forEach(function(links) {
                        links.classList.add('store-cart-empty');
                    });
                } else {
                    communityStore.setText('.store-utility-links .store-items-counter', itemCount);
                    communityStore.setText('.store-cart-grand-total-value', subTotal);
                    communityStore.setText('.store-utility-links .store-total-cart-amount', subTotal);
                    utilityLinks.forEach(function(links) {
                        links.classList.remove('store-cart-empty');
                    });
                }

                var shippingTotalElement = document.getElementById('shipping-total');
                if (shippingTotalElement) {
                    if (shippingTotalRaw === false) {
                        shippingTotalElement.textContent = shippingTotalElement.getAttribute('data-unknown-label');
                    } else if (shippingTotalRaw <= 0) {
                        shippingTotalElement.textContent = shippingTotalElement.getAttribute('data-no-charge-label');
                    } else {
                        shippingTotalElement.textContent = shippingTotal;
                    }
                }

                var taxList = document.getElementById('store-taxes');
                if (taxList) {
                    if (taxes.length > 0) {
                        taxList.innerHTML = '';
                        taxList.classList.remove('d-none', 'hidden');
                        for (var i = 0; i < taxes.length; i++) {
                            if (taxes[i].taxed === true) {
                                var item = document.createElement('li');
                                item.className = 'store-line-item store-tax-item list-group-item';
                                var label = document.createElement('strong');
                                label.textContent = taxes[i].name + ':';
                                var amount = document.createElement('span');
                                amount.className = 'store-tax-amount';
                                amount.textContent = taxes[i].taxamount;
                                item.appendChild(label);
                                item.appendChild(document.createTextNode(' '));
                                item.appendChild(amount);
                                taxList.appendChild(item);
                            }
                        }
                    } else {
                        taxList.classList.add('d-none', 'hidden');
                    }
                }

                communityStore.setText('.store-sub-total-amount', subTotal);
                document.querySelectorAll('.store-total-amount').forEach(function(element) {
                    element.textContent = total;
                    element.setAttribute('data-total-cents', totalCents);
                });

                if (callback) {
                    callback();
                }
            });
    },

    broadcastCartRefresh: function(message) {
        if (window.sysend) {
            sysend.broadcast('refresh_cart', message);
        }
    },

    // Checkout

    loadViaHash: function() {
        var hash = window.location.hash.replace('#', '');
        if (hash !== '') {
            var pane = document.getElementById('store-checkout-form-group-' + hash);
            if (!pane) {
                return;
            }
            document.querySelectorAll('.store-active-form-group').forEach(function(group) {
                group.classList.remove('store-active-form-group');
            });
            pane.classList.add('store-active-form-group');
            communityStore.scrollTo(pane);
        }
    },

    updateStates: function(type, load, callback) {
        var stateField = document.getElementById('store-checkout-' + type + '-state');
        if (!stateField) {
            return;
        }

        var selectedState = load ? communityStore.val('store-checkout-saved-' + type + '-state') : '';
        var dataList = {};
        Object.keys(stateField.dataset).forEach(function(key) {
            dataList[key] = stateField.dataset[key];
        });

        communityStore.request(HELPERSURL + '/stateprovince/getstates' + TRAILINGSLASH, {
            method: 'POST',
            data: {
                country: communityStore.val('store-checkout-' + type + '-country'),
                selectedState: selectedState,
                type: type,
                'class': stateField.className,
                data: JSON.stringify(dataList)
            }
        }).then(function(states) {
            return communityStore.replaceWith(document.getElementById('store-checkout-' + type + '-state'), states);
        }).then(function() {
            if (callback) {
                callback();
            }
        });
    },

    updateBillingStates: function(load, callback) {
        communityStore.updateStates('billing', load, callback);
    },

    updateShippingStates: function(load, callback) {
        communityStore.updateStates('shipping', load, callback);
    },

    nextPane: function(obj) {
        obj = communityStore.element(obj);
        if (typeof obj.checkValidity === 'undefined' || obj.checkValidity()) {
            var group = obj.closest('.store-checkout-form-group');
            var body = group ? group.querySelector('.store-checkout-form-group-body') : null;
            var pane = body ? body.parentElement.nextElementSibling : null;

            document.querySelectorAll('.store-active-form-group').forEach(function(active) {
                active.classList.remove('store-active-form-group');
            });

            if (group) {
                group.classList.add('store-checkout-form-group-complete');
            }

            if (!pane) {
                return;
            }

            pane.classList.add('store-active-form-group');
            communityStore.scrollTo(pane);

            var firstInput = pane.querySelector('input');
            if (firstInput) {
                firstInput.focus();
            }

            if (pane.id === 'store-checkout-form-group-payment') {
                communityStore.showPaymentMethods();
            }

            if (pane.id === 'store-checkout-form-group-shipping') {
                if (pane.getAttribute('data-autoskip')) {
                    communityStore.triggerSubmit(pane);
                }
            }
        }
    },

    showShippingMethods: function(callback) {
        var done = function() {
            communityStore.exitModal();
            if (callback) {
                callback();
            }
        };

        communityStore.request(HELPERSURL + '/shipping/getshippingmethods' + TRAILINGSLASH)
            .then(function(html) {
                return communityStore.setHTML('#store-checkout-shipping-method-options', html);
            })
            .then(done, done);
    },

    showPaymentMethods: function(callback) {
        communityStore.request(HELPERSURL + '/shipping/getpaymentmethods' + TRAILINGSLASH)
            .then(function(html) {
                return communityStore.setHTML('#store-checkout-payment-method-options', html);
            })
            .then(function() {
                window.dispatchEvent(new Event('load'));
                var paymentForm = document.getElementById('store-checkout-form-group-payment');
                if (paymentForm) {
                    paymentForm.classList.add('payment-form-ready');
                }
                communityStore.showPaymentForm();
                communityStore.exitModal();
                if (callback) {
                    callback();
                }
            })
            .catch(function() {
                communityStore.exitModal();
            });
    },

    showPaymentForm: function() {
        var checked = document.querySelector("#store-checkout-payment-method-options input[type='radio']:checked");
        var pmID = checked ? checked.getAttribute('data-payment-method-id') : null;

        document.querySelectorAll('.store-payment-method-container').forEach(function(container) {
            container.classList.toggle('hidden', container.getAttribute('data-payment-method-id') !== pmID);
        });
    },

    copyBillingToShipping: function() {
        ['first-name', 'last-name', 'email', 'phone', 'company', 'address-1', 'address-2', 'country', 'city', 'zip'].forEach(function(field) {
            var billing = document.getElementById('store-checkout-billing-' + field);
            var shipping = document.getElementById('store-checkout-shipping-' + field);
            if (billing && shipping) {
                shipping.value = billing.value;
            }
        });

        var billingState = document.getElementById('store-checkout-billing-state');
        var shippingState = document.getElementById('store-checkout-shipping-state');

        if (billingState && shippingState) {
            var state = billingState.cloneNode(true);
            state.setAttribute('name', 'store-checkout-shipping-state');
            state.setAttribute('id', 'store-checkout-shipping-state');
            shippingState.replaceWith(state);
            state.value = billingState.value;
        }
    },

    sortNumber: function(a, b) {
        return a - b;
    },

    hasFormValidation: function() {
        return (typeof document.createElement('input').checkValidity == 'function');
    },

    // Product filtering

    submitProductFilter: function(element) {
        element = communityStore.element(element);
        var filterform = element.closest('form');
        var checkboxes = filterform.querySelectorAll('input:checked');
        var search = {};
        var matchtypes = {};

        checkboxes.forEach(function(field) {
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

        Object.keys(search).forEach(function(key) {
            strings.push(key + '=' + search[key].join(matchtypes[key]));
        });

        var price = filterform.querySelector("[name='price']");

        if (price && price.value) {
            var min = parseFloat(price.getAttribute('data-min'));
            var max = parseFloat(price.getAttribute('data-max'));

            if (min + '-' + max !== price.value) {
                strings.push('price=' + encodeURIComponent(price.value));
            }
        }

        var searchstring = strings.join('&');
        var params = new URLSearchParams();
        var hasparams = false;

        new URLSearchParams(location.search).forEach(function(value, key) {
            if (key.indexOf('sort') === 0) {
                params.append(key, value);
                hasparams = true;
            }
        });

        if (hasparams) {
            searchstring = searchstring + '&' + params.toString();
        }

        if (filterform.id) {
            searchstring += '#' + filterform.id;
        }

        window.location = (filterform.getAttribute('action') || '') + '?' + searchstring;
    },

    clearProductFilter: function(element) {
        element = communityStore.element(element);
        var filterform = element.closest('form');

        filterform.querySelectorAll('input:checked').forEach(function(field) {
            field.checked = false;
        });

        filterform.querySelectorAll('[name="price"]').forEach(function(field) {
            field.value = '';
        });

        communityStore.submitProductFilter(element);
    },

    number_format: function(number, decimals, dec_point, thousands_sep) {
        number = number * 1; // makes sure `number` is numeric value
        var str = number.toFixed(decimals ? decimals : 0).toString().split('.');
        var parts = [];
        for (var i = str[0].length; i > 0; i -= 3) {
            parts.unshift(str[0].substring(Math.max(0, i - 3), i));
        }
        str[0] = parts.join(thousands_sep ? thousands_sep : ',');
        return str.join(dec_point ? dec_point : '.');
    },

    // Product option/variation handling

    /**
     * Update the price, availability, image, SKU, etc. of a product form when its options change.
     *
     * settings:
     *  - imageSelector: selector of the product image to swap for a variation image
     *  - wholesaleTemplate: whether to render the variation's wholesale price template (otherwise just the wholesale price is shown)
     */
    updateProductVariation: function(pdb, settings) {
        settings = settings || {};
        pdb = communityStore.element(pdb);

        var pID = pdb.getAttribute('data-product-id');
        var ar = [];
        var priceAdjust = 0;
        var lang = (window.CCM_ACTIVE_LOCALE || 'en_US').replace('_', '-');
        var numberFormatter = {};

        // mirrors jQuery's .data() behaviour, where "0" and "" are treated as empty
        var numericData = function(element, name) {
            var value = parseFloat(element.getAttribute('data-' + name));
            return isFinite(value) ? value : 0;
        };

        if (!CURRENCYCODE) {
            numberFormatter.format = function(amount) {
                return CURRENCYSYMBOL + communityStore.number_format(amount, 2, CURRENCYDECIMAL, CURRENCYGROUP);
            };
        } else {
            numberFormatter = Intl.NumberFormat(lang, {style: 'currency', currency: CURRENCYCODE});
        }

        pdb.querySelectorAll('.store-product-options select').forEach(function(select) {
            Array.prototype.forEach.call(select.selectedOptions, function(option) {
                priceAdjust += numericData(option, 'adjustment');
            });
        });

        pdb.querySelectorAll('.store-product-options input:checked').forEach(function(input) {
            priceAdjust += numericData(input, 'adjustment');
        });

        pdb.querySelectorAll('.store-product-options select.store-product-variation, .store-product-options input.store-product-variation:checked').forEach(function(field) {
            ar.push(field.value);
        });

        ar.sort(communityStore.sortNumber);

        var variation = window.variationData && variationData[pID] ? variationData[pID][ar.join('_')] : null;
        var priceHolder = pdb.querySelector('.store-product-price');

        var setPriceHTML = function(selector, html) {
            priceHolder.querySelectorAll(selector).forEach(function(element) {
                element.innerHTML = html;
            });
        };

        var setHidden = function(selector, hidden) {
            pdb.querySelectorAll(selector).forEach(function(element) {
                element.classList.toggle('hidden', hidden);
            });
        };

        if (variation) {
            var result = numberFormatter.format(parseFloat(variation.price) + priceAdjust);

            if (priceHolder) {
                if (variation.wholesalePrice) {
                    var wholesaleresult = numberFormatter.format(parseFloat(variation.wholesalePrice) + priceAdjust);

                    if (settings.wholesaleTemplate) {
                        priceHolder.innerHTML = variation.wholesaleTemplate;
                        setPriceHTML('.store-list-price', result);
                        setPriceHTML('.store-wholesale-price', wholesaleresult);
                    } else {
                        priceHolder.innerHTML = wholesaleresult;
                    }
                } else if (variation.salePrice) {
                    priceHolder.innerHTML = variation.saleTemplate;
                    setPriceHTML('.store-sale-price', numberFormatter.format(parseFloat(variation.salePrice) + priceAdjust));
                    setPriceHTML('.store-original-price', result);
                } else {
                    priceHolder.innerHTML = result;
                }
            }

            if (variation.available) {
                setHidden('.store-out-of-stock-label', true);
                setHidden('.store-not-available-label', true);
                setHidden('.store-btn-add-to-cart', false);
            } else {
                setHidden('.store-out-of-stock-label', !!variation.disabled);
                setHidden('.store-not-available-label', !variation.disabled);
                setHidden('.store-btn-add-to-cart', true);
            }

            if (variation.imageThumb && settings.imageSelector) {
                var image = pdb.querySelector(settings.imageSelector);

                if (image) {
                    image.setAttribute('src', variation.imageThumb);
                    if (image.parentElement && image.parentElement.tagName === 'A') {
                        image.parentElement.setAttribute('href', variation.image);
                    }
                }
            }

            if (variation.sku) {
                pdb.querySelectorAll('.store-product-sku span').forEach(function(element) {
                    element.innerHTML = variation.sku;
                });
            }

            if (variation.weight) {
                pdb.querySelectorAll('.store-product-weight-value').forEach(function(element) {
                    element.innerHTML = variation.weight;
                });
            }

            var quantityField = pdb.querySelector('.store-product-qty');
            if (quantityField) {
                if (variation.maxCart !== false) {
                    quantityField.setAttribute('max', variation.maxCart);
                    if (parseFloat(quantityField.value) > parseFloat(variation.maxCart)) {
                        quantityField.value = parseFloat(variation.maxCart);
                    }
                } else {
                    quantityField.removeAttribute('max');
                    if (parseFloat(quantityField.value) === 0) {
                        quantityField.value = 1;
                    }
                }
            }
        } else if (priceHolder) {
            if (numericData(priceHolder, 'original-price')) {
                setPriceHTML('.store-sale-price', numberFormatter.format(numericData(priceHolder, 'price') + priceAdjust));
                setPriceHTML('.store-original-price', numberFormatter.format(numericData(priceHolder, 'original-price') + priceAdjust));
            } else if (numericData(priceHolder, 'list-price')) {
                setPriceHTML('.store-list-price', numberFormatter.format(numericData(priceHolder, 'list-price') + priceAdjust));
                setPriceHTML('.store-wholesale-price', numberFormatter.format(numericData(priceHolder, 'price') + priceAdjust));
            } else {
                priceHolder.innerHTML = numberFormatter.format(numericData(priceHolder, 'price') + priceAdjust);
            }
        }
    },

    // Shared handling for the checkout address/VAT forms

    showCheckoutErrors: function(form, errors, extraClass) {
        var body = form.querySelector('.store-checkout-form-group-body');
        if (!body) {
            return;
        }
        var container = document.createElement('div');
        container.className = 'store-checkout-errors';
        var alertBox = document.createElement('div');
        alertBox.className = 'store-checkout-error alert alert-danger' + (extraClass ? ' ' + extraClass : '');
        errors.forEach(function(error, index) {
            if (index > 0) {
                alertBox.appendChild(document.createElement('br'));
            }
            alertBox.appendChild(document.createTextNode(error));
        });
        container.appendChild(alertBox);
        body.insertBefore(container, body.firstChild);
    },

    clearCheckoutErrors: function(form) {
        form.querySelectorAll('.store-checkout-errors').forEach(function(errors) {
            errors.remove();
        });
    },

    setSummary: function(form, selector, html) {
        form.querySelectorAll('.store-checkout-form-group-summary ' + selector).forEach(function(element) {
            element.innerHTML = html === null || html === undefined ? '' : html;
        });
    },

    toggleDisplay: function(element, show) {
        if (element) {
            element.style.display = show ? '' : 'none';
        }
    }
};

communityStore.ready(function() {
    if (window.sysend) {
        sysend.on('refresh_cart', function(data) {
            var isCart = window.location.href.indexOf(CARTURL);
            var isCheckout = window.location.href.indexOf(CHECKOUTURL);
            if (isCart !== -1 || (data.action == 'clear' && isCheckout !== -1)) {
                // we are on the /cart page
                // or we're on the /checkout page and we just cleared the cart
                // so let's just reload
                // Here we cannot use window.location.reload();
                // otherwise it sends all post values again when on the /cart page
                // and it might alter the cart in unpredictable ways
                // Also if we're on /checkout and the cart is empty the controller would redirect to /cart
                // so let's do it right away to avoid a redirect
                window.location = isCart !== -1 ? window.location : CARTURL;
            } else if (data.action == 'code' && isCheckout !== -1) {
                // we're on the /checkout page and we just added or removed a coupon
                // so let's just reload
                window.location = window.location;
            } else {
                // Let's just update the utility links
                if (isCheckout !== -1) {
                    // we are on the /checkout page so we can update the cart table
                    communityStore.updateCartList();
                }
                communityStore.refreshCartTotals(false, true);
                communityStore.exitModal();
            }
        });
    }

    if (document.querySelector('.store-checkout-form-shell form')) {
        communityStore.updateBillingStates(true);
        if (document.getElementById('store-checkout-form-group-shipping')) {
            communityStore.updateShippingStates(true);
            communityStore.showShippingMethods();
        }
        communityStore.showPaymentForm();
    }

    var billingForm = document.getElementById('store-checkout-form-group-billing');

    if (billingForm) {
        billingForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var form = this;
            communityStore.clearCheckoutErrors(form);

            var fieldData = communityStore.serialize(form) + '&adrType=billing';

            communityStore.waiting();
            communityStore.request(CHECKOUTURL + '/updater' + TRAILINGSLASH, {method: 'POST', data: fieldData})
                .then(function(result) {
                    var response = JSON.parse(result);

                    if (response.error == false) {
                        var copyBilling = document.getElementById('store-copy-billing');

                        if (copyBilling && copyBilling.checked) {
                            communityStore.copyBillingToShipping();
                            communityStore.triggerSubmit(document.getElementById('store-checkout-form-group-shipping'));
                            copyBilling.checked = false;
                        } else {
                            communityStore.exitModal();
                        }

                        communityStore.setSummary(form, '.store-summary-name', response.first_name + ' ' + response.last_name);
                        communityStore.setSummary(form, '.store-summary-phone', response.phone);
                        communityStore.setSummary(form, '.store-summary-email', response.email);
                        communityStore.setSummary(form, '.store-summary-address', response.address);
                        communityStore.setSummary(form, '.store-summary-notes', response.notes);
                        communityStore.setSummary(form, '.store-summary-company', response.company);

                        var attributeValues = form.querySelector('#store-attribute-values');
                        if (attributeValues) {
                            if (response.attribute_display) {
                                attributeValues.innerHTML = response.attribute_display;
                            }
                            communityStore.toggleDisplay(attributeValues, !!response.attribute_display);
                        }

                        communityStore.toggleDisplay(form.querySelector('#store-check-notes-container'), !!response.notes);

                        communityStore.nextPane(form);
                        communityStore.refreshCartTotals();
                    } else {
                        communityStore.showCheckoutErrors(form, response.errors);
                        communityStore.exitModal();
                    }
                })
                .catch(function() {
                    communityStore.exitModal();
                });
        });
    }

    var shippingForm = document.getElementById('store-checkout-form-group-shipping');

    if (shippingForm) {
        shippingForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var form = this;
            communityStore.clearCheckoutErrors(form);

            var fieldData = communityStore.serialize(form) + '&adrType=shipping';

            communityStore.waiting();
            communityStore.request(CHECKOUTURL + '/updater' + TRAILINGSLASH, {method: 'POST', data: fieldData})
                .then(function(result) {
                    var response = JSON.parse(result);
                    if (response.error == false) {
                        communityStore.setSummary(form, '.store-summary-name', response.first_name + ' ' + response.last_name);
                        communityStore.setSummary(form, '.store-summary-address', response.address);
                        communityStore.setSummary(form, '.store-summary-company', response.company);
                        communityStore.setSummary(form, '.store-summary-vat-number', response.vat_number ? response.vat_number : '-');

                        communityStore.showShippingMethods(function() {
                            communityStore.refreshCartTotals();
                            communityStore.nextPane(form);

                            var shippingMethodForm = document.getElementById('store-checkout-form-group-shipping-method');
                            if (shippingMethodForm && shippingMethodForm.getAttribute('data-autoskip')) {
                                if (shippingMethodForm.querySelectorAll('.store-shipping-method').length === 1) {
                                    communityStore.triggerSubmit(shippingMethodForm);
                                }
                            }
                        });
                    } else {
                        communityStore.showCheckoutErrors(form, response.errors);
                        communityStore.exitModal();
                    }
                })
                .catch(function() {
                    communityStore.exitModal();
                });
        });
    }

    var vatForm = document.getElementById('store-checkout-form-group-vat');

    if (vatForm) {
        vatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var form = this;
            communityStore.clearCheckoutErrors(form);
            var token = form.querySelector('[name=ccm_token]');

            communityStore.waiting();
            communityStore.request(HELPERSURL + '/tax/setvatnumber' + TRAILINGSLASH, {
                method: 'POST',
                data: {
                    vat_number: communityStore.val('store-checkout-shipping-vat-number'),
                    ccm_token: token ? token.value : ''
                }
            }).then(function(result) {
                var response = JSON.parse(result);
                if (response.error == false) {
                    var vatSummary = form.querySelector('.store-checkout-form-group-summary .store-summary-vat-number');
                    if (vatSummary) {
                        vatSummary.innerHTML = response.vat_number !== '' ? response.vat_number : vatSummary.getAttribute('data-vat-blank');
                    }
                    communityStore.refreshCartTotals();
                    communityStore.nextPane(form);
                    communityStore.exitModal();
                } else {
                    communityStore.showCheckoutErrors(form, response.errors);
                    communityStore.exitModal();
                }
            }).catch(function() {
                communityStore.exitModal();
            });
        });
    }

    var shippingMethodForm = document.getElementById('store-checkout-form-group-shipping-method');

    if (shippingMethodForm) {
        shippingMethodForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var form = this;
            communityStore.waiting();
            var token = form.querySelector('[name=ccm_token]');
            var checked = document.querySelector("#store-checkout-shipping-method-options input[type='radio']:checked");

            if (!checked) {
                communityStore.exitModal();
                alert(document.getElementById('store-checkout-shipping-method-options').getAttribute('data-error-message'));
            } else {
                var details = checked.parentElement.querySelector('.store-shipping-details');
                form.querySelectorAll('.summary-shipping-instructions').forEach(function(element) {
                    element.textContent = communityStore.val('store-checkout-shipping-instructions');
                });
                form.querySelectorAll('.summary-shipping-method').forEach(function(element) {
                    element.innerHTML = details ? details.innerHTML.trim() : '';
                });

                communityStore.request(HELPERSURL + '/shipping/selectshipping' + TRAILINGSLASH, {
                    method: 'POST',
                    data: {
                        smID: checked.value,
                        sInstructions: communityStore.val('store-checkout-shipping-instructions'),
                        ccm_token: token ? token.value : ''
                    }
                }).then(function() {
                    communityStore.refreshCartTotals(function() {
                        communityStore.nextPane(form);
                        communityStore.exitModal();
                    });
                }).catch(function() {
                    communityStore.exitModal();
                });
            }
        });
    }

    communityStore.on('click', '.store-btn-previous-pane', function(e) {
        // hide the body of the current pane, go to the previous pane, show that body.
        e.preventDefault();
        var group = this.closest('.store-checkout-form-group');
        var body = group ? group.querySelector('.store-checkout-form-group-body') : null;
        var pane = body ? body.parentElement.previousElementSibling : null;

        document.querySelectorAll('.store-active-form-group').forEach(function(active) {
            active.classList.remove('store-active-form-group');
        });

        if (pane) {
            pane.classList.add('store-active-form-group');
            communityStore.scrollTo(pane.parentElement);
        }

        if (group && group.previousElementSibling) {
            group.previousElementSibling.classList.remove('store-checkout-form-group-complete');
        }
    });

    communityStore.on('change', "#store-checkout-payment-method-options input[type='radio']", function() {
        communityStore.showPaymentForm();
    });

    communityStore.on('click', '.store-btn-cart-list-remove', function(e) {
        e.preventDefault();
        var deleteForm = document.getElementById('deleteform');

        if (deleteForm && this.closest('#store-cart')) {
            // on the cart page, submit the form to remove the item
            deleteForm.querySelector('input[name=instance]').value = this.getAttribute('data-instance');
            deleteForm.submit();
        } else {
            communityStore.removeItem(this.getAttribute('data-instance-id'), this.getAttribute('data-modal'));
        }
    });

    communityStore.on('click', '.store-btn-add-to-cart', function(e) {
        var form = this.closest('form');

        if (communityStore.hasFormValidation() && form && !form.checkValidity()) {
            // let the browser display the validation messages
            if (form.reportValidity) {
                e.preventDefault();
                form.reportValidity();
            }
            return;
        }

        e.preventDefault();
        communityStore.addToCart(form);
    });

    communityStore.on('submit', '.store-product-block', function(e) {
        e.preventDefault();
        if (this.querySelector('.store-btn-add-to-cart')) {
            communityStore.addToCart(this);
        }
    });

    communityStore.on('click', '.store-price-suggestion', function(e) {
        e.preventDefault();
        var productform = this.closest('form');
        var priceField = productform.querySelector('.store-product-customer-price-entry-field');
        if (priceField) {
            priceField.value = this.getAttribute('data-suggestion-value');
        }
        communityStore.addToCart(productform);
    });

    communityStore.on('click', '.store-cart-link-modal, .store-cart-modal-link', function(e) {
        e.preventDefault();
        communityStore.displayCart(false, true);
    });

    communityStore.on('click', '.store-modal-exit, .store-btn-cart-modal-continue', function(e) {
        e.preventDefault();
        communityStore.exitModal();
    });

    communityStore.on('click', '.store-btn-cart-modal-clear', function(e) {
        e.preventDefault();
        communityStore.clearCart(true);
        this.classList.add('disabled');
    });

    communityStore.on('click', '.store-btn-cart-modal-update', function(e) {
        var form = this.closest('form');

        if (communityStore.hasFormValidation() && form && !form.checkValidity()) {
            if (form.reportValidity) {
                e.preventDefault();
                form.reportValidity();
            }
            return;
        }

        e.preventDefault();

        var instances = Array.prototype.map.call(document.querySelectorAll("#store-modal-cart input[name='instance[]']"), function(field) {
            return field.value;
        });
        var pQty = Array.prototype.map.call(document.querySelectorAll("#store-modal-cart input[name='pQty[]']"), function(field) {
            return field.value;
        });

        communityStore.updateMultiple(instances, pQty, true);
        this.classList.add('disabled');
    });

    communityStore.on('click', '.store-btn-filter', function(e) {
        e.preventDefault();
        communityStore.submitProductFilter(this);
    });

    communityStore.on('change', '.store-product-filter-block-auto input[type="checkbox"]', function() {
        communityStore.submitProductFilter(this);
    });

    communityStore.on('click', '.store-btn-filter-clear', function(e) {
        e.preventDefault();
        communityStore.clearProductFilter(this);
    });

    document.addEventListener('keyup', function(e) {
        if (e.key === 'Escape' && document.querySelector('.store-whiteout')) {
            communityStore.exitModal();
        }
    });

    if (billingForm && billingForm.getAttribute('data-autoskip')) {
        communityStore.triggerSubmit(billingForm);
    }
});
