<?php

declare(strict_types=1);

namespace App\Services\Pricing\Modifiers;

use App\DTOs\PriceCalculationDTO;

/**
 * Aplica sobretaxa de frete para produtos pesados.
 * Produtos acima de 50kg têm acréscimo de R$ 15,00.
 */
class FreightModifier
{
    private const HEAVY_THRESHOLD_KG = 50.0;
    private const HEAVY_SURCHARGE    = 15.0;

    public function getSurcharge(PriceCalculationDTO $dto): float
    {
        if ($dto->weightKg > self::HEAVY_THRESHOLD_KG) {
            return self::HEAVY_SURCHARGE;
        }

        return 0.0;
    }
}
