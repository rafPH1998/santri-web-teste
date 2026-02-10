<?php

declare(strict_types=1);

namespace App\DTOs;

class PriceCalculationResult
{
    public function __construct(
        public readonly float $basePrice,
        public readonly float $priceWithMargin,
        public readonly float $quantityDiscount,
        public readonly float $customerDiscount,
        public readonly float $premiumDiscount,
        public readonly float $freightSurcharge,
        public readonly float $taxAmount,
        public readonly float $taxRate,
        public readonly float $unitPrice,
        public readonly float $totalPrice,
        public readonly int $quantity,
        public readonly array $breakdown = [],
    ) {}

    public function toArray(): array
    {
        return [
            'base_price'        => round($this->basePrice, 2),
            'price_with_margin' => round($this->priceWithMargin, 2),
            'discounts' => [
                'quantity'  => round($this->quantityDiscount, 2),
                'customer'  => round($this->customerDiscount, 2),
                'premium'   => round($this->premiumDiscount, 2),
            ],
            'freight_surcharge' => round($this->freightSurcharge, 2),
            'tax' => [
                'rate'   => $this->taxRate,
                'amount' => round($this->taxAmount, 2),
            ],
            'unit_price'  => round($this->unitPrice, 2),
            'quantity'    => $this->quantity,
            'total_price' => round($this->totalPrice, 2),
            'breakdown'   => $this->breakdown,
        ];
    }
}
