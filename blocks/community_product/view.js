document.addEventListener('change', function (e) {
    var field = e.target;

    if (field.matches && field.matches('.store-product-block .store-product-options select, .store-product-block .store-product-options input')) {
        communityStore.updateProductVariation(field.closest('.store-product-block'), {
            imageSelector: '.store-product-primary-image img',
            wholesaleTemplate: true
        });
    }
});
