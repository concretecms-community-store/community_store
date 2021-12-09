<?php defined('C5_EXECUTE') or die("Access Denied."); ?>
<?php
    $currency = Config::get('community_store.currency');

    if (!$currency) {
        $currency = 'USD';
    }

    $app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
    $site = $app->make('site')->getSite();

    $orderDetails = [
        'transaction_id' => $order->getOrderID(),
        'affiliation' => $site->getSiteName(),
        'value' => $order->getTotal(),
        'currency' => $currency,
        'tax' => $order->getTaxTotal(),
        'shipping' => $order->getShippingTotal(),
        'items' => []
    ];

    foreach ($orderItems as $item) {
        $itemArray = [
            'name' => $item->getProductName(),
            'quantity' => $item->getQuantity(),
            'price' => number_format($item->getPricePaid(), 2, '.', '')
        ];

        if ($item->getSKU()) {
            $itemArray['id'] = $item->getSKU();
        }

        if ($product = $item->getProductObject()) {
            if ($product->getManufacturer()) {
                $itemArray['item_brand'] = $product->getManufacturer()->getName();
            }

            if ($pages = $product->getLocationPages()) {
                if ($pages[0]) {
                    $itemArray['item_category'] = \Concrete\Core\Page\Page::getByID($pages[0]->getCollectionID())->getCollectionName();
                }
            }

            $variant = '';
            $options = $item->getProductOptions();
            if ($options) {

                foreach ($options as $option) {
                    if ($option['oioValue']) {
                        $variant .= $option['oioKey'] . ' - ' . $option['oioValue'];
                    }
                }
            }

            if ($variant) {
                $itemArray['item_variant'] = $variant;
            }

        }


        $orderDetails['items'][] = $itemArray;
    }
    ?>

<script>
gtag('event', 'purchase', <?= json_encode($orderDetails); ?> );
</script>
