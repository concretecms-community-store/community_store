function initAutocomplete() {
    var input = document.getElementById('store-checkout-billing-address-1');

    var autocompletebilling = new google.maps.places.Autocomplete(input);
    autocompletebilling.setFields(['address_components']);
    autocompletebilling.addListener('place_changed', function() {
        var place = autocompletebilling.getPlace();
        // console.log(place.address_components);
        cs_completeAddress('billing', place);
    });

    var input = document.getElementById('store-checkout-shipping-address-1');

    if (input) {
        var autocompleteshipping = new google.maps.places.Autocomplete(input);
        autocompleteshipping.setFields(['address_components']);
        autocompleteshipping.addListener('place_changed', function () {
            var place = autocompleteshipping.getPlace();
            // console.log(place.address_components);
            cs_completeAddress('shipping', place);
        });
    }
}

function cs_completeAddress(type, place) {
    var pieces = [];

    for(var i = 0; i < place.address_components.length; i++) {
        pieces[place.address_components[i].types[0]] = place.address_components[i];
    }

    console.log(pieces);

    $('#store-checkout-'+ type + '-address-1').val(pieces.street_number['short_name'] + ' ' + pieces.route.long_name);

    if (pieces.locality) {
        $('#store-checkout-' + type + '-city').val(pieces.locality['long_name']);
    } else if (pieces.postal_town) {
        $('#store-checkout-' + type + '-city').val(pieces.postal_town['long_name']);
    }

    if (pieces.postal_code) {

        $('#store-checkout-' + type + '-zip').val(pieces.postal_code['short_name']);
    } else if(pieces.postal_code_prefix) {
        $('#store-checkout-' + type + '-zip').val(pieces.postal_code_prefix['short_name']);
    }

    $('#store-checkout-' + type + '-country').val(pieces.country['short_name']);

    if (type == 'billing') {
        communityStore.updateBillingStates(true, function () {
            $('#store-checkout-' + type + '-state').val(pieces.administrative_area_level_1['short_name']);
        });
    } else {
        communityStore.updateShippingStates(true, function () {
            $('#store-checkout-' + type + '-state').val(pieces.administrative_area_level_1['short_name']);
        });
    }

}