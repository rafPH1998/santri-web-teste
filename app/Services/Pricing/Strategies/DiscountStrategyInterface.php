<?php

declare(strict_types=1);

namespace App\Services\Pricing\Strategies;

use App\DTOs\PriceCalculationDTO;

interface DiscountStrategyInterface
{
    /**
     * Calcula o desconto e retorna o percentual (ex: 3.0 = 3%)
     */
    public function calculate(PriceCalculationDTO $dto): float;

    public function getName(): string;
}
