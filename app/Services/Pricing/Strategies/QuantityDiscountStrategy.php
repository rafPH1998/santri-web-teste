<?php

declare(strict_types=1);

namespace App\Services\Pricing\Strategies;

use App\DTOs\PriceCalculationDTO;

/**
 * Desconto progressivo por quantidade.
 * 1-9 unidades: 0%
 * 10-49 unidades: 3%
 * 50+ unidades: 5%
 */
class QuantityDiscountStrategy implements DiscountStrategyInterface
{
    // Dá pra deixar isso configurável via construtor se precisar no futuro
    private array $tiers = [
        ['min' => 50, 'discount' => 5.0],
        ['min' => 10, 'discount' => 3.0],
        ['min' => 1,  'discount' => 0.0],
    ];

    public function calculate(PriceCalculationDTO $dto): float
    {
        foreach ($this->tiers as $tier) {
            if ($dto->quantity >= $tier['min']) {
                return $tier['discount'];
            }
        }

        return 0.0;
    }

    public function getName(): string
    {
        return 'quantity_discount';
    }
}
