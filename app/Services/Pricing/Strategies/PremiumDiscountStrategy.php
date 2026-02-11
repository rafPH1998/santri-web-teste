<?php

declare(strict_types=1);

namespace App\Services\Pricing\Strategies;

use App\DTOs\PriceCalculationDTO;

/**
 * Desconto adicional de 2% para clientes marcados como Premium.
 */
class PremiumDiscountStrategy implements DiscountStrategyInterface
{
    private const PREMIUM_DISCOUNT = 2.0;

    public function calculate(PriceCalculationDTO $dto): float
    {
        return $dto->isPremium ? self::PREMIUM_DISCOUNT : 0.0;
    }

    public function getName(): string
    {
        return 'premium_discount';
    }
}
