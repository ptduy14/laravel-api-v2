<?php

namespace App\Enums;

enum OrderStatus: int
{
    case PENDING = 0;
    case PROCESSING = 1;
    case COMPLETED = 2;
    case CANCELED = 3;

    /**
     * Get the description for the given order status.
     *
     * @param int $orderStatus
     * @return string
     */

    public static function getOrderStatusDesc(int $orderStatus): string
    {
        return match($orderStatus) {
            self::PENDING->value => 'Pending',
            self::PROCESSING->value => 'Processing',
            self::COMPLETED->value => 'Completed',
            self::CANCELED->value => 'Canceled',
            default => 'Unknown Status',
        };
    }
}
