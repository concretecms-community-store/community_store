$(function () {
    $('.store-product-block .store-product-options select, .store-product-block .store-product-options input').change(function () {

        let pdb = $(this).closest('.store-product-block');
        let pID = pdb.data('product-id');
        let ar = [];
        let priceAdjust = 0;
        let lang = CCM_ACTIVE_LOCALE.replace('_', '-');
        let numberFormatter = {};

        if (!CURRENCYCODE) {
            numberFormatter.format = function(amount) {
                return CURRENCYSYMBOL + communityStore.number_format(amount, 2, CURRENCYDECIMAL, CURRENCYGROUP);
            }
        } else {
            numberFormatter = Intl.NumberFormat(lang, { style: 'currency', currency: CURRENCYCODE })
        }

        pdb.find('.store-product-options select option:selected,  .store-product-options input:checked').each(function(){
            let optionAdjustment = $(this).data('adjustment');

            if (optionAdjustment) {
                priceAdjust += parseFloat($(this).data('adjustment'));
            }
        });

        pdb.find('.store-product-options select.store-product-variation, input.store-product-variation:checked').each(function () {
            ar.push($(this).val());
        });

        ar.sort(communityStore.sortNumber);

        let variation = variationData[pID][ar.join('_')];
        let priceHolder = pdb.find('.store-product-price');

        if (variation) {
            let total = parseFloat(variation['price']) + priceAdjust;
            let result = numberFormatter.format(total);

            if (variation['wholesalePrice']) {
                let wholesale = parseFloat(variation['wholesalePrice']) + priceAdjust;
                let wholesaleresult = numberFormatter.format(wholesale);

                priceHolder.html(variation['wholesaleTemplate']);
                priceHolder.find('.store-list-price').html(result);
                priceHolder.find('.store-wholesale-price').html(wholesaleresult);

            } else {
                if (variation['salePrice']) {
                    let saletotal = parseFloat(variation['salePrice']) + priceAdjust;
                    let saleresult = numberFormatter.format(saletotal);

                    priceHolder.html(variation['saleTemplate']);
                    priceHolder.find('.store-sale-price').html(saleresult);
                    priceHolder.find('.store-original-price').html(result);

                } else {
                    priceHolder.html(result);
                }
            }

            if (variation['available']) {
                pdb.find('.store-out-of-stock-label').addClass('hidden');
                pdb.find('.store-not-available-label').addClass('hidden');
                pdb.find('.store-btn-add-to-cart').removeClass('hidden');
            } else {
                if (variation['disabled']) {
                    pdb.find('.store-out-of-stock-label').addClass('hidden');
                    pdb.find('.store-not-available-label').removeClass('hidden');
                } else {
                    pdb.find('.store-out-of-stock-label').removeClass('hidden');
                    pdb.find('.store-not-available-label').addClass('hidden');
                }
                pdb.find('.store-btn-add-to-cart').addClass('hidden');
            }

            if (variation['imageThumb']) {
                let image = pdb.find('.store-product-primary-image img');

                if (image) {
                    image.attr('src', variation['imageThumb']);
                    let link = image.parent();
                    if (link) {
                        link.attr('href', variation['image'])
                    }
                }
            }

            if (variation['sku']) {
                let skuHolder = pdb.find('.store-product-sku');
                if (skuHolder) {
                    $('span',skuHolder).html(variation['sku']);
                }
            }

            if (variation['weight']) {
                let weightHolder = pdb.find('.store-product-weight-value');
                if (weightHolder) {
                    $(weightHolder).html(variation['weight']);
                }
            }

            var quantityField = pdb.find('.store-product-qty');
            if (variation['maxCart'] !== false) {
                quantityField.prop('max', variation['maxCart']);
                if (parseFloat(quantityField.val()) > parseFloat(variation['maxCart'])) {
                    quantityField.val(parseFloat(variation['maxCart']));
                }
            } else {
                quantityField.removeProp('max');
                if (parseFloat(quantityField.val()) === 0 ){
                    quantityField.val(1);
                }
            }

        } else {
            if (priceHolder.data('original-price')) {
                let saletotal = parseFloat(priceHolder.data('price')) + priceAdjust;
                let saleresult = numberFormatter.format(saletotal);

                let total = parseFloat(priceHolder.data('original-price')) + priceAdjust;
                let result = numberFormatter.format(total);

                priceHolder.find('.store-sale-price').html(saleresult);
                priceHolder.find('.store-original-price').html(result);

            } else if (priceHolder.data('list-price')) {
                let wholesale = parseFloat(priceHolder.data('price')) + priceAdjust;
                let wholesaleresult = numberFormatter.format(wholesale);

                let total = parseFloat(priceHolder.data('list-price')) + priceAdjust;
                let result = numberFormatter.format(total);

                priceHolder.find('.store-list-price').html(result);
                priceHolder.find('.store-wholesale-price').html(wholesaleresult);

            } else {
                let total = parseFloat(priceHolder.data('price')) + priceAdjust;
                let result = numberFormatter.format(total);
                priceHolder.html(result);
            }
        }
    });

});
