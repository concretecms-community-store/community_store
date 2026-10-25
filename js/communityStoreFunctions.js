/* global TomSelect, Sortable */

var communityStoreDashboard = {

    /**
     * Run a callback once the DOM is ready.
     */
    ready: function (callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback);
        } else {
            callback();
        }
    },

    /**
     * Resolve a selector, element or jQuery-like object to a DOM element.
     */
    element: function (element) {
        if (typeof element === 'string') {
            return document.querySelector(element);
        }
        if (element && element.jquery) {
            return element[0] || null;
        }
        return element || null;
    },

    /**
     * Attach a delegated event listener.
     */
    on: function (root, eventName, selector, handler) {
        root.addEventListener(eventName, function (e) {
            var target = e.target.closest ? e.target.closest(selector) : null;
            if (target && root.contains(target)) {
                handler.call(target, e, target);
            }
        });
    },

    /**
     * Serialize the named, enabled fields inside a container, in the same format as a form submission.
     */
    serialize: function (container) {
        var params = new URLSearchParams();
        container.querySelectorAll('input, select, textarea').forEach(function (field) {
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
                Array.prototype.forEach.call(field.selectedOptions, function (option) {
                    params.append(field.name, option.value);
                });
                return;
            }
            params.append(field.name, field.value);
        });

        return params.toString();
    },

    /**
     * Make the children of a container sortable by drag and drop.
     */
    sortable: function (element, options) {
        element = communityStoreDashboard.element(element);
        if (!element || typeof Sortable === 'undefined') {
            return null;
        }

        return Sortable.create(element, Object.assign({animation: 150}, options || {}));
    },

    /**
     * Enhance a select element with a searchable multi/single select.
     */
    select: function (element, options) {
        element = communityStoreDashboard.element(element);
        if (!element || typeof TomSelect === 'undefined' || element.tomselect) {
            return element ? element.tomselect || null : null;
        }

        var settings = {
            plugins: element.multiple ? ['remove_button'] : [],
            maxOptions: null
        };

        return new TomSelect(element, Object.assign(settings, options || {}));
    },

    /**
     * Enhance an input as a product search field, querying the /productfinder route.
     *
     * options:
     *  - url: the product finder URL
     *  - initial: {id: ..., text: ...} for a preselected product
     *  - onSelect: function({id, text}) called when a product is chosen. The field is cleared afterwards.
     *  - settings: additional Tom Select settings, e.g. {plugins: ['clear_button']}
     */
    productSearch: function (element, options) {
        element = communityStoreDashboard.element(element);
        options = options || {};

        if (!element || typeof TomSelect === 'undefined') {
            return null;
        }

        var settings = {
            valueField: 'id',
            labelField: 'text',
            searchField: ['text'],
            maxItems: 1,
            maxOptions: null,
            create: false,
            // results are already filtered server side
            score: function () {
                return function () {
                    return 1;
                };
            },
            shouldLoad: function (query) {
                return query.length >= 2;
            },
            load: function (query, callback) {
                var url = options.url + (options.url.indexOf('?') === -1 ? '?' : '&') + 'q=' + encodeURIComponent(query);
                fetch(url, {credentials: 'same-origin', headers: {'X-Requested-With': 'XMLHttpRequest'}})
                    .then(function (response) {
                        return response.json();
                    })
                    .then(function (data) {
                        callback((data || []).map(function (item) {
                            return {
                                id: String(item.pID),
                                text: item.name + (item.SKU ? ' (' + item.SKU + ')' : '')
                            };
                        }));
                    })
                    .catch(function () {
                        callback();
                    });
            }
        };

        if (options.initial && options.initial.id) {
            settings.options = [{id: String(options.initial.id), text: options.initial.text}];
            settings.items = [String(options.initial.id)];
        } else {
            element.value = '';
        }

        if (options.onSelect) {
            settings.onItemAdd = function (value) {
                var option = this.options[value];
                options.onSelect({id: value, text: option ? option.text : value});
                this.clear(true);
                this.clearOptions();
            };
        }

        return new TomSelect(element, Object.assign(settings, options.settings || {}));
    },

    /**
     * Create an element from a <template>, filling in values.
     *
     * Within the template:
     *  - data-value="key" sets the element's value (inputs, selects, textareas)
     *  - data-text="key" sets the element's text
     *  - data-checked="key" checks a checkbox when the value is truthy
     *  - data-show-types="a b" / data-hide-types="a b" keeps/removes the element based on options.type
     *
     * Values are set via the DOM, so they never need to be escaped.
     */
    renderTemplate: function (id, data, type) {
        var fragment = document.importNode(document.getElementById(id).content, true);
        data = data || {};

        var hasType = function (element, attribute) {
            return element.getAttribute(attribute).split(' ').indexOf(type) !== -1;
        };

        fragment.querySelectorAll('[data-show-types]').forEach(function (element) {
            if (!hasType(element, 'data-show-types')) {
                element.remove();
            }
        });
        fragment.querySelectorAll('[data-hide-types]').forEach(function (element) {
            if (hasType(element, 'data-hide-types')) {
                element.remove();
            }
        });

        var value = function (key) {
            return data[key] === null || data[key] === undefined ? '' : String(data[key]);
        };

        fragment.querySelectorAll('[data-value]').forEach(function (element) {
            element.value = value(element.getAttribute('data-value'));
            if (element.tagName === 'TEXTAREA') {
                element.textContent = element.value;
            }
            element.removeAttribute('data-value');
        });
        fragment.querySelectorAll('[data-text]').forEach(function (element) {
            element.textContent = value(element.getAttribute('data-text'));
            element.removeAttribute('data-text');
        });
        fragment.querySelectorAll('[data-checked]').forEach(function (element) {
            element.checked = !!data[element.getAttribute('data-checked')] && data[element.getAttribute('data-checked')] !== '0';
            element.removeAttribute('data-checked');
        });

        return fragment.firstElementChild;
    },

    /**
     * Build a list group item with a remove link and a hidden input.
     */
    listItem: function (text, inputName, value) {
        var li = document.createElement('li');
        li.className = 'list-group-item';
        li.appendChild(document.createTextNode(text + ' '));

        var link = document.createElement('a');
        link.innerHTML = '<i class="float-end pull-right fa fa-minus-circle"></i>';
        li.appendChild(link);

        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = inputName;
        input.value = value;
        li.appendChild(input);

        return li;
    },

    /**
     * Make clicking the remove link inside a list's items remove the item.
     */
    removableList: function (element) {
        element = communityStoreDashboard.element(element);
        if (!element) {
            return;
        }
        communityStoreDashboard.on(element, 'click', 'a', function (e, link) {
            e.preventDefault();
            link.closest('li').remove();
        });
    },

    /**
     * Ask for confirmation before a form is submitted.
     */
    confirmSubmit: function (element, message) {
        element = communityStoreDashboard.element(element);
        if (!element) {
            return;
        }
        element.addEventListener('submit', function (e) {
            if (!window.confirm(message)) {
                e.preventDefault();
            }
        });
    },

    /**
     * Open the content of an element in a modal dialog (using the native dialog element).
     * Any .dialog-buttons inside the content are displayed at the bottom of the dialog.
     * The content is put back in its original location when the dialog is closed.
     */
    openDialog: function (element, options) {
        element = communityStoreDashboard.element(element);
        options = options || {};
        if (!element) {
            return null;
        }

        var placeholder = document.createComment('community-store-dialog');
        element.parentNode.insertBefore(placeholder, element);

        var dialog = document.createElement('dialog');
        dialog.className = 'ccm-ui community-store-dialog';
        if (options.width) {
            dialog.style.width = options.width + 'px';
        }

        var header = document.createElement('div');
        header.className = 'community-store-dialog-header';
        var title = document.createElement('h4');
        title.textContent = options.title || '';
        var close = document.createElement('button');
        close.type = 'button';
        close.className = 'btn-close close';
        close.setAttribute('aria-label', 'Close');
        close.innerHTML = '<span aria-hidden="true">&times;</span>';
        close.addEventListener('click', function () {
            dialog.close();
        });
        header.appendChild(title);
        header.appendChild(close);

        var body = document.createElement('div');
        body.className = 'community-store-dialog-body';
        body.appendChild(element);

        dialog.appendChild(header);
        dialog.appendChild(body);

        var buttons = element.querySelector('.dialog-buttons');
        var buttonsPlaceholder = null;
        if (buttons) {
            buttonsPlaceholder = document.createComment('community-store-dialog-buttons');
            buttons.parentNode.insertBefore(buttonsPlaceholder, buttons);
            buttons.classList.add('community-store-dialog-buttons', 'clearfix');
            dialog.appendChild(buttons);
        }

        dialog.addEventListener('close', function () {
            if (buttons && buttonsPlaceholder) {
                buttonsPlaceholder.parentNode.replaceChild(buttons, buttonsPlaceholder);
            }
            placeholder.parentNode.replaceChild(element, placeholder);
            dialog.remove();
        });

        document.body.appendChild(dialog);
        dialog.showModal();

        return dialog;
    },

    /**
     * Close the most recently opened dialog.
     */
    closeDialog: function () {
        var dialogs = document.querySelectorAll('dialog.community-store-dialog[open]');
        if (dialogs.length) {
            dialogs[dialogs.length - 1].close();
        }
    },

    /**
     * POST data to a URL, returning a Promise that resolves with the response text.
     */
    post: function (url, data) {
        return fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'X-Requested-With': 'XMLHttpRequest'},
            body: data instanceof URLSearchParams || data instanceof FormData ? data : new URLSearchParams(data || {})
        }).then(function (response) {
            if (!response.ok) {
                throw new Error(response.statusText);
            }
            return response.text();
        });
    },

    /**
     * Show or hide an element (or a list of elements).
     */
    toggle: function (elements, show) {
        if (!elements) {
            return;
        }
        if (elements instanceof Element) {
            elements = [elements];
        }
        Array.prototype.forEach.call(elements, function (element) {
            element.style.display = show ? '' : 'none';
        });
    },

    /**
     * Add or remove the hidden classes (Bootstrap 3 and 5) on an element (or a list of elements).
     */
    setHidden: function (elements, hidden) {
        if (!elements) {
            return;
        }
        if (elements instanceof Element) {
            elements = [elements];
        }
        Array.prototype.forEach.call(elements, function (element) {
            element.classList.toggle('hidden', !!hidden);
            element.classList.toggle('d-none', !!hidden);
        });
    }
};

communityStoreDashboard.ready(function () {
    var url = window.location.pathname.toString();

    document.querySelectorAll('#group-filters li a').forEach(function (link) {
        if (link.getAttribute('href') === url) {
            link.parentElement.classList.add('active');
        }
    });

    var paneToggles = document.querySelectorAll('a[data-pane-toggle]');

    paneToggles.forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            var paneTarget = toggle.getAttribute('href').replace('#', '');

            document.querySelectorAll('.store-pane').forEach(function (pane) {
                pane.classList.remove('active');
            });
            paneToggles.forEach(function (other) {
                other.parentElement.classList.remove('active');
            });

            var pane = document.getElementById(paneTarget);
            if (pane) {
                pane.classList.add('active');
            }
            toggle.parentElement.classList.add('active');

            try {
                localStorage.setItem('selectedTab', paneTarget);
                localStorage.setItem('selectedTabIndex', Array.prototype.indexOf.call(toggle.parentElement.parentElement.children, toggle.parentElement));
            } catch (ex) {
            }

            if (history.pushState) {
                history.pushState(null, null, '#' + paneTarget);
            } else {
                location.hash = '#' + paneTarget;
            }
        });
    });

    document.querySelectorAll('.confirm-action').forEach(function (button) {
        button.addEventListener('click', function (e) {
            if (!window.confirm(button.getAttribute('data-confirm-message'))) {
                e.preventDefault();
            }
        });
    });

    var generatePage = document.getElementById('btn-generate-page');
    if (generatePage) {
        generatePage.addEventListener('click', function (e) {
            e.preventDefault();
            var pageTemplate = document.getElementById('selectPageTemplate');
            if (window.confirm(generatePage.getAttribute('data-confirm-message'))) {
                window.location = generatePage.getAttribute('href') + '/' + encodeURIComponent(pageTemplate ? pageTemplate.value : '') +
                    '?ccm_token=' + encodeURIComponent(generatePage.getAttribute('data-token') || '');
            }
        });
    }

    window.addEventListener('popstate', function () {
        if (document.querySelector('a[data-pane-toggle]')) {
            updateActiveTab(true);
        }
    });

    if (paneToggles.length > 0) {
        updateActiveTab();
    }
});

function updateActiveTab(historyPopEvent) {
    var hash = window.location.hash;

    if (hash) {
        var paneTarget = hash.replace('#', '');
        var pane = document.getElementById(paneTarget);

        if (!pane) {
            return;
        }

        document.querySelectorAll('.store-pane').forEach(function (other) {
            other.classList.remove('active');
        });

        document.querySelectorAll('a[data-pane-toggle]').forEach(function (toggle) {
            toggle.parentElement.classList.toggle('active', toggle.getAttribute('href') === hash);
        });

        pane.classList.add('active');

        window.location = '#';

        if (history.pushState) {
            history.pushState(null, null, hash);
        }
    }
}

function toggleTaxStatesAndCities(hide) {
    document.querySelectorAll('.stateCityWrapper').forEach(function (wrapper) {
        wrapper.classList.toggle('hidden', !!hide);
    });
}

function updateTaxStates() {
    var countrySelect = document.getElementById('taxCountry');
    if (!countrySelect) {
        return;
    }

    var countryCode;

    if (countrySelect.multiple) {
        var selected = Array.prototype.map.call(countrySelect.selectedOptions, function (option) {
            return option.value;
        });

        if (selected.length > 1) {
            toggleTaxStatesAndCities(true);
            return;
        }
        countryCode = selected.length === 1 ? selected[0] : '';
    } else {
        countryCode = countrySelect.value;
    }

    toggleTaxStatesAndCities();

    var savedState = document.getElementById('savedTaxState');
    var settings = document.getElementById('settings-tax');
    var body = new URLSearchParams({
        country: countryCode,
        selectedState: savedState ? savedState.value : '',
        type: 'tax'
    });

    fetch(settings.getAttribute('data-states-utility'), {
        method: 'POST',
        credentials: 'same-origin',
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        body: body
    })
        .then(function (response) {
            return response.text();
        })
        .then(function (states) {
            var taxState = document.getElementById('taxState');
            if (taxState) {
                taxState.outerHTML = states;
            }
        });
}
