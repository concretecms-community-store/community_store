(function () {
    function initPriceRange(range) {
        var hidden = range.querySelector('input[name="price"]');
        var from = range.querySelector('.store-price-range-from');
        var to = range.querySelector('.store-price-range-to');
        var fromLabel = range.querySelector('.store-price-range-from-label');
        var toLabel = range.querySelector('.store-price-range-to-label');
        var prefix = range.getAttribute('data-prefix') || '';

        function update(changed) {
            var fromValue = parseFloat(from.value);
            var toValue = parseFloat(to.value);

            // don't allow the handles to cross over
            if (fromValue > toValue) {
                if (changed === from) {
                    from.value = toValue;
                    fromValue = toValue;
                } else {
                    to.value = fromValue;
                    toValue = fromValue;
                }
            }

            // keep the top handle reachable when it's on top of the bottom one
            from.style.zIndex = fromValue >= parseFloat(from.max) ? 3 : '';

            hidden.value = fromValue + '-' + toValue;
            fromLabel.textContent = prefix + fromValue;
            toLabel.textContent = prefix + toValue;
        }

        [from, to].forEach(function (slider) {
            slider.addEventListener('input', function () {
                update(slider);
            });

            if (range.getAttribute('data-auto-update')) {
                slider.addEventListener('change', function () {
                    communityStore.submitProductFilter(hidden);
                });
            }
        });

        update();
    }

    function init() {
        document.querySelectorAll('.store-product-filter-block .store-price-range').forEach(initPriceRange);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
