document.addEventListener('change', function (e) {
    var field = e.target;

    if (field.matches && field.matches('.store-product-list-block .store-product-options select, .store-product-list-block .store-product-options input')) {
        communityStore.updateProductVariation(field.closest('.store-product-list-item').querySelector('form'), {
            imageSelector: '.store-product-list-thumbnail img',
            wholesaleTemplate: false
        });
    }
});
