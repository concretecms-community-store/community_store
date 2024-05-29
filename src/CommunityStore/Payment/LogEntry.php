<?php

namespace Concrete\Package\CommunityStore\Src\CommunityStore\Payment;

use Concrete\Package\CommunityStore\Src\CommunityStore\Order\Order;
use DateTimeInterface;
use JsonSerializable;

defined('C5_EXECUTE') or die('Access Denied.');

/*readonly */ class LogEntry implements JsonSerializable
{
    public DateTimeInterface $dateTime;

    public string $paymentMethod;

    public string $type;

    public ?Order $order;

    /**
     * @var string|array|null
     *
     * @example 'Text to be displayed'
     *
     * @example [
     *     'Section 1',
     *     ['Header 1.1', 'Value 1.1'],
     *     ['Header 1.2', 'Value 1.2'],
     *     'Section 2',
     *     ['Header 2.1', 'Value 2.1'],
     *     ['Header 2.2', 'Value 2.2'],
     * ]
     */
    public $data;

    public string $error;

    /**
     * @param string|array|null $data
     */
    public function __construct(
        DateTimeInterface $dateTime,
        string $paymentMethod,
        string $type,
        ?Order $order,
        $data,
        string $error = ''
    )
    {
        $this->dateTime = $dateTime;
        $this->paymentMethod = $paymentMethod;
        $this->type = $type;
        $this->order = $order;
        $this->data = $data;
        $this->error = $error;
    }

    public function jsonSerialize (): array
    {
        return [
            'timestamp' => $this->dateTime->getTimestamp(),
            'paymentMethod' => $this->paymentMethod,
            'type' => $this->type,
            'orderID' => $this->order === null ? null : $this->order->getOrderID(),
            'data' => $this->data,
            'error' => $this->error,
        ];
    }
}
