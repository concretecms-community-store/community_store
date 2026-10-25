function initAutocomplete() {
    var input = document.getElementById('store-checkout-billing-address-1');

    var autocompletebilling = new google.maps.places.Autocomplete(input);
    autocompletebilling.setFields(['address_components']);
    autocompletebilling.addListener('place_changed', function() {
        var place = autocompletebilling.getPlace();

        cs_completeAddress('billing', place);
    });

    var input = document.getElementById('store-checkout-shipping-address-1');

    if (input) {
        var autocompleteshipping = new google.maps.places.Autocomplete(input);
        autocompleteshipping.setFields(['address_components']);
        autocompleteshipping.addListener('place_changed', function () {
            var place = autocompleteshipping.getPlace();

            cs_completeAddress('shipping', place);
        });
    }
}

function cs_setField(id, value) {
    var field = document.getElementById(id);
    if (field) {
        field.value = value;
    }
}

function cs_completeAddress(type, place) {
    var pieces = [];
    var prefix = 'store-checkout-' + type + '-';

    for (var i = 0; i < place.address_components.length; i++) {
        pieces[place.address_components[i].types[0]] = place.address_components[i];
    }

    cs_setField(prefix + 'address-1', (pieces.street_number ? pieces.street_number.short_name + ' ' : '') + (pieces.route ? pieces.route.long_name : ''));

    if (pieces.locality) {
        cs_setField(prefix + 'city', pieces.locality.long_name);
    } else if (pieces.postal_town) {
        cs_setField(prefix + 'city', pieces.postal_town.long_name);
    }

    if (pieces.postal_code) {
        cs_setField(prefix + 'zip', pieces.postal_code.short_name);
    } else if (pieces.postal_code_prefix) {
        cs_setField(prefix + 'zip', pieces.postal_code_prefix.short_name);
    }

    if (pieces.country) {
        cs_setField(prefix + 'country', pieces.country.short_name);
    }

    communityStore.updateStates(type, true, function () {
        if (pieces.administrative_area_level_1) {
            cs_setField(prefix + 'state', pieces.administrative_area_level_1.short_name);
        }
    });
}
