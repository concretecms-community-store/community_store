<?php

return [
    'hideSize' => false,
    'hideWeight' => false,
    'hideBarcode' => false,
    // Set to true to handle automatically pQty/pQtyUnlim for products with variations
    'autoQuantityIfVariations' => false,
    'images' => [
        'autoUpdate' => [
            'title' => 'keep',
        ],
    ],
    'pages' => [
        'metadata' => [
            'updateDescription' => true,
            'updateOpenGraph' >= false,
        ],
    ],
];
